@extends('layouts.app')

@section('content')

    @if(auth()->user()->role != 'student')
        <div class="container-fluid rounded-3 shadow p-4">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h3 class="mb-3 text-primary-color">Tổng quan @if(isset($currentSchool))
                        ({{ $currentSchool->name }})
                    @endif</h3>
                <div class="d-flex">
                    <form method="GET" action="" class="d-flex">
                        <div class="me-2">
                            <select name="academic_year" id="academic-year-select" class="form-select">
                                @foreach($academicYears as $year)
                                    <option
                                        value="{{ $year->id }}" {{ $year->id == $currentAcademicYear->id ? 'selected' : '' }}>{{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if($request->has('school_id'))
                            <input type="hidden" name="school_id" value="{{ $request->school_id }}">
                        @endif
                    </form>
                </div>
            </div>

            @if(isset($error_message))
                <div class="alert alert-warning" role="alert">
                    {{ $error_message }}
                </div>
            @endif

            <div class="row">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Giáo viên
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $teacherCount }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Học sinh
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $currentYearStudentCount }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Lớp học
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $classCount }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-school fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Phân bổ học sinh theo khối</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-pie pt-4 pb-2">
                                <canvas id="gradeDistributionChart"></canvas>
                            </div>
                            <div class="mt-4 text-center small">
                                @foreach($gradeDistribution as $grade)
                                    <span class="mr-2">
                                        <i class="fas fa-circle" style="color: {{ $grade['color'] }}"></i> Khối {{ $grade['grade_number'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Học lực học sinh</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-pie pt-4 pb-2">
                                <canvas id="academicPerformanceChart"></canvas>
                            </div>
                            <div class="mt-4 text-center small">
                                @foreach($academicPerformanceData['labels'] as $index => $label)
                                    <span class="mr-2">
                        <i class="fas fa-circle" style="color: {{ $academicPerformanceData['colors'][$index] }}"></i> {{ $label }}
                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Thống kê học lực theo khối</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                    <tr>
                                        <th rowspan="2">Khối</th>
                                        <th colspan="5" class="text-center">Học kỳ 1</th>
                                        <th colspan="5" class="text-center">Học kỳ 2</th>
                                        <th colspan="5" class="text-center">Cả năm</th>
                                    </tr>
                                    <tr>
                                        @for($i = 0; $i < 3; $i++)
                                            <th>Tốt</th>
                                            <th>Khá</th>
                                            <th>Đạt</th>
                                            <th>Chưa đạt</th>
                                            <th>Chưa đánh giá</th>
                                        @endfor
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($academicPerformanceByGrade as $grade)
                                        <tr>
                                            <td>Khối {{ $grade['grade_number'] }}</td>
                                            @foreach(['semester1', 'semester2', 'year'] as $period)
                                                <td>{{ $grade[$period]['excellent'] }}
                                                    ({{ $grade[$period]['excellent_percent'] }}%)
                                                </td>
                                                <td>{{ $grade[$period]['good'] }}
                                                    ({{ $grade[$period]['good_percent'] }}%)
                                                </td>
                                                <td>{{ $grade[$period]['average'] }}
                                                    ({{ $grade[$period]['average_percent'] }}%)
                                                </td>
                                                <td>{{ $grade[$period]['weak'] }}
                                                    ({{ $grade[$period]['weak_percent'] }}%)
                                                </td>
                                                <td>{{ $grade[$period]['unrated'] }}
                                                    ({{ $grade[$period]['unrated_percent'] }}%)
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    <tr class="font-weight-bold">
                                        <td>Tổng</td>
                                        @foreach(['semester1', 'semester2', 'year'] as $period)
                                            <td>{{ $totalPerformance[$period]['excellent'] }}
                                                ({{ $totalPerformance[$period]['excellent_percent'] }}%)
                                            </td>
                                            <td>{{ $totalPerformance[$period]['good'] }}
                                                ({{ $totalPerformance[$period]['good_percent'] }}%)
                                            </td>
                                            <td>{{ $totalPerformance[$period]['average'] }}
                                                ({{ $totalPerformance[$period]['average_percent'] }}%)
                                            </td>
                                            <td>{{ $totalPerformance[$period]['weak'] }}
                                                ({{ $totalPerformance[$period]['weak_percent'] }}%)
                                            </td>
                                            <td>{{ $totalPerformance[$period]['unrated'] }}
                                                ({{ $totalPerformance[$period]['unrated_percent'] }}%)
                                            </td>
                                        @endforeach
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartOptions = {
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
            legend: {
                display: false
            },
            cutoutPercentage: 80,
        };

        $(document).ready(function () {
            const $gradeDistributionCtx = $('#gradeDistributionChart');
            if ($gradeDistributionCtx.length) {
                new Chart($gradeDistributionCtx[0].getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode(array_map(function($item) { return 'Khối ' . $item['grade_number']; }, $gradeDistribution)) !!},
                        datasets: [{
                            data: {!! json_encode(array_column($gradeDistribution, 'student_count')) !!},
                            backgroundColor: {!! json_encode(array_column($gradeDistribution, 'color')) !!},
                            hoverBackgroundColor: {!! json_encode(array_column($gradeDistribution, 'hover_color')) !!},
                            hoverBorderColor: "rgba(234, 236, 244, 1)",
                        }],
                    },
                    options: chartOptions
                });
            } else {
                console.error("Canvas element with ID 'gradeDistributionChart' not found.");
            }

            const $academicPerformanceCtx = $('#academicPerformanceChart');
            if ($academicPerformanceCtx.length) {
                new Chart($academicPerformanceCtx[0].getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($academicPerformanceData['labels']) !!},
                        datasets: [{
                            data: {!! json_encode($academicPerformanceData['data']) !!},
                            backgroundColor: {!! json_encode($academicPerformanceData['colors']) !!},
                            hoverBackgroundColor: {!! json_encode($academicPerformanceData['hoverColors']) !!},
                            hoverBorderColor: "rgba(234, 236, 244, 1)",
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    const label = data.labels[tooltipItem.index];
                                    const value = data.datasets[0].data[tooltipItem.index];
                                    return `${label}: ${value}%`;
                                }
                            }
                        },
                        legend: {
                            display: false
                        },
                        cutoutPercentage: 80,
                    }
                });
            }

            $('#academic-year-select').on('change', function () {
                let url = new URL(window.location.href);
                url.searchParams.set('academic_year', $(this).val());
                const schoolIdParam = url.searchParams.get('school_id');
                if (schoolIdParam) {
                    url.searchParams.set('school_id', schoolIdParam);
                }
                window.location.href = url.toString();
            });

            const $semesterSelect = $('#semester-select');
            if ($semesterSelect.length) {
                $semesterSelect.on('change', function () {
                    let url = new URL(window.location.href);
                    url.searchParams.set('academic_year', $('#academic-year-select').val());
                    url.searchParams.set('semester', $(this).val());
                    const schoolIdParam = url.searchParams.get('school_id');
                    if (schoolIdParam) {
                        url.searchParams.set('school_id', schoolIdParam);
                    }
                    window.location.href = url.toString();
                });
            }
        });
    </script>
@endpush
