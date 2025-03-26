<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use Illuminate\Http\Request;

class GradeLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $grades = GradeLevel::all();
        return view('grade_levels.index', compact('grades'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('grade_levels.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'grade_number' => 'required|integer|unique:grade_levels',
        ]);

        GradeLevel::create([
            'grade_number' => $request->grade_number
        ]);

        return redirect()->route('grade_levels.index')->with('success', 'Thêm khối thành công!');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $grade = GradeLevel::findOrFail($id);
        return view('grade_levels.show', compact('grade'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $grade = GradeLevel::findOrFail($id);
        return view('grade_levels.edit', compact('grade'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        {
            $request->validate([
                'grade_number' => 'required|integer|unique:grade_levels,grade_number,'.$id,
            ]);

            $grade = GradeLevel::findOrFail($id);
            $grade->update([
                'grade_number' => $request->grade_number
            ]);

            return redirect()->route('grade_levels.index')->with('success', 'Cập nhật khối thành công!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        GradeLevel::destroy($id);
        return redirect()->route('grade_levels.index')->with('success', 'Xóa khối thành công!');
    }
}
