@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h3 class="mb-3 text-primary-color fw-bold">Quản lý điểm lớp chủ nhiệm</h3>

        {{-- Filter Section --}}
        <div class="card shadow-sm border-0 mb-4 rounded-3">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('grades.homeroom') }}" id="filter-form">
                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <label for="academic_year_id" class="form-label fw-semibold">Năm học</label>
                                <select class="form-select border-2 rounded-3" id="academic_year_id" name="academic_year_id" onchange="this.form.submit()">
                                    @foreach($homeroomClasses->unique('academic_year_id') as $assignment)
                                        <option value="{{ $assignment->academic_year_id }}"
                                            {{ $selectedAcademicYearId == $assignment->academic_year_id ? 'selected' : '' }}>
                                            {{ $assignment->academicYear->year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <label for="class_id" class="form-label fw-semibold">Lớp chủ nhiệm</label>
                                <select class="form-select border-2 rounded-3" id="class_id" name="class_id" onchange="this.form.submit()">
                                    @foreach($homeroomClasses->where('academic_year_id', $selectedAcademicYearId) as $assignment)
                                        <option value="{{ $assignment->class_id }}"
                                            {{ $selectedClassId == $assignment->class_id ? 'selected' : '' }}>
                                            {{ $assignment->class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Grades Table Section --}}
        @if($students->isNotEmpty())
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light py-3 rounded-top-3">
                    <h6 class="m-0 text-primary-color fw-bold">
                        Bảng điểm lớp {{ $class->name }} - Năm học {{ $homeroomClasses->firstWhere('academic_year_id', $selectedAcademicYearId)->academicYear->year }}
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px;">
                        <table class="table table-hover table-striped mb-0 table-custom-bordered table-sticky-header">
                            <thead class="bg-primary-color text-white">
                            <tr>
                                <th rowspan="2" class="text-center align-middle">Mã HS</th>
                                <th rowspan="2" class="text-center align-middle">Họ và tên</th>
                                @foreach($subjects as $subject)
                                    <th colspan="3" class="text-center align-middle">{{ $subject->name }}</th>
                                @endforeach
                                <th colspan="3" class="text-center align-middle">Tổng hợp</th>
                            </tr>
                            <tr>
                                @foreach($subjects as $subject)
                                    <th class="text-center align-middle">HK1</th>
                                    <th class="text-center align-middle">HK2</th>
                                    <th class="text-center align-middle">CN</th>
                                @endforeach
                                <th class="text-center align-middle">ĐTB HK1</th>
                                <th class="text-center align-middle">ĐTB HK2</th>
                                <th class="text-center align-middle">ĐTB CN</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($students as $student)
                                @php
                                    $result = $studentResults[$student->id] ?? [];
                                @endphp
                                <tr>
                                    <td class="text-center fw-semibold">{{ $student->id }}</td>
                                    <td class="fw-semibold text-primary-color">{{ $student->full_name }}</td>

                                    @foreach($subjects as $subject)
                                        @php
                                            $hk1Display = $result['semester1']['display_subjects'][$subject->id] ?? '-';
                                            $hk2Display = $result['semester2']['display_subjects'][$subject->id] ?? '-';
                                            $cnDisplay = $result['yearly']['display_subjects'][$subject->id] ?? '-';
                                        @endphp
                                        <td class="text-center align-middle">
                                            @if($hk1Display !== '-')
                                                <strong class="text-primary-color">{{ $hk1Display }}</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($hk2Display !== '-')
                                                <strong class="text-primary-color">{{ $hk2Display }}</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($cnDisplay !== '-')
                                                <strong class="text-primary-color">{{ $cnDisplay }}</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    {{-- Display Average Grades --}}
                                    <td class="text-center fw-bold text-primary-color">
                                        @if(isset($result['semester1']['average']))
                                            {{ number_format($result['semester1']['average'], 1) }}
                                            @if(isset($result['semester1']['classification']) && $result['semester1']['classification'] !== '')
                                                <br><small class="text-muted">({{ $result['semester1']['classification'] }})</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold text-primary-color">
                                        @if(isset($result['semester2']['average']))
                                            {{ number_format($result['semester2']['average'], 1) }}
                                            @if(isset($result['semester2']['classification']) && $result['semester2']['classification'] !== '')
                                                <br><small class="text-muted">({{ $result['semester2']['classification'] }})</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold text-primary-color">
                                        @if(isset($result['yearly']['average']))
                                            {{ number_format($result['yearly']['average'], 1) }}
                                            @if(isset($result['yearly']['classification']) && $result['yearly']['classification'] !== '')
                                                <br><small class="text-muted">({{ $result['yearly']['classification'] }})</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info alert-custom-info rounded-2 shadow-sm">
                <i class="fas fa-info-circle me-2"></i>
                Không có học sinh nào trong lớp này hoặc chưa có dữ liệu điểm.
            </div>
        @endif
    </div>
@endsection

