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
        $academicYearId = $request->input('academic_year_id');

        $assignments = TeacherAssignment::with(['teacher', 'class', 'subject', 'academicYear'])
            ->whereHas('teacher', function ($query) {
                $query->where('users.school_id', auth()->user()->school_id);
            })
            ->whereHas('class', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->whereHas('subject', function ($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->when($subjectId, function ($query, $subjectId) {
                return $query->where('subject_id', $subjectId);
            })
            ->when($classId, function ($query, $classId) {
                return $query->where('class_id', $classId);
            })
            ->when($isHomeroom !== null, function ($query) use ($isHomeroom) {
                return $query->where('is_homeroom', $isHomeroom);
            })
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $subjects = Subject::where('school_id', auth()->user()->school_id)->get();

        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('year', 'asc')
            ->get();

        // Lấy tất cả lớp (dùng khi không chọn năm học)
        $allClasses = ClassModel::where('school_id', auth()->user()->school_id)
            ->with('gradeLevel')
            ->get();

        // Lấy lớp theo năm học nếu có filter
        $classes = ClassModel::where('school_id', auth()->user()->school_id)
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->with('gradeLevel')
            ->get();

        return view('teacher_assignments.index', compact(
            'assignments',
            'subjects',
            'classes',
            'academicYears',
            'allClasses',
            'subjectId',
            'classId',
            'isHomeroom',
            'academicYearId'
        ));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(User $teacher)
    {
        try {
            if ($teacher->school_id !== auth()->user()->school_id) {
                abort(403, 'Không được phép truy cập giáo viên từ trường khác');
            }

            $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
                ->orderBy('year', 'desc')
                ->get();

            $currentAcademicYear = $academicYears->first();

            // Khởi tạo classes là collection rỗng
            $classes = collect();

            if ($currentAcademicYear) {
                $classes = ClassModel::where('school_id', auth()->user()->school_id)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->with('gradeLevel')
                    ->get();
            }

            $subjects = Subject::where('school_id', auth()->user()->school_id)->get();

            return view('teacher_assignments.create', compact(
                'teacher',
                'classes',
                'subjects',
                'academicYears',
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
            if ($teacher->school_id !== auth()->user()->school_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không được phép phân công giáo viên từ trường khác'
                ], 403);
            }

            $validated = $request->validate([
                'class_ids' => 'required|array',
                'class_ids.*' => 'exists:classes,id,school_id,' . auth()->user()->school_id,
                'subject_id' => 'required|exists:subjects,id,school_id,' . auth()->user()->school_id,
                'academic_year_id' => 'required|exists:academic_years,id,school_id,' . auth()->user()->school_id,
                'is_homeroom' => 'sometimes|boolean',
            ]);

            // Kiểm tra GVCN chỉ được chọn 1 lớp
            if (!empty($validated['is_homeroom'])) {
                if (count($validated['class_ids']) > 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Giáo viên chủ nhiệm chỉ được phân công 1 lớp!'
                    ], 422);
                }

                // Kiểm tra lớp đã có GVCN chưa
                $homeroomExists = TeacherAssignment::whereIn('class_id', $validated['class_ids'])
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('is_homeroom', true)
                    ->exists();

                if ($homeroomExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Một trong các lớp đã có giáo viên chủ nhiệm!'
                    ], 422);
                }

                // Kiểm tra giáo viên đã là GVCN lớp khác chưa
                $teacherHomeroomExists = TeacherAssignment::where('teacher_id', $teacher->id)
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('is_homeroom', true)
                    ->exists();

                if ($teacherHomeroomExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Giáo viên này đã là chủ nhiệm lớp khác!'
                    ], 422);
                }
            }

            // Kiểm tra mỗi lớp đã có giáo viên khác dạy môn này chưa (bỏ kiểm tra giáo viên hiện tại)
            foreach ($validated['class_ids'] as $class_id) {
                $subjectTeacherExists = TeacherAssignment::where('class_id', $class_id)
                    ->where('subject_id', $validated['subject_id'])
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('teacher_id', '!=', $teacher->id) // Thêm điều kiện này
                    ->exists();

                if ($subjectTeacherExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Môn học này đã có giáo viên khác dạy trong lớp!'
                    ], 422);
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

            return response()->json([
                'success' => true,
                'message' => 'Phân công giảng dạy thành công!',
                'redirect' => route('teacher_assignments.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing assignment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $teacherAssignment = TeacherAssignment::whereHas('teacher', function($query) {
            $query->where('school_id', auth()->user()->school_id);
        })
            ->whereHas('class', function($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->findOrFail($id);

        return view('teacher_assignments.show', compact('teacherAssignment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $teacherAssignment = TeacherAssignment::whereHas('teacher', function($query) {
            $query->where('school_id', auth()->user()->school_id);
        })
            ->whereHas('class', function($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
            ->findOrFail($id);

        $teacher = $teacherAssignment->teacher;
        $currentAcademicYear = $teacherAssignment->academicYear;

        $classes = ClassModel::where('school_id', auth()->user()->school_id)
            ->where('academic_year_id', $currentAcademicYear->id)
            ->get();

        $subjects = Subject::where('school_id', auth()->user()->school_id)->get();

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
    public function update(Request $request, $id)
    {
        try {
            $teacherAssignment = TeacherAssignment::whereHas('teacher', function($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
                ->whereHas('class', function($query) {
                    $query->where('school_id', auth()->user()->school_id);
                })
                ->findOrFail($id);

            $validated = $request->validate([
                'class_id' => 'required|exists:classes,id,school_id,'.auth()->user()->school_id,
                'subject_id' => 'required|exists:subjects,id,school_id,'.auth()->user()->school_id,
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
    public function destroy($id)
    {
        try {
            $teacherAssignment = TeacherAssignment::whereHas('teacher', function($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
                ->whereHas('class', function($query) {
                    $query->where('school_id', auth()->user()->school_id);
                })
                ->findOrFail($id);
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

        // Kiểm tra giáo viên và lớp có cùng trường không
        $teacher = User::where('id', $teacherId)
            ->where('school_id', auth()->user()->school_id)
            ->firstOrFail();

        $class = ClassModel::where('id', $classId)
            ->where('school_id', auth()->user()->school_id)
            ->firstOrFail();

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
//    public function getClassesByAcademicYear(Request $request)
//    {
//        try {
//            $classes = ClassModel::with('gradeLevel')
//                ->where('academic_year_id', $request->academic_year_id)
//                ->get()
//                ->map(function ($class) {
//                    return [
//                        'id' => $class->id,
//                        'name' => $class->name,
//                        'grade_level' => [
//                            'grade_number' => $class->gradeLevel->grade_number
//                        ]
//                    ];
//                });
//
//            return response()->json($classes);
//
//        } catch (\Exception $e) {
//            Log::error('Error getting classes by year: '.$e->getMessage());
//            return response()->json(['error' => 'Lỗi hệ thống'], 500);
//        }
//    }
    public function getClassesByAcademicYear(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $classes = ClassModel::with('gradeLevel')
            ->where('academic_year_id', $request->academic_year_id)
            ->where('school_id', auth()->user()->school_id)
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'grade_level' => [
                        'grade_number' => $class->gradeLevel->grade_number
                    ]
                ];
            });

        return response()->json($classes);
    }
    }
