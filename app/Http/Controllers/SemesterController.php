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
    private function getValidationRules(bool $isUpdate = false, Semester $semester = null): array
    {

        $rules = [
            'name' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($isUpdate, $semester) {
                    if (!in_array($value, ['Học kỳ I', 'Học kỳ II'])) {
                        $fail('Tên học kỳ phải là "Học kỳ I" hoặc "Học kỳ II"');
                        return;
                    }

                    $exists = Semester::where('academic_year_id', request('academic_year_id'))
                        ->where('school_id', auth()->user()->school_id)
                        ->where('name', $value)
                        ->when($isUpdate, fn($q) => $q->where('id', '!=', $semester->id))
                        ->exists();

                    if ($exists) {
                        $fail('Năm học này đã có ' . $value . ' rồi');
                    }
                }
            ],
            'academic_year_id' => 'required|exists:academic_years,id,school_id,'.auth()->user()->school_id,
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (!request('academic_year_id') || !in_array(request('name'), ['Học kỳ I', 'Học kỳ II'])) {
                        return;
                    }

                    $academicYear = AcademicYear::find(request('academic_year_id'));
                    if (!$academicYear) return;

                    $startDate = date_create($value);
                    $month = $startDate->format('m');
                    $year = $startDate->format('Y');

                    if (request('name') === 'Học kỳ I' && $month !== '09') {
                        $fail('Học kỳ I phải bắt đầu vào tháng 9');
                    }

                    if (request('name') === 'Học kỳ II' && $month !== '01') {
                        $fail('Học kỳ II phải bắt đầu vào tháng 1');
                    }

                    if ($startDate < $academicYear->start_date) {
                        $fail('Ngày bắt đầu học kỳ không được trước ngày bắt đầu năm học');
                    }

                    if ($startDate > $academicYear->end_date) {
                        $fail('Ngày bắt đầu học kỳ không được sau ngày kết thúc năm học');
                    }
                }
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
                function ($attribute, $value, $fail) {
                    if (!request('academic_year_id') || !request('start_date') || !in_array(request('name'), ['Học kỳ I', 'Học kỳ II'])) {
                        return;
                    }

                    $academicYear = AcademicYear::find(request('academic_year_id'));
                    if (!$academicYear) return;

                    $endDate = date_create($value);
                    $month = $endDate->format('m');

                    if (request('name') === 'Học kỳ I' && !in_array($month, ['12', '01'])) {
                        $fail('Học kỳ I phải kết thúc vào tháng 12 hoặc tháng 1');
                    }

                    if (request('name') === 'Học kỳ II' && !in_array($month, ['05', '06'])) {
                        $fail('Học kỳ II phải kết thúc vào tháng 5 hoặc tháng 6');
                    }

                    $startDate = date_create(request('start_date'));
                    $interval = $startDate->diff($endDate);
                    $totalMonths = $interval->y * 12 + $interval->m;

                    if ($totalMonths < 4 || $totalMonths > 5) {
                        $fail('Học kỳ phải kéo dài từ 4 đến 5 tháng');
                    }

                    if ($endDate < $academicYear->start_date) {
                        $fail('Ngày kết thúc học kỳ không được trước ngày bắt đầu năm học');
                    }

                    if ($endDate > $academicYear->end_date) {
                        $fail('Ngày kết thúc học kỳ không được sau ngày kết thúc năm học');
                    }
                }
            ],
            'is_current' => 'sometimes|boolean'
        ];

        return $rules;
    }

    private function getValidationMessages(): array
    {
        return [
            'name.required' => 'Tên học kỳ không được để trống',
            'academic_year_id.required' => 'Năm học không được để trống',
            'start_date.required' => 'Ngày bắt đầu không được để trống',
            'end_date.required' => 'Ngày kết thúc không được để trống',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
            'start_date.semester1_start' => 'Học kỳ I phải bắt đầu vào tháng 9',
            'start_date.semester2_start' => 'Học kỳ II phải bắt đầu vào tháng 1',
            'end_date.semester1_end' => 'Học kỳ I phải kết thúc vào tháng 12 hoặc tháng 1',
            'end_date.semester2_end' =>  'Học kỳ II phải kết thúc vào tháng 5 hoặc tháng 6',
            'duration.valid' => 'Học kỳ phải kéo dài từ 4 đến 5 tháng',
            'overlap.exists' => 'Khoảng thời gian này đã có học kỳ khác',
            'name.unique_semester' => 'Năm học này đã có :attribute rồi'
        ];
    }

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
            $validator = Validator::make(
                $request->all(),
                $this->getValidationRules(),
                $this->getValidationMessages()
            );

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
        $validator = Validator::make(
            $request->all(),
            $this->getValidationRules(true, $semester),
            $this->getValidationMessages()
        );
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
    }
}
