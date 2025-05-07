<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GradeTemplateExport;
use App\Imports\GradesImport;

class GradeController extends Controller
{
    // Hiển thị màn hình quản lý điểm
    public function index(Request $request)
    {
        $teacherId = Auth::id();
        $schoolId = Auth::user()->school_id;

        // Lấy tất cả năm học
        $academicYears = AcademicYear::where('school_id', $schoolId)
            ->orderBy('start_date', 'desc')
            ->get();

        // Lấy năm học được chọn (mặc định là năm học hiện tại nếu có)
        $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        $selectedAcademicYearId = $request->input('academic_year_id', $currentAcademicYear->id ?? $academicYears->first()->id ?? null);

        // Lấy các học kỳ thuộc năm học được chọn
        $semesters = Semester::where('academic_year_id', $selectedAcademicYearId)
            ->where('school_id', $schoolId)
            ->get();

        // Lấy các lớp được phân công dạy trong năm học được chọn
        $assignedClasses = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('academic_year_id', $selectedAcademicYearId)
            ->where('school_id', $schoolId)
            ->with(['class' => function($query) {
                $query->with(['gradeLevel', 'academicYear']);
            }, 'subject'])
            ->get()
            ->groupBy('class_id');

        // Lấy lớp và học kỳ được chọn
        $selectedClassId = $request->input('class_id');
        $selectedSemesterId = $request->input('semester_id');

        // Lấy danh sách học sinh và điểm nếu đã chọn lớp
        $students = collect();
        $grades = collect();
        $subjectsTaught = collect();

        if ($selectedClassId) {
            $class = ClassModel::where('id', $selectedClassId)
                ->where('school_id', $schoolId)
                ->firstOrFail();

            // Lấy danh sách học sinh trong lớp
            $students = $class->students()
                ->wherePivot('academic_year_id', $selectedAcademicYearId)
                ->orderBy('full_name')
                ->get();

            // Lấy các môn giáo viên được phân công dạy trong lớp này
            $subjectsTaught = TeacherAssignment::where('teacher_id', $teacherId)
                ->where('class_id', $selectedClassId)
                ->where('academic_year_id', $selectedAcademicYearId)
                ->where('school_id', $schoolId)
                ->with('subject')
                ->get()
                ->pluck('subject');

            // Lấy điểm nếu đã chọn học kỳ
            if ($selectedSemesterId) {
                $grades = Grade::whereIn('student_id', $students->pluck('id'))
                    ->where('class_id', $selectedClassId)
                    ->where('academic_year_id', $selectedAcademicYearId)
                    ->where('semester_id', $selectedSemesterId)
                    ->where('school_id', $schoolId)
                    ->get()
                    ->groupBy(['student_id', 'subject_id', 'test_type']);
            }
        }

        return view('grades.index', compact(
            'assignedClasses',
            'students',
            'grades',
            'subjectsTaught',
            'academicYears',
            'semesters',
            'selectedClassId',
            'selectedSemesterId',
            'selectedAcademicYearId'
        ));
    }

