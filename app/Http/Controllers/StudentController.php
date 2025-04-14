<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index(Request $request)
    {
        $search = $request->input('search');
        $classId = $request->input('class_id');
        $academicYearId = $request->input('academic_year_id');

        $students = User::where('role', User::ROLE_STUDENT)
            ->where('school_id', auth()->user()->school_id)
            ->when($search, function ($query) use ($search) {
                return $query->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            })
            ->when($classId, function ($query, $classId) {
                return $query->whereHas('studentClasses', function ($q) use ($classId) {
                    $q->where('class_id', $classId);
                });
            })
            ->when($academicYearId, function ($query, $academicYearId) {
                return $query->whereHas('studentAcademicYears', function ($q) use ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                });
            })
            ->with(['school', 'studentClasses'])
            ->orderBy('full_name')
            ->paginate(20);

        $classes = ClassModel::where('school_id', auth()->user()->school_id)
            ->with('gradeLevel')
            ->get();

        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('students.index', compact(
            'students',
            'search',
            'classes',
            'classId',
            'academicYears',
            'academicYearId'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentAcademicYear = AcademicYear::where('school_id', auth()->user()->school_id)
            ->latest()
            ->first();

        $classes = ClassModel::where('school_id', auth()->user()->school_id)
            ->where('academic_year_id', $currentAcademicYear->id ?? null)
            ->with('gradeLevel')
            ->get();

        return view('students.create', compact('classes', 'currentAcademicYear'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email',
                'guardian_name' => 'nullable|string|max:255',
                'guardian_email' => 'nullable|email|unique:users,guardian_email',
                'guardian_phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'gender' => 'nullable|in:Nam,Nữ,Khác',
                'date_of_birth' => 'nullable|date',
//                'class_ids' => 'required|array',
//                'class_ids.*' => 'exists:classes,id,school_id,'.auth()->user()->school_id,
                'password' => 'nullable|string|min:8',
            ]);

            $validated['role'] = User::ROLE_STUDENT;
            $validated['school_id'] = auth()->user()->school_id;
            $validated['is_active'] = $request->has('is_active');

            if (empty($validated['password'])) {
                $validated['password'] = Str::random(8);
            }
            $validated['password'] = Hash::make($validated['password']);

            $student = User::create($validated);

            // Gán học sinh vào các lớp
//            if (!empty($validated['class_ids'])) {
//                $student->studentClasses()->attach($validated['class_ids'], [
//                    'academic_year_id' => $request->input('academic_year_id')
//                ]);
//            }

            return redirect()->route('students.index')->with('success', 'Học sinh đã được thêm thành công.');

        } catch (\Exception $e) {
            Log::error('Error creating student: '.$e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi thêm học sinh: '.$e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(User $student)
    {
        if ($student->school_id !== auth()->user()->school_id || !$student->isStudent()) {
            abort(403, 'Không được phép truy cập học sinh từ trường khác');
        }

        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $student)
    {
        if ($student->school_id !== auth()->user()->school_id || !$student->isStudent()) {
            abort(403, 'Không được phép sửa học sinh từ trường khác');
        }

        $currentAcademicYear = AcademicYear::where('school_id', auth()->user()->school_id)
            ->latest()
            ->first();

        $classes = ClassModel::where('school_id', auth()->user()->school_id)
            ->where('academic_year_id', $currentAcademicYear->id ?? null)
            ->with('gradeLevel')
            ->get();

        $studentClassIds = $student->studentClasses->pluck('id')->toArray();

        return view('students.edit', compact(
            'student',
            'classes',
            'currentAcademicYear',
            'studentClassIds'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $student)
    {
        try {
            if ($student->school_id !== auth()->user()->school_id || !$student->isStudent()) {
                abort(403, 'Không được phép cập nhật học sinh từ trường khác');
            }

            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email,'.$student->id,
                'guardian_name' => 'nullable|string|max:255',
                'guardian_email' => 'nullable|email|unique:users,guardian_email,'.$student->id,
                'guardian_phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'gender' => 'nullable|in:Nam,Nữ,Khác',
                'date_of_birth' => 'nullable|date',
                'class_ids' => 'required|array',
                'class_ids.*' => 'exists:classes,id,school_id,'.auth()->user()->school_id,
                'password' => 'nullable|string|min:8',
            ]);

            $validated['is_active'] = $request->has('is_active');

            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $student->update($validated);

            // Cập nhật lớp học của học sinh
            if (!empty($validated['class_ids'])) {
                $student->studentClasses()->syncWithPivotValues(
                    $validated['class_ids'],
                    ['academic_year_id' => $request->input('academic_year_id')]
                );
            }

            return redirect()->route('students.index')->with('success', 'Thông tin học sinh đã được cập nhật.');

        } catch (\Exception $e) {
            Log::error('Error updating student: '.$e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi cập nhật học sinh: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student)
    {
        try {
            if ($student->school_id !== auth()->user()->school_id || !$student->isStudent()) {
                abort(403, 'Không được phép xóa học sinh từ trường khác');
            }


            // Kiểm tra xem học sinh có đang trong lớp nào không
            if ($student->studentClasses()->count() > 0) {
                return back()->with('error', 'Không thể xóa học sinh vì đang có lớp học. Vui lòng xóa học sinh khỏi lớp trước.');
            }

            $school_id = $student->school_id;
            $student->delete();

            // Cập nhật lại STT cho các học sinh còn lại
            $this->reorderStudentNumbers($school_id);

            return redirect()->route('students.index')
                ->with('success', 'Học sinh đã được xóa thành công.');

        } catch (\Exception $e) {
            Log::error('Error deleting student: '.$e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi xóa học sinh: '.$e->getMessage());
        }
    }

    private function reorderStudentNumbers($school_id)
    {
        $students = User::where('school_id', $school_id)
            ->where('role', User::ROLE_STUDENT)
            ->orderBy('school_auto_id')
            ->get();

        foreach ($students as $index => $student) {
            $student->school_auto_id = $index + 1;
            $student->save();
        }
    }
}
