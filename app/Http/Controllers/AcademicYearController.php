<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private function getValidationRules(bool $isUpdate = false, int $academicYearId = null, Request $request = null): array
    {
        $schoolId = auth()->user()->school_id;

        $rules = [
            'year' => [
                'required',
                'string',
                'max:9',
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('academic_years')->where(function ($query) use ($schoolId) {
                    return $query->where('school_id', $schoolId);
                })->ignore($academicYearId),
                function ($attribute, $value, $fail) {
                    [$start, $end] = explode('-', $value);
                    if ((int)$end - (int)$start !== 1) {
                        $fail('Năm học phải là hai năm liên tiếp');
                    }
                }
            ],
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$request) return;
                    $inputDate = date_create($value);
                    if (preg_match('/^(\d{4})-(\d{4})$/', $request->year, $matches)) {
                        $startYear = $matches[1];
                        $month = $inputDate->format('m');
                        if ($inputDate->format('Y') != $startYear || ($month != '08' && $month != '09')) {
                            $fail('Năm học phải bắt đầu trong tháng 8 hoặc tháng 9 năm ' . $startYear);
                        }
                    }
                }
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
                function ($attribute, $value, $fail) use ($request) {
                    $inputDate = date_create($value);
                    if (preg_match('/^(\d{4})-(\d{4})$/', $request->year, $matches)) {
                        $endYear = $matches[2];
                        $startDate = date_create($request->start_date);

                        if ($inputDate->format('Y') != $endYear) {
                            $fail('Năm kết thúc phải là ' . $endYear);
                            return;
                        }

                        $maxEndDate = date_create($endYear . '-07-30');
                        if ($inputDate > $maxEndDate) {
                            $fail('Năm học phải kết thúc trước ngày 30/07');
                            return;
                        }

                        $minEndDate = (clone $startDate)->modify('+8 months');
                        if ($inputDate < $minEndDate) {
                            $fail('Năm học phải kéo dài ít nhất 8 tháng');
                        }
                    }
                }
            ]
        ];

        return $rules;
    }

    private function getValidationMessages(): array
    {
        return [
            'year.unique' => 'Năm học đã tồn tại',
            'year.regex' => 'Định dạng năm học không hợp lệ',
            'year.required' => 'Năm học không được để trống',
            'start_date.required' => 'Ngày bắt đầu không được để trống',
            'end_date.required' => 'Ngày kết thúc không được để trống',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
            'start_date.custom_start' => 'Năm học phải bắt đầu từ tháng 9',
            'end_date.custom_end' => 'Năm học THPT phải kết thúc trước ngày 30/6',
            'end_date.min_duration' => 'Năm học phải kéo dài ít nhất 8 tháng'
        ];
    }
    public function index()
    {
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('year', 'DESC')
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
    public function store(Request $request)    {

        $validator = Validator::make(
            $request->all(),
            $this->getValidationRules(false, null, $request),
            $this->getValidationMessages()
        );

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

        $validator = Validator::make(
            $request->all(),
            $this->getValidationRules(true, $academicYear->id, $request),
            $this->getValidationMessages()
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $academicYear->update([
            'year' => $request->year,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        return redirect()->route('academic_years.index')
            ->with('success', 'Cập nhật năm học thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
    }
}