    public function exportTemplate(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id,school_id,'.auth()->user()->school_id,
            'semester_id' => 'required|exists:semesters,id,school_id,'.auth()->user()->school_id,
            'academic_year_id' => 'required|exists:academic_years,id,school_id,'.auth()->user()->school_id,
        ]);

        $teacherId = Auth::id();
        $schoolId = Auth::user()->school_id;

        // Kiểm tra giáo viên có được phân công lớp này không
        $assignment = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('class_id', $request->class_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        // Lấy danh sách học sinh trong lớp
        $class = ClassModel::where('id', $request->class_id)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $students = $class->students()
            ->wherePivot('academic_year_id', $request->academic_year_id)
            ->orderBy('full_name')
            ->get();

        // Lấy các môn giáo viên được phân công dạy trong lớp này
        $subjects = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('class_id', $request->class_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('school_id', $schoolId)
            ->with('subject')
            ->get()
            ->pluck('subject');

        $fileName = 'Mau_nhap_diem_' . $class->school_auto_id . '_HK' . $request->semester_id . '.xlsx';

        return Excel::download(new GradeTemplateExport(
            $students,
            $subjects,
            $request->semester_id,
            $class,
            $request->academic_year_id
        ), $fileName);
    }

    public function import(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id,school_id,'.auth()->user()->school_id,
            'semester_id' => 'required|exists:semesters,id,school_id,'.auth()->user()->school_id,
            'academic_year_id' => 'required|exists:academic_years,id,school_id,'.auth()->user()->school_id,
            'grades_file' => 'required|file|mimes:xlsx,xls,csv',
            'subject_name' => 'required|string',
            'class_name' => 'required|string' // Thêm validation cho tên lớp
        ]);

        $teacherId = Auth::id();
        $schoolId = Auth::user()->school_id;

        try {
            // Kiểm tra giáo viên có được phân công lớp này không
            $assignment = TeacherAssignment::where('teacher_id', $teacherId)
                ->where('class_id', $request->class_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('school_id', $schoolId)
                ->firstOrFail();

            // Tìm môn học theo tên
            $subject = Subject::where('name', $request->subject_name)
                ->where('school_id', $schoolId)
                ->firstOrFail();

            $import = new GradesImport(
                $request->class_id,
                $request->semester_id,
                $request->academic_year_id,
                $teacherId,
                $schoolId,
                $subject,
                $request->class_name // Truyền tên lớp để kiểm tra
            );

            Excel::import($import, $request->file('grades_file'));

            $this->calculateAverages($import->getImportedStudentIds(),
                $request->class_id,
                $request->semester_id,
                $request->academic_year_id);

            return response()->json(['success' => true, 'message' => 'Nhập điểm thành công!']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi nhập điểm: ' . $e->getMessage()
            ], 500);
        }
    }
    protected function calculateAverages($studentIds, $classId, $semesterId, $academicYearId)
    {
        foreach ($studentIds as $studentId) {
            // Lấy tất cả điểm của học sinh
            $grades = Grade::where('student_id', $studentId)
                ->where('class_id', $classId)
                ->where('semester_id', $semesterId)
                ->where('academic_year_id', $academicYearId)
                ->get()
                ->groupBy('subject_id');

            foreach ($grades as $subjectId => $subjectGrades) {
                // Tính điểm trung bình môn
                $average = $this->calculateSubjectAverage($subjectGrades);

                // Lưu điểm trung bình môn
                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_id' => $classId,
                        'subject_id' => $subjectId,
                        'semester_id' => $semesterId,
                        'academic_year_id' => $academicYearId,
                        'test_type' => 'average',
                        'school_id' => Auth::user()->school_id
                    ],
                    ['score' => $average, 'teacher_id' => Auth::id()]
                );
            }

            // Tính điểm trung bình học kỳ (trung bình các môn)
            $semesterAverage = Grade::where('student_id', $studentId)
                ->where('class_id', $classId)
                ->where('semester_id', $semesterId)
                ->where('academic_year_id', $academicYearId)
                ->where('test_type', 'average')
                ->avg('score');

            if ($semesterAverage) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_id' => $classId,
                        'subject_id' => null, // Không thuộc môn nào cụ thể
                        'semester_id' => $semesterId,
                        'academic_year_id' => $academicYearId,
                        'test_type' => 'semester_average',
                        'school_id' => Auth::user()->school_id
                    ],
                    ['score' => $semesterAverage, 'teacher_id' => Auth::id()]
                );
            }
        }
    }

    protected function calculateSubjectAverage($grades)
    {
        $grouped = $grades->groupBy('test_type');

        $fifteenMinAvg = $grouped->get('fifteen_minutes')?->avg('score') ?? 0;
        $onePeriodAvg = $grouped->get('one_period')?->avg('score') ?? 0;
        $semesterScore = $grouped->get('semester')?->first()->score ?? 0;

        // Tính theo trọng số: 15p (20%), 1 tiết (30%), thi HK (50%)
        return ($fifteenMinAvg * 0.2) + ($onePeriodAvg * 0.3) + ($semesterScore * 0.5);
    }

    public function viewAllGrades(Request $request, $studentId)
    {
        $teacherId = Auth::id();
        $schoolId = Auth::user()->school_id;

        // Kiểm tra học sinh có thuộc lớp chủ nhiệm không
        $isHomeroomTeacher = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('is_homeroom', true)
            ->where('school_id', $schoolId)
            ->whereHas('class.students', function($query) use ($studentId) {
                $query->where('users.id', $studentId);
            })
            ->exists();

        if (!$isHomeroomTeacher) {
            abort(403, 'Bạn không phải giáo viên chủ nhiệm của học sinh này');
        }

        $student = User::where('id', $studentId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $classId = $request->input('class_id');
        $semesterId = $request->input('semester_id');
        $academicYearId = $request->input('academic_year_id');

        // Lấy tất cả điểm của học sinh
        $grades = Grade::where('student_id', $studentId)
            ->where('school_id', $schoolId)
            ->when($classId, function($query) use ($classId) {
                return $query->where('class_id', $classId);
            })
            ->when($semesterId, function($query) use ($semesterId) {
                return $query->where('semester_id', $semesterId);
            })
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->with(['subject', 'semester'])
            ->get()
            ->groupBy(['semester_id', 'subject_id', 'test_type']);

        // Tính điểm trung bình môn học kỳ
        $subjectAverages = [];
        foreach ($grades as $semesterId => $semesterGrades) {
            foreach ($semesterGrades as $subjectId => $subjectGrades) {
                $subjectAverages[$semesterId][$subjectId] = $this->calculateSubjectAverage($subjectGrades);
            }
        }

        return view('grades.student_grades', compact(
            'student',
            'grades',
            'subjectAverages'
        ));
    }



    public function getmestersByYear(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $schoolId = Auth::user()->school_id;

        $semesters = Semester::where('academic_year_id', $academicYearId)
            ->where('school_id', $schoolId)
            ->get();

        return response()->json($semesters);
    }

    public function getClassesByYear(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $teacherId = Auth::id();
        $schoolId = Auth::user()->school_id;

        if (!$academicYearId) {
            return response()->json([]);
        }

        $assignments = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->where('school_id', $schoolId)
            ->with(['class' => function($query) {
                $query->with('gradeLevel');
            }])
            ->get();

        // Sửa lại cách trả về dữ liệu
        $classes = $assignments->map(function($assignment) {
            return [
                'id' => $assignment->class->id,
                'name' => $assignment->class->name,
                'grade_level' => $assignment->class->gradeLevel
            ];// Giữ nguyên object
       })->unique('id')->values();

        return response()->json($classes);
    }

}
