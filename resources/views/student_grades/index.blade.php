@extends('layouts.app')

@section('title', 'Điểm số theo năm học')

@section('content')
    <div class="container">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Điểm số theo năm học</h5>
                    <form method="GET" action="{{ route('student_grades') }}" class="ms-3">
                        <div class="input-group" style="width: 250px;">
                            <select name="academic_year_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Chọn năm học --</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}"
                                        {{ $selectedYearId == $year->id ? 'selected' : '' }}>{{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                            @if($selectedYearId)
                                <a href="{{ route('student_grades') }}" class="btn btn-outline-light">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body">
                @if($selectedYearId)
                    {{-- Hiển thị khi đã chọn năm học --}}
                    @if($hasData)
                        {{-- Hiển thị kết quả nếu có dữ liệu --}}


                        {{-- Tab học kỳ --}}
                        <div class="tab-container">
                            <ul class="nav nav-tabs" id="semesterTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="semester1-tab" data-bs-toggle="tab" data-bs-target="#semester1" type="button" role="tab" aria-controls="semester1" aria-selected="true">
                                        Học kỳ 1
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="semester2-tab" data-bs-toggle="tab" data-bs-target="#semester2" type="button" role="tab" aria-controls="semester2" aria-selected="false">
                                        Học kỳ 2
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="yearly-tab" data-bs-toggle="tab" data-bs-target="#yearly" type="button" role="tab" aria-controls="yearly" aria-selected="false">
                                        Cả năm
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="semesterTabsContent">
                                <div class="tab-pane fade show active" id="semester1" role="tabpanel" aria-labelledby="semester1-tab">
                                    @if($grades->has(1))
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0">Học kỳ 2</h5>
                                            <a href="{{ route('student_grades.detail', ['academic_year_id' => $selectedYearId, 'semester_id' => 2]) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-search-plus me-1"></i> Xem chi tiết
                                            </a>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Môn học</th>
                                                    <th width="120">Điểm</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($subjects as $subject)
                                                    @php
                                                        $grade = $grades[1][$subject->id][0] ?? null;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $subject->name }}</td>
                                                        <td class="text-center">
                                                            @if($grade)
                                                                {{ $grade->score }}
                                                            @else
                                                                <span class="text-muted">Chưa có điểm</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Học kỳ 1 chưa có dữ liệu điểm.
                                        </div>
                                    @endif
                                </div>
                                <div class="tab-pane fade" id="semester2" role="tabpanel" aria-labelledby="semester2-tab">
                                    @if($grades->has(2))
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0">Học kỳ 2</h5>
                                            <a href="{{ route('student_grades.detail', ['academic_year_id' => $selectedYearId, 'semester_id' => 2]) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-search-plus me-1"></i> Xem chi tiết
                                            </a>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Môn học</th>
                                                    <th width="120">Điểm</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($subjects as $subject)
                                                    @php
                                                        $grade = $grades[2][$subject->id][0] ?? null;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $subject->name }}</td>
                                                        <td class="text-center">
                                                            @if($grade)
                                                                {{ $grade->score }}
                                                            @else
                                                                <span class="text-muted">Chưa có điểm</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Học kỳ 2 chưa có dữ liệu điểm.
                                        </div>
                                    @endif
                                </div>
                                <div class="tab-pane fade" id="yearly" role="tabpanel" aria-labelledby="yearly-tab">
                                    <h5 class="mb-3">Tổng hợp điểm các môn cả năm</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Môn học</th>
                                                <th width="120">Điểm HK1</th>
                                                <th width="120">Điểm HK2</th>
                                                <th width="120">Điểm cả năm</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($subjects as $subject)
                                                @php
                                                    $grade1 = $grades[1][$subject->id][0] ?? null;
                                                    $grade2 = $grades[2][$subject->id][0] ?? null;
                                                    $yearlyGrade = $yearlyResults['subject_grades'][$subject->id] ?? null;
                                                @endphp
                                                <tr>
                                                    <td>{{ $subject->name }}</td>
                                                    <td class="text-center">
                                                        @if($grade1)
                                                            {{ $grade1->score }}
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($grade2)
                                                            {{ $grade2->score }}
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($yearlyGrade)
                                                            {{ $yearlyGrade }}
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="alert alert-info">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Điểm trung bình cả năm:</strong> {{ $yearlyResults['yearly_avg'] ?? 'N/A' }}
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Xếp loại:</strong> {{ $yearlyResults['classification'] ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Hiển thị thông báo nếu năm học không có dữ liệu --}}
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Năm học này chưa có dữ liệu điểm.
                        </div>
                    @endif
                @else
                    {{-- Hiển thị khi chưa chọn năm học --}}
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Vui lòng chọn năm học để xem điểm.
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Kích hoạt tab khi trang được tải
            document.addEventListener('DOMContentLoaded', function() {
                // Kiểm tra nếu có hash trong URL
                if (window.location.hash) {
                    const tabTrigger = new bootstrap.Tab(document.querySelector(
                        `a[href="${window.location.hash}"][data-bs-toggle="tab"]`
                    ));
                    tabTrigger.show();
                }
            });
        </script>
    @endpush
@endsection
