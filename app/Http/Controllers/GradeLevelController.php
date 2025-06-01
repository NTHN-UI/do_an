<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\GradeLevel;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GradeLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $gradeLevels = GradeLevel::with(['school', 'classes'])
            ->where('school_id', auth()->user()->school_id)
            ->whereIn('grade_number', [10, 11, 12])
            ->orderBy('grade_number')
            ->paginate(10);

        return view('grade_levels.index', compact('gradeLevels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return view('grade_levels.create');
    }
    private function validateGradeNumber($grade_number)
    {
        return $grade_number >= 10 && $grade_number <= 12;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $school = auth()->user()->school;

        $messages = [
            'grade_number.required' => 'Vui lòng nhập số khối',
            'grade_number.unique' => 'Khối học này đã tồn tại trong trường',
        ];

        $validator = Validator::make($request->all(), [
            'grade_number' => [
                'required',
                'integer',
                Rule::unique('grade_levels')->where(function ($query) use ($school) {
                    return $query->where('school_id', $school->id);
                }),
                function ($attribute, $value, $fail) {
                    if (!$this->validateGradeNumber($value)) {
                        $fail('Số khối không hợp lệ với trường');
                    }
                }

            ],
        ], $messages);



        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            GradeLevel::create([
                'grade_number' => $request->grade_number,
                'school_id' => $school->id,
            ]);

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
    public function show($id)
    {


        $gradeLevel = GradeLevel::where('school_id', auth()->user()->school_id)
            ->with(['classes' => function($query) {
                $query->with('academicYear')
                    ->orderBy('academic_year_id', 'desc')
                    ->orderBy('name', 'asc');
            }])
            ->findOrFail($id);

        // Nhóm lớp theo năm học
        $groupedClasses = ClassModel::with('academicYear')
            ->withCount('students')
            ->where('grade_level_id', $gradeLevel->id)
            ->get()
            ->groupBy('academic_year_id');
        return view('grade_levels.show', compact('gradeLevel', 'groupedClasses'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $gradeLevel = GradeLevel::where('school_id', auth()->user()->school_id)
            ->findOrFail($id);


        return view('grade_levels.edit', compact('gradeLevel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $school = auth()->user()->school;
        $gradeLevel = GradeLevel::where('school_id', $school->id)
            ->findOrFail($id);

        $messages = [
            'grade_number.required' => 'Vui lòng nhập số khối',
            'grade_number.unique' => 'Khối học này đã tồn tại trong trường',
        ];

        $validator = Validator::make($request->all(), [
            'grade_number' => [
                'required',
                'integer',
                Rule::unique('grade_levels')->where(function ($query) use ($school) {
                    return $query->where('school_id', $school->id);
                })->ignore($gradeLevel->id),
                function ($attribute, $value, $fail) {
                    if (!$this->validateGradeNumber($value)) {
                        $fail('Số khối không hợp lệ với trường');
                    }
                }
            ],
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $gradeLevel->update([
                'grade_number' => $request->grade_number,
            ]);

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
    public function destroy($id)
    {
        $gradeLevel = GradeLevel::where('school_id', auth()->user()->school_id)
            ->findOrFail($id);

        try {
            if ($gradeLevel->classes()->exists()) {
                return redirect()->route('grade_levels.index')
                    ->with('error', 'Không thể xóa khối học vì có lớp học thuộc khối này!');
            }

            $school_id = $gradeLevel->school_id;
            $gradeLevel->delete();

            return redirect()->route('grade_levels.index')
                ->with('success', 'Xóa khối học thành công!');
        } catch (\Exception $e) {
            return redirect()->route('grade_levels.index')
                ->with('error', 'Có lỗi xảy ra khi xóa: ' . $e->getMessage());
        }
    }
}
