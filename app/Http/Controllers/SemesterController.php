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
        $semesters = Semester::with('academicYear')->paginate(10);
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
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'academic_year_id' => 'required|exists:academic_years,id',
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = Semester::where('academic_year_id', $request->academic_year_id)
                        ->where(function($query) use ($value, $request) {
                            $query->whereBetween('start_date', [$value, $request->end_date])
                                ->orWhereBetween('end_date', [$value, $request->end_date]);
                        })->exists();

                    if ($exists) {
                        $fail('Khoảng thời gian này đã có học kỳ khác trong cùng năm học');
                    }
                }
            ],
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'sometimes|boolean' // Thêm validate cho is_current
        ]);
        // Xử lý checkbox
        $validated['is_current'] = $request->has('is_current');

        // Nếu là học kỳ hiện tại, reset các học kỳ khác
        if ($validated['is_current']) {
            Semester::query()->update(['is_current' => false]);
        }

        Semester::create($validated);

        return redirect()->route('semesters.index')
            ->with('success', 'Thêm học kỳ thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Semester $semester)
    {
        return view('semesters.show', compact('semester'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Semester $semester)
    {
        $academicYears = AcademicYear::all();
        return view('semesters.edit', compact('semester', 'academicYears'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'academic_year_id' => 'required|exists:academic_years,id',
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request, $semester) {
                    $exists = Semester::where('academic_year_id', $request->academic_year_id)
                        ->where('id', '!=', $semester->id)
                        ->where(function ($query) use ($value, $request) {
                            $query->whereBetween('start_date', [$value, $request->end_date])
                                ->orWhereBetween('end_date', [$value, $request->end_date]);
                        })->exists();

                    if ($exists) {
                        $fail('Khoảng thời gian này đã có học kỳ khác trong cùng năm học');
                    }
                }
            ],
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'sometimes|boolean'
        ]);
        // Xử lý checkbox
        $validated['is_current'] = $request->has('is_current');

        // Nếu là học kỳ hiện tại, reset các học kỳ khác
        if ($validated['is_current']) {
            Semester::where('id', '!=', $semester->id)
                ->update(['is_current' => false]);
        }

        $semester->update($validated);

        return redirect()->route('semesters.index')
            ->with('success', 'Cập nhật học kỳ thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Semester $semester)
    {
        $semester->delete();
        return redirect()->route('semesters.index')
            ->with('success', 'Xóa học kỳ thành công!');
    }
}
