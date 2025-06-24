<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\GradeLevel;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    // Hiển thị form phân công tự động
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

                    // Lấy năm học hiện tại
                    $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->first();

                    if (!$currentAcademicYear) {
                        // Nếu không có năm học hiện tại, coi như năm học nào cũng hợp lệ, hoặc bạn có thể thêm logic cụ thể
                        return;
                    }

                    // Kiểm tra năm học: hiện tại, sắp tới hoặc chưa kết thúc
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

            // Kiểm tra 4a: Không có lớp học nào thuộc khối và năm học được chọn
            if ($classes->isEmpty()) {
                // Thay vì throw ValidationException, trả về back() với flash error
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

            // Kiểm tra 4b: Không có học sinh nào chưa được phân lớp
            if ($unassignedStudents->isEmpty()) {
                if ($unassignedStudents->isEmpty()) {
                    return back()->with('error', 'Không có học sinh nào cần phân lớp trong khối và năm học được chọn.')->withInput();
                }
            }

            // Kiểm tra 4c: Một số học sinh không có điểm đầu vào (chỉ áp dụng cho khối 10)
            if ($isGrade10) {
                $studentsWithoutEntryScore = $unassignedStudents->filter(function ($student) {
                    return $student->entry_score === null;
                });

                if ($studentsWithoutEntryScore->isNotEmpty()) {
                    $countWithoutScore = $studentsWithoutEntryScore->count();
                    $studentNames = $studentsWithoutEntryScore->pluck('full_name')->implode(', ');

                    // Thay vì throw ValidationException, trả về back() với flash error
                    return back()->with('error', "Có $countWithoutScore học sinh chưa có điểm đầu vào: $studentNames. Vui lòng cập nhật điểm đầu vào cho học sinh trước khi thực hiện phân lớp theo điểm số.")->withInput();
                }
            }
            $totalStudents = $unassignedStudents->count();
            $classCount = $classes->count();
            $studentsPerClass = $classCount > 0 ? ceil($totalStudents / $classCount) : 0;

            $assignmentDetails = [];

            // Chuẩn bị dữ liệu để insert hàng loạt
            $assignments = [];
            $now = now();

            foreach ($unassignedStudents as $index => $student) {
                // Xác định lớp dựa trên thứ tự
                $classIndex = floor($index / $studentsPerClass);
                // Đảm bảo không vượt quá số lớp
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
            // Thực hiện insert hàng loạt
            StudentClass::insert($assignments);

            DB::commit();

            // Tạo thông báo chi tiết
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

    // Hiển thị danh sách học sinh trong lớp
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
    // Chuyển học sinh sang lớp khác
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
            // Xóa khỏi lớp cũ
            StudentClass::where('user_id', $student->id)
                ->where('class_id', $request->current_class_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->delete();

            // Thêm vào lớp mới
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
            $currentClass = ClassModel::findOrFail($current_class_id);
            $currentAcademicYearId = $currentClass->academic_year_id;
            $nextAcademicYear = AcademicYear::findOrFail($next_academic_year_id);

            foreach ($student_ids as $student_id) {
                $user = User::find($student_id);

                if (!$user) {
                    $failCount++;
                    $messages[] = "Học sinh ID {$student_id} không tồn tại.";
                    continue;
                }

                $averageGrade = $user->getAverageOverallGradeForAcademicYear($currentAcademicYearId);
                $newClass = null; // Khởi tạo biến $newClass
                $promotionStatus = '';

                if ($averageGrade === null) {
                    $messages[] = "Không tìm thấy điểm trung bình cả năm cho học sinh {$user->full_name} ({$user->id}) trong năm học hiện tại. Học sinh sẽ ở lại lớp.";
                    $newClass = $this->getRetainedClass($currentClass, $nextAcademicYear);
                    $promotionStatus = 'Ở lại lớp (không có điểm)';
                } elseif ($averageGrade >= 5.0) {
                    try {
                        $newClass = $this->getPromotedClass($currentClass, $nextAcademicYear);
                        $promotionStatus = 'Lên lớp';
                    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                        $messages[] = "Không tìm thấy lớp để chuyển lên cho học sinh {$user->full_name} ({$user->id}). Học sinh sẽ ở lại lớp.";
                        $newClass = $this->getRetainedClass($currentClass, $nextAcademicYear);
                        $promotionStatus = 'Ở lại lớp (không tìm thấy lớp lên)';
                    }
                } else {
                    $newClass = $this->getRetainedClass($currentClass, $nextAcademicYear);
                    $promotionStatus = 'Ở lại lớp';
                }

                StudentClass::create([
                    'user_id' => $user->id,
                    'class_id' => $newClass->id,
                    'academic_year_id' => $nextAcademicYear->id,
                    // created_at và updated_at sẽ tự động được thêm bởi Eloquent
                ]);

                $successCount++;
                $messages[] = "Học sinh {$user->full_name} ({$user->id}) đã được chuyển: {$promotionStatus} vào lớp {$newClass->name} ({$nextAcademicYear->year}).";
            }

            DB::commit();
            return response()->json([
                'message' => "Đã xử lý chuyển lớp cho {$successCount} học sinh thành công, {$failCount} thất bại. Chi tiết: " . implode('; ', $messages),
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi chuyển lớp hàng loạt: ' . $e->getMessage() . ' - Stack: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Đã xảy ra lỗi hệ thống khi chuyển lớp: ' . $e->getMessage()], 500);
        }
    }

    // Hàm helper để lấy lớp lên khối
    private function getPromotedClass(ClassModel $currentClass, AcademicYear $nextAcademicYear)
    {
        // Lấy cấp độ khối hiện tại
        $currentGradeNumber = $currentClass->gradeLevel->grade_number;
        $nextGradeNumber = $currentGradeNumber + 1;

        if ($nextGradeNumber > 12) { // Giả sử 12 là khối cuối cùng
            return null; // Hoặc ném một Exception tùy chỉnh để báo là học sinh đã tốt nghiệp
        }
        $nextGradeLevel = GradeLevel::where('grade_number', $nextGradeNumber)
            ->where('school_id', $currentClass->school_id)
            ->first(); // <-- Thay firstOrFail() bằng first()

        if (!$nextGradeLevel) {
            Log::warning("Không tìm thấy GradeLevel cho khối {$nextGradeNumber} trong trường {$currentClass->school_id}.");
            return null;
        }

        // Cố gắng tìm lớp có tên tương ứng (ví dụ: 10A1 -> 11A1)
        $targetClassName = str_replace(
            (string)$currentGradeNumber,
            (string)$nextGradeNumber,
            $currentClass->name
        );

        $promotedClass = ClassModel::where('academic_year_id', $nextAcademicYear->id)
            ->where('grade_level_id', $nextGradeLevel->id)
            ->where('name', $targetClassName)
            ->where('school_id', $currentClass->school_id)
            ->first(); // <-- Thay firstOrFail() bằng first()

        if (!$promotedClass) {
            Log::warning("Không tìm thấy lớp '{$targetClassName}' trong năm học {$nextAcademicYear->year} và khối {$nextGradeNumber}.");
            return null;
        }

        return $promotedClass;
    }

    // Hàm helper để lấy lớp ở lại
    private function getRetainedClass(ClassModel $currentClass, AcademicYear $nextAcademicYear)
    {
        $retainedClass = ClassModel::where('academic_year_id', $nextAcademicYear->id)
            ->where('grade_level_id', $currentClass->grade_level_id)
            ->where('name', $currentClass->name)
            ->where('school_id', $currentClass->school_id)
            ->first(); // <-- Thay firstOrFail() bằng first()

        if (!$retainedClass) {
            Log::warning("Không tìm thấy lớp ở lại '{$currentClass->name}' trong năm học {$nextAcademicYear->year}.");
            return null;
        }

        return $retainedClass;
    }

}
