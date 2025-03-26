<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teachers = User::teachers()->get();
        return view('teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('teachers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:Nam,Nữ,Khác',
            'date_of_birth' => 'required|date',
        ]);

        User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'role' => 'teacher',
        ]);

        return redirect()->route('teachers.index')->with('success', 'Giáo viên đã được tạo!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $teacher = User::findOrFail($id);
        return view('teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $teacher = User::findOrFail($id);
        return view('teachers.edit', compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $teacher = User::findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $teacher->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:Nam,Nữ,Khác',
            'date_of_birth' => 'required|date',
            'password' => 'nullable|min:6',
        ]);

        $teacher->update([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'password' => $request->password ? Hash::make($request->password) : $teacher->password,
        ]);

        return redirect()->route('teachers.index')->with('success', 'Cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $teacher = User::findOrFail($id);
        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', 'Xoá giáo viên thành công!');
    }
}
