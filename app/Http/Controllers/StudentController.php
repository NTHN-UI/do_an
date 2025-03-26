<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */


        public function index(Request $request)
    {
        $search = $request->input('search');

        $students = User::students()
            ->when($search, function($query) use ($search) {
                return $query->search($search);
            })
            ->with('school')
            ->paginate(10);

        return view('students.index', compact('students', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::all();
        return view('students.create', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'school_id' => 'required|exists:schools,id',
        ]);

        $validated['role'] = 'student';
        $validated['is_active'] = $request->has('is_active');

        User::create($validated);

        return redirect()->route('students.index')->with('success', 'Học sinh đã được thêm thành công.');
    }


    /**
     * Display the specified resource.
     */
    public function show(User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }

        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $student)
    {
        // Đảm bảo chỉ sửa học sinh
        if ($student->role !== 'student') {
            abort(404);
        }

        $schools = School::all();
        return view('students.edit', compact('student', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $student)
    {
        // Đảm bảo chỉ cập nhật học sinh
        if ($student->role !== 'student') {
            abort(404);
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
            'school_id' => 'required|exists:schools,id',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $student->update($validated);


        return redirect()->route('students.index')->with('success', 'Thông tin học sinh đã được cập nhật.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student)
    {
        // Đảm bảo chỉ xóa học sinh
        if ($student->role !== 'student') {
            abort(404);
        }

        $student->delete();
        return redirect()->route('students.index')->with('success', 'Học sinh đã được xóa thành công.');
    }
}
