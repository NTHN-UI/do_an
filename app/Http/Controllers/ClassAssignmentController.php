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

        // Lấy năm học được chọn (nếu không có thì lấy năm học hiện tại)
        $selectedAcademicYear = $request->input('academic_year_id');

        // Lấy danh sách lớp với số học sinh theo năm học
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

            // Lấy thông tin khối học
            $gradeLevel = GradeLevel::find($gradeLevelId);
            $isGrade10 = $gradeLevel && $gradeLevel->grade_number == 10;

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
                ->when($isGrade10, function ($query) {
                    return $query->orderByDesc('entry_score');
                }, function ($query) {
                    return $query->orderBy('full_name');
                })
                ->orderBy('full_name')
                ->get();

            // Lấy danh sách lớp trong khối
            $classes = ClassModel::where('grade_level_id', $gradeLevelId)
                ->where('academic_year_id', $academicYearId)
                ->where('school_id', auth()->user()->school_id)
                ->orderBy('name')
                ->get();

            if ($classes->isEmpty()) {
                return back()->with('error', 'Không có lớp nào trong khối và năm học được chọn.');
            }

            if ($unassignedStudents->isEmpty()) {
                return back()->with('info', 'Không có học sinh nào cần phân lớp trong khối và năm học được chọn.');
            }


            $totalStudents = $unassignedStudents->count();
            $classCount = $classes->count();
            $studentsPerClass = ceil($totalStudents / $classCount);

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
            $message = "Đã phân công $totalStudents học sinh vào $classCount lớp ";
            $message .= $isGrade10 ? "theo điểm đầu vào:" : "theo thứ tự tên:";

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
        try {
            $currentClassId = $request->input('current_class_id');
            $targetClassId = $request->input('target_class_id');
            $targetYear = $request->input('target_academic_year_id');
            $selectedStudents = $request->input('selected_student_ids');
            $selectedStudentsId = [explode(',', $selectedStudents)];

            $targetYearId = AcademicYear::where('year', $targetYear)
                ->where('school_id', auth()->user()->school_id)
                ->value('id');

            foreach($selectedStudentsId as $id){
                $student = User::find($id);
                Log::info("Du lieu:", [$student]);
                Log::info("Diem trung binh cua: " . $student->getAveragesByYear($targetYearId));
            }

            // Những học sinh chưa đủ điều kiện lên lớp


            // Những học sinh đủ điều kiện

            return redirect()->back();
        } catch (\Exception $ex) {
            Log::error("Error in ClassAssignmentController@advanceClassStudents: " . $ex->getMessage());
            return response()->json("Lỗi khi lên lớp chp học sinh: " . $ex->getMessage(), 500);
        }
    }
}
