<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('school_auto_id', 'asc')
            ->paginate(10);

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
        $school = auth()->user()->school;

        $messages = [
            'year.unique' => 'Năm học đã tồn tại',
            'year.regex' => 'Định dạng năm học không hợp lệ',
            'year.required' => 'Năm học không được để trống',
            'start_date.required' => 'Ngày bắt đầu không được để trống',
            'end_date.required' => 'Ngày kết thúc không được để trống',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
            'start_date.custom_start' => 'Năm học phải bắt đầu từ tháng 9',
            'end_date.custom_end' => function ($attribute, $value, $parameters) use($school) {
                return match($school->education_level){
                    School::LEVEL_SECONDARY => 'Năm học THCS phải kết thúc trước ngày 15/06',
                    School::LEVEL_HIGH => 'Năm học THPT phải kết thúc trước ngày 30/6',
                    default => 'Năm học phải kết thúc trong tháng 5 hoặc 6'
                };
            }
        ];

        $validator = Validator::make($request->all(), [
            'year' => [
                'required',
                'string',
                'max:9',
                'unique:academic_years,year,NULL,id,school_id,'.auth()->user()->school_id,
                'regex:/^\d{4}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    list($start, $end) = explode('-', $value);
                    if ((int)$end - (int)$start !== 1) {
                        $fail('Năm học phải là hai năm liên tiếp');
                    }
                }
            ],
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    $inputDate = date_create($value);
                    if (preg_match('/^(\d{4})-(\d{4})$/', $request->year, $matches)) {
                        $startYear = $matches[1];
                        if ($inputDate->format('Y') != $startYear || $inputDate->format('m') != '09') {
                            $fail('Năm học phải bắt đầu trong tháng 9 năm '.$startYear);
                        }
                    }
                }
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
                function ($attribute, $value, $fail) use ($request, $school) {
                    $inputDate = date_create($value);
                    if (preg_match('/^(\d{4})-(\d{4})$/', $request->year, $matches)) {
                        $endYear = $matches[2];

                        // Kiểm tra năm kết thúc phải là năm sau năm bắt đầu
                        $startDate = date_create($request->start_date);
                        if ($inputDate->format('Y') != $endYear) {
                            $fail('Năm kết thúc phải là '.$endYear);
                            return;
                        }

                        // Kiểm tra theo cấp học
                        $isValid = match($school->education_level){
                            School::LEVEL_SECONDARY =>
                                ($inputDate->format('m') == '05' && $inputDate->format('d') <= 31) ||
                                ($inputDate->format('m') == '06' && $inputDate->format('d') <= 15),

                            School::LEVEL_HIGH =>
                                ($inputDate->format('m') == '05' && $inputDate->format('d') <= 31) ||
                                ($inputDate->format('m') == '06' && $inputDate->format('d') <= 30),

                            default => false
                        };

                        if (!$isValid) {
                            $fail(match($school->education_level){
                                School::LEVEL_SECONDARY => 'Năm học THCS phải kết thúc trước ngày 15/06',
                                School::LEVEL_HIGH => 'Năm học THPT phải kết thúc trước ngày 30/06',
                                default => 'Ngày kết thúc không hợp lệ'
                            });
                        }

                        // Kiểm tra thời lượng tối thiểu 8 tháng
                        $interval = $startDate->diff($inputDate);
                        if ($interval->y < 1 && $interval->m < 8) {
                            $fail('Năm học phải kéo dài ít nhất 8 tháng');
                        }
                    }
                }
            ]
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        AcademicYear::create([
            'school_id' => auth()->user()->school_id,
            'year' => $request->year,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('academic_years.index')
            ->with('success', 'Thêm năm học thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $academicYear = AcademicYear::where('school_id', auth()->user()->school_id)
            ->findOrFail($id);
        return view('academic_years.show', compact('academicYear'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $academicYear = AcademicYear::where('school_id', auth()->user()->school_id)
            ->findOrFail($id);
        return view('academic_years.edit', compact('academicYear'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        if ($academicYear->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $school = auth()->user()->school;

        $messages = [
            'year.unique' => 'Năm học đã tồn tại',
            'year.regex' => 'Định dạng năm học không hợp lệ',
            'year.required' => 'Năm học không được để trống',
            'start_date.required' => 'Ngày bắt đầu không được để trống',
            'end_date.required' => 'Ngày kết thúc không được để trống',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
            'start_date.custom_start' => 'Năm học phải bắt đầu từ tháng 9',
            'end_date.custom_end' => function ($attribute, $value, $parameters) use($school) {
                return match($school->education_level){
                    School::LEVEL_SECONDARY => 'Năm học THCS phải kết thúc trước ngày 15/06',
                    School::LEVEL_HIGH => 'Năm học THPT phải kết thúc trước ngày 30/6',
                    default => 'Năm học phải kết thúc trong tháng 5 hoặc 6'
                };
            }
        ];

        $validator = Validator::make($request->all(), [
            'year' => [
                'required',
                'string',
                'max:9',
                'unique:academic_years,year,'.$academicYear->id.',id,school_id,'.auth()->user()->school_id,
                'regex:/^\d{4}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    list($start, $end) = explode('-', $value);
                    if ((int)$end - (int)$start !== 1) {
                        $fail('Năm học phải là hai năm liên tiếp');
                    }
                }
            ],
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    $inputDate = date_create($value);
                    if (preg_match('/^(\d{4})-(\d{4})$/', $request->year, $matches)) {
                        $startYear = $matches[1];
                        if ($inputDate->format('Y') != $startYear || $inputDate->format('m') != '09') {
                            $fail('Năm học phải bắt đầu trong tháng 9 năm '.$startYear);
                        }
                    }
                }
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
                function ($attribute, $value, $fail) use ($request, $school) {
                    $inputDate = date_create($value);
                    if (preg_match('/^(\d{4})-(\d{4})$/', $request->year, $matches)) {
                        $endYear = $matches[2];

                        // Kiểm tra năm kết thúc phải là năm sau năm bắt đầu
                        $startDate = date_create($request->start_date);
                        if ($inputDate->format('Y') != $endYear) {
                            $fail('Năm kết thúc phải là '.$endYear);
                            return;
                        }

                        // Kiểm tra theo cấp học
                        $isValid = match($school->education_level){
                            School::LEVEL_SECONDARY =>
                                ($inputDate->format('m') == '05' && $inputDate->format('d') <= 31) ||
                                ($inputDate->format('m') == '06' && $inputDate->format('d') <= 15),

                            School::LEVEL_HIGH =>
                                ($inputDate->format('m') == '05' && $inputDate->format('d') <= 31) ||
                                ($inputDate->format('m') == '06' && $inputDate->format('d') <= 30),

                            default => false
                        };

                        if (!$isValid) {
                            $fail(match($school->education_level){
                                School::LEVEL_SECONDARY => 'Năm học THCS phải kết thúc trước ngày 15/06',
                                School::LEVEL_HIGH => 'Năm học THPT phải kết thúc trước ngày 30/06',
                                default => 'Ngày kết thúc không hợp lệ'
                            });
                        }

                        // Kiểm tra thời lượng tối thiểu 8 tháng
                        $interval = $startDate->diff($inputDate);
                        if ($interval->y < 1 && $interval->m < 8) {
                            $fail('Năm học phải kéo dài ít nhất 8 tháng');
                        }
                    }
                }
            ]
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $academicYear->update($request->all());

        return redirect()->route('academic_years.index')
            ->with('success', 'Cập nhật năm học thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
        // Kiểm tra quyền: chỉ được xóa năm học của trường hiện tại
        if ($academicYear->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        // Kiểm tra các bảng phụ thuộc
        $dependencies = [];

        if ($academicYear->semesters()->exists()) {
            $dependencies[] = 'Học kỳ';
        }

        if ($academicYear->classes()->exists()) {
            $dependencies[] = 'Lớp học';
        }

        if ($academicYear->studentClasses()->exists()) {
            $dependencies[] = 'Phân công học sinh';
        }

        if ($academicYear->teacherAssignments()->exists()) {
            $dependencies[] = 'Phân công giáo viên';
        }

        if ($academicYear->grades()->exists()) {
            $dependencies[] = 'Điểm số';
        }

        // Nếu có dữ liệu phụ thuộc, không cho phép xóa
        if (!empty($dependencies)) {
            $message = 'Không thể xóa năm học vì có dữ liệu liên quan: ' . implode(', ', $dependencies);
            return redirect()->back()->with('error', $message);
        }

        try {
            $school_id = $academicYear->school_id;
            $academicYear->delete();

            // Cập nhật lại STT cho các năm học còn lại
            $this->reorderAcademicYearNumbers($school_id);

            return redirect()->route('academic_years.index')
                ->with('success', 'Xóa năm học thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Xóa năm học thất bại: ' . $e->getMessage());
        }
    }
    private function reorderAcademicYearNumbers($school_id)
    {
        $academicYears = AcademicYear::where('school_id', $school_id)
            ->orderBy('school_auto_id')
            ->get();

        foreach ($academicYears as $index => $academicYear) {
            $academicYear->school_auto_id = $index + 1;
            $academicYear->save();
        }
    }
}
