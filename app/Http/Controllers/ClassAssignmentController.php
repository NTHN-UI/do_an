<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClassAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'desc')
            ->get();

        $selectedAcademicYear = $request->input('academic_year_id');

        $classes = ClassModel::withCount(['students' => function ($query) use ($selectedAcademicYear) {
            $query->where('student_classes.academic_year_id', $selectedAcademicYear);
        }])
            ->with(['gradeLevel', 'homeroomTeacher'])
            ->where('school_id', auth()->user()->school_id)
            ->when($selectedAcademicYear, function ($query) use ($selectedAcademicYear) {
                return $query->where('academic_year_id', $selectedAcademicYear);
            })
            ->orderBy('name')
            ->get();

        return view('class_assignments.index', [
            'classes' => $classes,
            'academicYears' => $academicYears,
            'selectedAcademicYear' => $selectedAcademicYear
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function showAutoAssignmentForm()
    {
        $gradeLevels = GradeLevel::where('school_id', auth()->user()->school_id)->get();
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'desc')
            ->get();

        $currentAcademicYear = AcademicYear::where('school_id', auth()->user()->school_id)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        return view('class_assignments.auto_assign', compact('gradeLevels', 'academicYears', 'currentAcademicYear'));
    }

    public function autoAssign(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,id,school_id,' . $schoolId,
            'academic_year_id' => [
                'required',
                'exists:academic_years,id,school_id,' . $schoolId,
                function ($attribute, $value, $fail) use ($schoolId) {
                    $academicYear = AcademicYear::where('id', $value)
                        ->where('school_id', $schoolId)
                        ->first();

                    if (!$academicYear) {
                        return $fail('Năm học không hợp lệ.');
                    }
                    $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->first();

                    if (!$currentAcademicYear) {
                        return;
                    }

                    if ($academicYear->end_date < now() && $academicYear->id !== $currentAcademicYear->id) {
                        $fail('Năm học đã chọn đã kết thúc và không phải là năm học hiện tại.');
                    }
                },
            ],
        ], [
            'grade_level_id.required' => 'Khối học không được để trống.',
            'grade_level_id.exists' => 'Khối học không hợp lệ.',
            'academic_year_id.required' => 'Năm học không được để trống.',
            'academic_year_id.exists' => 'Năm học không hợp lệ.',
        ]);

        try {
            DB::beginTransaction();

            $gradeLevelId = $request->grade_level_id;
            $academicYearId = $request->academic_year_id;

            $gradeLevel = GradeLevel::find($gradeLevelId);
            $isGrade10 = $gradeLevel && $gradeLevel->grade_number == 10;

            // Chỉ cho phép phân lớp tự động với khối 10
            if (!$isGrade10) {
                return back()->with('error', 'Chức năng phân lớp tự động chỉ áp dụng cho khối 10')->withInput();
            }


            $classes = ClassModel::where('grade_level_id', $gradeLevelId)
                ->where('academic_year_id', $academicYearId)
                ->where('school_id', $schoolId)
                ->orderBy('name')
                ->get();

            if ($classes->isEmpty()) {
                return back()->with('error', 'Không có lớp nào trong khối và năm học được chọn')->withInput();
            }

            // Nhóm lớp theo khối đăng ký
            $groupedClasses = $classes->groupBy(function($class) {
                // Xử lý tên lớp dạng "Lớp 10A1", "Lớp 10A11",...
                if (preg_match('/^Lớp 10([A-Za-z]\d{1,2})$/', $class->name, $matches)) {
                    if (!isset($matches[1])) {
                        return 'invalid_format';
                    }

                    $blockCode = $matches[1];

                    // Phân biệt khối A (1 chữ số) và A1 (2 chữ số)
                    if (strlen($blockCode) == 2 && is_numeric(substr($blockCode, 1, 1))) {
                        return substr($blockCode, 0, 1); // A1 → A
                    }
                    return $blockCode;
                }
                return 'invalid_format';
            })->reject(function ($value, $key) {
                return $key === 'invalid_format';
            });

            if ($groupedClasses->isEmpty()) {
                $exampleClasses = collect(['Lớp 10A1', 'Lớp 10A11', 'Lớp 10B1', 'Lớp 10C1']);
                return back()->with('error', 'Không có lớp nào có tên đúng định dạng. Ví dụ: ' . $exampleClasses->implode(', '))->withInput();
            }

            // Lấy học sinh chưa phân lớp
            $unassignedStudents = User::where('role', User::ROLE_STUDENT)
                ->where('school_id', $schoolId)
                ->whereHas('studentGrades', function ($query) use ($gradeLevelId, $academicYearId) {
                    $query->where('grade_id', $gradeLevelId)
                        ->where('academic_year_id', $academicYearId);
                })
                ->whereDoesntHave('studentClasses', function ($query) use ($academicYearId) {
                    $query->where('student_classes.academic_year_id', $academicYearId);
                })
                ->orderBy('exam_block')
                ->orderBy('gender')
                ->orderByDesc('entry_score')
                ->get();

            if ($unassignedStudents->isEmpty()) {
                return back()->with('error', 'Không có học sinh nào cần phân lớp trong khối và năm học được chọn.')->withInput();
            }

            // Kiểm tra học sinh chưa có điểm đầu vào
            $studentsWithoutEntryScore = $unassignedStudents->filter(function ($student) {
                return $student->entry_score === null;
            });

            if ($studentsWithoutEntryScore->isNotEmpty()) {
                $countWithoutScore = $studentsWithoutEntryScore->count();
                $studentNames = $studentsWithoutEntryScore->pluck('full_name')->take(5)->implode(', ');
                $message = "Có $countWithoutScore học sinh chưa có điểm đầu vào";
                $message .= $countWithoutScore > 5 ? " (hiển thị 5 đầu tiên: $studentNames, ...)" : ": $studentNames";
                $message .= ". Vui lòng cập nhật điểm đầu vào cho học sinh trước khi thực hiện phân lớp theo điểm số.";

                return back()->with('error', $message)->withInput();
            }

            // Phân loại học sinh theo khối đăng ký với chuẩn hóa
            $studentsByBlockAndGender = $unassignedStudents->groupBy(function($student) {
                $block = strtoupper(preg_replace('/^(Khối\s*)?/i', '', $student->exam_block));

                // Chuẩn hóa khối đăng ký
                if ($block === 'A1' || $block === 'A-1') {
                    $block = 'A1';
                } else {
                    $block = preg_replace('/[^A-Z]/', '', $block);
                }

                return $block . '_' . $student->gender;
            });

            $assignmentDetails = [];
            $assignments = [];
            $now = now();
            $direction = 1; // 1 = tăng, -1 = giảm
            $classIndex = 0;

            foreach ($studentsByBlockAndGender as $blockGender => $students) {
                list($block, $gender) = explode('_', $blockGender);

                // Tìm lớp phù hợp với khối đăng ký
                $matchingClasses = $classes->filter(function($class) use ($block) {
                    if ($block === 'A') {
                        return preg_match('/^Lớp 10A\d$/', $class->name);
                    } else if ($block === 'A1') {
                        return preg_match('/^Lớp 10A\d{2}$/', $class->name);
                    } else if ($block === 'B') {
                        return preg_match('/^Lớp 10B\d$/', $class->name);
                    } else {
                        return preg_match('/^Lớp 10'.$block.'\d$/', $class->name);
                    }
                })->sortBy('name')->values();

                if ($matchingClasses->isEmpty()) {
                    continue;
                }

                // Sắp xếp lại classes để đảm bảo thứ tự
                $classCount = $matchingClasses->count();
                $students = $students->values();
                foreach ($students as $i => $student) {
                    if ($i % 2 === 0) {
                        // Chẵn: từ lớp đầu tiên đi lên
                        $classIndex = ($i / 2) % $classCount;
                    } else {
                        // Lẻ: từ lớp cuối đi xuống
                        $classIndex = $classCount - 1 - (($i - 1) / 2) % $classCount;
                    }

                    $selectedClass = $matchingClasses[$classIndex];


                    // Thêm vào danh sách phân lớp
                    $assignments[] = [
                        'user_id' => $student->id,
                        'class_id' => $selectedClass->id,
                        'academic_year_id' => $academicYearId,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];

                    $classIndex += $direction;

                    // Đảo chiều khi đến đầu/cuối
                    if ($classIndex >= $classCount || $classIndex < 0) {
                        $direction *= -1;
                        $classIndex += $direction;
                    }

                    $assignmentDetails[$selectedClass->id][] = [
                        'name' => $student->full_name,
                        'score' => $student->entry_score,
                        'block' => $student->exam_block
                    ];
                }
            }

            if (empty($assignments)) {
                return back()->with('error', 'Không có học sinh nào được phân lớp do không tìm thấy lớp phù hợp với khối đăng ký.')->withInput();
            }

            // Thực hiện phân lớp
            StudentClass::insert($assignments);
            DB::commit();

            $totalAssigned = count($assignments);
            $message = "Đã phân công thành công $totalAssigned học sinh khối 10 vào các lớp theo khối đăng ký";

            return redirect()
                ->route('class_assignments.index', ['academic_year_id' => $academicYearId])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi phân công tự động: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Đã xảy ra lỗi khi phân công tự động: ' . $e->getMessage())->withInput();
        }
    }
    public function showClassStudents(ClassModel $class)
    {
        if ($class->school_id !== auth()->user()->school_id) {
            abort(403, 'Không được phép truy cập lớp từ trường khác');
        }

        $students = $class->students()
            ->where('student_classes.academic_year_id', $class->academic_year_id)
            ->paginate(20);

        $academicYear = AcademicYear::find($class->academic_year_id);

        $yearParts = explode('-', $academicYear->year);
        $currentEndYear = trim($yearParts[1]);

        $nextYear = AcademicYear::where('year', 'like', $currentEndYear . '%')->first();

        $nextGrade = $class->gradeLevel->grade_number + 1;

        $nextGradeLevel = GradeLevel::where('grade_number', $nextGrade)->first();

        $nextGradeClasses = [];

        if ($nextYear && $nextGradeLevel) {
            $nextGradeClasses = ClassModel::where('academic_year_id', $nextYear->id)
                ->where('grade_level_id', $nextGradeLevel->id)
                ->where('school_id', auth()->user()->school_id)
                ->get();
        }
        $targetClasses = ClassModel::where('grade_level_id', $class->grade_level_id)
            ->where('academic_year_id', $academicYear->id)
            ->where('school_id', auth()->user()->school_id)
            ->where('id', '!=', $class->id)
            ->get();

        return view('class_assignments.class_students', compact(
            'class',
            'students',
            'academicYear',
            'nextYear',
            'nextGrade',
            'nextGradeClasses',
            'targetClasses'
        ));
    }
    public function changeClassStudent(Request $request, string $id)
    {
        $student = User::find($id);
        if (auth()->user()->role !== 'admin' && $student->school_id !== auth()->user()->school_id) {
            abort(403, 'Không được phép thao tác với học sinh từ trường khác');
        }

        $request->validate([
            'current_class_id' => 'required|exists:classes,id,school_id,' . auth()->user()->school_id,
            'new_class_id' => 'required|exists:classes,id,school_id,' . auth()->user()->school_id,
            'academic_year_id' => 'required|exists:academic_years,id,school_id,' . auth()->user()->school_id,
        ]);

        try {
            DB::beginTransaction();
            StudentClass::where('user_id', $student->id)
                ->where('class_id', $request->current_class_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->delete();

            StudentClass::create([
                'user_id' => $student->id,
                'class_id' => $request->new_class_id,
                'academic_year_id' => $request->academic_year_id,
            ]);

            DB::commit();

            return back()->with('success', 'Đã chuyển học sinh sang lớp mới.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Move student error: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi chuyển lớp: ' . $e->getMessage());
        }
    }

    public function advanceClassStudents(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
            'current_class_id' => 'required|exists:classes,id',
            'next_academic_year_id' => 'required|exists:academic_years,id',
        ]);

        DB::beginTransaction();
        try {
            $currentClass = ClassModel::with('gradeLevel')->findOrFail($request->current_class_id);
            $currentAcademicYearId = $currentClass->academic_year_id;
            $nextAcademicYear = AcademicYear::findOrFail($request->next_academic_year_id);

            // Lấy toàn bộ dữ liệu cần thiết trong 1 query
            $students = User::whereIn('id', $request->student_ids)
                ->with(['grades' => function($query) use ($currentAcademicYearId) {
                    $query->where('academic_year_id', $currentAcademicYearId)
                        ->where('test_type', 'final')
                        ->with(['subject', 'semester']);
                }])
                ->get();

            $semesters = Semester::where('academic_year_id', $currentAcademicYearId)
                ->orderBy('start_date')
                ->get();

            $results = [];
            $promotedCount = 0;
            $retainedCount = 0;
            $graduatedCount = 0;
            $failedCount = 0;

            foreach ($students as $student) {
                $yearlyData = $this->getStudentYearlyGrades($student->id, $currentAcademicYearId);

                $classification = $this->classifyStudent(
                    $yearlyData['average'],
                    $yearlyData['subjects'],
                    'yearly'
                );

                // Xử lý chuyển lớp
                $result = $this->processStudentTransfer(
                    $student,
                    $classification,
                    $currentClass,
                    $nextAcademicYear
                );
                // Đếm số lượng theo từng loại
                if ($result['status'] === 'failed') {
                    $failedCount++;
                } elseif ($result['new_class'] === 'Tốt nghiệp') {
                    $graduatedCount++;
                } elseif ($result['message'] === 'Được lên lớp') {
                    $promotedCount++;
                } else {
                    $retainedCount++;
                }

                $results[] = $result;
            }

            DB::commit();
            $successMessage = sprintf(
                "Thành công: %d học sinh lên lớp, %d học sinh ở lại, %d học sinh tốt nghiệp",
                $promotedCount,
                $retainedCount,
                $graduatedCount
            );

            // Thông báo lỗi nếu có
            $errorMessage = $failedCount > 0
                ? sprintf(" (%d học sinh gặp lỗi)", $failedCount)
                : '';

            return response()->json([
                'success' => $failedCount === 0,
                'message' => $successMessage . $errorMessage,
                'statistics' => [
                    'promoted' => $promotedCount,
                    'retained' => $retainedCount,
                    'graduated' => $graduatedCount,
                    'failed' => $failedCount,
                    'total' => count($students)
                ],
                'results' => $results
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function calculateStudentClassification($student, $semesters)
    {
        $yearlyData = $this->getStudentYearlyGrades($student->id, $semesters->first()->academic_year_id);

        return $this->classifyStudent(
            $yearlyData['average'],
            $yearlyData['subjects'],
            'yearly'
        );
    }
    protected function getStudentYearlyGrades($studentId, $academicYearId)
    {
        // Lấy thông tin học sinh và lớp hiện tại
        $student = User::find($studentId);
        $studentClass = StudentClass::where('user_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->with('class')
            ->first();

        if (!$student || !$studentClass) {
            return ['subjects' => [], 'average' => 0];
        }

        $subjects = Subject::where('school_id', $student->school_id)->get();

        $semesters = Semester::where('academic_year_id', $academicYearId)
            ->orderBy('start_date')
            ->get();

        $semester1 = $semesters->first();
        $semester2 = $semesters->slice(1)->first();

        if (!$semester1 || !$semester2) {
            return ['subjects' => [], 'average' => 0];
        }

        $finalGrades = Grade::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->whereIn('semester_id', [$semester1->id, $semester2->id])
            ->where('test_type', 'final')
            ->get();

        $groupedGrades = $finalGrades->groupBy(['semester_id', 'subject_id']);

        $subjectScores = [];
        $specialSubjects = ['Giáo dục quốc phòng và an ninh', 'Giáo dục thể chất', 'Nghệ thuật'];

        $totalScoreYearly = 0;
        $countYearly = 0;

        foreach ($subjects as $subject) {
            $isSpecial = in_array($subject->name, $specialSubjects);

            // Lấy điểm HK1 và HK2
            $semester1Grade = $groupedGrades[$semester1->id][$subject->id][0] ?? null;
            $semester2Grade = $groupedGrades[$semester2->id][$subject->id][0] ?? null;

            $semester1Score = $semester1Grade ? ($semester1Grade->score ?? 0) : 0;
            $semester2Score = $semester2Grade ? ($semester2Grade->score ?? 0) : 0;

            $semester1Text = $semester1Grade ? ($semester1Grade->text_value ?? null) : null;
            $semester2Text = $semester2Grade ? ($semester2Grade->text_value ?? null) : null;

            if ($isSpecial) {
                $yearlyResult = $semester2Grade ? ($semester2Grade->text_value ?? 'Chưa đạt') : 'Chưa đạt';

                $subjectScores[$subject->id] = [
                    'name' => $subject->name,
                    'yearly_result' => $yearlyResult,
                    'is_special' => true,
                ];

            } else {
                // Môn thường
                $yearlyAvg = ($semester1Grade && $semester2Grade)
                    ? round(($semester1Score + $semester2Score * 2) / 3, 1)
                    : 0;


                $subjectScores[$subject->id] = [
                    'name' => $subject->name,
                    'average' => $yearlyAvg,
                    'is_special' => false,
                ];

                // Tính tổng điểm cho các môn thường
                $totalScoreYearly += $yearlyAvg;
                $countYearly++;
            }
        }

        // Tính điểm trung bình cả năm
        $average = $countYearly > 0 ? round($totalScoreYearly / $countYearly, 1) : 0;

        return [
            'subjects' => $subjectScores,
            'average' => $average
        ];
    }

    protected function classifyStudent($averageScore, $subjectScores, $semesterType = 'semester')
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

        // Lặp qua các môn học để thống kê
        foreach ($subjectScores as $subjectId => $subjectData) {
            if (!is_array($subjectData)) {
                continue;
            }
            $isSpecial = $subjectData['is_special'] ?? false;

            if ($isSpecial) {
                // Xử lý môn đặc biệt
                $result = ($semesterType === 'yearly')
                    ? ($subjectData['yearly_result'] ?? 'Chưa đạt')
                    : ($subjectData['display_average'] ?? 'Chưa đạt');

                // Chuẩn hóa kết quả và kiểm tra trạng thái Đạt
                $normalizedResult = is_string($result) ? mb_strtolower(trim($result)) : $result;
                $isPassed = in_array($normalizedResult, ['đạt', 'đ', 'd']);

                if (!$isPassed) {
                    $stats['special_not_passed']++;
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

                // Ép kiểu về số
                $score = is_numeric($score) ? (float)$score : 0;

                // Đếm tổng số môn thường
                $stats['total_regular']++;

                // Phân loại điểm
                if ($score >= 8) $stats['regular_above8']++;
                if ($score >= 6.5) $stats['regular_above6_5']++;
                if ($score >= 5) $stats['regular_above5']++;

                // Kiểm tra điểm dưới 3.5 (bao gồm cả điểm 0)
                if ($score < 3.5) {
                    $stats['has_below3_5'] = true;
                }
            }
        }


        if ($semesterType === 'yearly') {
            if ($stats['special_not_passed'] === 0 &&
                $averageScore >= 6.5 &&
                $stats['regular_above8'] >= 6 &&
                $stats['regular_above6_5'] === $stats['total_regular'] &&
                !$stats['has_below3_5']) {
                return 'Tốt';
            }

            if ($stats['special_not_passed'] === 0 &&
                $averageScore >= 5.0 &&
                $stats['regular_above6_5'] >= 6 &&
                $stats['regular_above5'] === $stats['total_regular'] &&
                !$stats['has_below3_5']) {
                return 'Khá';
            }

            if ($stats['special_not_passed'] <= 1 &&
                $stats['regular_above5'] >= 6 &&
                !$stats['has_below3_5']) {
                return 'Đạt';
            }
        }
        else {
            if ($stats['special_not_passed'] === 0 &&
                $averageScore >= 6.5 &&
                $stats['regular_above8'] >= 6 &&
                $stats['regular_above6_5'] == $stats['total_regular'] &&
                !$stats['has_below3_5']) {
                return 'Tốt';
            }

            if ($stats['special_not_passed'] === 0 &&
                $averageScore >= 5.0 &&
                $stats['regular_above6_5'] >= 6 &&
                $stats['regular_above5'] == $stats['total_regular'] &&
                !$stats['has_below3_5']) {
                return 'Khá';
            }

            if ($stats['special_not_passed'] <= 1 &&
                $stats['regular_above5'] >= 6 &&
                !$stats['has_below3_5']) {
                return 'Đạt';
            }
        }

        return 'Chưa đạt';
    }

    protected function processStudentTransfer($student, $classification, $currentClass, $nextAcademicYear)
    {
        $result = [
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'current_class' => $currentClass->name,
            'classification' => $classification,
            'status' => 'success',
            'new_class' => null,
            'message' => ''

        ];

        // Tốt nghiệp nếu là lớp 12
        if ($currentClass->gradeLevel->grade_number == 12 && in_array($classification, ['Đạt', 'Khá', 'Tốt'])) {
            $result['new_class'] = 'Tốt nghiệp';
            $result['message'] = 'Đã hoàn thành chương trình lớp 12';
            return $result;
        }

        // Tìm lớp mới
        if (in_array($classification, ['Đạt', 'Khá', 'Tốt'])) {
            $newClass = $this->findPromotedClass($currentClass, $nextAcademicYear);
            $result['message'] = 'Được lên lớp';
        } else {
            $newClass = $this->findRetainedClass($currentClass, $nextAcademicYear);
            $result['message'] = 'Ở lại lớp';
        }

        if (!$newClass) {
            $result['status'] = 'failed';
            $result['message'] = 'Không tìm thấy lớp phù hợp';
            return $result;
        }

        // Cập nhật lớp
        StudentClass::updateOrCreate(
            ['user_id' => $student->id, 'academic_year_id' => $nextAcademicYear->id],
            ['class_id' => $newClass->id]
        );

        $result['new_class'] = $newClass->name;
        return $result;
    }


    protected function formatGradeDisplay($score, $textValue, $isSpecial)
    {
        if ($isSpecial) {
            if ($textValue) {
                return $textValue;
            }
            return ($score !== null && $score >= 5) ? 'Đạt' : 'Chưa đạt';
        } else {
            return $score !== null ? $score : '-';
        }
    }

    protected function findPromotedClass($currentClass, $nextAcademicYear)
    {
        $currentName = $currentClass->name; // "Lớp 10A1"

        // Tách tên lớp thành: [Lớp ][Khối][Chữ][Số] (ví dụ: "Lớp 10A11" → [10][A][11])
        if (!preg_match('/^(Lớp\s?)(\d+)([A-Za-z])(\d+)$/u', $currentName, $matches)) {
            Log::error("Tên lớp không đúng định dạng: {$currentName}");
            return null;
        }

        $prefix = $matches[1]; // "Lớp " hoặc "Lớp"
        $currentGrade = $matches[2]; // 10
        $classLetter = $matches[3];  // A
        $classNumber = $matches[4];  // 11

        $nextGradeNumber = (int)$currentGrade + 1;
        $expectedClassName = $prefix . $nextGradeNumber . $classLetter . $classNumber;

        // Tìm lớp đích
        $nextGradeLevel = GradeLevel::where('grade_number', $nextGradeNumber)
            ->where('school_id', $currentClass->school_id)
            ->first();

        if (!$nextGradeLevel) {
            Log::error("Không tìm thấy khối lớp {$nextGradeNumber}");
            return null;
        }

        // TÌM CHÍNH XÁC LỚP THEO TÊN
        $newClass = ClassModel::where('academic_year_id', $nextAcademicYear->id)
            ->where('grade_level_id', $nextGradeLevel->id)
            ->where('name', $expectedClassName)
            ->first();

        if (!$newClass) {
            Log::error("Không tìm thấy lớp {$expectedClassName} trong năm học mới");
            return null;
        }

        return $newClass;
    }

    protected function findRetainedClass($currentClass, $nextAcademicYear)
    {
        // Giữ nguyên tên lớp, chỉ thay năm học
        return ClassModel::where('academic_year_id', $nextAcademicYear->id)
            ->where('grade_level_id', $currentClass->grade_level_id)
            ->where('name', $currentClass->name)
            ->first();
    }
    private function getDirectStudentValidationRules(Request $request, ClassModel $class): array
    {
        return [
            'full_name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[\p{L}\s\-]+$/u'
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^(0[3|5|7|8|9])[0-9]{8,9}$/',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $cleanNumber = preg_replace('/[^0-9]/', '', $value);
                        if (!in_array(strlen($cleanNumber), [10, 11])) {
                            $fail('Số điện thoại phải có 10 hoặc 11 số');
                        }

                        if (User::where('phone', $cleanNumber)
                            ->where('school_id', auth()->user()->school_id)
                            ->exists()) {
                            $fail('Số điện thoại đã được sử dụng');
                        }
                    }
                }
            ],
            'gender' => [
                'required',
                'in:Nam,Nữ,Khác'
            ],
            'date_of_birth' => [
                'required',
                'date',
                'before_or_equal:' . now()->subYears(5)->format('Y-m-d')
            ],
            'address' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[\p{L}0-9\s\-\/,]+$/u'
            ],
            'guardian_name' => [
                'nullable',
                'string',
                'max:50'
            ],
            'guardian_phone' => [
                'nullable',
                'string',
                'regex:/^(0[3|5|7|8|9])[0-9]{8,9}$/'
            ],
            'guardian_email' => [
                'required',
                'email',
                Rule::unique('users', 'guardian_email')
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ]
        ];
    }

    private function getDirectStudentValidationMessages(): array
    {
        return [
            'full_name.required' => 'Họ và tên không được để trống',
            'full_name.max' => 'Họ và tên không được vượt quá 50 ký tự',
            'full_name.regex' => 'Họ và tên chỉ được chứa chữ cái, khoảng trắng và dấu gạch ngang',

            'phone.regex' => 'Số điện thoại phải bắt đầu bằng 03, 05, 07, 08 hoặc 09',

            'gender.required' => 'Vui lòng chọn giới tính',
            'gender.in' => 'Giới tính không hợp lệ',

            'date_of_birth.required' => 'Ngày sinh không được để trống',
            'date_of_birth.date' => 'Ngày sinh không hợp lệ',
            'date_of_birth.before_or_equal' => 'Học sinh phải từ 5 tuổi trở lên',

            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự',
            'address.regex' => 'Địa chỉ không được chứa ký tự đặc biệt',

            'guardian_name.max' => 'Tên người giám hộ không được vượt quá 50 ký tự',

            'guardian_phone.regex' => 'Số điện thoại người giám hộ không hợp lệ',

            'guardian_email.required' => 'Email phụ huynh không để trống',
            'guardian_email.email' => 'Email phụ huynh không hợp lệ',
            'guardian_email.unique' => 'Email phụ huynh đã được sử dụng',

        ];
    }

    public function storeDirectStudentToClass(Request $request, $classId)
    {
        $class = ClassModel::with('academicYear')->findOrFail($classId);



        if ($class->academicYear->end_date < now()) {
            return back()->with('error', 'Không thể thêm học sinh vào lớp thuộc năm học đã kết thúc')->withInput();
        }

        DB::beginTransaction();
        try {
            // Tạo học sinh mới
            $student = new User();
            $student->fill([
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'guardian_name' => $request->guardian_name,
                'guardian_email' => $request->guardian_email,
                'guardian_phone' => $request->guardian_phone,
                'role' => User::ROLE_STUDENT,
                'school_id' => auth()->user()->school_id,
                'is_active' => $request->has('is_active'),
                'password' => Hash::make('12345678'),
                'email' => $this->generateStudentEmail($request->full_name)
            ]);
            $student->save();

            // Thêm vào lớp học
            StudentClass::create([
                'user_id' => $student->id,
                'class_id' => $class->id,
                'academic_year_id' => $class->academic_year_id
            ]);

            // Thêm vào khối lớp
            $student->studentGrades()->attach($class->grade_level_id, [
                'academic_year_id' => $class->academic_year_id,
                'school_id' => auth()->user()->school_id
            ]);

            DB::commit();

            return redirect()->route('class_assignments.show', $class->id)
                ->with('success', 'Thêm học sinh vào lớp thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi thêm học sinh: '.$e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Lỗi khi thêm học sinh: '.$e->getMessage())->withInput();
        }
    }
    private function generateStudentEmail($fullName)
    {
        $school = School::find(auth()->user()->school_id);
        $schoolName = $school->name;

        // Xử lý tên trường để tạo domain
        $slug = Str::slug(mb_strtolower($schoolName));
        $slugParts = explode('-', $slug);
        $slugParts = array_slice($slugParts, 1, (count($slugParts) - 1)); // Bỏ phần đầu (thường là "trường")
        $schoolDomain = join('', $slugParts) . '.edu.vn';

        // Xử lý tên học sinh
        $nameParts = explode(' ', $fullName);
        $lastName = array_pop($nameParts);
        $lastName = mb_strtolower(Str::ascii($lastName)); // Chuyển về không dấu

        // Lấy các chữ cái đầu của các tên còn lại
        $firstLetters = '';
        foreach ($nameParts as $part) {
            $firstLetters .= mb_substr($part, 0, 1);
        }
        $firstLetters = mb_strtolower(Str::ascii($firstLetters)); // Chuyển về không dấu

        // Tạo email base
        $username = $lastName . '.' . $firstLetters;
        $email = $username . '@' . $schoolDomain;

        // Xử lý trường hợp trùng email
        $counter = 1;
        $originalEmail = $email;
        while (User::where('email', $email)->exists()) {
            $email = $username . $counter . '@' . $schoolDomain;
            $counter++;
        }

        return $email;
    }
    public function showAddDirectStudentForm($class)
    {
        $class = ClassModel::findOrFail($class);
        $academicYear = AcademicYear::current()->first();

        if (!$academicYear) {
            return redirect()
                ->route('class_assignments.show', $class->id)
                ->with('error', 'Hiện không trong thời gian năm học! Không thể thêm học sinh.');
        }

        if (now() < $academicYear->start_date || now() > $academicYear->end_date) {
            return redirect()
                ->route('class_assignments.show', $class->id)
                ->with('error', 'Năm học chưa bắt đầu hoặc đã kết thúc, không thể thêm học sinh mới');
        }

        return view('class_assignments.add_direct_student', [
            'class' => $class,
            'academicYear' => $academicYear,
        ]);
    }
}
