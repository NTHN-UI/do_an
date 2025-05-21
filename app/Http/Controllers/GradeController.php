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
use Illuminate\Support\Facades\Log;
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

        $semesters = $semesters->push((object)[
            'id' => 0,
            'name' => 'Cả năm',
            'academic_year_id' => $selectedAcademicYearId
        ]);

        // Lấy các lớp được phân công dạy trong năm học được chọn
        $assignedClasses = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('academic_year_id', $selectedAcademicYearId)
            ->where('school_id', $schoolId)
            ->with(['class' => function ($query) {
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

        if ($selectedClassId && $selectedSemesterId !== null) {
            $class = ClassModel::where('id', $selectedClassId)
                ->where('school_id', $schoolId)
                ->firstOrFail();

            // Lấy danh sách học sinh
            $students = $class->students()
                ->wherePivot('academic_year_id', $selectedAcademicYearId)
                ->orderBy('full_name')
                ->get();

            // Lấy các môn giáo viên được phân công
            $subjectsTaught = TeacherAssignment::where('teacher_id', $teacherId)
                ->where('class_id', $selectedClassId)
                ->where('academic_year_id', $selectedAcademicYearId)
                ->where('school_id', $schoolId)
                ->with('subject') // Chỉ cần with('subject') thôi
                ->get()
                ->pluck('subject');
            // Khởi tạo mảng grades
            $grades = [];

            if ($selectedClassId && $selectedSemesterId !== null) {
                if ($selectedSemesterId == 0) {
                    // Lấy điểm TB học kỳ từ test_type = 'final'
                    $semester1Avgs = Grade::where('class_id', $selectedClassId)
                        ->where('semester_id', 1)
                        ->where('test_type', 'final')
                        ->where('academic_year_id', $selectedAcademicYearId)
                        ->get()
                        ->groupBy(['student_id', 'subject_id']);

                    $semester2Avgs = Grade::where('class_id', $selectedClassId)
                        ->where('semester_id', 2)
                        ->where('test_type', 'final')
                        ->where('academic_year_id', $selectedAcademicYearId)
                        ->get()
                        ->groupBy(['student_id', 'subject_id']);

                    foreach ($students as $student) {
                        $totalScore = 0;
                        $subjectCount = 0;

                        foreach ($subjectsTaught as $subject) {
                            // Lấy điểm TB đã lưu
                            $semester1Avg = $semester1Avgs[$student->id][$subject->id][0]->score ?? 0;
                            $semester2Avg = $semester2Avgs[$student->id][$subject->id][0]->score ?? 0;

                            $grades[$student->id][$subject->id]['semester1_avg'] = $semester1Avg;
                            $grades[$student->id][$subject->id]['semester2_avg'] = $semester2Avg;

                            $yearlyAverage = ($semester1Avg + $semester2Avg * 2) / 3;
                            $grades[$student->id][$subject->id]['average'] = round($yearlyAverage, 1);

                            if ($yearlyAverage > 0) {
                                $totalScore += $yearlyAverage;
                                $subjectCount++;
                            }
                        }

                        $grades[$student->id]['yearly_average'] = $subjectCount > 0
                            ? round($totalScore / $subjectCount, 1)
                            : 0;
                    }
                } else {
                    // For semester grades (not yearly)
                    $allGrades = Grade::where('class_id', $selectedClassId)
                        ->where('semester_id', $selectedSemesterId)
                        ->where('academic_year_id', $selectedAcademicYearId)
                        ->where('school_id', $schoolId)
                        ->get()
                        ->groupBy(['student_id', 'subject_id', 'test_type']);

                    foreach ($students as $student) {
                        $totalScore = 0;
                        $subjectCount = 0;

                        foreach ($subjectsTaught as $subject) {
                            $subjectGrades = $allGrades[$student->id][$subject->id] ?? [];

                            $grades[$student->id][$subject->id] = [
                                'fifteen_minutes' => [
                                    $subjectGrades['fifteen_minutes'][0] ?? null,
                                    $subjectGrades['fifteen_minutes'][1] ?? null,
                                    $subjectGrades['fifteen_minutes'][2] ?? null,
                                ],
                                'one_period' => $subjectGrades['one_period'][0] ?? null,
                                'semester' => $subjectGrades['semester'][0] ?? null,
                            ];

                            if (!$subject->is_text_based) {
                                // Calculate average
                                $subjectAverage = $this->calculateSubjectAverage($grades[$student->id][$subject->id]);

                                if ($subjectAverage > 0) {
                                    $totalScore += $subjectAverage;
                                    $subjectCount++;
                                }

                                $grades[$student->id][$subject->id]['average'] = round($subjectAverage, 1);
                            }
                        }

                        $grades[$student->id]['semester_average'] = $subjectCount > 0
                            ? round($totalScore / $subjectCount, 1)
                            : 0;
                    }
                }
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

    private function calculateSubjectAverage($subjectGrades)
    {
        $fifteenMinutes = array_filter($subjectGrades['fifteen_minutes'] ?? [], function ($score) {
            return !is_null($score) && $score >= 0;
        });

        $onePeriod = !is_null($subjectGrades['one_period'] ?? null) && $subjectGrades['one_period'] >= 0
            ? [$subjectGrades['one_period']]
            : [];

        $semester = !is_null($subjectGrades['semester'] ?? null) && $subjectGrades['semester'] >= 0
            ? [$subjectGrades['semester']]
            : [];

        // Tổng điểm có trọng số
        $total = 0;
        $weights = 0;

        // Điểm 15 phút: hệ số 1 (lấy tối đa 3 điểm)
        $count = 0;
        foreach ($fifteenMinutes as $score) {
            if ($count >= 3) break;
            $total += $score * 1;
            $weights += 1;
            $count++;
        }

        // Điểm 1 tiết: hệ số 2 (lấy 1 điểm)
        if (!empty($onePeriod)) {
            $total += $onePeriod[0] * 2;
            $weights += 2;
        }

        // Điểm cuối kỳ: hệ số 3 (lấy 1 điểm)
        if (!empty($semester)) {
            $total += $semester[0] * 3;
            $weights += 3;
        }

        return $weights > 0 ? round($total / $weights, 1) : 0;
    }

    public function exportTemplate(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id,school_id,' . auth()->user()->school_id,
            'semester_id' => 'required|exists:semesters,id,school_id,' . auth()->user()->school_id,
            'academic_year_id' => 'required|exists:academic_years,id,school_id,' . auth()->user()->school_id,
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

        $fileName = 'Mau_nhap_diem_' . $class->id . '_HK' . $request->semester_id . '.xlsx';

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
            'class_id' => 'required|exists:classes,id,school_id,' . auth()->user()->school_id,
            'semester_id' => 'required|exists:semesters,id,school_id,' . auth()->user()->school_id,
            'academic_year_id' => 'required|exists:academic_years,id,school_id,' . auth()->user()->school_id,
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
            );

            Excel::import($import, $request->file('grades_file'));

            return response()->json(['success' => true, 'message' => 'Nhập điểm thành công!']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi nhập điểm: ' . $e->getMessage()
            ], 500);
        }
    }

    public function viewAllGrades(Request $request, $studentId)
    {
        $teacherId = Auth::id();
        $schoolId = Auth::user()->school_id;

        // Kiểm tra học sinh có thuộc lớp chủ nhiệm không
        $isHomeroomTeacher = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('is_homeroom', true)
            ->where('school_id', $schoolId)
            ->whereHas('class.students', function ($query) use ($studentId) {
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
            ->when($classId, function ($query) use ($classId) {
                return $query->where('class_id', $classId);
            })
            ->when($semesterId, function ($query) use ($semesterId) {
                return $query->where('semester_id', $semesterId);
            })
            ->when($academicYearId, function ($query) use ($academicYearId) {
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


    public function getSemestersByYear(Request $request)
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
            ->with(['class' => function ($query) {
                $query->with('gradeLevel');
            }])
            ->get();

        // Sửa lại cách trả về dữ liệu
        $classes = $assignments->map(function ($assignment) {
            return [
                'id' => $assignment->class->id,
                'name' => $assignment->class->name,
                'grade_level' => $assignment->class->gradeLevel
            ];// Giữ nguyên object
        })->unique('id')->values();

        return response()->json($classes);
    }

// Thêm vào GradeController.php

    public function homeroomGrades(Request $request)
    {
        $teacherId = Auth::id();
        $schoolId = Auth::user()->school_id;

        // Kiểm tra giáo viên có là chủ nhiệm lớp nào không
        $homeroomClasses = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('is_homeroom', true)
            ->where('school_id', $schoolId)
            ->with(['class', 'academicYear'])
            ->get();

        if ($homeroomClasses->isEmpty()) {
            return redirect()->back()->with('error', 'Bạn không phải giáo viên chủ nhiệm của lớp nào');
        }

        // Lấy năm học được chọn
        $selectedAcademicYearId = $request->input('academic_year_id', $homeroomClasses->first()->academic_year_id);

        // Lấy lớp được chọn
        $selectedClassId = $request->input('class_id', $homeroomClasses->first()->class_id);

        // Lấy thông tin lớp học
        $class = ClassModel::with('gradeLevel')->findOrFail($selectedClassId);

        // Lấy danh sách học sinh trong lớp
        $students = User::whereHas('studentClasses', function ($query) use ($selectedClassId, $selectedAcademicYearId) {
            $query->where('student_classes.class_id', $selectedClassId)
                ->where('student_classes.academic_year_id', $selectedAcademicYearId);
        })->orderBy('full_name')->get();

        // Lấy tất cả môn học của lớp (không cần kiểm tra giáo viên phân công)
        $subjects = Subject::where('school_id', $schoolId)->get();

        // Lấy điểm đã tổng kết từ bảng grades (điểm cuối kỳ)
        $finalGrades = Grade::where('class_id', $selectedClassId)
            ->where('academic_year_id', $selectedAcademicYearId)
            ->whereIn('semester_id', [1, 2])
            ->where('test_type', 'final') // Thay 'semester' bằng 'final'
            ->get()
            ->groupBy(['student_id', 'semester_id', 'subject_id']);

        // Tính toán điểm trung bình và xếp loại
        $studentResults = [];

        foreach ($students as $student) {
            $result = [
                'semester1' => ['subjects' => [], 'average' => 0, 'classification' => ''],
                'semester2' => ['subjects' => [], 'average' => 0, 'classification' => ''],
                'yearly' => ['average' => 0, 'classification' => '']
            ];

            $semester1Total = 0;
            $semester1Count = 0;
            $semester2Total = 0;
            $semester2Count = 0;


            foreach ($subjects as $subject) {
                $semester1Avg = $finalGrades[$student->id][1][$subject->id][0]->score ?? 0;
                $semester2Avg = $finalGrades[$student->id][2][$subject->id][0]->score ?? 0;

                // Lưu điểm HK1, HK2
                $result['semester1']['subjects'][$subject->id] = $semester1Avg;
                $result['semester2']['subjects'][$subject->id] = $semester2Avg;

                // Tính điểm cả năm cho từng môn (CN) = (ĐTB HK1 + ĐTB HK2 * 2) / 3
                $yearlySubjectAvg = round(($semester1Avg + $semester2Avg * 2) / 3, 1);
                $result['yearly']['subjects'][$subject->id] = $yearlySubjectAvg;


                // Cập nhật tổng điểm và số môn cho HK1 (chỉ tính các môn có điểm > 0)
                if ($semester1Avg > 0) {
                    $semester1Total += $semester1Avg;
                    $semester1Count++;
                }

                // Cập nhật tổng điểm và số môn cho HK2 (chỉ tính các môn có điểm > 0)
                if ($semester2Avg > 0) {
                    $semester2Total += $semester2Avg;
                    $semester2Count++;
                }
            }

            // Tính điểm TB và xếp loại nếu có dữ liệu
            if ($semester1Count > 0) {
                $result['semester1']['average'] = round($semester1Total / $semester1Count, 1);
                $result['semester1']['classification'] = $this->classifyStudent($result['semester1']['average'], $result['semester1']['subjects']);
            }

            if ($semester2Count > 0) {
                $result['semester2']['average'] = round($semester2Total / $semester2Count, 1);
                $result['semester2']['classification'] = $this->classifyStudent($result['semester2']['average'], $result['semester2']['subjects']);
            }

            if ($semester1Count > 0 && $semester2Count > 0) {
                $yearAvg = round(($result['semester1']['average'] + $result['semester2']['average'] * 2) / 3, 1);
                $result['yearly']['average'] = $yearAvg;
                $result['yearly']['classification'] = $this->classifyStudent($yearAvg, $result['yearly']['subjects']);
            }

            $studentResults[$student->id] = $result;
        }

        return view('grades.homeroom', compact(
            'homeroomClasses',
            'selectedAcademicYearId',
            'selectedClassId',
            'class',
            'students',
            'subjects',
            'studentResults'
        ));
    }

// Hàm xếp loại học lực theo quy định
    private function classifyStudent($averageScore, $subjectScores)
    {
        if (!is_numeric($averageScore)) return 'Chưa đủ điểm';

        // Đếm số môn dưới 5.0 (không tính môn GDCD, Thể dục, Âm nhạc, Mỹ thuật)
        $failedSubjects = count(array_filter($subjectScores, function ($score, $subjectId) {
            $excludedSubjects = ['GDCD', 'THEDUC', 'AMNHAC', 'MYTHUAT']; // Các môn không tính
            return is_numeric($score) && $score < 5.0 && !in_array($subjectId, $excludedSubjects);
        }, ARRAY_FILTER_USE_BOTH));

        // Xếp loại theo quy định Bộ GD&ĐT
        switch (true) {
            case ($averageScore >= 8.0):
                return ($failedSubjects == 0) ? 'Giỏi' : 'Khá';

            case ($averageScore >= 6.5):
                return ($failedSubjects <= 1) ? 'Khá' : 'Trung bình';

            case ($averageScore >= 5.0):
                return ($failedSubjects <= 2) ? 'Trung bình' : 'Yếu';

            case ($averageScore >= 3.5):
                return ($failedSubjects <= 3) ? 'Yếu' : 'Kém';

            default:
                return 'Kém'; // Dưới 3.5
        }
    }
}
