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
    public function index(Request $request)
    {
        $teacherId = Auth::id();
        $schoolId = Auth::user()->school_id;

        $academicYears = AcademicYear::where('school_id', $schoolId)
            ->orderBy('start_date', 'desc')
            ->get();

        $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        $selectedAcademicYearId = $request->input('academic_year_id', $currentAcademicYear->id ?? $academicYears->first()->id ?? null);

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

        $assignedClasses = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('academic_year_id', $selectedAcademicYearId)
            ->where('school_id', $schoolId)
            ->with(['class' => function ($query) {
                $query->with(['gradeLevel', 'academicYear']);
            }, 'subject'])
            ->get()
            ->groupBy('class_id');

        $selectedClassId = $request->input('class_id');
        $selectedSemesterId = $request->input('semester_id');

        $students = collect();
        $grades = collect();
        $subjectsTaught = collect();

        if ($selectedClassId && $selectedSemesterId !== null) {
            $class = ClassModel::where('id', $selectedClassId)
                ->where('school_id', $schoolId)
                ->firstOrFail();

            $students = $class->students()
                ->wherePivot('academic_year_id', $selectedAcademicYearId)
                ->orderBy('full_name')
                ->get();

            $subjectsTaught = TeacherAssignment::where('teacher_id', $teacherId)
                ->where('class_id', $selectedClassId)
                ->where('academic_year_id', $selectedAcademicYearId)
                ->where('school_id', $schoolId)
                ->with('subject')
                ->get()
                ->pluck('subject');
            $grades = [];

            if ($selectedClassId && $selectedSemesterId !== null) {
                if ($selectedSemesterId == 0) {
                    foreach ($students as $index => $student) {
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

                        $isSpecialSubject = in_array($subjectsTaught->first()->name, [
                            'Giáo dục quốc phòng và an ninh',
                            'Giáo dục thể chất',
                            'Nghệ thuật'
                        ]);

                        if ($isSpecialSubject) {

                            if ($isSpecialSubject) {
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
                            $final1 = $semester1Grades->where('test_type', 'final')->first();
                            $final2 = $semester2Grades->where('test_type', 'final')->first();

                            $final1Score = $final1 ? $final1->score : null;
                            $final2Score = $final2 ? $final2->score : null;

                            $average = ($final1Score && $final2Score) ? round(($final1Score + $final2Score * 2) / 3,1 ) : null;

                            $grades[$student->id]['semester1'] = $final1Score ?? '-';
                            $grades[$student->id]['semester2'] = $final2Score ?? '-';
                            $grades[$student->id]['yearly_average'] = $average ?? '-';
                        }
                    }
                } else {
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

                                $grades[$student->id][$subject->id] = [
                                    'fifteen_minutes' => [
                                        $subjectGrades['fifteen_minutes'][0] ?? null,
                                        $subjectGrades['fifteen_minutes'][1] ?? null,
                                        $subjectGrades['fifteen_minutes'][2] ?? null,
                                    ],
                                    'one_period' => $subjectGrades['one_period'][0] ?? null,
                                    'semester' => $subjectGrades['semester'][0] ?? null,
                                    'is_special' => true,
                                    'display_average' => $specialResult['result'],
                                    'reason' => $specialResult['reason'],
                                ];
                                $grades[$student->id]['semester_result'] = $specialResult['result'];

                            } else {
//                                $grades[$student->id][$subject->id] = [
//                                    'fifteen_minutes' => [
//                                        $subjectGrades['fifteen_minutes'][0] ?? null,
//                                        $subjectGrades['fifteen_minutes'][1] ?? null,
//                                        $subjectGrades['fifteen_minutes'][2] ?? null,
//                                    ],
//                                    'one_period' => $subjectGrades['one_period'][0] ?? null,
//                                    'semester' => $subjectGrades['semester'][0] ?? null,
//                                    'is_special' => false
//                                ];
//
//                                $subjectAverage = $this->calculateSubjectAverage($grades[$student->id][$subject->id]);
//
//                                    $totalScore += $subjectAverage;
//                                    $subjectCount++;
//
//
//                                $grades[$student->id][$subject->id]['average'] = round($subjectAverage, 1);
//                                $grades[$student->id][$subject->id]['display_average'] = round($subjectAverage, 1);
//                            }
                                $finalGrade = $subjectGrades['final'][0] ?? null;
                                $subjectAverage = $finalGrade ? $finalGrade->score : null;

                                if ($subjectAverage !== null) {
                                    $totalScore += $subjectAverage;
                                    $subjectCount++;
                                }

                                $grades[$student->id][$subject->id] = [
                                    'fifteen_minutes' => [
                                        $subjectGrades['fifteen_minutes'][0] ?? null,
                                        $subjectGrades['fifteen_minutes'][1] ?? null,
                                        $subjectGrades['fifteen_minutes'][2] ?? null,
                                    ],
                                    'one_period' => $subjectGrades['one_period'][0] ?? null,
                                    'semester' => $subjectGrades['semester'][0] ?? null,
                                    'is_special' => false,
                                    'average' => $subjectAverage,
                                    'display_average' => $subjectAverage ?? '-'
                                ];
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
    private function calculateSubjectAverage($subjectData)
    {
        $getScore = function ($item) {
            if (is_object($item) && isset($item->score)) {
                return (float) $item->score;
            }
            return is_numeric($item) ? (float) $item : null;
        };

        $fifteenMinutes = array_filter($subjectData['fifteen_minutes'] ?? [], function ($item) use ($getScore) {
            $score = $getScore($item);
            return !is_null($score) && $score >= 0;
        });

        $onePeriodValue = isset($subjectData['one_period']) ? $getScore($subjectData['one_period']) : null;
        $onePeriod = !is_null($onePeriodValue) && $onePeriodValue >= 0 ? [$onePeriodValue] : [];

        $semesterValue = isset($subjectData['semester']) ? $getScore($subjectData['semester']) : null;
        $semester = !is_null($semesterValue) && $semesterValue >= 0 ? [$semesterValue] : [];

        $total = 0;
        $weights = 0;

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

        if (!empty($semester)) {
            $total += $semester[0] * 3;
            $weights += 3;
        }

        return $weights > 0 ? round($total / $weights, 1) : 0;
    }
//    private function calculateSemesterResult($grades)
//    {
//        $passedFifteenMinutes = 0;
//        $fifteenMinutes = $grades->where('test_type', 'fifteen_minute');
//
//        foreach ($fifteenMinutes as $grade) {
//            if ($grade->text_value === 'Đạt' || $grade->score >= 5) {
//                $passedFifteenMinutes++;
//            }
//        }
//
//        $onePeriod = $grades->where('test_type', 'one_period')->first();
//        $onePeriodPassed = $onePeriod && ($onePeriod->text_value === 'Đạt' || $onePeriod->score >= 5);
//        $eligibleForFinal = ($passedFifteenMinutes >= 2) && $onePeriodPassed;
//
//        $final = $grades->where('test_type', 'final')->first();
//        if (!$eligibleForFinal) {
//            return ['result' => 'Chưa đạt', 'reason' => 'Không đủ điều kiện thi'];
//        }
//
//        if ($final) {
//            $finalPassed = ($final->text_value === 'Đạt' || $final->score >= 5);
//            return ['result' => $finalPassed ? 'Đạt' : 'Chưa đạt', 'reason' => $finalPassed ? '' : 'Không đạt điểm cuối kỳ'];
//        }
//
//        return ['result' => 'Chưa đạt', 'reason' => 'Chưa có điểm cuối kỳ'];
//    }
    private function calculateSpecialSubjectResult($subjectGrades)
    {
        $passedFifteenMinutes = 0;
        foreach ($subjectGrades['fifteen_minutes'] ?? [] as $grade) {
            if ($grade && ($grade->text_value === 'Đạt')) {
                $passedFifteenMinutes++;
            }
        }
        $onePeriodPassed = false;
        if (isset($subjectGrades['one_period'][0])) {
            $onePeriodGrade = $subjectGrades['one_period'][0];
            $onePeriodPassed = ($onePeriodGrade->text_value === 'Đạt');
        }

        $eligibleForFinal = ($passedFifteenMinutes >= 2) && $onePeriodPassed;

        $finalPassed = false;
        if (isset($subjectGrades['semester'][0])) {
            $finalGrade = $subjectGrades['semester'][0];
            $finalPassed = ($finalGrade->text_value === 'Đạt' );
        }

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

        $assignment = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('class_id', $request->class_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $class = ClassModel::where('id', $request->class_id)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $students = $class->students()
            ->wherePivot('academic_year_id', $request->academic_year_id)
            ->orderBy('full_name')
            ->get();

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
                {return [1 => $this,];}
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

        $subjectAverages = [];
        foreach ($grades as $semesterId => $semesterGrades) {
            foreach ($semesterGrades as $subjectId => $subjectGrades) {
//                $subjectAverages[$semesterId][$subjectId] = $this->calculateSubjectAverage($subjectGrades);
                $finalGrade = $subjectGrades['final'][0] ?? null;
                $subjectAverages[$semesterId][$subjectId] = $finalGrade ? $finalGrade->score : null;
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

        $classes = $assignments->map(function ($assignment) {
            return [
                'id' => $assignment->class->id,
                'name' => $assignment->class->name,
                'grade_level' => $assignment->class->gradeLevel
            ];
        })->unique('id')->values();

        return response()->json($classes);
    }

    public function homeroomGrades(Request $request)
    {
        $teacherId = Auth::id();
        $schoolId = Auth::user()->school_id;

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

        $selectedAcademicYearId = $request->input('academic_year_id');
        if (!$selectedAcademicYearId) {
            $firstAssignment = $homeroomClasses->first();
            $selectedAcademicYearId = $firstAssignment->academic_year_id ?? null;

            if (!$selectedAcademicYearId) {
                return view('grades.homeroom', [
                    'error' => 'Không tìm thấy năm học cho lớp chủ nhiệm'
                ]);
            }
        }

        $semesters = Semester::where('academic_year_id', $selectedAcademicYearId)
            ->where('school_id', $schoolId)
            ->get();

        $semester1 = $semesters->first(function ($semester) {
            return stripos($semester->name, 'Học kỳ I') !== false;
        });

        $semester2 = $semesters->first(function ($semester) {
            return
                stripos($semester->name, 'Học kỳ II') !== false;
        });

        if (!$semester1 || !$semester2) {
            return view('grades.homeroom', [
                'error' => 'Không tìm thấy đủ học kỳ trong năm học này'
            ]);
        }

        $classesInSelectedYear = $homeroomClasses->where('academic_year_id', $selectedAcademicYearId);

        $selectedClassId = $request->input('class_id');
        if (!$selectedClassId || !$classesInSelectedYear->contains('class_id', $selectedClassId)) {
            $selectedClassId = $classesInSelectedYear->first()->class_id ?? null;
        }

        $class = null;
        $students = collect();
        $subjects = collect();
        $studentResults = [];

        if ($selectedClassId) {
            $class = ClassModel::with('gradeLevel')->find($selectedClassId);

            $students = User::whereHas('studentClasses', function($query) use ($selectedClassId, $selectedAcademicYearId) {
                $query->where('student_classes.class_id', $selectedClassId)
                    ->where('student_classes.academic_year_id', $selectedAcademicYearId);
            })->orderBy('full_name')->get();

            $subjects = Subject::where('school_id', $schoolId)->get();

            if ($students->isNotEmpty()) {
                $finalGrades = Grade::where('class_id', $selectedClassId)
                    ->where('academic_year_id', $selectedAcademicYearId)
                    ->whereIn('semester_id', [$semester1->id, $semester2->id])
                    ->where('test_type', 'final')
                    ->get();

                $groupedGrades = $finalGrades->groupBy(['student_id', 'semester_id', 'subject_id']);

                $specialSubjects = ['Giáo dục quốc phòng và an ninh', 'Giáo dục thể chất', 'Nghệ thuật'];

                foreach ($students as $student) {
                    // Khởi tạo biến đếm và tổng điểm cho các môn điểm số
                    $totalScoreHK1 = 0;
                    $countHK1 = 0;
                    $totalScoreHK2 = 0;
                    $countHK2 = 0;
                    $totalScoreYearly = 0;
                    $countYearly = 0;

                    $result = [
                        'semester1' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => ''],
                        'semester2' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => ''],
                        'yearly' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => '']
                    ];

                    // Khởi tạo mảng để lưu dữ liệu chi tiết cho classifyStudent
                    $classificationDataHK1 = [];
                    $classificationDataHK2 = [];
                    $classificationDataCN = [];

                    foreach ($subjects as $subject) {
                        $isSpecial = in_array($subject->name, $specialSubjects);

                        // Lấy điểm HK1
                        $semester1Grade = $groupedGrades[$student->id][$semester1->id][$subject->id][0] ?? null;
                        $semester1Score = $semester1Grade ? ($semester1Grade->score ?? 0) : 0;
                        $semester1Text = $semester1Grade ? ($semester1Grade->text_value ?? null) : null;
                        $result['semester1']['subjects'][$subject->id] = $semester1Score;
                        $result['semester1']['display_subjects'][$subject->id] = $this->formatGradeDisplay($semester1Score, $semester1Text, $isSpecial);

                        // Lấy điểm HK2
                        $semester2Grade = $groupedGrades[$student->id][$semester2->id][$subject->id][0] ?? null;
                        $semester2Score = $semester2Grade ? ($semester2Grade->score ?? 0) : 0;
                        $semester2Text = $semester2Grade ? ($semester2Grade->text_value ?? null) : null;
                        $result['semester2']['subjects'][$subject->id] = $semester2Score;
                        $result['semester2']['display_subjects'][$subject->id] = $this->formatGradeDisplay($semester2Score, $semester2Text, $isSpecial);

                        $yearlyScore = null;

                        if ($isSpecial) {
                            // [ĐÃ SỬA] Tính kết quả cả năm cho môn đặc biệt dựa trên HK2, dù có đủ điểm hay không
                            $yearlyResult = $this->formatGradeDisplay($semester2Score, $semester2Text, true);

                            // [ĐÃ SỬA] Luôn thiết lập yearly display subject để tránh lỗi "Undefined array key"
                            $result['yearly']['display_subjects'][$subject->id] = $yearlyResult;

                            // Nếu có điểm HK1 và HK2, tính yearlyScore (cho trường hợp điểm số, không phải nhận xét)
                            if ($semester1Grade && $semester2Grade) {
                                $yearlyScore = $semester2Score; // Lấy điểm HK2 nếu có
                                $result['yearly']['subjects'][$subject->id] = $yearlyScore;
                            } else {
                                $result['yearly']['subjects'][$subject->id] = 0; // Mặc định 0 nếu không có đủ điểm
                            }

                        } else {
                            // Môn thường
                            if ($semester1Grade && $semester2Grade) {
                                $yearlyScore = round(($semester1Score + $semester2Score * 2) / 3, 1);
                                $result['yearly']['subjects'][$subject->id] = $yearlyScore;
                                $result['yearly']['display_subjects'][$subject->id] = $yearlyScore;
                            } else {
                                $result['yearly']['subjects'][$subject->id] = 0;
                                $result['yearly']['display_subjects'][$subject->id] = '-';
                            }
                        }

                        // Tính tổng điểm và đếm số môn chỉ đối với Môn Điểm Số
                        if (!$isSpecial) {
                            $totalScoreHK1 += $semester1Score;
                            $countHK1++;

                            $totalScoreHK2 += $semester2Score;
                            $countHK2++;

                            // Chỉ tính điểm TBCN nếu có đủ điểm HK1 và HK2
                            $totalScoreYearly += ($yearlyScore !== null) ? $yearlyScore : 0;
                            $countYearly++;
                        }

                        // Lưu dữ liệu chi tiết môn học cho classifyStudent
                        if ($isSpecial) {
                            $classificationDataHK1[$subject->id] = [
                                'name' => $subject->name,
                                'is_special' => true,
                                'display_average' => $result['semester1']['display_subjects'][$subject->id]
                            ];
                            $classificationDataHK2[$subject->id] = [
                                'name' => $subject->name,
                                'is_special' => true,
                                'display_average' => $result['semester2']['display_subjects'][$subject->id]
                            ];
                            $classificationDataCN[$subject->id] = [
                                'name' => $subject->name,
                                'is_special' => true,
                                // [ĐÃ SỬA] Lấy dữ liệu từ $result['yearly']['display_subjects'] đã được thiết lập bên trên
                                'yearly_result' => $result['yearly']['display_subjects'][$subject->id]
                            ];
                        } else {
                            // Môn điểm số
                            $classificationDataHK1[$subject->id] = [
                                'name' => $subject->name,
                                'is_special' => false,
                                'average' => $semester1Score
                            ];
                            $classificationDataHK2[$subject->id] = [
                                'name' => $subject->name,
                                'is_special' => false,
                                'average' => $semester2Score
                            ];
                            $classificationDataCN[$subject->id] = [
                                'name' => $subject->name,
                                'is_special' => false,
                                'average' => $yearlyScore
                            ];
                        }
                    }

                    // Tính điểm trung bình (TB) cho từng kỳ và cả năm
                    $result['semester1']['average'] = $countHK1 > 0 ? round($totalScoreHK1 / $countHK1, 1) : null;
                    $result['semester2']['average'] = $countHK2 > 0 ? round($totalScoreHK2 / $countHK2, 1) : null;
                    $result['yearly']['average'] = $countYearly > 0 ? round($totalScoreYearly / $countYearly, 1) : null;

                    // Gọi classifyStudent
                    $result['semester1']['classification'] = $this->classifyStudent(
                        $result['semester1']['average'],
                        $classificationDataHK1,
                        'semester1'
                    );

                    $result['semester2']['classification'] = $this->classifyStudent(
                        $result['semester2']['average'],
                        $classificationDataHK2,
                        'semester2'
                    );

                    $result['yearly']['classification'] = $this->classifyStudent(
                        $result['yearly']['average'],
                        $classificationDataCN,
                        'yearly'
                    );
                    $studentResults[$student->id] = $result;
                }
            }
        }

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
        if ($score == 0 && $textValue === null) {
            return '-';
        }

        if ($isSpecial) {
            if ($textValue !== null) {
                return $textValue;
            }
            return $score >= 5 ? 'Đạt' : 'Chưa đạt';
        }
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
                $semesterType = ($semester === 'yearly') ? 'yearly' : 'semester';

                $result[$semester]['classification'] = $this->classifyStudent($average, $result[$semester]['subjects'],$semesterType
                );
            }
        }
    }

        public function adminViewAllGrades(Request $request)
        {
            // Kiểm tra quyền admin
            if (!Auth::user()->isSchoolAdmin()) {
                abort(403, 'Bạn không có quyền truy cập chức năng này');
            }

            $schoolId = Auth::user()->school_id;

            // Lấy dữ liệu năm học
            $academicYears = AcademicYear::where('school_id', $schoolId)
                ->orderBy('start_date', 'desc')
                ->get();

            $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            $selectedAcademicYearId = $request->input('academic_year_id', $currentAcademicYear->id ?? $academicYears->first()->id ?? null);

            // Lấy dữ liệu học kỳ
            $semesters = Semester::where('academic_year_id', $selectedAcademicYearId)
                ->where('school_id', $schoolId)
                ->orderBy('start_date')
                ->get();

            $semester1 = $semesters->first();
            $semester2 = $semesters->slice(1)->first();

            // Thêm option "Cả năm"
            $semesters = $semesters->push((object)[
                'id' => 0,
                'name' => 'Cả năm',
                'academic_year_id' => $selectedAcademicYearId
            ]);

            // Lấy danh sách lớp
            $classes = ClassModel::where('academic_year_id', $selectedAcademicYearId)
                ->where('school_id', $schoolId)
                ->with('gradeLevel')
                ->orderBy('grade_level_id')
                ->orderBy('name')
                ->get();

            $selectedClassId = $request->input('class_id');
            $selectedSemesterId = $request->input('semester_id');

            // Khởi tạo biến
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
                $students = User::whereHas('studentClasses', function ($query) use ($selectedClassId, $selectedAcademicYearId) {
                    $query->where('student_classes.class_id', $selectedClassId)
                        ->where('student_classes.academic_year_id', $selectedAcademicYearId);
                })->orderBy('full_name')->get();

                $subjects = Subject::where('school_id', $schoolId)->get();

                if ($students->isNotEmpty()) {
                    if ($selectedSemesterId == 0) {
                        // Xử lý xem điểm cả năm
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
                            $totalYearlyScoreNumeric = 0;
                            $numericSubjectCount = 0;

                            $result = [
                                'semester1' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => ''],
                                'semester2' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => ''],
                                'yearly' => ['subjects' => [], 'display_subjects' => [], 'average' => null, 'classification' => '']
                            ];

                            foreach ($subjects as $subject) {
                                $isSpecial = in_array($subject->name, $specialSubjects);

                                // Lấy điểm học kỳ 1
                                $semester1Grade = $finalGrades[$student->id][$semester1->id][$subject->id][0] ?? null;
                                $semester1Score = $semester1Grade ? ($semester1Grade->score ?? 0) : 0;
                                $semester1Text = $semester1Grade ? ($semester1Grade->text_value ?? null) : null;

                                // Lấy điểm học kỳ 2
                                $semester2Grade = $finalGrades[$student->id][$semester2->id][$subject->id][0] ?? null;
                                $semester2Score = $semester2Grade ? ($semester2Grade->score ?? 0) : 0;
                                $semester2Text = $semester2Grade ? ($semester2Grade->text_value ?? null) : null;

                                if ($isSpecial) {
                                    // Xử lý môn đặc biệt
                                    $result1 = $this->formatGradeDisplay($semester1Score, $semester1Text, true);
                                    $result2 = $this->formatGradeDisplay($semester2Score, $semester2Text, true);

                                    $yearlyResult = $result2;

                                    $yearlyPassStatus = ($result2 === 'Đạt' || $result2 === 'Đ' || $result2 === 'D') ? 'Đạt' : 'Chưa đạt';

                                    $grades[$student->id][$subject->id] = [
                                        'semester1_avg' => $result1,
                                        'semester2_avg' => $result2,
                                        'average' => $yearlyResult,
                                        'semester1_text' => $result1,
                                        'semester2_text' => $result2,
                                        'yearly_result' => $yearlyResult,
                                        'yearly_pass_status' => $yearlyPassStatus,
                                        'is_special' => true,
                                        'name' => $subject->name
                                    ];

                                    // Lưu vào result để tính xếp loại
                                    $result['yearly']['subjects'][$subject->id] = $yearlyPassStatus;
                                    $result['yearly']['special_subjects'][$subject->id] = $yearlyPassStatus;
                                } else {
                                    // Xử lý môn thường
                                    $yearlyAvg = ($semester1Grade && $semester2Grade)
                                        ? round(($semester1Score + $semester2Score * 2) / 3, 1)
                                        : 0;

                                    $grades[$student->id][$subject->id] = [
                                        // Hiển thị '-' nếu điểm 0, nhưng vẫn tính 0 vào trung bình
                                        'semester1_avg' => $semester1Score > 0 ? $semester1Score : '-',
                                        'semester2_avg' => $semester2Score > 0 ? $semester2Score : '-',
                                        'average' => $yearlyAvg > 0 ? $yearlyAvg : '-',
                                        'is_special' => false,
                                        'name' => $subject->name
                                    ];

                                    $totalYearlyScoreNumeric += $yearlyAvg;
                                    $numericSubjectCount++;
                                }

                                // Thêm điểm vào kết quả để tính trung bình
                                $result['semester1']['subjects'][$subject->id] = $semester1Score;
                                $result['semester2']['subjects'][$subject->id] = $semester2Score;
                                $result['yearly']['subjects'][$subject->id] = $isSpecial
                                    ? ($semester2Score >= 5 ? $semester2Score : 0)
                                    : $yearlyAvg;
                            }

                            $yearlyAverage = $numericSubjectCount > 0
                                ? round($totalYearlyScoreNumeric / $numericSubjectCount, 1)
                                : null;

                            // Lưu kết quả
                            $grades[$student->id]['yearly_average'] = $yearlyAverage;
                            $grades[$student->id]['classification'] = $this->classifyStudent(
                                $yearlyAverage, // Dùng $yearlyAverage đã được tính chỉ cho môn điểm số
                                $grades[$student->id],
                                'yearly'
                            );
                        }
                    } else {
                        // Xử lý xem điểm theo học kỳ
                        $allGrades = Grade::where('class_id', $selectedClassId)
                            ->where('semester_id', $selectedSemesterId)
                            ->where('academic_year_id', $selectedAcademicYearId)
                            ->get()
                            ->groupBy(['student_id', 'subject_id', 'test_type']);

                        $specialSubjects = ['Giáo dục quốc phòng và an ninh', 'Giáo dục thể chất', 'Nghệ thuật'];

                        foreach ($students as $student) {
                            $totalScore = 0;
                            $subjectCount = 0;
                            $studentGrades = [];

//                            foreach ($subjects as $subject) {
//                                $subjectGrades = $allGrades[$student->id][$subject->id] ?? [];
//                                $isSpecial = in_array($subject->name, $specialSubjects);
//
//                                $gradeData = [
//                                    'fifteen_minutes' => [
//                                        $subjectGrades['fifteen_minutes'][0] ?? null,
//                                        $subjectGrades['fifteen_minutes'][1] ?? null,
//                                        $subjectGrades['fifteen_minutes'][2] ?? null,
//                                    ],
//                                    'one_period' => $subjectGrades['one_period'][0] ?? null,
//                                    'semester' => $subjectGrades['semester'][0] ?? null,
//                                    'is_special' => $isSpecial,
//                                    'name' => $subject->name
//                                ];
//
//                                if ($isSpecial) {
//                                    $specialResult = $this->calculateSpecialSubjectResult($subjectGrades);
//                                    $gradeData['display_average'] = $specialResult['result'];
//                                    $gradeData['average'] = null;
//                                } else {
//                                    $subjectAverage = $this->calculateSubjectAverage($gradeData);
//                                    $gradeData['average'] = round($subjectAverage, 1);
//                                    $gradeData['display_average'] = round($subjectAverage, 1);
//
//                                    $totalScore += $subjectAverage;
//                                    $subjectCount++;
//                                }
//
//                                $studentGrades[$subject->id] = $gradeData;
//                            }

                            foreach ($students as $student) {
                                $totalScore = 0;
                                $subjectCount = 0;
                                $studentGrades = [];

                                foreach ($subjects as $subject) {
                                    $subjectGrades = $allGrades[$student->id][$subject->id] ?? [];
                                    $isSpecial = in_array($subject->name, $specialSubjects);

                                    // Lấy điểm trung bình đã tính sẵn
                                    $finalGrade = $subjectGrades['final'][0] ?? null;
                                    $subjectAverage = $finalGrade ? $finalGrade->score : null;

                                    $gradeData = [
                                        'fifteen_minutes' => [
                                            $subjectGrades['fifteen_minutes'][0] ?? null,
                                            $subjectGrades['fifteen_minutes'][1] ?? null,
                                            $subjectGrades['fifteen_minutes'][2] ?? null,
                                        ],
                                        'one_period' => $subjectGrades['one_period'][0] ?? null,
                                        'semester' => $subjectGrades['semester'][0] ?? null,
                                        'is_special' => $isSpecial,
                                        'name' => $subject->name,
                                        'average' => $subjectAverage,
                                        'display_average' => $subjectAverage ?? '-'
                                    ];

                                    if ($isSpecial) {
                                        // Xử lý môn đặc biệt (giữ nguyên)
                                        $specialResult = $this->calculateSpecialSubjectResult($subjectGrades);
                                        $gradeData['display_average'] = $specialResult['result'];
                                    } elseif ($subjectAverage !== null) {
                                        $totalScore += $subjectAverage;
                                        $subjectCount++;
                                    }

                                    $studentGrades[$subject->id] = $gradeData;
                                }

                                $semesterAverage = $subjectCount > 0 ? round($totalScore / $subjectCount, 1) : null;

                                $grades[$student->id] = $studentGrades;
                                $grades[$student->id]['semester_average'] = $semesterAverage;

                                // Xác định loại học kỳ để xếp loại
                                $semesterType = ($selectedSemesterId == $semester1->id) ? 'semester1' : 'semester2';
                                $grades[$student->id]['classification'] = $this->classifyStudent(
                                    $semesterAverage,
                                    $grades[$student->id],
                                    $semesterType
                                );
                            }
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

    public function classifyStudent($averageScore, $subjectScores, $semesterType = 'semester')
    {
        if (!is_numeric($averageScore)) {
            return 'Chưa đạt';
        }

        $specialSubjects = [
            'Giáo dục quốc phòng và an ninh',
            'Giáo dục thể chất',
            'Nghệ thuật'
        ];

        $stats = [
            'special_not_passed' => 0,
            'regular_above8' => 0,
            'regular_above6_5' => 0,
            'regular_above5' => 0,
            'has_below3_5' => false,
            'total_regular' => 0,
            'counted_regular' => 0
        ];

        foreach ($subjectScores as $subjectId => $subjectData) {
            if (!is_array($subjectData)) {
                continue;
            }

            $isSpecial = in_array($subjectData['name'] ?? '', $specialSubjects);

            if ($isSpecial) {
                // Xử lý môn đặc biệt
                $result = ($semesterType === 'yearly')
                    ? ($subjectData['yearly_result'] ?? $subjectData['semester2_text'] ?? 'Chưa đạt')
                    : ($subjectData['display_average'] ?? 'Chưa đạt');

                // Xử lý trường hợp không nhập (để "-")
                if ($result === '-' || $result === '') {
                    $result = 'Chưa đạt';
                }

                $normalizedResult = is_string($result) ? mb_strtolower(trim($result)) : $result;
                $isPassed = in_array($normalizedResult, ['đạt', 'đ', 'd']);

                if (!$isPassed) {
                    $stats['special_not_passed']++;
                    error_log("Môn đặc biệt chưa đạt: " . ($subjectData['name'] ?? '') . " - Kết quả: " . $result);
                }
            } else {
                // Xử lý môn thường
                $score = ($semesterType === 'yearly')
                    ? ($subjectData['average'] ?? 0)
                    : ($subjectData['average'] ?? 0);

                // Xử lý trường hợp không nhập (để "-")
                if ($score === '-' || $score === '') {
                    $score = 0;
                }

                // Ép kiểu về số nếu cần
                $score = is_numeric($score) ? (float)$score : 0;

                if ($score > 0) {
                    $stats['total_regular']++;
                    $stats['counted_regular']++;

                    if ($score >= 8) $stats['regular_above8']++;
                    if ($score >= 6.5) $stats['regular_above6_5']++;
                    if ($score >= 5) $stats['regular_above5']++;
                    if ($score < 3.5) {
                        $stats['has_below3_5'] = true;
                        error_log("Môn dưới 3.5 điểm: " . ($subjectData['name'] ?? '') . " - Điểm: $score");
                    }
                } elseif ($score == 0) {
                    // Xử lý điểm 0 (bao gồm cả trường hợp không nhập)
                    $stats['has_below3_5'] = true;
                    error_log("Môn có điểm 0 hoặc không nhập: " . ($subjectData['name'] ?? ''));
                }
            }
        }

        // Phần xếp loại giữ nguyên như trước
        if ($semesterType === 'yearly') {
            // 1. Loại TỐT
            if ($stats['special_not_passed'] === 0 &&
                $averageScore >= 6.5 &&
                $stats['regular_above8'] >= 6 &&
                $stats['regular_above6_5'] === $stats['counted_regular'] &&
                !$stats['has_below3_5']) {
                return 'Tốt';
            }

            // 2. Loại KHÁ
            if ($stats['special_not_passed'] === 0 &&
                $averageScore >= 5.0 &&
                $stats['regular_above6_5'] >= 6 &&
                $stats['regular_above5'] === $stats['counted_regular'] &&
                !$stats['has_below3_5']) {
                return 'Khá';
            }

            // 3. Loại ĐẠT
            if ($stats['special_not_passed'] <= 1 &&
                $stats['regular_above5'] >= 6 &&
                !$stats['has_below3_5']) {
                return 'Đạt';
            }
        }
        // XẾP LOẠI HỌC KỲ
        else {
            // 1. Loại TỐT
            if ($stats['special_not_passed'] === 0 &&
                $averageScore >= 6.5 &&
                $stats['regular_above8'] >= 6 &&
                $stats['regular_above6_5'] == $stats['total_regular'] &&
                !$stats['has_below3_5']) {
                return 'Tốt';
            }

            // 2. Loại KHÁ
            if ($stats['special_not_passed'] === 0 &&
                $averageScore >= 5.0 &&
                $stats['regular_above6_5'] >= 6 &&
                $stats['regular_above5'] == $stats['total_regular'] &&
                !$stats['has_below3_5']) {
                return 'Khá';
            }

            // 3. Loại ĐẠT
            if ($stats['special_not_passed'] <= 1 &&
                $stats['regular_above5'] >= 6 &&
                !$stats['has_below3_5']) {
                return 'Đạt';
            }
        }

        return 'Chưa đạt';
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
