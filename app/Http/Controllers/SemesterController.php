<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SemesterController extends Controller
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

        // Lấy danh sách học kỳ theo năm học được chọn
        $semesters = Semester::with('academicYear')
            ->where('school_id', auth()->user()->school_id)
            ->where('academic_year_id', $selectedYearId)
            ->paginate(10);

        return view('semesters.index', compact('semesters', 'academicYears', 'selectedYearId'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $selectedYearId = session('selected_academic_year_id');

        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)->get();

        return view('semesters.create', compact('academicYears', 'selectedYearId'));
    }

    /**
     * Store a newly created resource in storage.
     */
        public function store(Request $request)
        {
            $school = auth()->user()->school;

            $messages = [
                'name.required' => 'Tên học kỳ không được để trống',
                'academic_year_id.required' => 'Năm học không được để trống',
                'start_date.required' => 'Ngày bắt đầu không được để trống',
                'end_date.required' => 'Ngày kết thúc không được để trống',
                'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
                'start_date.semester1_start' => 'Học kỳ 1 phải bắt đầu vào tháng 9',
                'start_date.semester2_start' => 'Học kỳ 2 phải bắt đầu vào tháng 1',
                'end_date.semester1_end' => 'Học kỳ 1 phải kết thúc vào tháng 12 hoặc tháng 1',
                'end_date.semester2_end' =>  'Học kỳ 2 phải kết thúc vào tháng 5 hoặc tháng 6',
                'duration.valid' => 'Học kỳ phải kéo dài từ 4 đến 5 tháng',
                'overlap.exists' => 'Khoảng thời gian này đã có học kỳ khác',
                'name.unique_semester' => 'Năm học này đã có :attribute rồi'
            ];
            // Lấy năm học từ session nếu không có trong request
            $academicYearId = $request->input('academic_year_id', session('selected_academic_year_id'));
            $request->merge(['academic_year_id' => $academicYearId]);

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:50',
                    function ($attribute, $value, $fail) use ($request) {
                        if (!in_array($value, ['Học kỳ 1', 'Học kỳ 2'])) {
                            $fail('Tên học kỳ phải là "Học kỳ 1" hoặc "Học kỳ 2"');
                            return;
                        }
                        // Chỉ kiểm tra trùng nếu academic_year_id hợp lệ
                        if ($request->academic_year_id) {
                            $exists = Semester::where('academic_year_id', $request->academic_year_id)
                                ->where('school_id', auth()->user()->school_id)
                                ->where('name', $value)
                                ->exists();

                            if ($exists) {
                                $fail('Năm học này đã có ' . $value . ' rồi');
                            }
                        }
                    }
                ],
                'academic_year_id' => 'required|exists:academic_years,id,school_id,'.auth()->user()->school_id,
                'start_date' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        // Chỉ kiểm tra nếu có academic_year_id và name hợp lệ
                        if (!$request->academic_year_id || !in_array($request->name, ['Học kỳ 1', 'Học kỳ 2'])) {
                            return;
                        }

                        $academicYear = AcademicYear::find($request->academic_year_id);
                        if (!$academicYear) return;

                        $startDate = date_create($value);
                        $month = $startDate->format('m');
                        $year = $startDate->format('Y');

                        if ($request->name === 'Học kỳ 1' && $month !== '09') {
                            $fail('Học kỳ 1 phải bắt đầu vào tháng 9');
                        }

                        if ($request->name === 'Học kỳ 2' && $month !== '01') {
                            $fail('Học kỳ 2 phải bắt đầu vào tháng 1');
                        }

                        if ($request->name === 'Học kỳ 1' && $year != $academicYear->start_date->format('Y')) {
                            $fail('Học kỳ 1 phải thuộc năm bắt đầu của năm học');
                        }
                    }
                ],
                'end_date' => [
                    'required',
                    'date',
                    'after:start_date',
                    function ($attribute, $value, $fail) use ($request, $school) {
                        // Chỉ kiểm tra nếu có đủ thông tin cần thiết
                        if (!$request->academic_year_id || !$request->start_date || !in_array($request->name, ['Học kỳ 1', 'Học kỳ 2'])) {
                            return;
                        }

                        $academicYear = AcademicYear::find($request->academic_year_id);
                        if (!$academicYear) return;

                        $endDate = date_create($value);
                        $month = $endDate->format('m');
                        $year = $endDate->format('Y');

                        if ($request->name === 'Học kỳ 1') {
                            if (!in_array($month, ['12', '01'])) {
                                $fail('Học kỳ 1 phải kết thúc vào tháng 12 hoặc tháng 1');
                            }
                        } else {
                            if (!in_array($month, ['05', '06'])) {
                                $fail('Học kỳ 2 phải kết thúc vào tháng 5 hoặc tháng 6');
                            }
                        }


                        // Kiểm tra thời lượng học kỳ
                        $startDate = date_create($request->start_date);
                        $interval = $startDate->diff($endDate);
                        $totalMonths = $interval->y * 12 + $interval->m;

                        if ($totalMonths < 4 || $totalMonths > 5) {
                            $fail('Học kỳ phải kéo dài từ 4 đến 5 tháng');
                        }
                    }
                ],
                'is_current' => 'sometimes|boolean'
            ], $messages);

            // Thêm validation kiểm tra trùng lịch học kỳ
            $validator->after(function ($validator) use ($request) {
                if ($validator->errors()->any() || !$request->academic_year_id || !$request->start_date || !$request->end_date) {
                    return;
                }

                $exists = Semester::where('academic_year_id', $request->academic_year_id)
                    ->where('school_id', auth()->user()->school_id)
                    ->where(function($query) use ($request) {
                        $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                            ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                            ->orWhere(function($q) use ($request) {
                                $q->where('start_date', '<=', $request->start_date)
                                    ->where('end_date', '>=', $request->end_date);
                            });
                    })->exists();

                if ($exists) {
                    $validator->errors()->add('start_date', 'Khoảng thời gian này đã có học kỳ khác trong cùng năm học');
                }
            });

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Xử lý is_current
            // Tạo học kỳ mới
            $validated = $validator->validated();
            $validated['school_id'] = auth()->user()->school_id;

            // Xử lý is_current
            $isCurrent = $request->input('is_current', false);

            if ($isCurrent) {
                // Đặt học kỳ mới là hiện tại
                $validated['is_current'] = true;

                // Tắt trạng thái hiện tại của tất cả học kỳ khác
                Semester::where('school_id', auth()->user()->school_id)
                    ->update(['is_current' => false]);
            } else {
                // Nếu không check thì mặc định là false
                $validated['is_current'] = false;
            }

            Semester::create($validated);

            return redirect()->route('semesters.index')
                ->with('success', 'Thêm học kỳ thành công!');
        }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $semester = Semester::where('school_id', auth()->user()->school_id)
            ->findOrFail($id);

        return view('semesters.show', compact('semester'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $semester = Semester::where('school_id', auth()->user()->school_id)
            ->findOrFail($id);

        // Lấy năm học từ session
        $selectedYearId = session('selected_academic_year_id');

        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)->get();

        return view('semesters.edit', compact('semester', 'academicYears', 'selectedYearId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Semester $semester)
    {
        if ($semester->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $school = auth()->user()->school;

        $messages = [
            'name.required' => 'Tên học kỳ không được để trống',
            'academic_year_id.required' => 'Năm học không được để trống',
            'start_date.required' => 'Ngày bắt đầu không được để trống',
            'end_date.required' => 'Ngày kết thúc không được để trống',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
            'start_date.semester1_start' => 'Học kỳ 1 phải bắt đầu vào tháng 9',
            'start_date.semester2_start' => 'Học kỳ 2 phải bắt đầu vào tháng 1',
            'end_date.semester1_end' => 'Học kỳ 1 phải kết thúc vào tháng 12 hoặc tháng 1',
            'end_date.semester2_end' =>  'Học kỳ 2 phải kết thúc vào tháng 5 hoặc tháng 6',
            'duration.valid' => 'Học kỳ phải kéo dài từ 4 đến 5 tháng',
            'overlap.exists' => 'Khoảng thời gian này đã có học kỳ khác',
            'name.unique_semester' => 'Năm học này đã có :attribute rồi'
        ];
        // Lấy năm học từ session nếu không có trong request
        $academicYearId = $request->input('academic_year_id', session('selected_academic_year_id'));

        // Thêm vào request để validation
        $request->merge(['academic_year_id' => $academicYearId]);

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($request, $semester) {
                    if (!in_array($value, ['Học kỳ 1', 'Học kỳ 2'])) {
                        $fail('Tên học kỳ phải là "Học kỳ 1" hoặc "Học kỳ 2"');
                        return;
                    }
                    // Chỉ kiểm tra trùng nếu academic_year_id hợp lệ
                    if ($request->academic_year_id) {
                        $exists = Semester::where('academic_year_id', $request->academic_year_id)
                            ->where('school_id', auth()->user()->school_id)
                            ->where('name', $value)
                            ->where('id', '!=', $semester->id)
                            ->exists();

                        if ($exists) {
                            $fail('Năm học này đã có ' . $value . ' rồi');
                        }
                    }
                }
            ],
            'academic_year_id' => 'required|exists:academic_years,id,school_id,'.auth()->user()->school_id,
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    // Chỉ kiểm tra nếu có academic_year_id và name hợp lệ
                    if (!$request->academic_year_id || !in_array($request->name, ['Học kỳ 1', 'Học kỳ 2'])) {
                        return;
                    }

                    $academicYear = AcademicYear::find($request->academic_year_id);
                    if (!$academicYear) return;

                    $startDate = date_create($value);
                    $month = $startDate->format('m');
                    $year = $startDate->format('Y');

                    if ($request->name === 'Học kỳ 1' && $month !== '09') {
                        $fail('Học kỳ 1 phải bắt đầu vào tháng 9');
                    }

                    if ($request->name === 'Học kỳ 2' && $month !== '01') {
                        $fail('Học kỳ 2 phải bắt đầu vào tháng 1');
                    }

                    if ($request->name === 'Học kỳ 1' && $year != $academicYear->start_date->format('Y')) {
                        $fail('Học kỳ 1 phải thuộc năm bắt đầu của năm học');
                    }
                }
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
                function ($attribute, $value, $fail) use ($request, $school) {
                    // Chỉ kiểm tra nếu có đủ thông tin cần thiết
                    if (!$request->academic_year_id || !$request->start_date || !in_array($request->name, ['Học kỳ 1', 'Học kỳ 2'])) {
                        return;
                    }

                    $academicYear = AcademicYear::find($request->academic_year_id);
                    if (!$academicYear) return;

                    $endDate = date_create($value);
                    $month = $endDate->format('m');
                    $year = $endDate->format('Y');

                    if ($request->name === 'Học kỳ 1') {
                        if (!in_array($month, ['12', '01'])) {
                            $fail('Học kỳ 1 phải kết thúc vào tháng 12 hoặc tháng 1');
                        }
                    } else {
                        if (!in_array($month, ['05', '06'])) {
                            $fail('Học kỳ 2 phải kết thúc vào tháng 5 hoặc tháng 6');
                        }
                    }

                    // Kiểm tra thời lượng học kỳ
                    $startDate = date_create($request->start_date);
                    $interval = $startDate->diff($endDate);
                    $totalMonths = $interval->y * 12 + $interval->m;

                    if ($totalMonths < 4 || $totalMonths > 5) {
                        $fail('Học kỳ phải kéo dài từ 4 đến 5 tháng');
                    }
                }
            ],
            'is_current' => 'sometimes|boolean'
        ], $messages);

        // Thêm validation kiểm tra trùng lịch học kỳ
        $validator->after(function ($validator) use ($request, $semester) {
            if ($validator->errors()->any() || !$request->academic_year_id || !$request->start_date || !$request->end_date) {
                return;
            }

            $exists = Semester::where('academic_year_id', $request->academic_year_id)
                ->where('school_id', auth()->user()->school_id)
                ->where('id', '!=', $semester->id)
                ->where(function($query) use ($request) {
                    $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                        ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                        ->orWhere(function($q) use ($request) {
                            $q->where('start_date', '<=', $request->start_date)
                                ->where('end_date', '>=', $request->end_date);
                        });
                })->exists();

            if ($exists) {
                $validator->errors()->add('start_date', 'Khoảng thời gian này đã có học kỳ khác trong cùng năm học');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Cập nhật học kỳ
        $validated = $validator->validated();
        // Chỉ xử lý is_current nếu checkbox được chọn
        $isCurrent = $request->input('is_current');
        if ($isCurrent) {
            // Đặt học kỳ này là hiện tại
            $validated['is_current'] = true;

            // Tắt trạng thái hiện tại của tất cả học kỳ khác
            Semester::where('school_id', auth()->user()->school_id)
                ->where('id', '!=', $semester->id)
                ->update(['is_current' => false]);
        } else {
            // Nếu không check thì giữ nguyên giá trị hiện tại
            $validated['is_current'] = $semester->is_current;
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
        // Kiểm tra quyền: chỉ được xóa học kỳ của trường hiện tại
        if ($semester->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        // Kiểm tra xem học kỳ này có phải là học kỳ hiện tại không
        if ($semester->is_current) {
            return redirect()->back()
                ->with('error', 'Không thể xóa học kỳ hiện tại. Vui lòng chọn học kỳ khác làm hiện tại trước.');
        }

        // Kiểm tra các bảng phụ thuộc
        $dependencies = [];

        // Kiểm tra xem năm học của học kỳ này có lớp học không
        if ($semester->academicYear->classes()->count() > 0) {
            $dependencies[] = 'lớp học (' . $semester->academicYear->classes()->count() . ')';
        }

        // Kiểm tra phân công học sinh trong năm học này
        if ($semester->academicYear->studentClasses()->count() > 0) {
            $dependencies[] = 'phân công học sinh (' . $semester->academicYear->studentClasses()->count() . ')';
        }

        // Kiểm tra phân công giáo viên trong năm học này
        if ($semester->academicYear->teacherAssignments()->count() > 0) {
            $dependencies[] = 'phân công giáo viên (' . $semester->academicYear->teacherAssignments()->count() . ')';
        }

        // Kiểm tra điểm số trong học kỳ này
        if ($semester->grades()->count() > 0) {
            $dependencies[] = 'điểm số (' . $semester->grades()->count() . ')';
        }

        // Nếu có dữ liệu phụ thuộc, không cho phép xóa
        if (!empty($dependencies)) {
            $message = 'Không thể xóa học kỳ "' . $semester->name . '" vì có dữ liệu liên quan: '
                . implode(', ', $dependencies) . '.';
            return redirect()->back()->with('error', $message);
        }

        try {
            $academic_year_id = $semester->academic_year_id;
            $semester_name = $semester->name;
            $semester->delete();

            return redirect()->route('semesters.index')
                ->with('success', 'Đã xóa học kỳ "' . $semester_name . '" thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Xóa học kỳ thất bại: ' . $e->getMessage());
        }
    }
}
