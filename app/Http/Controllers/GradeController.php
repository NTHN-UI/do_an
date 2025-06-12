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
        ]);

        $teacherId = Auth::id();
        $schoolId = Auth::user()->school_id;

        try {
            // Tìm môn học theo tên
            $subject = Subject::where('name', $request->subject_name)
                ->where('school_id', $schoolId)
                ->firstOrFail();

            // Khởi tạo importer với đầy đủ tham số
            $import = new GradesImport(
                $request->class_id,
                $request->semester_id,
                $request->academic_year_id,
                $teacherId,
                $schoolId,
                $subject, // Truyền subject vào constructor
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
                $subject
            ) extends GradesImport implements WithMultipleSheets {

                public function sheets(): array
                {
                    return [
                        1 => $this, // Chỉ import sheet thứ 2 (sheet môn học)
                    ];
                }
            };

            Excel::import($import, $file);

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

    public
    function homeroomGrades(Request $request)
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
        $specialSubjectNames = [
            'Giáo dục quốc phòng và an ninh',
            'Giáo dục thể chất',
            'Nghệ thuật'
        ];

        foreach ($students as $student) {
            $result = [
                'semester1' => [
                    'subjects' => [],
                    'display_subjects' => [],
                    'average' => 0,
                    'classification' => ''
                ],
                'semester2' => [
                    'subjects' => [],
                    'display_subjects' => [],
                    'average' => 0,
                    'classification' => ''
                ],
                'yearly' => [
                    'subjects' => [],
                    'display_subjects' => [],
                    'average' => 0,
                    'classification' => ''
                ]
            ];

            $semester1Total = 0;
            $semester1Count = 0;
            $semester2Total = 0;
            $semester2Count = 0;
            $yearlyTotal = 0;
            $yearlyCount = 0;

            foreach ($subjects as $subject) {
                $isSpecialSubject = in_array($subject->name, $specialSubjectNames);

                // Lấy điểm HK1
                $semester1Grade = $finalGrades[$student->id][1][$subject->id][0] ?? null;
                $semester1Score = $semester1Grade ? $semester1Grade->score : 0;
                $semester1TextValue = $semester1Grade ? $semester1Grade->text_value : null;

                // Lấy điểm HK2
                $semester2Grade = $finalGrades[$student->id][2][$subject->id][0] ?? null;
                $semester2Score = $semester2Grade ? $semester2Grade->score : 0;
                $semester2TextValue = $semester2Grade ? $semester2Grade->text_value : null;

                // Xử lý hiển thị điểm HK1
                if ($semester1Grade) {
                    if ($isSpecialSubject) {
                        $semester1Display = $semester1TextValue ?: ($semester1Score >= 5 ? 'Đạt' : 'Chưa đạt');
                    } else {
                        $semester1Display = $semester1Score;
                    }
                } else {
                    $semester1Display = '-';
                }

                // Xử lý hiển thị điểm HK2
                if ($semester2Grade) {
                    if ($isSpecialSubject) {
                        $semester2Display = $semester2TextValue ?: ($semester2Score >= 5 ? 'Đạt' : 'Chưa đạt');
                    } else {
                        $semester2Display = $semester2Score;
                    }
                } else {
                    $semester2Display = '-';
                }

                // Tính điểm cả năm cho môn học
                $yearlySubjectScore = 0;
                $yearlyDisplay = '-';

                if ($semester1Grade && $semester2Grade) {
                    if ($isSpecialSubject) {
                        // Đối với môn đặc biệt, điểm cả năm phụ thuộc vào HK2
                        $yearlySubjectScore = $semester2Score;
                        $yearlyDisplay = $semester2Display;
                    } else {
                        // Môn thường: (HK1 + HK2*2)/3
                        $yearlySubjectScore = round(($semester1Score + $semester2Score * 2) / 3, 1);
                        $yearlyDisplay = $yearlySubjectScore;
                    }
                }

                // Lưu điểm số để tính toán
                $result['semester1']['subjects'][$subject->id] = $semester1Score;
                $result['semester2']['subjects'][$subject->id] = $semester2Score;
                $result['yearly']['subjects'][$subject->id] = $yearlySubjectScore;

                // Lưu giá trị hiển thị
                $result['semester1']['display_subjects'][$subject->id] = $semester1Display;
                $result['semester2']['display_subjects'][$subject->id] = $semester2Display;
                $result['yearly']['display_subjects'][$subject->id] = $yearlyDisplay;

                // Cập nhật tổng điểm và số môn cho HK1 (chỉ tính các môn có điểm > 0)
                if ($semester1Score > 0) {
                    $semester1Total += $semester1Score;
                    $semester1Count++;
                }

                // Cập nhật tổng điểm và số môn cho HK2 (chỉ tính các môn có điểm > 0)
                if ($semester2Score > 0) {
                    $semester2Total += $semester2Score;
                    $semester2Count++;
                }

                // Cập nhật tổng điểm và số môn cho cả năm
                if ($yearlySubjectScore > 0) {
                    $yearlyTotal += $yearlySubjectScore;
                    $yearlyCount++;
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

            if ($yearlyCount > 0) {
                $result['yearly']['average'] = round($yearlyTotal / $yearlyCount, 1);
                $result['yearly']['classification'] = $this->classifyStudent($result['yearly']['average'], $result['yearly']['subjects']);
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
