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
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
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
                            $isSpecialSubject = in_array($subject->name, [
                                'Giáo dục quốc phòng và an ninh',
                                'Giáo dục thể chất',
                                'Nghệ thuật'
                            ]);

                            if ($isSpecialSubject) {
                                // For special subjects, just copy semester 2 result
                                $semester2Result = $semester2Avgs[$student->id][$subject->id][0]->text_value ?? null;

                                $grades[$student->id][$subject->id] = [
                                    'semester1_text' => $semester1Avgs[$student->id][$subject->id][0]->text_value ?? '-',
                                    'semester2_text' => $semester2Result ?? '-',
                                    'is_special' => true,
                                    'yearly_result' => $semester2Result ?? 'Chưa đạt' // Default to "Chưa đạt" if no result
                                ];
                            } else {
                                // For regular subjects, calculate averages as before
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
                            $isSpecialSubject = in_array($subject->name, [
                                'Giáo dục quốc phòng và an ninh',
                                'Giáo dục thể chất',
                                'Nghệ thuật'
                            ]);

                            if ($isSpecialSubject) {
                                $specialResult = $this->calculateSpecialSubjectResult($subjectGrades);

                                // Xử lý môn đặc biệt
                                $grades[$student->id][$subject->id] = [
                                    'fifteen_minutes' => [
                                        $subjectGrades['fifteen_minutes'][0] ?? null,
                                        $subjectGrades['fifteen_minutes'][1] ?? null,
                                        $subjectGrades['fifteen_minutes'][2] ?? null,
                                    ],
                                    'one_period' => $subjectGrades['one_period'][0] ?? null,
                                    'semester' => $subjectGrades['semester'][0] ?? null,
                                    'is_special' => true, // Thêm flag đánh dấu môn đặc biệt
                                    'display_average' => $specialResult['result'] // Gán kết quả "Đạt"/"Chưa đạt" để hiển thị


                                ];
                            } else {
                                // Xử lý môn thường
                                $grades[$student->id][$subject->id] = [
                                    'fifteen_minutes' => [
                                        $subjectGrades['fifteen_minutes'][0] ?? null,
                                        $subjectGrades['fifteen_minutes'][1] ?? null,
                                        $subjectGrades['fifteen_minutes'][2] ?? null,
                                    ],
                                    'one_period' => $subjectGrades['one_period'][0] ?? null,
                                    'semester' => $subjectGrades['semester'][0] ?? null,
                                    'is_special' => false
                                ];

                                // Calculate average
                                $subjectAverage = $this->calculateSubjectAverage($grades[$student->id][$subject->id]);

                                if ($subjectAverage > 0) {
                                    $totalScore += $subjectAverage;
                                    $subjectCount++;
                                }

                                $grades[$student->id][$subject->id]['average'] = round($subjectAverage, 1);
                                $grades[$student->id][$subject->id]['display_average'] = round($subjectAverage, 1); // Gán điểm số để hiển thị

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
        // Kiểm tra nếu là môn đặc biệt và điểm cuối kỳ là "Chưa đạt"
        // Nếu là môn đặc biệt
        if ($subjectGrades['is_special'] ?? false) {
            $semesterValue = $subjectGrades['semester']->text_value ?? null;
            // Trả về text value nếu là môn đặc biệt
            return $semesterValue === 'Đạt' ? 'Đạt' : 'Chưa đạt';
        }
        // Hàm helper để lấy giá trị điểm
        $getScore = function ($item) {
            if (is_object($item) && isset($item->score)) {
                return $item->score;
            }
            return is_numeric($item) ? $item : null;
        };

//        Log::info("Dữ liệu:" . json_encode($subjectGrades));
        // Lọc và lấy điểm 15 phút hợp lệ
        $fifteenMinutes = array_filter($subjectGrades['fifteen_minutes'] ?? [], function ($item) use ($getScore) {
            $score = $getScore($item);
            return !is_null($score) && $score >= 0;
        });

// Lấy điểm 1 tiết
        $onePeriodValue = isset($subjectGrades['one_period']) ? $getScore($subjectGrades['one_period']) : null;
        $onePeriod = !is_null($onePeriodValue) && $onePeriodValue >= 0 ? [$onePeriodValue] : [];

// Lấy điểm cuối kỳ
        $semesterValue = isset($subjectGrades['semester']) ? $getScore($subjectGrades['semester']) : null;
        $semester = !is_null($semesterValue) && $semesterValue >= 0 ? [$semesterValue] : [];

// Tính toán tổng điểm có trọng số
        $total = 0;
        $weights = 0;

// Điểm 15 phút (tối đa 3 điểm, hệ số 1)
        $count = 0;
        foreach ($fifteenMinutes as $item) {
            if ($count >= 3) break;
            $score = $getScore($item);
            $total += $score * 1;
            $weights += 1;
            $count++;
        }

        if (!empty($onePeriod)) {
            $total += $onePeriod[0] * 2;
            $weights += 2;
        }

// Điểm cuối kỳ (hệ số 3)
        if (!empty($semester)) {
            $total += $semester[0] * 3;
            $weights += 3;
        }
        return $weights > 0 ? round($total / $weights, 1) : 0;
    }

    private
    function calculateSpecialSubjectResult($subjectGrades)
    {
        // Đếm số bài 15 phút đạt (>=5 điểm hoặc "Đạt")
        $passedFifteenMinutes = 0;
        foreach ($subjectGrades['fifteen_minutes'] ?? [] as $grade) {
            if ($grade && ($grade->text_value === 'Đạt' || $grade->score >= 5)) {
                $passedFifteenMinutes++;
            }
        }

        // Kiểm tra bài 1 tiết
        $onePeriodPassed = false;
        if (isset($subjectGrades['one_period'][0])) {
            $onePeriodGrade = $subjectGrades['one_period'][0];
            $onePeriodPassed = ($onePeriodGrade->text_value === 'Đạt' || $onePeriodGrade->score >= 5);
        }

        // Kiểm tra điều kiện thi cuối kỳ
        $eligibleForFinal = ($passedFifteenMinutes >= 2) && $onePeriodPassed;

        if (!$eligibleForFinal) {
            return [
                'result' => 'Chưa đạt',
                'reason' => 'Không đủ điều kiện thi cuối kỳ'
            ];
        }

//        // Kiểm tra điểm cuối kỳ (nếu có)
//        if (isset($subjectGrades['semester'][0])) {
//            $finalGrade = $subjectGrades['semester'][0];
//            $finalPassed = ($finalGrade->text_value === 'Đạt' || $finalGrade->score >= 5);
//
//            return [
//                'result' => $finalPassed ? 'Đạt' : 'Chưa đạt',
//                'reason' => $finalPassed ? '' : 'Không đạt điểm cuối kỳ'
//            ];
//        }
        if (isset($subjectGrades['semester'][0])) {
            $finalGrade = $subjectGrades['semester'][0];
            $finalPassed = ($finalGrade->text_value === 'Đạt' || $finalGrade->score >= 5);

            return [
                'result' => $finalPassed ? 'Đạt' : 'Chưa đạt',
                'reason' => $finalPassed ? '' : 'Không đạt điểm cuối kỳ'
            ];
        }

        return [
            'result' => 'Chưa đạt',
            'reason' => 'Chưa có điểm cuối kỳ'
        ];
    }

    public
    function exportTemplate(Request $request)
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
            'force_update' => 'sometimes|boolean'
        ]);

        $teacherId = Auth::id();
        $schoolId = Auth::user()->school_id;

        try {
            $subject = Subject::where('name', $request->subject_name)
                ->where('school_id', $schoolId)
                ->firstOrFail();

            $forceUpdate = $request->boolean('force_update');

            // Tạo instance duy nhất
            $import = new GradesImport(
                $request->class_id,
                $request->semester_id,
                $request->academic_year_id,
                $teacherId,
                $schoolId,
                $subject,
                $forceUpdate
            );

            $assignment = TeacherAssignment::where('teacher_id', $teacherId)
                ->where('class_id', $request->class_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('subject_id', $subject->id)
                ->where('school_id', $schoolId)
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không được phân công dạy môn này trong lớp này'
                ], 403);
            }

            $file = $request->file('grades_file');

            // Sử dụng WithMultipleSheets để kiểm soát sheet nào được import
            $import = new class(
                $request->class_id,
                $request->semester_id,
                $request->academic_year_id,
                Auth::id(),
                Auth::user()->school_id,
                $subject,
                $forceUpdate

            ) extends GradesImport implements WithMultipleSheets {

                public function sheets(): array
                {
                    return [
                        1 => $this, // Chỉ import sheet thứ 2 (sheet môn học)
                    ];
                }
            };

            Excel::import($import, $file);
            $importedCount = count($import->getImportedStudentIds());
            $message = "Import thành công. Đã cập nhật điểm cho {$importedCount} học sinh.";
            return response()->json(['success' => true, 'message' => 'Import thành công']);

        } catch (\Exception $e) {
            $errorMessage = str_contains($e->getMessage(), 'HƯỚNG DẪN')
                ? "BẠN ĐANG CHỌN NHẦM SHEET HƯỚNG DẪN. VUI LÒNG:<br>1. Mở file Excel<br>2. Chọn sheet có tên môn học ở dưới cùng<br>3. Thực hiện import lại"
                : $e->getMessage();

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        }
    }

    public
    function viewAllGrades(Request $request, $studentId)
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


    public
    function getSemestersByYear(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $schoolId = Auth::user()->school_id;

        $semesters = Semester::where('academic_year_id', $academicYearId)
            ->where('school_id', $schoolId)
            ->get();

        return response()->json($semesters);
    }

    public
    function getClassesByYear(Request $request)
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

        // Lấy tất cả lớp chủ nhiệm của giáo viên, sắp xếp theo năm học mới nhất
        $homeroomClasses = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('is_homeroom', true)
            ->where('school_id', $schoolId)
            ->with(['class.gradeLevel', 'academicYear'])
            ->orderBy('academic_year_id', 'desc')
            ->get();

        if ($homeroomClasses->isEmpty()) {
            return view('grades.homeroom', [
                'error' => 'Bạn không phải giáo viên chủ nhiệm của lớp nào'
            ]);
        }

        // Xác định năm học được chọn
        $selectedAcademicYearId = $request->input('academic_year_id', $homeroomClasses->first()->academic_year_id);

        // Lọc ra các lớp thuộc năm học được chọn
        $classesInSelectedYear = $homeroomClasses->where('academic_year_id', $selectedAcademicYearId);

        // Xác định lớp được chọn
        $selectedClassId = $request->input('class_id');
        if (!$selectedClassId || !$classesInSelectedYear->contains('class_id', $selectedClassId)) {
            $selectedClassId = $classesInSelectedYear->first()->class_id ?? null;
        }

        // Khởi tạo các biến dữ liệu
        $class = null;
        $students = collect();
        $subjects = collect();
        $studentResults = [];

        if ($selectedClassId) {
            $class = ClassModel::with('gradeLevel')->find($selectedClassId);

            // Lấy danh sách học sinh
            $students = User::whereHas('studentClasses', function($query) use ($selectedClassId, $selectedAcademicYearId) {
                $query->where('student_classes.class_id', $selectedClassId)
                    ->where('student_classes.academic_year_id', $selectedAcademicYearId);
            })->orderBy('full_name')->get();

            // Lấy tất cả môn học
            $subjects = Subject::where('school_id', $schoolId)->get();

            // Lấy dữ liệu điểm nếu có học sinh
            if ($students->isNotEmpty()) {
                $finalGrades = Grade::where('class_id', $selectedClassId)
                    ->where('academic_year_id', $selectedAcademicYearId)
                    ->whereIn('semester_id', [1, 2])
                    ->where('test_type', 'final')
                    ->get()
                    ->groupBy(['student_id', 'semester_id', 'subject_id']);

                $specialSubjects = ['Giáo dục quốc phòng và an ninh', 'Giáo dục thể chất', 'Nghệ thuật'];

                foreach ($students as $student) {
                    $result = [
                        'semester1' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => 'Chưa đủ điểm'],
                        'semester2' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => 'Chưa đủ điểm'],
                        'yearly' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => 'Chưa đủ điểm']
                    ];

                    foreach ($subjects as $subject) {
                        $isSpecial = in_array($subject->name, $specialSubjects);

                        // Xử lý điểm HK1
                        $semester1Grade = $finalGrades[$student->id][1][$subject->id][0] ?? null;
                        $semester1Score = $semester1Grade ? ($semester1Grade->score ?? 0) : 0;
                        $semester1Text = $semester1Grade ? ($semester1Grade->text_value ?? null) : null;
                        $result['semester1']['subjects'][$subject->id] = $semester1Score;
                        $result['semester1']['display_subjects'][$subject->id] = $this->formatGradeDisplay($semester1Score, $semester1Text, $isSpecial);

                        // Xử lý điểm HK2
                        $semester2Grade = $finalGrades[$student->id][2][$subject->id][0] ?? null;
                        $semester2Score = $semester2Grade ? ($semester2Grade->score ?? 0) : 0;
                        $semester2Text = $semester2Grade ? ($semester2Grade->text_value ?? null) : null;
                        $result['semester2']['subjects'][$subject->id] = $semester2Score;
                        $result['semester2']['display_subjects'][$subject->id] = $this->formatGradeDisplay($semester2Score, $semester2Text, $isSpecial);

                        // Tính điểm cả năm
                        if ($semester1Grade && $semester2Grade) {
                            $yearlyScore = $isSpecial ? $semester2Score : round(($semester1Score + $semester2Score * 2) / 3, 1);
                            $result['yearly']['subjects'][$subject->id] = $yearlyScore;
                            $result['yearly']['display_subjects'][$subject->id] = $isSpecial
                                ? $this->formatGradeDisplay($semester2Score, $semester2Text, $isSpecial)
                                : $yearlyScore;
                        } else {
                            $result['yearly']['subjects'][$subject->id] = 0;
                            $result['yearly']['display_subjects'][$subject->id] = '-';
                        }
                    }

                    // Tính điểm trung bình
                    $this->calculateAverages($result);
                    $studentResults[$student->id] = $result;
                }
            }
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

    private function formatGradeDisplay($score, $textValue, $isSpecial)
    {
        // Nếu không có dữ liệu (score = 0 và textValue = null)
        if ($score == 0 && $textValue === null) {
            return '-';
        }

        // Nếu là môn đặc biệt và có dữ liệu
        if ($isSpecial) {
            return $textValue ?: ($score >= 5 ? 'Đạt' : 'Chưa đạt');
        }

        // Môn thường có dữ liệu
        return $score > 0 ? $score : '-';
    }

    private function calculateAverages(&$result)
    {
        foreach (['semester1', 'semester2', 'yearly'] as $semester) {
            $validScores = array_filter($result[$semester]['subjects'], function($score) {
                return $score > 0;
            });

            if (!empty($validScores)) {
                $average = round(array_sum($validScores) / count($validScores), 1);
                $result[$semester]['average'] = $average;
                $result[$semester]['classification'] = $this->classifyStudent($average, $result[$semester]['subjects']);
            }
        }
    }

