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

        $classes = ClassModel::withCount(['students' => function ($query) use ($selectedAcademicYear) {
            $query->where('student_classes.academic_year_id', $selectedAcademicYear);
        }])
            ->when($selectedAcademicYear, function ($query) use ($selectedAcademicYear) {
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
            'grade_level_id' => 'required|exists:grade_levels,id,school_id,' . auth()->user()->school_id,
            'academic_year_id' => 'required|exists:academic_years,id,school_id,' . auth()->user()->school_id,
        ]);

        try {
            DB::beginTransaction();

            $gradeLevelId = $request->grade_level_id;
            $academicYearId = $request->academic_year_id;

            // Lấy tất cả học sinh chưa phân lớp trong khối và năm học này
            $unassignedStudents = User::where('role', User::ROLE_STUDENT)
                ->where('school_id', auth()->user()->school_id)
                ->whereHas('studentGrades', function ($query) use ($gradeLevelId, $academicYearId) {
                    $query->where('grade_id', $gradeLevelId)
                        ->where('academic_year_id', $academicYearId);
                })
                ->whereDoesntHave('studentClasses', function ($query) use ($academicYearId) {
                    $query->where('student_classes.academic_year_id', $academicYearId);
                })
                ->orderByDesc('entry_score')
                ->orderBy('full_name')
                ->get();


            // Lấy tất cả lớp trong khối và năm học
            $classes = ClassModel::where('grade_level_id', $gradeLevelId)
                ->where('academic_year_id', $academicYearId)
                ->where('school_id', auth()->user()->school_id)
                ->withCount(['students' => function ($query) use ($academicYearId) {
                    $query->where('student_classes.academic_year_id', $academicYearId);
                }])
                ->orderBy('students_count') // Ưu tiên lớp có ít học sinh hơn trước
                ->get();

            if ($classes->isEmpty()) {
                return back()->with('error', 'Không có lớp nào trong khối và năm học được chọn.');
            }

            if ($unassignedStudents->isEmpty()) {
                return back()->with('info', 'Không có học sinh nào cần phân lớp trong khối và năm học được chọn.');
            }


            $assignedCount = 0;
            $classIndex = 0;
            $totalClasses = $classes->count();
            $assignmentDetails = [];

            // Chuẩn bị dữ liệu để insert hàng loạt
            $assignments = [];
            $now = now();

            foreach ($unassignedStudents as $student) {
                $selectedClass = $classes[$classIndex];

                $assignments[] = [
                    'user_id' => $student->id,
                    'class_id' => $selectedClass->id,
                    'academic_year_id' => $academicYearId,
                    'created_at' => $now,
                    'updated_at' => $now
                ];

                // Ghi nhận thông tin phân lớp
                $assignmentDetails[$selectedClass->id][] = $student->full_name;

                $assignedCount++;
                $classIndex = ($classIndex + 1) % $totalClasses;
            }

            // Thực hiện insert hàng loạt
            StudentClass::insert($assignments);

            DB::commit();

            // Tạo thông báo chi tiết
            $message = "Đã phân công tự động $assignedCount học sinh vào các lớp:<br>";
            foreach ($assignmentDetails as $classId => $students) {
                $className = $classes->firstWhere('id', $classId)->name;
                $message .= "<br>- Lớp $className: " . count($students) . " học sinh";
            }

            return redirect()
                ->route('class_assignments.index', ['academic_year_id' => $academicYearId])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi phân công tự động: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi phân công tự động');
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
    public function moveStudent(Request $request, string $id)
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
}
