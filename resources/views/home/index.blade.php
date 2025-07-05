@extends('layouts.app')

@section('content')

    @if(auth()->user()->role != 'student')
        <div class="container-fluid rounded-3 shadow p-4">
            <!-- Header Dashboard -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h3 class="mb-3 text-primary-color">Tổng quan @if(isset($currentSchool)) ({{ $currentSchool->name }}) @endif</h3>
                <div class="d-flex">
                    <form method="GET" action="" class="d-flex">
                        <div class="me-2">
                            {{-- THÊM id="academic-year-select" VÀO ĐÂY --}}
                            <select name="academic_year" id="academic-year-select" class="form-select">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->id == $currentAcademicYear->id ? 'selected' : '' }}>{{ $year->year }}
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

            {{-- Hiển thị thông báo lỗi nếu không tìm thấy năm học --}}
            @if(isset($error_message))
                <div class="alert alert-warning" role="alert">
                    {{ $error_message }}
                </div>
            @endif

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
                                        {{ $currentYearStudentCount }} {{-- Đảm bảo sử dụng biến này --}}
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
            </div> {{-- End of row for summary cards --}}

            <div class="row">
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

                <!-- Học lực học sinh -->
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
                                <span class="mr-2">
                                    <i class="fas fa-circle text-secondary"></i> Chưa đánh giá
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div> {{-- End of row for charts --}}

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
            </div> {{-- End of row for detailed performance table --}}

        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Cấu hình chung cho biểu đồ
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

        // Khởi tạo biểu đồ khi trang tải xong
        document.addEventListener('DOMContentLoaded', function() {
            // Chart phân bổ khối
            const gradeDistributionCtx = document.getElementById('gradeDistributionChart');
            if (gradeDistributionCtx) {
                new Chart(gradeDistributionCtx.getContext('2d'), {
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


            // Chart học lực
            const academicPerformanceCtx = document.getElementById('academicPerformanceChart');
            if (academicPerformanceCtx) {
                new Chart(academicPerformanceCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Tốt', 'Khá', 'Đạt', 'Chưa đạt', 'Chưa đánh giá'], // Thêm 'Chưa đánh giá'
                        datasets: [{
                            data: [
                                {{ $totalPerformance['year']['excellent'] }},
                                {{ $totalPerformance['year']['good'] }},
                                {{ $totalPerformance['year']['average'] }},
                                {{ $totalPerformance['year']['weak'] }},
                                {{ $totalPerformance['year']['unrated'] }} // Thêm dữ liệu cho 'Chưa đánh giá'
                            ],
                            backgroundColor: ['#1cc88a', '#36b9cc', '#4e73df', '#f6c23e', '#858796'], // Thêm màu cho 'Chưa đánh giá'
                            hoverBackgroundColor: ['#17a673', '#2c9faf', '#2e59d9', '#dda20a', '#6d6e7a'], // Thêm màu hover
                            hoverBorderColor: "rgba(234, 236, 244, 1)",
                        }],
                    },
                    options: chartOptions
                });
            } else {
                console.error("Canvas element with ID 'academicPerformanceChart' not found.");
            }
        });

        // Xử lý thay đổi năm học
        document.getElementById('academic-year-select').addEventListener('change', function() {
            let url = new URL(window.location.href);
            url.searchParams.set('academic_year', this.value);
            // Giữ lại tham số school_id nếu có
            const schoolIdParam = url.searchParams.get('school_id');
            if (schoolIdParam) {
                url.searchParams.set('school_id', schoolIdParam);
            }
            window.location.href = url.toString();
        });

        // Xử lý thay đổi học kỳ (nếu có select học kỳ) - cần id="semester-select" trên thẻ select
        // Nếu bạn có một thẻ select cho học kỳ, hãy thêm id="semester-select" vào đó.
        // Ví dụ: <select name="semester" id="semester-select" class="form-select">...</select>
        const semesterSelect = document.getElementById('semester-select');
        if (semesterSelect) {
            semesterSelect.addEventListener('change', function() {
                let url = new URL(window.location.href);
                url.searchParams.set('academic_year', document.getElementById('academic-year-select').value);
                url.searchParams.set('semester', this.value);
                const schoolIdParam = url.searchParams.get('school_id');
                if (schoolIdParam) {
                    url.searchParams.set('school_id', schoolIdParam);
                }
                window.location.href = url.toString();
            });
        }
    </script>
@endpush
