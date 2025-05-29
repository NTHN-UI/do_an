@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-white rounded shadow-sm">
            <div class="d-flex align-items-center">
                <a href="{{ url()->previous() }}" class="btn btn-outline-primary me-3 rounded-circle" title="Quay lại">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="mb-0 text-primary-color fw-bold">
                        <i class="fas fa-chart-line me-2"></i>Quản lý điểm học sinh
                    </h2>
                    <small class="text-muted">Theo dõi và quản lý kết quả học tập</small>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary-color text-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-filter me-2"></i>Bộ lọc dữ liệu
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="GET" action="{{ route('grades.index') }}" id="filter-form">
                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <label for="academic_year_id" class="form-label fw-semibold text-primary-color">
                                    <i class="fas fa-calendar-alt me-1"></i>Năm học *
                                </label>
                                <select class="form-select form-select-lg border-2" id="academic_year_id" name="academic_year_id" required>
                                    <option value="">-- Chọn năm học --</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ $selectedAcademicYearId == $year->id ? 'selected' : '' }}>
                                            {{ $year->year }} ({{ $year->start_date->format('d/m/Y') }} - {{ $year->end_date->format('d/m/Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <label for="class_id" class="form-label fw-semibold text-primary-color">
                                    <i class="fas fa-users me-1"></i>Lớp học *
                                </label>
                                <select class="form-select form-select-lg border-2" id="class_id" name="class_id" required>
                                    <option value="">-- Chọn lớp --</option>
                                    @foreach($assignedClasses as $classId => $assignments)
                                        @php $class = $assignments->first()->class; @endphp
                                        <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-12">
                            <div class="form-group">
                                <label for="semester_id" class="form-label fw-semibold text-primary-color">
                                    <i class="fas fa-book me-1"></i>Học kỳ *
                                </label>
                                <select class="form-select form-select-lg border-2" id="semester_id" name="semester_id" required>
                                    <option value="">-- Chọn học kỳ --</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}" {{ $selectedSemesterId == $semester->id ? 'selected' : '' }}>
                                            {{ $semester->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        @if($selectedAcademicYearId && $selectedClassId && $selectedSemesterId)
                            <a href="{{ route('grades.exportTemplate', [
                                'academic_year_id' => $selectedAcademicYearId,
                                'class_id' => $selectedClassId,
                                'semester_id' => $selectedSemesterId
                            ]) }}" class="btn btn-primary-color btn-lg px-4" id="btn-download">
                                <i class="fas fa-download me-2"></i>Tải file mẫu
                            </a>
                        @else
                            <button class="btn btn-outline-secondary btn-lg px-4" disabled
                                    title="Vui lòng chọn đầy đủ năm học, lớp và học kỳ">
                                <i class="fas fa-download me-2"></i>Tải file mẫu
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Grades Table Section -->
        <div id="grades-container">
            @if($selectedClassId && $selectedSemesterId !== null)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-primary-color fw-bold">
                                <i class="fas fa-table me-2"></i>Bảng điểm học sinh
                            </h5>
                            <small class="text-muted">Dữ liệu được cập nhật theo thời gian thực</small>
                        </div>
                        <button type="button" class="btn btn-primary-color btn-lg px-4" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-upload me-2"></i>Import điểm
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 600px;">
                            <table class="table table-hover table-striped mb-0" id="dataTable">
                                <thead class="bg-primary-color text-white sticky-top">
                                <tr>
                                    <th rowspan="2" class="text-center align-middle">
                                        <i class="fas fa-id-card me-1"></i>Mã HS
                                    </th>
                                    <th rowspan="2" class="text-center align-middle">
                                        <i class="fas fa-user me-1"></i>Họ và tên
                                    </th>
                                    @foreach($subjectsTaught as $subject)
                                        @if($selectedSemesterId == 0)
                                            <th colspan="2" class="text-center">
                                                <i class="fas fa-book me-1"></i>{{ $subject->name }}
                                            </th>
                                        @else
                                            <th colspan="5" class="text-center">
                                                <i class="fas fa-book me-1"></i>{{ $subject->name }}
                                            </th>
                                        @endif
                                    @endforeach
                                    <th rowspan="2" class="text-center align-middle">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        @if($selectedSemesterId == 0)
                                            Điểm TB cả năm
                                        @else
                                            Điểm TB HK
                                        @endif
                                    </th>
                                </tr>
                                <tr>
                                    @foreach($subjectsTaught as $subject)
                                        @if($selectedSemesterId == 0)
                                            <th class="text-center">HK1</th>
                                            <th class="text-center">HK2</th>
                                        @else
                                            <th class="text-center">15p 1</th>
                                            <th class="text-center">15p 2</th>
                                            <th class="text-center">15p 3</th>
                                            <th class="text-center">1 tiết</th>
                                            <th class="text-center">Cuối kỳ</th>
                                        @endif
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($students as $student)
                                    <tr>
                                        <td class="text-center fw-semibold">{{ $student->id }}</td>
                                        <td class="fw-semibold text-primary-color">{{ $student->full_name }}</td>

                                        @foreach($subjectsTaught as $subject)
                                            @php
                                                $subjectData = $grades[$student->id][$subject->id] ?? [];
                                                $isSpecialSubject = $subjectData['is_special'] ?? false;
                                            @endphp

                                            @if($selectedSemesterId == 0)
                                                <!-- Hiển thị điểm cả năm -->
                                                <td class="text-center">
                                                    @if($isSpecialSubject)
                                                        <span class="badge {{ ($subjectData['semester1_text'] ?? '-') == 'Đạt' ? 'bg-success' : 'bg-warning' }}">
                                                                {{ $subjectData['semester1_text'] ?? '-' }}
                                                            </span>
                                                    @else
                                                        <span class="badge bg-info">{{ $subjectData['semester1_avg'] ?? '-' }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($isSpecialSubject)
                                                        <span class="badge {{ ($subjectData['semester2_text'] ?? '-') == 'Đạt' ? 'bg-success' : 'bg-warning' }}">
                                                                {{ $subjectData['semester2_text'] ?? '-' }}
                                                            </span>
                                                    @else
                                                        <span class="badge bg-info">{{ $subjectData['semester2_avg'] ?? '-' }}</span>
                                                    @endif
                                                </td>
                                            @else
                                                <!-- Hiển thị điểm học kỳ -->
                                                <td class="text-center">
                                                    @if($isSpecialSubject)
                                                        {{ $subjectData['fifteen_minutes'][0]->text_value ?? '' }}
                                                    @else
                                                        {{ $subjectData['fifteen_minutes'][0]->score ?? '' }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($isSpecialSubject)
                                                        {{ $subjectData['fifteen_minutes'][1]->text_value ?? '' }}
                                                    @else
                                                        {{ $subjectData['fifteen_minutes'][1]->score ?? '' }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($isSpecialSubject)
                                                        {{ $subjectData['fifteen_minutes'][2]->text_value ?? '' }}
                                                    @else
                                                        {{ $subjectData['fifteen_minutes'][2]->score ?? '' }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($isSpecialSubject)
                                                        {{ $subjectData['one_period']->text_value ?? '' }}
                                                    @else
                                                        {{ $subjectData['one_period']->score ?? '' }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($isSpecialSubject)
                                                        {{ $subjectData['semester']->text_value ?? '' }}
                                                    @else
                                                        {{ $subjectData['semester']->score ?? '' }}
                                                    @endif
                                                </td>
                                            @endif
                                        @endforeach

                                        <!-- Điểm trung bình -->
                                        <td class="text-center fw-bold">
                                            @if($selectedSemesterId == 0)
                                                <span class="badge bg-primary-color fs-6">{{ $grades[$student->id]['yearly_average'] ?? '-' }}</span>
                                            @else
                                                @php
                                                    $allSubjectsPassed = true;
                                                    $hasSpecialSubjects = false;
                                                    $numericAverage = 0;
                                                    $numericCount = 0;
                                                    $hasAnyGrade = false;

                                                    foreach($subjectsTaught as $subject) {
                                                        $subjectData = $grades[$student->id][$subject->id] ?? [];

                                                        // Xử lý môn đặc biệt
                                                        if ($subjectData['is_special'] ?? false) {
                                                            $hasSpecialSubjects = true;
                                                            $semesterValue = $subjectData['semester']->text_value ?? '';
                                                            if ($semesterValue !== '') {
                                                                $hasAnyGrade = true;
                                                            }
                                                            if ($semesterValue !== 'Đạt') {
                                                                $allSubjectsPassed = false;
                                                            }
                                                        }
                                                        // Xử lý môn thường
                                                        else {
                                                            $subjectAvg = $subjectData['average'] ?? null;
                                                            if ($subjectAvg !== null && $subjectAvg > 0) {
                                                                $numericAverage += $subjectAvg;
                                                                $numericCount++;
                                                                $hasAnyGrade = true;
                                                            }
                                                        }
                                                    }

                                                    if (!$hasAnyGrade) {
                                                        echo '<span class="text-muted">-</span>';
                                                    } elseif ($hasSpecialSubjects) {
                                                        $result = $allSubjectsPassed ? 'Đạt' : 'Chưa đạt';
                                                        $badgeClass = $allSubjectsPassed ? 'bg-success' : 'bg-danger';
                                                        echo "<span class='badge $badgeClass fs-6'>$result</span>";
                                                    } elseif ($numericCount > 0) {
                                                        $avg = round($numericAverage / $numericCount, 1);
                                                        echo "<span class='badge bg-primary-color fs-6'>$avg</span>";
                                                    } else {
                                                        echo '<span class="text-muted">-</span>';
                                                    }
                                                @endphp
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Enhanced Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary-color text-white">
                    <h5 class="modal-title fw-bold" id="importModalLabel">
                        <i class="fas fa-upload me-2"></i>Import điểm từ file Excel
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Lưu ý:</strong> Chỉ chấp nhận file Excel (.xlsx, .xls) theo mẫu đã tải về từ hệ thống.
                    </div>

                    <div class="form-group">
                        <label for="fileInput" class="form-label fw-semibold text-primary-color">
                            <i class="fas fa-file-excel me-1"></i>Chọn file Excel
                        </label>
                        <div class="input-group">
                            <input type="file" class="form-control form-control-lg border-2" id="fileInput" name="file"
                                   accept=".xlsx,.xls" required>
                            <span class="input-group-text bg-light">
                                <i class="fas fa-file-excel text-success"></i>
                            </span>
                        </div>
                        <div class="form-text text-muted mt-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Đảm bảo file Excel tuân theo đúng format mẫu để tránh lỗi khi import.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Hủy bỏ
                    </button>
                    <button type="button" class="btn btn-primary-color px-4">
                        <i class="fas fa-check me-2"></i>Xác nhận Import
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Kiểm tra và cập nhật trạng thái nút tải file
        function updateDownloadButton() {
            const academicYear = $('#academic_year_id').val();
            const classId = $('#class_id').val();
            const semesterId = $('#semester_id').val();

            if (academicYear && classId && semesterId) {
                $('#btn-download').removeClass('btn-outline-secondary').addClass('btn-primary-color')
                    .prop('disabled', false).removeAttr('title');

                // Cập nhật link download
                const downloadUrl = "{{ route('grades.exportTemplate') }}" +
                    `?academic_year_id=${academicYear}&class_id=${classId}&semester_id=${semesterId}`;
                $('#btn-download').attr('href', downloadUrl);
            } else {
                $('#btn-download').removeClass('btn-primary-color').addClass('btn-outline-secondary')
                    .prop('disabled', true)
                    .attr('title', 'Vui lòng chọn đầy đủ năm học, lớp và học kỳ');
            }
        }

        // Gọi khi trang load
        updateDownloadButton();

        $(document).ready(function(){
            $("#academic_year_id, #class_id, #semester_id").on("change", () => {
                updateDownloadButton();
                if ($('#academic_year_id').val() && $('#class_id').val() && $('#semester_id').val()) {
                    $('#filter-form').submit();
                }
            })

            // Xử lý khi năm học thay đổi
            $('#academic_year_id').change(function () {
                var academicYearId = $(this).val();

                if (!academicYearId) {
                    $('#class_id').empty().append('<option value="">-- Chọn lớp --</option>');
                    $('#semester_id').empty().append('<option value="">-- Chọn học kỳ --</option>');
                    return;
                }

                $.ajax({
                    url: '/grades/get-classes-by-year',
                    method: 'GET',
                    data: {academic_year_id: academicYearId},
                    success: function (data) {
                        var $classSelect = $('#class_id').empty().append('<option value="">-- Chọn lớp --</option>');

                        if (data && data.length > 0) {
                            $(data).each(function (index, value) {
                                let html = $('<option></option>').attr('value', value.id).text(value.name);
                                $classSelect.append(html);
                            });
                        }
                    }
                });

                // Load danh sách học kỳ
                $.ajax({
                    url: '/grades/get-semesters-by-year',
                    method: 'GET',
                    data: {academic_year_id: academicYearId},
                    success: function (data) {
                        var $semesterSelect = $('#semester_id').empty().append('<option value="">-- Chọn học kỳ --</option>');

                        if (data && data.length > 0) {
                            $(data).each(function (index, value) {
                                let html = ($('<option></option>').attr('value', value.id).text(value.name));
                                $semesterSelect.append(html);
                            });
                        }
                        // Thêm option "Cả năm"
                        $semesterSelect.append($('<option></option>').attr('value', 0).text('Cả năm'));
                    }
                });
            });

            // Xử lý hiển thị tên file khi chọn
            $('#fileInput').on('change', function () {
                let fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $(this).addClass('is-valid');
                } else {
                    $(this).removeClass('is-valid');
                }
            });

            // Xử lý import điểm
            $('#importModal').on('click', '.btn-primary-color', function() {
                const fileInput = $('#fileInput')[0];
                const file = fileInput.files[0];

                if (!file) {
                    alert('Vui lòng chọn file Excel để import');
                    return;
                }

                // Hiển thị loading
                $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...').prop('disabled', true);

                const formData = new FormData();
                formData.append('grades_file', file);
                formData.append('class_id', $('#class_id').val());
                formData.append('semester_id', $('#semester_id').val());
                formData.append('academic_year_id', $('#academic_year_id').val());
                formData.append('subject_name', '{{ $subjectsTaught->first()?->name }}');
                formData.append('class_name', $('#class_id option:selected').text());
                formData.append("_token", window.csrfToken);

                $.ajax({
                    url: '{{ route("grades.import") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#importModal').modal('hide');
                        location.reload(); // Reload trang để hiển thị dữ liệu mới
                    },
                    error: function(xhr) {
                        alert('Lỗi khi import: ' + xhr.responseJSON.message);
                    },
                    complete: function() {
                        $('#importModal .btn-primary-color').html('<i class="fas fa-check me-2"></i>Xác nhận Import').prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush
