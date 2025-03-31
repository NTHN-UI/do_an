<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\GradeLevel;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Http\Request;

class ClassAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::all();
        $currentAcademicYear = AcademicYear::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        $selectedAcademicYear = $request->input('academic_year_id', $currentAcademicYear?->id);

        $classes = ClassModel::withCount('students')
            ->when($selectedAcademicYear, function($query) use ($selectedAcademicYear) {
                return $query->where('academic_year_id', $selectedAcademicYear);
            })
            ->with(['gradeLevel', 'homeroomTeacher'])
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
        $gradeLevels = GradeLevel::all();
        $academicYears = AcademicYear::all();
        $currentAcademicYear = AcademicYear::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        return view('class_assignments.auto_assign', compact('gradeLevels', 'academicYears', 'currentAcademicYear'));
    }

    // Xử lý phân công tự động
    public function autoAssign(Request $request)
    {
        $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'max_students_per_class' => 'required|integer|min:1',
        ]);

        $gradeLevelId = $request->grade_level_id;
        $academicYearId = $request->academic_year_id;
        $maxStudents = $request->max_students_per_class;

        // Sửa lại phần truy vấn học sinh chưa phân lớp
        $unassignedStudents = User::role('student')
            ->whereDoesntHave('studentClasses', function($query) use ($academicYearId) {
                $query->where('student_classes.academic_year_id', $academicYearId); // Chỉ rõ bảng student_classes
            })
            ->get();

        // Sửa lại phần truy vấn lớp học
        $classes = ClassModel::where('grade_level_id', $gradeLevelId)
            ->where('academic_year_id', $academicYearId) // Đây là academic_year_id của bảng classes
            ->get();

        if ($classes->isEmpty()) {
            return back()->with('error', 'Không có lớp nào trong khối và năm học được chọn.');
        }

        // Phân bổ học sinh vào các lớp
        $assignedCount = 0;
        foreach ($unassignedStudents as $index => $student) {
            $classIndex = $index % $classes->count();
            $class = $classes[$classIndex];

            // Kiểm tra số lượng học sinh trong lớp
            $currentStudentCount = StudentClass::where('class_id', $class->id)
                ->where('academic_year_id', $academicYearId)
                ->count();

            if ($currentStudentCount < $maxStudents) {
                StudentClass::create([
                    'user_id' => $student->id,
                    'class_id' => $class->id,
                    'academic_year_id' => $academicYearId,
                ]);
                $assignedCount++;
            }
        }

        return redirect()->route('class_assignments.index')
            ->with('success', "Đã phân công tự động $assignedCount học sinh vào các lớp.");
    }

    // Hiển thị danh sách học sinh trong lớp
    public function showClassStudents(ClassModel $class)
    {
        $students = $class->students()->paginate(20);
        $academicYear = $class->academicYear;

        return view('class_assignments.class_students', compact('class', 'students', 'academicYear'));
    }

    // Chuyển học sinh sang lớp khác
    public function moveStudent(Request $request, User $student)
    {
        $request->validate([
            'current_class_id' => 'required|exists:classes,id',
            'new_class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

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

        return back()->with('success', 'Đã chuyển học sinh sang lớp mới.');
    }
}