// Hàm xếp loại học lực theo quy định
    private function classifyStudent($averageScore, $subjectScores)
    {
        if (!is_numeric($averageScore)) {
            return 'Chưa xếp loại';
        }
        $specialSubjects = [
            'Giáo dục quốc phòng và an ninh',
            'Giáo dục thể chất',
            'Nghệ thuật'];

        // Đếm số môn có điểm TB dưới 6.5 và dưới 5.0 (không tính các môn đặc biệt)
        $under6_5 = 0;
        $under5_0 = 0;

        foreach ($subjectScores as $subjectName => $score) {
            $isSpecial = false;
            foreach ($specialSubjects as $special) {
                if (stripos($subjectName, $special) !== false) {
                    $isSpecial = true;
                    break;
                }
            }

            if (!$isSpecial && is_numeric($score)) {
                if ($score < 6.5) $under6_5++;
                if ($score < 5.0) $under5_0++;
            }
        }

        // Xếp loại theo quy định mới
        if ($averageScore >= 9.0 && $under6_5 == 0) {
            return 'Xuất sắc';
        }
        elseif ($averageScore >= 8.0) {
            return ($under6_5 == 0) ? 'Giỏi' : 'Khá';
        }
        elseif ($averageScore >= 6.5) {
            // Điều kiện xếp loại Khá:
            // - Không có môn nào dưới 5.0
            return ($under5_0 == 0) ? 'Khá' : 'Đạt';
        }
        elseif ($averageScore >= 5.0) {
            $hasUnder3_5 = false;
            foreach ($subjectScores as $subjectName => $score) {
                $isSpecial = false;
                foreach ($specialSubjects as $special) {
                    if (stripos($subjectName, $special) !== false) {
                        $isSpecial = true;
                        break;
                    }
                }

                if (!$isSpecial && is_numeric($score) && $score < 3.5) {
                    $hasUnder3_5 = true;
                    break;
                }
            }

            return !$hasUnder3_5 ? 'Đạt' : 'Chưa đạt';
        }
        else {
            return 'Chưa đạt';
        }
    }
}
