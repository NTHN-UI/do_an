<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\GradeLevel;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private function getValidationRules(bool $isUpdate = false, ClassModel $class = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($isUpdate, $class) {
                    $query = ClassModel::where('school_id', auth()->user()->school_id)
                        ->where('name', $value)
                        ->where('grade_level_id', request('grade_level_id'))
                        ->where('academic_year_id', request('academic_year_id'));

                    if ($isUpdate) {
                        $query->where('id', '!=', $class->id);
                    }

                    if ($query->exists()) {
                        $fail('Lớp học đã tồn tại trong khối và năm học đã chọn');
                    }
                }
            ],
            'grade_level_id' => [
                'required',
                Rule::exists('grade_levels', 'id')->where('school_id', auth()->user()->school_id)
            ],
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', auth()->user()->school_id)
            ]
        ];
    }

    private function getValidationMessages(): array
    {
        return [
            'name.required' => 'Tên lớp học không được để trống',
            'name.max' => 'Tối đa 50 ký tự',
            'grade_level_id.required' => 'Vui lòng chọn khối học',
            'academic_year_id.required' => 'Vui lòng chọn năm học',
            'name.unique_class' => 'Lớp học đã tồn tại trong khối và năm học đã chọn'
        ];
    }
    public function index(Request $request)
    {
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'asc')
            ->get();

        $selectedYearId = $request->input('academic_year_id',
            session('selected_academic_year_id', $academicYears->first()->id ?? null));

        session(['selected_academic_year_id' => $selectedYearId]);

        $classes = ClassModel::where('school_id', auth()->user()->school_id)
            ->when($selectedYearId, function($query) use ($selectedYearId) {
                return $query->where('academic_year_id', $selectedYearId);
            })
            ->with(['gradeLevel', 'academicYear'])
            ->orderBy('name')
            ->paginate(10);

        return view('classes.index', compact('classes', 'academicYears', 'selectedYearId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $selectedYearId = session('selected_academic_year_id');

        $gradeLevels = GradeLevel::where('school_id', auth()->user()->school_id)->get();
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)->get();

        return view('classes.create', compact('gradeLevels', 'academicYears', 'selectedYearId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $school = auth()->user()->school;

        $academicYearId = $request->input('academic_year_id', session('selected_academic_year_id'));

        $request->merge(['academic_year_id' => $academicYearId]);

        $validator = Validator::make(
            $request->all(),
            $this->getValidationRules(),
            $this->getValidationMessages()
        );
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $class = ClassModel::create([
            'name' => $request->name,
            'school_id' => $school->id,
            'grade_level_id' => $request->grade_level_id,
            'academic_year_id' => $request->academic_year_id,
        ]);


        return redirect()->route('classes.index')
            ->with('success', 'Thêm lớp học "' . $class->name . '" thành công!');
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $class = ClassModel::where('school_id', auth()->user()->school_id)
            ->with(['students', 'gradeLevel', 'academicYear'])
            ->findOrFail($id);
        return view('classes.show', compact('class'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $class = ClassModel::where('school_id', auth()->user()->school_id)
            ->findOrFail($id);

        $gradeLevels = GradeLevel::where('school_id', auth()->user()->school_id)->get();
        $selectedYearId = session('selected_academic_year_id');

        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)->get();

        return view('classes.edit', compact('class', 'gradeLevels', 'academicYears', 'selectedYearId'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $school = auth()->user()->school;
        $class = ClassModel::where('school_id', $school->id)
            ->findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            $this->getValidationRules(true, $class),
            $this->getValidationMessages()
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $class->update($request->only(['name', 'grade_level_id', 'academic_year_id']));

            return redirect()->route('classes.index')
                ->with('success', 'Cập nhật lớp học thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassModel $class)
    {
        try {
            $dependencies = [];

            if ($class->students()->exists()) {
                $dependencies[] = 'Học sinh';
            }

            if ($class->teacherAssignments()->exists()) {
                $dependencies[] = 'Giáo viên được phân công';
            }

            if (!empty($dependencies)) {
                $message = 'Không thể xóa lớp học vì có dữ liệu liên quan: ' . implode(', ', $dependencies);
                return redirect()->back()->with('error', $message);
            }

            $class->delete();

            return redirect()->route('classes.index')
                ->with('success', 'Xóa lớp học "' . $class->name . '" thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Xóa lớp học thất bại: ' . $e->getMessage());
        }
    }
}
