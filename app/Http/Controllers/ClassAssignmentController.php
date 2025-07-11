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

            $classes = ClassModel::where('grade_level_id', $gradeLevelId)
                ->where('academic_year_id', $academicYearId)
                ->where('school_id', $schoolId)
                ->orderBy('name')
                ->get();

            if ($classes->isEmpty()) {
                return back()->with('error', 'Không có lớp nào trong khối và năm học được chọn.')->withInput();
            }

            $unassignedStudents = User::where('role', User::ROLE_STUDENT)
                ->where('school_id', $schoolId)
                ->whereHas('studentGrades', function ($query) use ($gradeLevelId, $academicYearId) {
                    $query->where('grade_id', $gradeLevelId)
                        ->where('academic_year_id', $academicYearId);
                })
                ->whereDoesntHave('studentClasses', function ($query) use ($academicYearId) {
                    $query->where('student_classes.academic_year_id', $academicYearId);
                })
                ->when($isGrade10, function ($query) {
                    return $query->orderByDesc('entry_score');
                }, function ($query) {
                    return $query->orderBy('full_name');
                })
                ->get();

            if ($unassignedStudents->isEmpty()) {
                if ($unassignedStudents->isEmpty()) {
                    return back()->with('error', 'Không có học sinh nào cần phân lớp trong khối và năm học được chọn.')->withInput();
                }
            }

            if ($isGrade10) {
                $studentsWithoutEntryScore = $unassignedStudents->filter(function ($student) {
                    return $student->entry_score === null;
                });

                if ($studentsWithoutEntryScore->isNotEmpty()) {
                    $countWithoutScore = $studentsWithoutEntryScore->count();
                    $studentNames = $studentsWithoutEntryScore->pluck('full_name')->implode(', ');

                    return back()->with('error', "Có $countWithoutScore học sinh chưa có điểm đầu vào: $studentNames. Vui lòng cập nhật điểm đầu vào cho học sinh trước khi thực hiện phân lớp theo điểm số.")->withInput();
                }
            }
            $totalStudents = $unassignedStudents->count();
            $classCount = $classes->count();
            $studentsPerClass = $classCount > 0 ? ceil($totalStudents / $classCount) : 0;

            $assignmentDetails = [];

            $assignments = [];
            $now = now();

            foreach ($unassignedStudents as $index => $student) {
                $classIndex = floor($index / $studentsPerClass);
                if ($classIndex >= $classCount) {
                    $classIndex = $classCount - 1;
                }

                $selectedClass = $classes[$classIndex];

                $assignments[] = [
                    'user_id' => $student->id,
                    'class_id' => $selectedClass->id,
                    'academic_year_id' => $academicYearId,
                    'created_at' => $now,
                    'updated_at' => $now
                ];

                $assignmentDetails[$selectedClass->id][] = [
                    'name' => $student->full_name,
                    'score' => $isGrade10 ? $student->entry_score : null
                ];
            }

            StudentClass::insert($assignments);
            DB::commit();
            $message = "Đã phân công $totalStudents học sinh ";

            return redirect()
                ->route('class_assignments.index', ['academic_year_id' => $academicYearId])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi phân công tự động: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi phân công tự động: ' . $e->getMessage());
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
        if (auth()->user()->role !== 'school_admin' && $student->school_id !== auth()->user()->school_id) {
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

        $student_ids = $request->input('student_ids');
        $current_class_id = $request->input('current_class_id');
        $next_academic_year_id = $request->input('next_academic_year_id');

        $successCount = 0;
        $failCount = 0;
        $messages = [];

        DB::beginTransaction();
        try {
            $currentClass = ClassModel::with('gradeLevel')->findOrFail($current_class_id);
            $currentAcademicYearId = $currentClass->academic_year_id;
            $nextAcademicYear = AcademicYear::findOrFail($next_academic_year_id);

            foreach ($student_ids as $student_id) {
                $user = User::find($student_id);

                if (!$user) {
                    $failCount++;
                    $messages[] = "Học sinh ID {$student_id} không tồn tại.";
                    continue;
                }

                $hasAnyGrades = Grade::where('student_id', $user->id)
                    ->where('academic_year_id', $currentAcademicYearId)
                    ->exists();

                if (!$hasAnyGrades) {
                    $newClass = $this->getRetainedClass($currentClass, $nextAcademicYear);
                    if ($newClass) {
                        StudentClass::updateOrCreate(
                            ['user_id' => $user->id, 'academic_year_id' => $nextAcademicYear->id],
                            ['class_id' => $newClass->id]
                        );
                        $successCount++;
                        $messages[] = "Học sinh {$user->full_name} ({$user->id}) không có điểm nào và sẽ ở lại lớp {$newClass->name}.";
                    } else {
                        $failCount++;
                        $messages[] = "Không thể xác định lớp ở lại cho học sinh {$user->full_name} ({$user->id}) không có điểm.";
                    }
                    continue;
                }

                $classification = $this->calculateYearlyClassification($user->id, $currentAcademicYearId);
                $newClass = null;
                $promotionStatus = '';


                if ($classification === null) {
                    $messages[] = "Không đủ dữ liệu để xếp loại cho học sinh {$user->full_name} ({$user->id}). Học sinh sẽ ở lại lớp.";
                    $newClass = $this->getRetainedClass($currentClass, $nextAcademicYear);
                    $promotionStatus = 'Ở lại lớp (không đủ dữ liệu)';
                }
                elseif (in_array($classification, ['Đạt', 'Khá', 'Tốt'])) {
                    $newClass = $this->getPromotedClass($currentClass, $nextAcademicYear);
                    if ($newClass) {
                        $promotionStatus = 'Lên lớp (' . $classification . ')';
                    } else {
                        $messages[] = "Không tìm thấy lớp để chuyển lên. Học sinh sẽ ở lại lớp.";
                        $newClass = $this->getRetainedClass($currentClass, $nextAcademicYear);
                        $promotionStatus = 'Ở lại lớp (không tìm thấy lớp lên)';
                    }
                } else {
                    $newClass = $this->getRetainedClass($currentClass, $nextAcademicYear);
                    $promotionStatus = 'Ở lại lớp (' . $classification . ')';
                }

                if (!$newClass) {
                    $failCount++;
                    $messages[] = "Không thể xác định lớp cho học sinh {$user->full_name} ({$user->id}).";
                    continue;
                }

                StudentClass::updateOrCreate(
                    ['user_id' => $user->id, 'academic_year_id' => $nextAcademicYear->id],
                    ['class_id' => $newClass->id]
                );

                $successCount++;
                $messages[] = "Học sinh {$user->full_name} ({$user->id}) đã được xử lý: {$promotionStatus} vào lớp {$newClass->name}.";
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Đã xử lý {$successCount} học sinh thành công, {$failCount} thất bại.",
                'details' => $messages
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : null
            ], 500);
        }
    }

    protected function calculateYearlyClassification($studentId, $academicYearId)
    {

        $grades = Grade::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('test_type', 'final')
            ->with(['subject', 'semester'])
            ->get()
            ->groupBy(['semester_id', 'subject_id']);

        if ($grades->isEmpty()) {
            return null;
        }


        $semesters = Semester::where('academic_year_id', $academicYearId)
            ->orderBy('start_date')
            ->get();

        $semester1 = $semesters->first();
        $semester2 = $semesters->slice(1)->first();

        if (!$semester1 || !$semester2) {
            return null;
        }

        $subjectAverages = [];
        $specialSubjects = ['Giáo dục quốc phòng và an ninh', 'Giáo dục thể chất', 'Nghệ thuật'];

        $subjects = Subject::where('school_id', auth()->user()->school_id)->get();

        foreach ($subjects as $subject) {
            $isSpecial = in_array($subject->name, $specialSubjects);

            $grade1 = $grades[$semester1->id][$subject->id][0] ?? null;
            $grade2 = $grades[$semester2->id][$subject->id][0] ?? null;

            if ($grade1 && $grade2) {
                if ($isSpecial) {
                    $subjectAverages[$subject->id] = [
                        'value' => $grade2->text_value ?? ($grade2->score >= 5 ? 'Đạt' : 'Chưa đạt'),
                        'is_special' => true,
                        'name' => $subject->name
                    ];
                } else {
                    $subjectAverages[$subject->id] = [
                        'value' => round(($grade1->score + $grade2->score * 2) / 3, 1),
                        'is_special' => false,
                        'name' => $subject->name
                    ];
                }
            }
        }

        $total = 0;
        $count = 0;
        foreach ($subjectAverages as $subjectAverage) {
            if (!$subjectAverage['is_special'] && is_numeric($subjectAverage['value'])) {
                $total += $subjectAverage['value'];
                $count++;
            }
        }

        $yearlyAverage = $count > 0 ? $total / $count : 0;


        return $this->classifyStudentPerformance($yearlyAverage, $subjectAverages);
    }

    protected function classifyStudentPerformance($averageScore, $subjectAverages)
    {
        if (!is_numeric($averageScore)) {
            return 'Chưa đạt';
        }

        $specialSubjects = ['Giáo dục quốc phòng và an ninh', 'Giáo dục thể chất', 'Nghệ thuật'];

        $specialNotPassed = 0;
        $countAbove8 = 0;
        $countAbove6_5 = 0;
        $countAbove5 = 0;
        $hasBelow3_5 = false;
        $totalRegularSubjects = 0;


        foreach ($subjectAverages as $data) {
            $isSpecial = in_array($data['name'], $specialSubjects);

            if ($isSpecial) {
                if ($data['value'] === 'Chưa đạt') {
                    $specialNotPassed++;
                }
            } else {
                $totalRegularSubjects++;
                if (is_numeric($data['value'])) {
                    if ($data['value'] >= 8.0) $countAbove8++;
                    if ($data['value'] >= 6.5) $countAbove6_5++;
                    if ($data['value'] >= 5.0) $countAbove5++;
                    if ($data['value'] < 3.5) $hasBelow3_5 = true;
                }
            }
        }

        if ($specialNotPassed === 0) {
            if ($countAbove8 >= 6 && $countAbove6_5 === $totalRegularSubjects) {
                return 'Tốt';
            }

            if ($countAbove6_5 >= 6 && $countAbove5 === $totalRegularSubjects) {
                return 'Khá';
            }
        }

        if ($specialNotPassed <= 1 && $countAbove5 >= 6 && !$hasBelow3_5) {
            return 'Đạt';
        }

        return 'Chưa đạt';
    }

    protected function getPromotedClass(ClassModel $currentClass, AcademicYear $nextAcademicYear)
    {

        $currentGradeNumber = $currentClass->gradeLevel->grade_number;
        $nextGradeNumber = $currentGradeNumber + 1;
        if ($currentGradeNumber == 12) {
            Log::info("Học sinh lớp 12 đã tốt nghiệp: {$currentClass->name}");
            return null;
        }

        $nextGradeLevel = GradeLevel::where('grade_number', $nextGradeNumber)
            ->where('school_id', $currentClass->school_id)
            ->first();

        if (!$nextGradeLevel) {
            Log::warning("Không tìm thấy GradeLevel cho khối {$nextGradeNumber} trong trường {$currentClass->school_id}.");
            return null;
        }

        $targetClassName = preg_replace('/\d+/', $nextGradeNumber, $currentClass->name);

        $promotedClass = ClassModel::where('academic_year_id', $nextAcademicYear->id)
            ->where('grade_level_id', $nextGradeLevel->id)
            ->where('name', $targetClassName)
            ->where('school_id', $currentClass->school_id)
            ->first();

        if (!$promotedClass) {
            $promotedClass = ClassModel::where('academic_year_id', $nextAcademicYear->id)
                ->where('grade_level_id', $nextGradeLevel->id)
                ->where('school_id', $currentClass->school_id)
                ->first();
        }

        if (!$promotedClass) {
            Log::warning("Không tìm thấy lớp phù hợp trong khối {$nextGradeNumber} năm học {$nextAcademicYear->year}.");
        }

        return $promotedClass;
    }

    protected function getRetainedClass(ClassModel $currentClass, AcademicYear $nextAcademicYear)
    {
        $retainedClass = ClassModel::where('academic_year_id', $nextAcademicYear->id)
            ->where('grade_level_id', $currentClass->grade_level_id)
            ->where('name', $currentClass->name)
            ->where('school_id', $currentClass->school_id)
            ->first();

        if (!$retainedClass) {
            $retainedClass = ClassModel::where('academic_year_id', $nextAcademicYear->id)
                ->where('grade_level_id', $currentClass->grade_level_id)
                ->where('school_id', $currentClass->school_id)
                ->first();
        }

        if (!$retainedClass) {
            Log::warning("Không tìm thấy lớp ở lại phù hợp trong khối {$currentClass->gradeLevel->grade_number} năm học {$nextAcademicYear->year}.");
        }

        return $retainedClass;
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
