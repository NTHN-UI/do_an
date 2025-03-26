<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $schools = School::when($search, function($query, $search) {
            return $query->where('name', 'like', "%$search%")
                ->orWhere('district', 'like', "%$search%")
                ->orWhere('province', 'like', "%$search%");
        })->paginate(10);

        return view('schools.index', compact('schools', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('schools.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'district' => 'required|max:255',
            'province' => 'required|max:255',
            'education_level' => 'required|in:primary,secondary,high',
        ]);

        School::create($request->all());

        return redirect()->route('schools.index')
            ->with('success', 'Thêm trường học thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school)
    {
        return view('schools.show', compact('school'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school)
    {
        return view('schools.edit', compact('school'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, School $school)
    {
        $request->validate([
            'name' => 'required|max:255',
            'district' => 'required|max:255',
            'province' => 'required|max:255',
            'education_level' => 'required|in:primary,secondary,high',
        ]);

        $school->update($request->all());

        return redirect()->route('schools.index')
            ->with('success', 'Cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        $school->delete();

        return redirect()->route('schools.index')
            ->with('success', 'Xóa trường học thành công!');
    }
}
