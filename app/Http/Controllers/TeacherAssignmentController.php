<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeacherAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $subjectId = $request->input('subject_id');
        $classId = $request->input('class_id');
        $isHomeroom = $request->input('is_homeroom');

        $assignments = TeacherAssignment::with(['teacher', 'class', 'subject', 'academicYear'])
            ->when($subjectId, function($query, $subjectId) {
                return $query->where('subject_id', $subjectId);
            })
            ->when($classId, function($query, $classId) {
                return $query->where('class_id', $classId);
            })
            ->when($isHomeroom !== null, function($query) use ($isHomeroom) {
                return $query->where('is_homeroom', $isHomeroom);
            })
            ->orderBy('created_at', 'desc') // Sắp xếp mới nhất lên đầu
            ->paginate(20);

        $subjects = Subject::all();
        $classes = ClassModel::with('gradeLevel')->get();
        $currentAcademicYear = AcademicYear::latest()->first();

        return view('teacher_assignments.index', compact(
            'assignments',
            'subjects',
            'classes',
            'subjectId',
            'classId',
            'isHomeroom',
            'currentAcademicYear'
        ));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(User $teacher)
    {
        try {
            if (!$teacher->school) {
                return redirect()->route('teachers.index')
                    ->with('error', 'Giáo viên chưa được gán vào trường học!');
            }

            $currentAcademicYear = AcademicYear::latest()->firstOrFail();

            $classes = ClassModel::where('school_id', $teacher->school->id)
                ->where('academic_year_id', $currentAcademicYear->id)
                ->get();

            $subjects = Subject::where('school_id', $teacher->school->id)->get();

            return view('teacher_assignments.create', compact(
                'teacher',
                'classes',
                'subjects',
                'currentAcademicYear'
            ));

        } catch (\Exception $e) {
            Log::error('Error in create assignment: '.$e->getMessage());
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi tạo phân công');
        }
    }
    public function store(Request $request, User $teacher)
    {
        try {
            $validated = $request->validate([
                'class_ids' => 'required|array',
                'class_ids.*' => 'exists:classes,id',
                'subject_id' => 'required|exists:subjects,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'is_homeroom' => 'sometimes|boolean',
            ]);

            // Kiểm tra GVCN chỉ được chọn 1 lớp
            if (!empty($validated['is_homeroom'])) {
                if (count($validated['class_ids']) > 1) {
                    return back()->with('error', 'Giáo viên chủ nhiệm chỉ được phân công 1 lớp!');
                }

                // Kiểm tra lớp đã có GVCN chưa
                $homeroomExists = TeacherAssignment::whereIn('class_id', $validated['class_ids'])
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('is_homeroom', true)
                    ->exists();

                if ($homeroomExists) {
                    return back()->with('error', 'Một trong các lớp đã có giáo viên chủ nhiệm!');
                }

                // Kiểm tra giáo viên đã là GVCN lớp khác chưa
                $teacherHomeroomExists = TeacherAssignment::where('teacher_id', $teacher->id)
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('is_homeroom', true)
                    ->exists();

                if ($teacherHomeroomExists) {
                    return back()->with('error', 'Giáo viên này đã là chủ nhiệm lớp khác!');
                }
            }

            // Kiểm tra môn học đã có giáo viên dạy trong lớp chưa
            foreach ($validated['class_ids'] as $class_id) {
                $subjectTeacherExists = TeacherAssignment::where('class_id', $class_id)
                    ->where('subject_id', $validated['subject_id'])
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->exists();

                if ($subjectTeacherExists) {
                    return back()->with('error', 'Môn học này đã có giáo viên dạy trong lớp!');
                }
            }

            // Tạo phân công
            foreach ($validated['class_ids'] as $class_id) {
                TeacherAssignment::create([
                    'teacher_id' => $teacher->id,
                    'class_id' => $class_id,
                    'subject_id' => $validated['subject_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'is_homeroom' => $validated['is_homeroom'] ?? false,
                ]);
            }

            return redirect()->route('teacher_assignments.index')
                ->with('success', 'Phân công giảng dạy thành công!');

        } catch (\Exception $e) {
            Log::error('Error storing assignment: '.$e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi: '.$e->getMessage());
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(TeacherAssignment $teacherAssignment)
    {
        return view('teacher_assignments.show', compact('teacherAssignment'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TeacherAssignment $teacherAssignment)
    {
        $teacher = $teacherAssignment->teacher;
        $currentAcademicYear = $teacherAssignment->academicYear;

        $classes = ClassModel::where('school_id', $teacher->school->id)
            ->where('academic_year_id', $currentAcademicYear->id)
            ->get();

        $subjects = Subject::where('school_id', $teacher->school->id)->get();

        return view('teacher_assignments.edit', compact(
            'teacherAssignment',
            'teacher',
            'classes',
            'subjects',
            'currentAcademicYear'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeacherAssignment $teacherAssignment)
    {
        try {
            $validated = $request->validate([
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'required|exists:subjects,id',
                'is_homeroom' => 'sometimes|boolean',
            ]);

            // Kiểm tra nếu là GVCN
            if (!empty($validated['is_homeroom'])) {
                // Kiểm tra lớp đã có chủ nhiệm khác chưa
                $homeroomExists = TeacherAssignment::where('class_id', $validated['class_id'])
                    ->where('academic_year_id', $teacherAssignment->academic_year_id)
                    ->where('is_homeroom', true)
                    ->where('id', '!=', $teacherAssignment->id)
                    ->exists();

                if ($homeroomExists) {
                    return back()->with('error', 'Lớp này đã có giáo viên chủ nhiệm khác!');
                }

                // Kiểm tra giáo viên đã là chủ nhiệm lớp khác chưa
                $teacherHomeroomExists = TeacherAssignment::where('teacher_id', $teacherAssignment->teacher_id)
                    ->where('academic_year_id', $teacherAssignment->academic_year_id)
                    ->where('is_homeroom', true)
                    ->where('id', '!=', $teacherAssignment->id)
                    ->exists();

                if ($teacherHomeroomExists) {
                    return back()->with('error', 'Giáo viên này đã là chủ nhiệm lớp khác!');
                }
            }

            $teacherAssignment->update([
                'class_id' => $validated['class_id'],
                'subject_id' => $validated['subject_id'],
                'is_homeroom' => $validated['is_homeroom'] ?? false,
            ]);

            return redirect()->route('teacher_assignments.index')
                ->with('success', 'Cập nhật phân công thành công!');

        } catch (\Exception $e) {
            Log::error('Error updating assignment: '.$e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi cập nhật phân công');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeacherAssignment $teacherAssignment)
    {
        try {
            $teacherAssignment->delete();
            return redirect()->route('teacher_assignments.index')
                ->with('success', 'Xóa phân công thành công!');

        } catch (\Exception $e) {
            Log::error('Error deleting assignment: '.$e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi xóa phân công');
        }
    }
// Thêm vào TeacherAssignmentController
    public function checkAssignment(Request $request)
    {
        $teacherId = $request->input('teacher_id');
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $academicYearId = $request->input('academic_year_id');
        $isHomeroom = $request->input('is_homeroom', false);

        // Kiểm tra trùng lặp phân công
        $exists = TeacherAssignment::where([
            'teacher_id' => $teacherId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'academic_year_id' => $academicYearId,
        ])->exists();

        if ($exists) {
            return response()->json(['available' => false, 'message' => 'Phân công đã tồn tại']);
        }

        // Kiểm tra GVCN
        if ($isHomeroom) {
            $homeroomExists = TeacherAssignment::where([
                'class_id' => $classId,
                'academic_year_id' => $academicYearId,
                'is_homeroom' => true
            ])->exists();

            if ($homeroomExists) {
                return response()->json(['available' => false, 'message' => 'Lớp đã có giáo viên chủ nhiệm']);
            }

            $teacherHomeroomExists = TeacherAssignment::where([
                'teacher_id' => $teacherId,
                'academic_year_id' => $academicYearId,
                'is_homeroom' => true
            ])->exists();

            if ($teacherHomeroomExists) {
                return response()->json(['available' => false, 'message' => 'Giáo viên đã là chủ nhiệm lớp khác']);
            }
        }

        return response()->json(['available' => true]);
    }

}
