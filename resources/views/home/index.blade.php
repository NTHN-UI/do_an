@extends('layouts.app')

@section('content')

    @if(auth()->user()->role != 'student')
        <div class="container-fluid rounded-3 shadow p-4">
            <!-- Header Dashboard -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h3 class="mb-3 text-primary-color">Tổng quan</h3>
                <div class="d-flex">
                    <div class="custom-select-wrapper ">
                        <select id="academic-year-select" class="form-select">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->id == $currentAcademicYear->id ? 'selected' : '' }}>
                                    {{ $year->year }}
                                </option>
                            @endforeach
                        </select>
                        <div class="custom-select-arrow"></div>
                    </div>
                </div>
            </div>

            <!-- Thống kê tổng quan -->
            <div class="row">
                <!-- Giáo viên -->
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

                <!-- Học sinh -->
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

                <!-- Lớp học -->
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
                <!-- Phân bổ theo khối -->

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
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Tốt
                        </span>
                                    <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> Khá
                        </span>
                                    <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> Đạt
                        </span>
                                    <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Chưa đạt
                        </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thống kê học lực chi tiết -->
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
                                                        ({{ $grade[$period]['good_percent'] }}
                                                        %)
                                                    </td>
                                                    <td>{{ $grade[$period]['average'] }}
                                                        ({{ $grade[$period]['average_percent'] }}%)
                                                    </td>
                                                    <td>{{ $grade[$period]['weak'] }}
                                                        ({{ $grade[$period]['weak_percent'] }}
                                                        %)
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
        // Chart phân bổ khối
        var gradeCtx = document.getElementById('gradeDistributionChart').getContext('2d');
        var gradeChart = new Chart(gradeCtx, {
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
            options: {
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
            },
        });

        // Chart học lực
        var performanceCtx = document.getElementById('academicPerformanceChart').getContext('2d');
        var performanceChart = new Chart(performanceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Tốt', 'Khá', 'Đạt', 'Chưa đạt'],
                datasets: [{
                    data: [
                        {{ $totalPerformance['year']['excellent'] }},
                        {{ $totalPerformance['year']['good'] }},
                        {{ $totalPerformance['year']['average'] }},
                        {{ $totalPerformance['year']['weak'] }}
                    ],
                    backgroundColor: ['#1cc88a', '#36b9cc', '#4e73df', '#f6c23e'],
                    hoverBackgroundColor: ['#17a673', '#2c9faf', '#2e59d9', '#dda20a'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
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
            },
        });

        // Xử lý thay đổi năm học và học kỳ
        $('#academic-year-select, #semester-select').change(function () {
            window.location.href = "{{ route('home.index') }}?academic_year=" +
                $('#academic-year-select').val() +
                "&semester=" + $('#semester-select').val();
        });
    </script>
@endpush
