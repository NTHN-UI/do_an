<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;


class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $teachers = User::with('school')
            ->where('role', User::ROLE_TEACHER)
            ->when($search, function($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                        ->orWhereHas('school', function($q) use ($search) {
                            $q->where('name', 'like', "%$search%");
                        });
                });
            })
            ->orderBy('full_name')
            ->paginate(10)
            ->withQueryString();

        return view('teachers.index', compact('teachers', 'search'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::all();
        return view('teachers.create', compact('schools'));    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Password::defaults()],
            'gender' => 'required|in:Nam,Nữ,Khác',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'school_id' => 'required|exists:schools,id',
        ]);

        User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'role' => User::ROLE_TEACHER,
            'school_id' => $request->school_id,
            'is_active' => true
        ]);

        return redirect()->route('teachers.index')
            ->with('success', 'Thêm giáo viên thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404);
        return view('teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404);
        $schools = School::all();
        return view('teachers.edit', compact('teacher', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$teacher->id,
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:Nam,Nữ,Khác',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'school_id' => 'required|exists:schools,id',
            'is_active' => 'boolean'
        ]);

        $data = $request->only([
            'full_name', 'email', 'phone', 'gender',
            'date_of_birth', 'address', 'school_id', 'is_active'
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $teacher->update($data);

        return redirect()->route('teachers.index')
            ->with('success', 'Cập nhật giáo viên thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404);

        // Kiểm tra phân công giảng dạy
        $assignments = TeacherAssignment::with(['class', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get();

        if ($assignments->count() > 0) {
            $errorMessage = 'Không thể xóa giáo viên <strong>"' . $teacher->full_name . '"</strong> vì đang có <strong>' . $assignments->count() . '</strong> phân công giảng dạy:';

            $errorMessage .= '<ul style="margin-top: 8px; margin-bottom: 8px; padding-left: 20px;">';
            foreach ($assignments as $assignment) {
                $role = $assignment->is_homeroom ? ' <span class="badge bg-primary">Chủ nhiệm</span>' : '';
                $errorMessage .= '<li>Lớp <strong>' . $assignment->class->name . '</strong> - Môn <strong>' . $assignment->subject->name . '</strong>' . $role . '</li>';
            }
            $errorMessage .= '</ul>';
            $errorMessage .= '<p style="margin-top: 10px; margin-bottom: 0;"><i class="fas fa-info-circle"></i> Vui lòng xóa hết các phân công trước khi xóa giáo viên này.</p>';

            return redirect()
                ->route('teachers.index')
                ->with('error', $errorMessage);
        }

        try {
            $teacherName = $teacher->full_name;
            $teacher->delete();

            return redirect()
                ->route('teachers.index')
                ->with('success', 'Đã xóa giáo viên <strong>"' . $teacherName . '"</strong> thành công! <i class="fas fa-check-circle"></i>');
        } catch (\Exception $e) {
            return redirect()
                ->route('teachers.index')
                ->with('error', 'Xảy ra lỗi khi xóa giáo viên: ' . $e->getMessage());
        }
    }
}
