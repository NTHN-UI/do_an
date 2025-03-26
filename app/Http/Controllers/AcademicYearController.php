<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $academicYears = AcademicYear::all();
        return view('academic_years.index', compact('academicYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('academic_years.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|string|max:255',
        ]);

        AcademicYear::create([
            'year' => $request->year,
        ]);

        return redirect()->route('academic_years.index')->with('success', 'Thêm năm học thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        return view('academic_years.show', compact('academicYear'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        return view('academic_years.edit', compact('academicYear'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'year' => 'required|string|max:255',
        ]);

        $academicYear = AcademicYear::findOrFail($id);
        $academicYear->update([
            'year' => $request->year,
        ]);

        return redirect()->route('academic_years.index')->with('success', 'Cập nhật năm học thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        $academicYear->delete();

        return redirect()->route('academic_years.index')->with('success', 'Xóa năm học thành công!');
    }
}
