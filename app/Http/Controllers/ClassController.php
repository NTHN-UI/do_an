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
    public function index(Request $request)
    {
        // Lấy danh sách năm học
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'asc')
            ->get();

        // Lấy năm học được chọn (ưu tiên từ request, sau đó từ session, cuối cùng là năm đầu tiên)
        $selectedYearId = $request->input('academic_year_id',
            session('selected_academic_year_id', $academicYears->first()->id ?? null));

        // Lưu năm học đã chọn vào session
        session(['selected_academic_year_id' => $selectedYearId]);

        // Lấy danh sách lớp học theo năm học được chọn
        $classes = ClassModel::where('school_id', auth()->user()->school_id)
            ->when($selectedYearId, function($query) use ($selectedYearId) {
                return $query->where('academic_year_id', $selectedYearId);
            })
            ->with(['gradeLevel', 'academicYear'])
            ->orderBy('school_auto_id', 'asc')
            ->paginate(10);

        return view('classes.index', compact('classes', 'academicYears', 'selectedYearId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Lấy năm học từ session
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

        $messages = [
            'name.required' => 'Tên lớp học không được để trống',
            'name.max' => 'Tối đa 50 ký tự',
            'grade_level_id.required' => 'Vui lòng chọn khối học',
            'academic_year_id.required' => 'Vui lòng chọn năm học',
            'name.unique_class' => 'Lớp học đã tồn tại trong khối và năm học đã chọn'
        ];
        // Lấy năm học từ session nếu không có trong request
        $academicYearId = $request->input('academic_year_id', session('selected_academic_year_id'));

        // Thêm vào request để validation
        $request->merge(['academic_year_id' => $academicYearId]);

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($request, $school) {
                    if (ClassModel::where('school_id', $school->id)
                        ->where('name', $value)
                        ->where('grade_level_id', $request->grade_level_id)
                        ->where('academic_year_id', $request->academic_year_id)
                        ->exists()) {
                        $fail('Lớp học đã tồn tại trong khối và năm học đã chọn');
                    }
                }
            ],
            'grade_level_id' => [
                'required',
                Rule::exists('grade_levels', 'id')->where('school_id', $school->id)
            ],
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', $school->id)
            ]
        ], $messages);

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
        // Lấy năm học từ session
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

        $messages = [
            'name.required' => 'Tên lớp học không được để trống',
            'name.max' => 'Tối đa 50 ký tự',
            'grade_level_id.required' => 'Vui lòng chọn khối học',
            'academic_year_id.required' => 'Vui lòng chọn năm học',
                'name.unique_class' => 'Lớp học đã tồn tại trong khối và năm học đã chọn'
        ];

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($request, $school, $class) {
                    if (ClassModel::where('school_id', $school->id)
                        ->where('name', $value)
                        ->where('grade_level_id', $request->grade_level_id)
                        ->where('academic_year_id', $request->academic_year_id)
                        ->where('id', '!=', $class->id) // Loại trừ lớp hiện tại
                        ->exists()) {
                        $fail('Lớp học đã tồn tại trong khối và năm học đã chọn');
                    }
                }
            ],
            'grade_level_id' => [
                'required',
                Rule::exists('grade_levels', 'id')->where('school_id', $school->id)
            ],
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', $school->id)
            ]
        ], $messages);

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

            if ($class->teachers()->exists()) {
                $dependencies[] = 'Giáo viên';
            }

            if (!empty($dependencies)) {
                $message = 'Không thể xóa lớp học vì có dữ liệu liên quan: ' . implode(', ', $dependencies);
                return redirect()->back()->with('error', $message);
            }

            $school_id = $class->school_id;
            $class->delete();
            $this->reorderClassNumbers($school_id);

            return redirect()->route('classes.index')
                ->with('success', 'Xóa lớp học thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    private function reorderClassNumbers($school_id)
    {
        $classes = ClassModel::where('school_id', $school_id)
            ->orderBy('school_auto_id')
            ->get();

        foreach ($classes as $index => $class) {
            $class->school_auto_id = $index + 1;
            $class->save();
        }
    }
}
