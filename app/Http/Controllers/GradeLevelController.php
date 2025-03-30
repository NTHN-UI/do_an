<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use App\Models\School;
use Illuminate\Http\Request;

class GradeLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $gradeLevels = GradeLevel::with('school')->paginate(10);
        $schools = School::all();

        return view('grade_levels.index', compact('gradeLevels', 'schools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::all();
        return view('grade_levels.create', compact('schools'));
    }
    private function validateGradeNumber($school_id, $grade_number)
    {
        $school = School::findOrFail($school_id);

        switch ($school->education_level) {
            case 'primary':
                return $grade_number >= 1 && $grade_number <= 5;
            case 'secondary':
                return $grade_number >= 6 && $grade_number <= 9;
            case 'high':
                return $grade_number >= 10 && $grade_number <= 12;
            default:
                return false;
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'grade_number' => 'required|integer',
            'school_id' => 'required|exists:schools,id',
        ]);


        try {
            if (!$this->validateGradeNumber($request->school_id, $request->grade_number)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Số khối không hợp lệ với cấp học của trường!');
            }

            if (GradeLevel::where('school_id', $request->school_id)
                ->where('grade_number', $request->grade_number)
                ->exists()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Khối học này đã tồn tại trong trường!');
            }

            GradeLevel::create($request->all());

            return redirect()->route('grade_levels.index')
                ->with('success', 'Thêm khối học thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
}
    /**
     * Display the specified resource.
     */
    public function show(GradeLevel $gradeLevel)
    {
        $gradeLevel->load(['school', 'classes']);
        return view('grade_levels.show', compact('gradeLevel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GradeLevel $gradeLevel)
    {
        $schools = School::all();
        return view('grade_levels.edit', compact('gradeLevel', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GradeLevel $gradeLevel)
    {
        $request->validate([
            'grade_number' => 'required|integer',
            'school_id' => 'required|exists:schools,id',
        ]);

        try {
            if (!$this->validateGradeNumber($request->school_id, $request->grade_number)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Số khối không hợp lệ với cấp học của trường!');
            }

            if (GradeLevel::where('school_id', $request->school_id)
                ->where('grade_number', $request->grade_number)
                ->where('id', '!=', $gradeLevel->id)
                ->exists()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Khối học này đã tồn tại trong trường!');
            }

            $gradeLevel->update($request->all());

            return redirect()->route('grade_levels.index')
                ->with('success', 'Cập nhật khối học thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GradeLevel $gradeLevel)
    {
        try {
            if ($gradeLevel->classes()->exists()) {
                return redirect()->route('grade_levels.index')
                    ->with('error', 'Không thể xóa khối học vì có lớp học thuộc khối này!');
            }

            $gradeLevel->delete();

            return redirect()->route('grade_levels.index')
                ->with('success', 'Xóa khối học thành công!');
        } catch (\Exception $e) {
            return redirect()->route('grade_levels.index')
                ->with('error', 'Có lỗi xảy ra khi xóa: ' . $e->getMessage());
        }

    }
}
