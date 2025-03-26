<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $semesters = Semester::with('academicYear')->get();
        return view('semesters.index', compact('semesters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $academicYears = AcademicYear::all();
        return view('semesters.create', compact('academicYears'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id'
        ]);

        Semester::create($request->all());

        return redirect()->route('semesters.index')->with('success', 'Học kỳ đã được thêm!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $academicYears = AcademicYear::all();
        return view('semesters.create', compact('academicYears'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $semester = Semester::findOrFail($id);
        $academicYears = AcademicYear::all();
        return view('semesters.edit', compact('semester', 'academicYears'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id'
        ]);

        $semester = Semester::findOrFail($id);
        $semester->update($request->all());

        return redirect()->route('semesters.index')->with('success', 'Học kỳ đã được cập nhật!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $semester = Semester::findOrFail($id);
        $semester->delete();

        return redirect()->route('semesters.index')->with('success', 'Học kỳ đã được xóa!');
    }
}
