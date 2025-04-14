<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\GradeLevel;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $currentAcademicYear = AcademicYear::where('school_id', auth()->user()->school_id)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        $selectedAcademicYear = $request->input('academic_year_id', $currentAcademicYear?->id);

        $classes = ClassModel::withCount(['students' => function($query) use ($selectedAcademicYear) {
            $query->where('student_classes.academic_year_id', $selectedAcademicYear);
        }])
            ->when($selectedAcademicYear, function($query) use ($selectedAcademicYear) {
                return $query->where('academic_year_id', $selectedAcademicYear);
            })
            ->with(['gradeLevel', 'homeroomTeacher'])
            ->where('school_id', auth()->user()->school_id)
            ->get();

        return view('class_assignments.index', compact('classes', 'academicYears', 'selectedAcademicYear'));
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

    // Xử lý phân công tự động
    public function autoAssign(Request $request)
    {
        $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,id,school_id,'.auth()->user()->school_id,
            'academic_year_id' => 'required|exists:academic_years,id,school_id,'.auth()->user()->school_id,
            'max_students_per_class' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $gradeLevelId = $request->grade_level_id;
            $academicYearId = $request->academic_year_id;
            $maxStudents = $request->max_students_per_class;

            // Debug: Log thông tin đầu vào
            Log::info("Starting auto assignment for grade $gradeLevelId, year $academicYearId, max $maxStudents");

            // Lấy tất cả học sinh chưa phân lớp trong năm học này
            $unassignedStudents = User::where('role', User::ROLE_STUDENT)
                ->where('school_id', auth()->user()->school_id)
                ->whereDoesntHave('studentClasses', function($query) use ($academicYearId) {
                    $query->where('student_classes.academic_year_id', $academicYearId);
                })
                ->orderBy('full_name')
                ->get();

            Log::info("Found ".count($unassignedStudents)." unassigned students");

            // Lấy tất cả lớp trong khối và năm học, sắp xếp theo số học sinh ít nhất
            $classes = ClassModel::where('grade_level_id', $gradeLevelId)
                ->where('academic_year_id', $academicYearId)
                ->where('school_id', auth()->user()->school_id)
                ->withCount(['students' => function($query) use ($academicYearId) {
                    $query->where('student_classes.academic_year_id', $academicYearId);
                }])
                ->orderBy('students_count') // Ưu tiên lớp có ít học sinh trước
                ->get();

            Log::info("Found ".count($classes)." classes");

            if ($classes->isEmpty()) {
                Log::error("No classes found for grade $gradeLevelId and year $academicYearId");
                return back()->with('error', 'Không có lớp nào trong khối và năm học được chọn.');
            }

            $assignedCount = 0;

            foreach ($unassignedStudents as $student) {
                // Tìm lớp có ít học sinh nhất chưa đạt tối đa
                $selectedClass = null;

                foreach ($classes as $class) {
                    if ($class->students_count < $maxStudents) {
                        $selectedClass = $class;
                        break;
                    }
                }

                if ($selectedClass) {
                    // Phân công học sinh vào lớp
                    StudentClass::updateOrCreate(
                        [
                            'user_id' => $student->id,
                            'academic_year_id' => $academicYearId
                        ],
                        [
                            'class_id' => $selectedClass->id
                        ]
                    );

                    $assignedCount++;
                    $selectedClass->students_count++; // Tăng số lượng học sinh của lớp

                    Log::info("Assigned student {$student->id} to class {$selectedClass->id}");
                } else {
                    Log::warning("No available class for student {$student->id}");
                }
            }

            DB::commit();

            Log::info("Successfully assigned $assignedCount students");
            return redirect()->route('class_assignments.index', [
                'academic_year_id' => $academicYearId
            ])->with('success', "Đã phân công tự động $assignedCount học sinh vào các lớp.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto assign error: '.$e->getMessage());
            return back()->with('error', 'Lỗi phân công tự động: '.$e->getMessage());
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

        // Lấy academic year từ lớp
        $academicYear = AcademicYear::find($class->academic_year_id);

        return view('class_assignments.class_students', compact('class', 'students', 'academicYear'));
    }

    // Chuyển học sinh sang lớp khác
    public function moveStudent(Request $request, User $student)
    {
        if ($student->school_id !== auth()->user()->school_id) {
            abort(403, 'Không được phép thao tác với học sinh từ trường khác');
        }

        $request->validate([
            'current_class_id' => 'required|exists:classes,id,school_id,'.auth()->user()->school_id,
            'new_class_id' => 'required|exists:classes,id,school_id,'.auth()->user()->school_id,
            'academic_year_id' => 'required|exists:academic_years,id,school_id,'.auth()->user()->school_id,
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
             Log::error('Move student error: '.$e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi chuyển lớp: '.$e->getMessage());
        }
    }
}
