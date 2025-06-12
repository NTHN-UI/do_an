@extends('layouts.app')

@section('title', 'Điểm số theo năm học')

@section('content')
    <div class="container rounded-3 shadow p-4">
        {{-- Header Section - simplified to match 'Danh sách học kỳ' --}}
        <h3 class="mb-3 text-primary-color">Điểm số theo năm học</h3>

        {{-- Filter Section - moved outside the main card --}}
        <div class="row mb-3 justify-content-end">
            <div class="col-md-3">
                <form method="GET" action="{{ route('student_grades') }}">
                    <div class="input-group">
                        <select name="academic_year_id" class="form-select rounded-3" onchange="this.form.submit()"> {{-- Changed rounded-pill to rounded-3 for consistency --}}
                            <option value="">-- Chọn năm học --</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ $selectedYearId == $year->id ? 'selected' : '' }}>{{ $year->year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                @if($selectedYearId)
                    @if($hasData)
                        <div class="tab-container">
                            <ul class="nav nav-tabs" id="semesterTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-primary-color active" id="semester1-tab" data-bs-toggle="tab" data-bs-target="#semester1" type="button" role="tab" aria-controls="semester1" aria-selected="true">
                                        Học kỳ I
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-primary-color" id="semester2-tab" data-bs-toggle="tab" data-bs-target="#semester2" type="button" role="tab" aria-controls="semester2" aria-selected="false">
                                        Học kỳ II
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-primary-color" id="yearly-tab" data-bs-toggle="tab" data-bs-target="#yearly" type="button" role="tab" aria-controls="yearly" aria-selected="false">
                                        Cả năm
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content p-3 custom-border" id="semesterTabsContent">
                                <div class="tab-pane fade show active" id="semester1" role="tabpanel" aria-labelledby="semester1-tab">
                                    @if($grades->has(1))
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0 text-primary-color">Kết quả học kỳ I</h5>
                                            <a href="{{ route('student_grades.detail', ['academic_year_id' => $selectedYearId, 'semester_id' => 1]) }}"
                                               class="btn btn-sm btn-primary-color">Xem chi tiết
                                            </a>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0 rounded-3 overflow-hidden table-custom-bordered">
                                                <thead class="table-secondary">
                                                <tr>
                                                    <th>Môn học</th>
                                                    <th width="120" class="text-center">Điểm</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($subjects as $subject)
                                                    @php
                                                        $grade = $grades[1][$subject->id][0] ?? null;
                                                        $isSpecialSubject = in_array($subject->name, [
                                                            'Giáo dục quốc phòng và an ninh',
                                                            'Giáo dục thể chất',
                                                            'Nghệ thuật'
                                                        ]);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $subject->name }}</td>
                                                        <td class="text-center">
                                                            @if($grade)
                                                                @if($isSpecialSubject)
                                                                    <strong class="text-primary-color">{{ $grade->text_value ?? ($grade->score >= 5 ? 'Đạt' : 'Chưa đạt') }}</strong>
                                                                @else
                                                                    <strong class="text-primary-color">{{ number_format($grade->score, 1) }}</strong>
                                                                @endif
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
                                        <div class="alert alert-warning alert-custom-warning rounded-2 shadow-sm">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Học kỳ I chưa có dữ liệu điểm.
                                        </div>
                                    @endif
                                </div>
                                <div class="tab-pane fade" id="semester2" role="tabpanel" aria-labelledby="semester2-tab">
                                    @if($grades->has(2))
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0 text-primary-color">Kết quả học kỳ II</h5>
                                            <a href="{{ route('student_grades.detail', ['academic_year_id' => $selectedYearId, 'semester_id' => 2]) }}"
                                               class="btn btn-sm btn-primary-color">Xem chi tiết
                                            </a>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0 rounded-3 overflow-hidden table-custom-bordered">
                                                <thead class="table-secondary text-center"> {{-- Used table-secondary for consistent header --}}
                                                <tr>
                                                    <th>Môn học</th>
                                                    <th width="120" class="text-center">Điểm</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($subjects as $subject)
                                                    @php
                                                        $grade = $grades[2][$subject->id][0] ?? null;
                                                        $isSpecialSubject = in_array($subject->name, [
            'Giáo dục quốc phòng và an ninh',
            'Giáo dục thể chất',
            'Nghệ thuật'
        ]);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $subject->name }}</td>
                                                        <td class="text-center">
                                                            @if($grade)
                                                                @if($isSpecialSubject)
                                                                    <strong class="text-primary-color">{{ $grade->text_value ?? ($grade->score >= 5 ? 'Đạt' : 'Chưa đạt') }}</strong>
                                                                @else
                                                                    <strong class="text-primary-color">{{ number_format($grade->score, 1) }}</strong>
                                                                @endif
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
                                        <div class="alert alert-warning alert-custom-warning rounded-2 shadow-sm">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Học kỳ II chưa có dữ liệu điểm.
                                        </div>
                                    @endif
                                </div>
                                <div class="tab-pane fade" id="yearly" role="tabpanel" aria-labelledby="yearly-tab">
                                    <h5 class="mb-3 text-primary-color">Tổng hợp điểm các môn cả năm</h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0 rounded-3 overflow-hidden table-custom-bordered">
                                            <thead class="table-secondary text-center"> {{-- Used table-secondary for consistent header --}}
                                            <tr>
                                                <th>Môn học</th>
                                                <th width="120" class="text-center">Điểm HK1</th>
                                                <th width="120" class="text-center">Điểm HK2</th>
                                                <th width="120" class="text-center">Điểm cả năm</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($subjects as $subject)
                                                @php
                                                    $grade1 = $grades[1][$subject->id][0] ?? null;
                                                    $grade2 = $grades[2][$subject->id][0] ?? null;
                                                    $yearlyGrade = $yearlyResults['subject_grades'][$subject->id] ?? null;
                                                    $isSpecialSubject = in_array($subject->name, [
                                                        'Giáo dục quốc phòng và an ninh',
                                                        'Giáo dục thể chất',
                                                        'Nghệ thuật'
                                                    ]);
                                                @endphp
                                                <tr>
                                                    <td>{{ $subject->name }}</td>
                                                    <td class="text-center">
                                                        @if($grade1)
                                                            @if($isSpecialSubject)
                                                                <strong class="text-primary-color">{{ $grade1->text_value ?? ($grade1->score >= 5 ? 'Đạt' : 'Chưa đạt') }}</strong>
                                                            @else
                                                                <strong class="text-primary-color">{{ number_format($grade1->score, 1) }}</strong>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($grade2)
                                                            @if($isSpecialSubject)
                                                                <strong class="text-primary-color">{{ $grade2->text_value ?? ($grade2->score >= 5 ? 'Đạt' : 'Chưa đạt') }}</strong>
                                                            @else
                                                                <strong class="text-primary-color">{{ number_format($grade2->score, 1) }}</strong>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($yearlyGrade)
                                                            @if($isSpecialSubject)
                                                                <strong class="text-primary-color">
                                                                    {{ is_array($yearlyGrade) ? $yearlyGrade['value'] : ($grade2 ? ($grade2->text_value ?? ($grade2->score >= 5 ? 'Đạt' : 'Chưa đạt')) : 'Chưa đạt') }}
                                                                </strong>
                                                            @else
                                                                <strong class="text-primary-color">
                                                                    {{ is_array($yearlyGrade) ? number_format($yearlyGrade['value'], 1) : number_format($yearlyGrade, 1) }}
                                                                </strong>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="alert alert-info alert-custom-info mt-4 rounded-2 shadow-sm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Điểm trung bình cả năm:</strong> <strong class="text-primary-color">{{ number_format($yearlyResults['yearly_avg'] ?? 'N/A', 1) }}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Xếp loại:</strong> <strong class="text-primary-color">{{ $yearlyResults['classification'] ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Hiển thị thông báo nếu năm học không có dữ liệu --}}
                        <div class="alert alert-warning alert-custom-warning rounded-2 shadow-sm">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Năm học này chưa có dữ liệu điểm.
                        </div>
                    @endif
                @else
                    {{-- Hiển thị khi chưa chọn năm học --}}
                    <div class="alert alert-info alert-custom-info rounded-2 shadow-sm">
                        <i class="fas fa-info-circle me-2"></i>
                        Vui lòng chọn năm học để xem điểm.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
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
