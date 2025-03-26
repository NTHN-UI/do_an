<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\GradeLevel;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = ClassModel::with(['gradeLevel', 'academicYear'])->get();
        return view('classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $grades = GradeLevel::all();
        $academicYears = AcademicYear::all();
        return view('classes.create', compact('grades', 'academicYears'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:classes',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        ClassModel::create([
            'name' => $request->name,
            'grade_level_id' => $request->grade_level_id,
            'academic_year_id' => $request->academic_year_id,
        ]);

        return redirect()->route('classes.index')->with('success', 'Thêm lớp học thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $class = ClassModel::with(['gradeLevel', 'academicYear'])->findOrFail($id);
        return view('classes.show', compact('class'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $class = ClassModel::findOrFail($id);
        $grades = GradeLevel::all();
        $academicYears = AcademicYear::all();
        return view('classes.edit', compact('class', 'grades', 'academicYears'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|unique:classes,name,' . $id,
            'grade_level_id' => 'required|exists:grade_levels,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $class = ClassModel::findOrFail($id);
        $class->update([
            'name' => $request->name,
            'grade_level_id' => $request->grade_level_id,
            'academic_year_id' => $request->academic_year_id,
        ]);

        return redirect()->route('classes.index')->with('success', 'Cập nhật lớp học thành công!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        ClassModel::destroy($id);
        return redirect()->route('classes.index')->with('success', 'Xóa lớp học thành công!');
    }
}
