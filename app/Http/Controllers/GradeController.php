<?php

namespace App\Http\Controllers;

use App\Exports\GradeTemplateExport;
use App\Imports\GradesImport;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Facades\Excel;

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
        $semester1 = $semesters->firstWhere('name', 'Học kỳ I');
        $semester2 = $semesters->firstWhere('name', 'Học kỳ II');

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
                    foreach ($students as $index => $student) {
                        // Lấy điểm tất cả các bài kiểm tra của cả 2 học kỳ
                        $semester1Grades = $semester1 ? Grade::where('class_id', $selectedClassId)
                            ->where('semester_id', $semester1->id)
                            ->where('academic_year_id', $selectedAcademicYearId)
                            ->where('subject_id', $subjectsTaught->first()->id)
                            ->where('student_id', $student->id)
                            ->get() : collect();

                        $semester2Grades = $semester2 ? Grade::where('class_id', $selectedClassId)
                            ->where('semester_id', $semester2->id)
                            ->where('academic_year_id', $selectedAcademicYearId)
                            ->where('subject_id', $subjectsTaught->first()->id)
                            ->where('student_id', $student->id)
                            ->get() : collect();

                        // Kiểm tra có phải môn đặc biệt không
                        $isSpecialSubject = in_array($subjectsTaught->first()->name, [
                            'Giáo dục quốc phòng và an ninh',
                            'Giáo dục thể chất',
                            'Nghệ thuật'
                        ]);

                        if ($isSpecialSubject) {

                            if ($isSpecialSubject) {
                                // Sử dụng hàm tính toán đã dùng cho học kỳ riêng
                                $result1 = $this->calculateSpecialSubjectResult($semester1Grades->groupBy('test_type'));
                                $result2 = $this->calculateSpecialSubjectResult($semester2Grades->groupBy('test_type'));

                                $yearlyResult = ($result2['result'] === 'Đạt') ? 'Đạt' : 'Chưa đạt';


                                $grades[$student->id] = [
                                    'semester1' => $result1['result'],
                                    'semester2' => $result2['result'],
                                    'yearly_average' => $yearlyResult,
                                    'reason' => $yearlyResult === 'Đạt'
                                        ? "Đạt theo HK2"
                                        : "HK2 chưa đạt"
                                ];
                            }

                        } else {
                            // Xử lý môn thường - hiển thị điểm các bài kiểm tra khi xem học kỳ, và điểm TB khi xem cả năm
                            $final1 = $semester1Grades->where('test_type', 'final')->first();
                            $final2 = $semester2Grades->where('test_type', 'final')->first();

                            $final1Score = $final1 ? $final1->score : null;
                            $final2Score = $final2 ? $final2->score : null;

                            $average = ($final1Score && $final2Score) ? ($final1Score + $final2Score) / 2 : null;

                            $grades[$student->id]['semester1'] = $final1Score ?? '-';
                            $grades[$student->id]['semester2'] = $final2Score ?? '-';
                            $grades[$student->id]['yearly_average'] = $average ?? '-';
                        }
                    }
                } else {
                    // Xử lý khi xem theo học kỳ (hiển thị đầy đủ các loại điểm)
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

                                // Giữ nguyên hiển thị các điểm kiểm tra
                                $grades[$student->id][$subject->id] = [
                                    'fifteen_minutes' => [
                                        $subjectGrades['fifteen_minutes'][0] ?? null,
                                        $subjectGrades['fifteen_minutes'][1] ?? null,
                                        $subjectGrades['fifteen_minutes'][2] ?? null,
                                    ],
                                    'one_period' => $subjectGrades['one_period'][0] ?? null,
                                    'semester' => $subjectGrades['semester'][0] ?? null,
                                    'is_special' => true,
                                    'display_average' => $specialResult['result'], // Hiển thị Đạt/Chưa đạt
                                    'reason' => $specialResult['reason'],
                                    $grades[$student->id]['semester_result'] = $specialResult['result']

                                ];
                            } else {
                                // Xử lý môn thường - hiển thị đầy đủ các điểm
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

                                // Tính điểm trung bình
                                $subjectAverage = $this->calculateSubjectAverage($grades[$student->id][$subject->id]);

                                if ($subjectAverage > 0) {
                                    $totalScore += $subjectAverage;
                                    $subjectCount++;
                                }

                                $grades[$student->id][$subject->id]['average'] = round($subjectAverage, 1);
                                $grades[$student->id][$subject->id]['display_average'] = round($subjectAverage, 1);
                            }
                        }

                        $grades[$student->id]['semester_average'] = $subjectCount > 0
                            ? round($totalScore / $subjectCount, 1)
                            : ($isSpecialSubject ? $grades[$student->id]['semester_result'] : 0);
                    }
                }
            }}

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
    public function getAssignedClasses(Request $request)
    {
        $yearId = $request->input('academic_year_id');
        $teacherId = $request->input('teacher_id');

        $classes = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('academic_year_id', $yearId)
            ->with('class')
            ->get()
            ->map(function($assignment) {
                return $assignment->class;
            })
            ->unique()
            ->values();

        return response()->json($classes);
    }

    public function getSemesters(Request $request)
    {
        $yearId = $request->input('academic_year_id');

        $semesters = Semester::where('academic_year_id', $yearId)
            ->where('school_id', Auth::user()->school_id)
            ->get(['id', 'name']);

        return response()->json($semesters);
    }
    private function calculateSubjectAverage($subjectData) // Đổi tên biến cho rõ ràng
    {
        // Hàm helper để lấy giá trị điểm (đảm bảo là số)
        $getScore = function ($item) {
            if (is_object($item) && isset($item->score)) {
                return (float) $item->score; // Convert to float
            }
            return is_numeric($item) ? (float) $item : null;
        };

        // Lọc và lấy điểm 15 phút hợp lệ
        $fifteenMinutes = array_filter($subjectData['fifteen_minutes'] ?? [], function ($item) use ($getScore) {
            $score = $getScore($item);
            return !is_null($score) && $score >= 0;
        });

        // Lấy điểm 1 tiết
        $onePeriodValue = isset($subjectData['one_period']) ? $getScore($subjectData['one_period']) : null;
        $onePeriod = !is_null($onePeriodValue) && $onePeriodValue >= 0 ? [$onePeriodValue] : [];

        // Lấy điểm cuối kỳ
        $semesterValue = isset($subjectData['semester']) ? $getScore($subjectData['semester']) : null;
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
    private function calculateSemesterResult($grades)
    {
        // Đếm số bài 15 phút đạt
        $passedFifteenMinutes = 0;
        $fifteenMinutes = $grades->where('test_type', 'fifteen_minute');

        foreach ($fifteenMinutes as $grade) {
            if ($grade->text_value === 'Đạt' || $grade->score >= 5) {
                $passedFifteenMinutes++;
            }
        }

        // Kiểm tra bài 1 tiết
        $onePeriod = $grades->where('test_type', 'one_period')->first();
        $onePeriodPassed = $onePeriod && ($onePeriod->text_value === 'Đạt' || $onePeriod->score >= 5);

        // Kiểm tra điều kiện thi cuối kỳ
        $eligibleForFinal = ($passedFifteenMinutes >= 2) && $onePeriodPassed;

        // Kiểm tra điểm cuối kỳ
        $final = $grades->where('test_type', 'final')->first();
        if (!$eligibleForFinal) {
            return ['result' => 'Chưa đạt', 'reason' => 'Không đủ điều kiện thi'];
        }

        if ($final) {
            $finalPassed = ($final->text_value === 'Đạt' || $final->score >= 5);
            return ['result' => $finalPassed ? 'Đạt' : 'Chưa đạt', 'reason' => $finalPassed ? '' : 'Không đạt điểm cuối kỳ'];
        }

        return ['result' => 'Chưa đạt', 'reason' => 'Chưa có điểm cuối kỳ'];
    }
    private function calculateSpecialSubjectResult($subjectGrades)
    {
        // 1. Kiểm tra điểm 15 phút (cần ít nhất 2/3 bài đạt)
        $passedFifteenMinutes = 0;
        foreach ($subjectGrades['fifteen_minutes'] ?? [] as $grade) {
            if ($grade && ($grade->text_value === 'Đạt' || $grade->score >= 5)) {
                $passedFifteenMinutes++;
            }
        }

        // 2. Kiểm tra điểm 1 tiết
        $onePeriodPassed = false;
        if (isset($subjectGrades['one_period'][0])) {
            $onePeriodGrade = $subjectGrades['one_period'][0];
            $onePeriodPassed = ($onePeriodGrade->text_value === 'Đạt' || $onePeriodGrade->score >= 5);
        }

        // 3. Kiểm tra điều kiện thi cuối kỳ
        $eligibleForFinal = ($passedFifteenMinutes >= 2) && $onePeriodPassed;

        // 4. Xử lý kết quả cuối kỳ
        $finalPassed = false;
        if (isset($subjectGrades['semester'][0])) {
            $finalGrade = $subjectGrades['semester'][0];
            $finalPassed = ($finalGrade->text_value === 'Đạt' || $finalGrade->score >= 5);
        }

        // 5. Quyết định kết quả cuối cùng
        if (!$eligibleForFinal) {
            return [
                'result' => 'Chưa đạt',
                'reason' => $onePeriodPassed ?
                    'Không đủ 2/3 điểm 15 phút đạt' :
                    'Điểm 1 tiết chưa đạt'
            ];
        }

        return [
            'result' => $finalPassed ? 'Đạt' : 'Chưa đạt',
            'reason' => $finalPassed ? '' : 'Điểm cuối kỳ chưa đạt'
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

    public function homeroomGrades(Request $request)
    {
        $teacherId = Auth::id();
        $schoolId = Auth::user()->school_id;

        // Lấy tất cả lớp chủ nhiệm của giáo viên với thông tin năm học và lớp
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

        // Xác định năm học được chọn (kiểm tra nếu có academicYear)
        $selectedAcademicYearId = $request->input('academic_year_id');
        if (!$selectedAcademicYearId) {
            $firstAssignment = $homeroomClasses->first();
            $selectedAcademicYearId = $firstAssignment->academic_year_id ?? null;

            // Kiểm tra nếu không có academicYear
            if (!$selectedAcademicYearId) {
                return view('grades.homeroom', [
                    'error' => 'Không tìm thấy năm học cho lớp chủ nhiệm'
                ]);
            }
        }

        // Lấy các học kỳ thuộc năm học được chọn
        $semesters = Semester::where('academic_year_id', $selectedAcademicYearId)
            ->where('school_id', $schoolId)
            ->get();

        // Xác định học kỳ 1 và học kỳ 2 (sử dụng cả số và chữ)
        $semester1 = $semesters->first(function ($semester) {
            return stripos($semester->name, 'Học kỳ I') !== false;
        });

        $semester2 = $semesters->first(function ($semester) {
            return
                stripos($semester->name, 'Học kỳ II') !== false;
        });

        // Kiểm tra nếu không tìm thấy học kỳ
        if (!$semester1 || !$semester2) {
            return view('grades.homeroom', [
                'error' => 'Không tìm thấy đủ học kỳ trong năm học này'
            ]);
        }

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
                    ->whereIn('semester_id', [$semester1->id, $semester2->id])
                    ->where('test_type', 'final')
                    ->get();

                $groupedGrades = $finalGrades->groupBy(['student_id', 'semester_id', 'subject_id']);

                $specialSubjects = ['Giáo dục quốc phòng và an ninh', 'Giáo dục thể chất', 'Nghệ thuật'];

                foreach ($students as $student) {
                    $result = [
                        'semester1' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => ''],
                        'semester2' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => ''],
                        'yearly' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => '']
                    ];

                    foreach ($subjects as $subject) {
                        $isSpecial = in_array($subject->name, $specialSubjects);

                        // Xử lý điểm HK1
                        $semester1Grade = $groupedGrades[$student->id][$semester1->id][$subject->id][0] ?? null;
                        $semester1Score = $semester1Grade ? ($semester1Grade->score ?? 0) : 0;
                        $semester1Text = $semester1Grade ? ($semester1Grade->text_value ?? null) : null;
                        $result['semester1']['subjects'][$subject->id] = $semester1Score;
                        $result['semester1']['display_subjects'][$subject->id] = $this->formatGradeDisplay($semester1Score, $semester1Text, $isSpecial);

                        // Xử lý điểm HK2
                        $semester2Grade = $groupedGrades[$student->id][$semester2->id][$subject->id][0] ?? null;
                        $semester2Score = $semester2Grade ? ($semester2Grade->score ?? 0) : 0;
                        $semester2Text = $semester2Grade ? ($semester2Grade->text_value ?? null) : null;
                        $result['semester2']['subjects'][$subject->id] = $semester2Score;
                        $result['semester2']['display_subjects'][$subject->id] = $this->formatGradeDisplay($semester2Score, $semester2Text, $isSpecial);

                        // Tính điểm cả năm
                        // Trong phần tính điểm cả năm cho môn đặc biệt
                        if ($semester1Grade && $semester2Grade) {
                            if ($isSpecial) {
                                // Đối với môn đặc biệt, điểm cả năm = điểm học kỳ 2
                                $yearlyScore = $semester2Score;
                                $yearlyText = $semester2Text ?: ($semester2Score >= 5 ? 'Đạt' : 'Chưa đạt');

                                $result['yearly']['subjects'][$subject->id] = $yearlyScore;
                                $result['yearly']['display_subjects'][$subject->id] = $yearlyText;
                            } else {
                                // Môn thường tính theo công thức (HK1 + HK2*2)/3
                                $yearlyScore = round(($semester1Score + $semester2Score * 2) / 3, 1);
                                $result['yearly']['subjects'][$subject->id] = $yearlyScore;
                                $result['yearly']['display_subjects'][$subject->id] = $yearlyScore;
                            }
                        }
                    }

                    // Tính điểm trung bình
                    $this->calculateAverages($result);
                    $studentResults[$student->id] = $result;
                }
            }
        }

        // Lấy thông tin năm học cho dropdown
        $academicYears = $homeroomClasses->unique('academic_year_id')->map(function ($item) {
            return $item->academicYear;
        });

        return view('grades.homeroom', compact(
            'homeroomClasses',
            'academicYears',
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

        // Nếu là môn đặc biệt
        if ($isSpecial) {
            // Ưu tiên sử dụng text_value nếu có
            if ($textValue !== null) {
                return $textValue;
            }
            // Nếu không có text_value thì dựa vào điểm số
            return $score >= 5 ? 'Đạt' : 'Chưa đạt';
        }

        // Môn thường
        return $score > 0 ? number_format($score, 1) : '-';
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
            return 'Chưa đạt';
        }

        $specialSubjects = [
            'Giáo dục quốc phòng và an ninh',
            'Giáo dục thể chất',
            'Nghệ thuật'
        ];

        // Đếm số môn đặc biệt chưa đạt
        $specialNotPassed = 0;

        // Đếm số môn thường từ 8.0 trở lên và từ 6.5 trở lên
        $countAbove8 = 0;
        $countAbove6_5 = 0;
        $countAbove5 = 0;
        $hasBelow3_5 = false;

        foreach ($subjectScores as $subjectName => $scoreData) {
            $isSpecial = in_array($subjectName, $specialSubjects);

            if ($isSpecial) {
                // Xử lý môn đặc biệt (đánh giá bằng nhận xét)
                $isPassed = false;
                if (is_array($scoreData) && isset($scoreData['value'])) {
                    $isPassed = $scoreData['value'] === 'Đạt';
                } elseif (is_object($scoreData) && isset($scoreData->text_value)) {
                    $isPassed = $scoreData->text_value === 'Đạt';
                } elseif (is_numeric($scoreData)) {
                    $isPassed = $scoreData >= 5.0;
                }

                if (!$isPassed) {
                    $specialNotPassed++;
                }
            } else {
                // Xử lý môn thường (đánh giá bằng điểm số)
                $score = is_array($scoreData) ? ($scoreData['value'] ?? 0) :
                    (is_object($scoreData) ? ($scoreData->score ?? 0) : $scoreData);

                if (is_numeric($score)) {
                    if ($score >= 8.0) $countAbove8++;
                    if ($score >= 6.5) $countAbove6_5++;
                    if ($score >= 5.0) $countAbove5++;
                    if ($score < 3.5) $hasBelow3_5 = true;
                }
            }
        }

        // Kiểm tra điều kiện từ cao xuống thấp
        if ($specialNotPassed === 0) {
            // Mức Tốt
            if ($countAbove8 >= 6 && $countAbove6_5 === (count($subjectScores) - count($specialSubjects))) {
                return 'Tốt';
            }

            // Mức Khá
            if ($countAbove6_5 >= 6 && $countAbove5 === (count($subjectScores) - count($specialSubjects))) {
                return 'Khá';
            }
        }

        // Mức Đạt
        if ($specialNotPassed <= 1 && $countAbove5 >= 6 && !$hasBelow3_5) {
            return 'Đạt';
        }

        // Mức Chưa đạt - các trường hợp còn lại
        return 'Chưa đạt';
    }
    public function adminViewAllGrades(Request $request)
    {
        // Kiểm tra quyền admin
        if (!Auth::user()->isSchoolAdmin()) {
            abort(403, 'Bạn không có quyền truy cập chức năng này');
        }

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
            ->orderBy('start_date')
            ->get();

        // Xác định học kỳ 1 và học kỳ 2 (2 học kỳ đầu tiên khi sắp xếp theo ngày bắt đầu)
        $semester1 = $semesters->first();
        $semester2 = $semesters->slice(1)->first();

        // Thêm option "Cả năm"
        $semesters = $semesters->push((object)[
            'id' => 0,
            'name' => 'Cả năm',
            'academic_year_id' => $selectedAcademicYearId
        ]);

        // Lấy tất cả lớp học trong năm học được chọn
        $classes = ClassModel::where('academic_year_id', $selectedAcademicYearId)
            ->where('school_id', $schoolId)
            ->with('gradeLevel')
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get();

        // Lấy lớp và học kỳ được chọn
        $selectedClassId = $request->input('class_id');
        $selectedSemesterId = $request->input('semester_id');

        // Khởi tạo biến dữ liệu
        $students = collect();
        $grades = [];
        $subjects = collect();
        $classDetails = null;
        $selectedSemester = null;

        if ($selectedClassId && $selectedSemesterId !== null) {
            $classDetails = ClassModel::with('gradeLevel')->find($selectedClassId);

            $selectedSemester = $selectedSemesterId == 0
                ? (object)['id' => 0, 'name' => 'Cả năm']
                : Semester::find($selectedSemesterId);

            // Lấy danh sách học sinh
            $students = User::whereHas('studentClasses', function($query) use ($selectedClassId, $selectedAcademicYearId) {
                $query->where('student_classes.class_id', $selectedClassId)
                    ->where('student_classes.academic_year_id', $selectedAcademicYearId);
            })->orderBy('full_name')->get();

            // Lấy tất cả môn học của trường
            $subjects = Subject::where('school_id', $schoolId)->get();

            if ($students->isNotEmpty()) {
                if ($selectedSemesterId == 0) {
                    // Xử lý dữ liệu cả năm - chỉ khi có đủ 2 học kỳ
                    if (!$semester1 || !$semester2) {
                        return redirect()->back()->with('error', 'Năm học này không có đủ 2 học kỳ để tính điểm cả năm');
                    }

                    $finalGrades = Grade::where('class_id', $selectedClassId)
                        ->where('academic_year_id', $selectedAcademicYearId)
                        ->whereIn('semester_id', [$semester1->id, $semester2->id])
                        ->where('test_type', 'final')
                        ->get()
                        ->groupBy(['student_id', 'semester_id', 'subject_id']);

                    $specialSubjects = ['Giáo dục quốc phòng và an ninh', 'Giáo dục thể chất', 'Nghệ thuật'];

                    foreach ($students as $student) {
                        $result = [
                            'semester1' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => ''],
                            'semester2' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => ''],
                            'yearly' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => '']
                        ];

                        foreach ($students as $student) {
                            $result = [
                                'semester1' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => ''],
                                'semester2' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => ''],
                                'yearly' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => '']
                            ];

                            foreach ($subjects as $subject) {
                                $isSpecial = in_array($subject->name, $specialSubjects);

                                // Xử lý điểm HK1
                                $semester1Grade = $finalGrades[$student->id][$semester1->id][$subject->id][0] ?? null;
                                $semester1Score = $semester1Grade ? ($semester1Grade->score ?? 0) : 0;
                                $semester1Text = $semester1Grade ? ($semester1Grade->text_value ?? null) : null;

                                // Xử lý điểm HK2
                                $semester2Grade = $finalGrades[$student->id][$semester2->id][$subject->id][0] ?? null;
                                $semester2Score = $semester2Grade ? ($semester2Grade->score ?? 0) : 0;
                                $semester2Text = $semester2Grade ? ($semester2Grade->text_value ?? null) : null;

                                if ($isSpecial) {
                                    // Xử lý môn đặc biệt
                                    $result1 = $semester1Text ?: 'Chưa đạt';
                                    $result2 = $semester2Text ?: 'Chưa đạt';

                                    // Quy tắc cả năm: chỉ xét HK2
                                    $yearlyResult = $result2;

                                    $result['semester1']['subjects'][$subject->id] = $semester1Score;
                                    $result['semester2']['subjects'][$subject->id] = $semester2Score;
                                    $result['yearly']['subjects'][$subject->id] = $semester2Score;

                                    $result['semester1']['display_subjects'][$subject->id] = $result1;
                                    $result['semester2']['display_subjects'][$subject->id] = $result2;
                                    $result['yearly']['display_subjects'][$subject->id] = $yearlyResult;

                                    // Thêm vào mảng grades
                                    $grades[$student->id][$subject->id] = [
                                        'semester1_avg' => $result1,
                                        'semester2_avg' => $result2,
                                        'average' => $yearlyResult,
                                        'semester1_text' => $result1,
                                        'semester2_text' => $result2,
                                        'yearly_result' => $yearlyResult,
                                        'is_special' => true
                                    ];
                                } else {
                                    // Xử lý môn thường
                                    $result['semester1']['subjects'][$subject->id] = $semester1Score;
                                    $result['semester2']['subjects'][$subject->id] = $semester2Score;
                                    $result['yearly']['subjects'][$subject->id] = ($semester1Grade && $semester2Grade)
                                        ? round(($semester1Score + $semester2Score * 2) / 3, 1)
                                        : 0;

                                    $result['semester1']['display_subjects'][$subject->id] = $semester1Score > 0 ? $semester1Score : '-';
                                    $result['semester2']['display_subjects'][$subject->id] = $semester2Score > 0 ? $semester2Score : '-';
                                    $result['yearly']['display_subjects'][$subject->id] = ($semester1Grade && $semester2Grade)
                                        ? round(($semester1Score + $semester2Score * 2) / 3, 1)
                                        : '-';

                                    $grades[$student->id][$subject->id] = [
                                        'semester1_avg' => $semester1Score > 0 ? $semester1Score : '-',
                                        'semester2_avg' => $semester2Score > 0 ? $semester2Score : '-',
                                        'average' => ($semester1Grade && $semester2Grade)
                                            ? round(($semester1Score + $semester2Score * 2) / 3, 1)
                                            : '-',
                                        'is_special' => false
                                    ];
                                }
                            }

                            // Tính điểm trung bình
                            $this->calculateAverages($result);

                            // Thêm thông tin tổng hợp vào grades
                            $grades[$student->id]['yearly_average'] = $result['yearly']['average'];
                            $grades[$student->id]['classification'] = $result['yearly']['classification'];
                        }
                    }

                } else {
                    // Xử lý dữ liệu theo học kỳ được chọn
                    $allGrades = Grade::where('class_id', $selectedClassId)
                        ->where('semester_id', $selectedSemesterId)
                        ->where('academic_year_id', $selectedAcademicYearId)
                        ->get()
                        ->groupBy(['student_id', 'subject_id', 'test_type']);

                    // Danh sách môn học đặc biệt
                    $specialSubjects = ['Giáo dục quốc phòng và an ninh', 'Giáo dục thể chất', 'Nghệ thuật'];

                    foreach ($students as $student) {
                        $totalScore = 0;
                        $subjectCount = 0;
                        $studentGrades = [];

                        foreach ($subjects as $subject) {
                            $subjectGrades = $allGrades[$student->id][$subject->id] ?? [];
                            $isSpecial = in_array($subject->name, $specialSubjects);

                            $gradeData = [
                                'fifteen_minutes' => [
                                    $subjectGrades['fifteen_minutes'][0] ?? null,
                                    $subjectGrades['fifteen_minutes'][1] ?? null,
                                    $subjectGrades['fifteen_minutes'][2] ?? null,
                                ],
                                'one_period' => $subjectGrades['one_period'][0] ?? null,
                                'semester' => $subjectGrades['semester'][0] ?? null,
                                'is_special' => $isSpecial
                            ];

                            if ($isSpecial) {
                                $specialResult = $this->calculateSpecialSubjectResult($subjectGrades);
                                $gradeData['display_average'] = $specialResult['result'];
                                $gradeData['average'] = null; // Môn đặc biệt không tính điểm trung bình
                            } else {
                                $subjectAverage = $this->calculateSubjectAverage($gradeData);
                                $gradeData['average'] = round($subjectAverage, 1);
                                $gradeData['display_average'] = round($subjectAverage, 1);

                                if ($subjectAverage > 0) {
                                    $totalScore += $subjectAverage;
                                    $subjectCount++;
                                }
                            }

                            // Thêm dữ liệu giả lập để tương thích với view cả năm
                            $gradeData['semester1_avg'] = '-';
                            $gradeData['semester2_avg'] = '-';

                            $studentGrades[$subject->id] = $gradeData;
                        }

                        // Tính điểm trung bình học kỳ và xếp loại
                        $semesterAverage = $subjectCount > 0 ? round($totalScore / $subjectCount, 1) : null;

                        $grades[$student->id] = $studentGrades;
                        $grades[$student->id]['semester_average'] = $semesterAverage;
                        $grades[$student->id]['yearly_average'] = $semesterAverage; // Tương thích với view
                        $grades[$student->id]['classification'] = $this->classifyStudent(
                            $semesterAverage,
                            $studentGrades
                        );
                    }
                }
            }
        }

        return view('grades.admin_grades', compact(
            'academicYears',
            'semesters',
            'classes',
            'students',
            'grades',
            'subjects',
            'selectedAcademicYearId',
            'selectedClassId',
            'selectedSemesterId',
            'classDetails',
            'selectedSemester',
            'semester1',
            'semester2'
        ));
    }
    public function getClassesByYearAdmin(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $schoolId = Auth::user()->school_id;

        if (!$academicYearId) {
            return response()->json([
                'classes' => [],
                'semesters' => []
            ]);
        }

        try {
            // Lấy danh sách lớp học
            $classes = ClassModel::where('academic_year_id', $academicYearId)
                ->where('school_id', $schoolId)
                ->with('gradeLevel')
                ->orderBy('grade_level_id')
                ->orderBy('name')
                ->get()
                ->map(function($class) {
                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'grade_level' => [
                            'id' => $class->gradeLevel->id,
                            'name' => $class->gradeLevel->name
                        ]
                    ];
                });

            // Lấy danh sách học kỳ
            $semesters = Semester::where('academic_year_id', $academicYearId)
                ->where('school_id', $schoolId)
                ->get()
                ->map(function($semester) {
                    return [
                        'id' => $semester->id,
                        'name' => $semester->name
                    ];
                });

            return response()->json([
                'success' => true,
                'classes' => $classes,
                'semesters' => $semesters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
}
