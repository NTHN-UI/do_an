<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Document;
use App\Models\ExamAssignment;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

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

        $allClasses = ClassModel::where('school_id', auth()->user()->school_id)
            ->with('gradeLevel')
            ->get();

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
            $validator = Validator::make($request->all(), [
                'class_ids' => 'required|array',
                'class_ids.*' => 'exists:classes,id,school_id,' . auth()->user()->school_id,
                'subject_id' => [
                    'required',
                    'exists:subjects,id,school_id,' . auth()->user()->school_id,
                    function ($attribute, $value, $fail) use ($teacher) {
                        if ($teacher->subject_id != $value) {
                            $fail('Giáo viên không được phân công dạy môn này');
                        }
                    }
                ],
                'academic_year_id' => 'required|exists:academic_years,id,school_id,' . auth()->user()->school_id,
                'is_homeroom' => 'sometimes|boolean',
            ], [
                'class_ids.required' => 'Vui lòng chọn ít nhất một lớp',
                'class_ids.*.exists' => 'Lớp học không tồn tại hoặc không thuộc trường của bạn',
                'subject_id.required' => 'Vui lòng chọn môn học',
                'subject_id.exists' => 'Môn học không tồn tại hoặc không thuộc trường của bạn',
                'academic_year_id.required' => 'Vui lòng chọn năm học',
                'academic_year_id.exists' => 'Năm học không tồn tại hoặc không thuộc trường của bạn',
            ]);

            if ($validator->passes()) {
                if (!empty($request->is_homeroom)) {
                    if (count($request->class_ids) > 1) {
                        $validator->errors()->add('is_homeroom', 'Giáo viên chủ nhiệm chỉ được phân công 1 lớp');
                    }

                    $homeroomExists = TeacherAssignment::whereIn('class_id', $request->class_ids)
                        ->where('academic_year_id', $request->academic_year_id)
                        ->where('is_homeroom', true)
                        ->exists();

                    if ($homeroomExists) {
                        $validator->errors()->add('class_ids', 'Một trong các lớp đã có giáo viên chủ nhiệm');
                    }

                    $teacherHomeroomExists = TeacherAssignment::where('teacher_id', $teacher->id)
                        ->where('academic_year_id', $request->academic_year_id)
                        ->where('is_homeroom', true)
                        ->exists();

                    if ($teacherHomeroomExists) {
                        $validator->errors()->add('is_homeroom', 'Giáo viên này đã là chủ nhiệm lớp khác');
                    }
                }

                foreach ($request->class_ids as $class_id) {
                    $subjectTeacherExists = TeacherAssignment::where('class_id', $class_id)
                        ->where('subject_id', $request->subject_id)
                        ->where('academic_year_id', $request->academic_year_id)
                        ->where('teacher_id', '!=', $teacher->id)
                        ->exists();

                    if ($subjectTeacherExists) {
                        $validator->errors()->add('subject_id', 'Môn học này đã có giáo viên khác dạy trong lớp');
                        break;
                    }
                }
            }

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            foreach ($request->class_ids as $class_id) {
                TeacherAssignment::create([
                    'teacher_id' => $teacher->id,
                    'class_id' => $class_id,
                    'subject_id' => $request->subject_id,
                    'academic_year_id' => $request->academic_year_id,
                    'is_homeroom' => $request->is_homeroom ?? false,
                    'school_id' => auth()->user()->school_id,
                ]);
            }

            return redirect()
                ->route('teacher_assignments.index')
                ->with('success', 'Phân công giảng dạy thành công!');

        } catch (\Exception $e) {
            Log::error('Error storing assignment: ' . $e->getMessage());
            $validator->errors()->add('system_error', 'Đã xảy ra lỗi khi lưu phân công');
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
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

            $validator = Validator::make($request->all(), [
                'class_id' => 'required|exists:classes,id,school_id,'.auth()->user()->school_id,
                'subject_id' => [
                    'required',
                    'exists:subjects,id,school_id,'.auth()->user()->school_id,
                    function ($attribute, $value, $fail) use ($teacherAssignment) {
                        if ($teacherAssignment->teacher->subject_id != $value) {
                            $fail('Giáo viên không được phân công dạy môn này');
                        }
                    }
                ],
                'is_homeroom' => 'sometimes|boolean',
            ], [
                'class_id.required' => 'Vui lòng chọn lớp học',
                'class_id.exists' => 'Lớp học không tồn tại hoặc không thuộc trường của bạn',
                'subject_id.required' => 'Vui lòng chọn môn học',
                'subject_id.exists' => 'Môn học không tồn tại hoặc không thuộc trường của bạn',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $validated = $validator->validated();

            if (!empty($validated['is_homeroom'])) {
                $homeroomExists = TeacherAssignment::where('class_id', $validated['class_id'])
                    ->where('academic_year_id', $teacherAssignment->academic_year_id)
                    ->where('is_homeroom', true)
                    ->where('id', '!=', $teacherAssignment->id)
                    ->exists();

                if ($homeroomExists) {
                    $validator->errors()->add('is_homeroom', 'Lớp này đã có giáo viên chủ nhiệm khác!');
                    return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
                }

                $teacherHomeroomExists = TeacherAssignment::where('teacher_id', $teacherAssignment->teacher_id)
                    ->where('academic_year_id', $teacherAssignment->academic_year_id)
                    ->where('is_homeroom', true)
                    ->where('id', '!=', $teacherAssignment->id)
                    ->exists();

                if ($teacherHomeroomExists) {
                    $validator->errors()->add('is_homeroom', 'Giáo viên này đã là chủ nhiệm lớp khác!');
                    return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
                }
            }

            $subjectTeacherExists = TeacherAssignment::where('class_id', $validated['class_id'])
                ->where('subject_id', $validated['subject_id'])
                ->where('academic_year_id', $teacherAssignment->academic_year_id)
                ->where('teacher_id', '!=', $teacherAssignment->teacher_id)
                ->exists();

            if ($subjectTeacherExists) {
                $validator->errors()->add('subject_id', 'Môn học này đã có giáo viên khác dạy trong lớp!');
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
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
        DB::beginTransaction();
        try {
            $teacherAssignment = TeacherAssignment::whereHas('teacher', function($query) {
                $query->where('school_id', auth()->user()->school_id);
            })
                ->whereHas('class', function($query) {
                    $query->where('school_id', auth()->user()->school_id);
                })
                ->with(['teacher', 'class', 'subject'])
                ->findOrFail($id);

            $gradeCount = Grade::where('teacher_id', $teacherAssignment->teacher_id)
                ->where('class_id', $teacherAssignment->class_id)
                ->where('subject_id', $teacherAssignment->subject_id)
                ->where('academic_year_id', $teacherAssignment->academic_year_id)
                ->count();

            if ($gradeCount > 0) {
                return back()->with('error', "Không thể xóa phân công vì giáo viên đã nhập $gradeCount bản điểm cho lớp này");
            }

            $examCount = ExamAssignment::whereHas('exam', function($query) use ($teacherAssignment) {
                $query->where('teacher_id', $teacherAssignment->teacher_id)
                    ->where('subject_id', $teacherAssignment->subject_id);
            })
                ->where('class_id', $teacherAssignment->class_id)
                ->count();

            if ($examCount > 0) {
                return back()->with('error', "Không thể xóa phân công vì giáo viên đã giao $examCount đề thi cho lớp này");
            }

            $documentCount = Document::where('teacher_id', $teacherAssignment->teacher_id)
                ->where('subject_id', $teacherAssignment->subject_id)
                ->count();

            if ($documentCount > 0) {
                return back()->with('error', "Không thể xóa phân công vì giáo viên đã tải lên $documentCount tài liệu cho môn này");
            }

            $notificationCount = Notification::where('sender_id', $teacherAssignment->teacher_id)
                ->where('class_id', $teacherAssignment->class_id)
                ->where('academic_year_id', $teacherAssignment->academic_year_id)
                ->count();

            if ($notificationCount > 0) {
                return back()->with('error', "Không thể xóa phân công vì giáo viên đã gửi $notificationCount thông báo cho lớp này");
            }

            $teacherAssignment->delete();

            DB::commit();

            return redirect()->route('teacher_assignments.index')
                ->with('success', 'Xóa phân công thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting assignment: '.$e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi xóa phân công: '.$e->getMessage());
        }
    }
        public function checkAssignment(Request $request)
        {
            $teacherId = $request->input('teacher_id');
            $classId = $request->input('class_id');
            $subjectId = $request->input('subject_id');
            $academicYearId = $request->input('academic_year_id');
            $isHomeroom = $request->input('is_homeroom', false);

            $teacher = User::where('id', $teacherId)
                ->where('school_id', auth()->user()->school_id)
                ->firstOrFail();

            $class = ClassModel::where('id', $classId)
                ->where('school_id', auth()->user()->school_id)
                ->firstOrFail();

            $exists = TeacherAssignment::where([
                'teacher_id' => $teacherId,
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'academic_year_id' => $academicYearId,
            ])->exists();

            if ($exists) {
                return response()->json(['available' => false, 'message' => 'Phân công đã tồn tại']);
            }


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
