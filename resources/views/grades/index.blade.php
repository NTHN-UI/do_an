@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-white rounded shadow-sm">
            <div class="d-flex align-items-center">
                <div>
                    <h3 class="mb-0 text-primary-color fw-bold">Quản lý điểm học sinh
                    </h3>
                    <small class="text-muted">Theo dõi và quản lý kết quả học tập</small>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('grades.index') }}" id="filter-form">
                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group mt-2">
                                <label for="academic_year_id" class="form-label fw-semibold">
                                   Năm học
                                </label>
                                <select class="form-select border-2" id="academic_year_id" name="academic_year_id" required>
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
                            <div class="form-group mt-2">
                                <label for="class_id" class="form-label fw-semibold">Lớp học
                                </label>
                                <select class="form-select border-2" id="class_id" name="class_id" required>
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
                            <div class="form-group mt-2">
                                <label for="semester_id" class="form-label fw-semibold">Học kỳ
                                </label>
                                <select class="form-select border-2" id="semester_id" name="semester_id" required>
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

                    <div class="d-flex justify-content-end mt-3 download-container">
                        <button class="btn btn-outline-secondary px-3" id="btn-download" disabled
                                title="Vui lòng chọn đầy đủ năm học, lớp và học kỳ">
                            <i class="fas fa-download me-2"></i>Tải file mẫu
                        </button>
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
                            <h5 class="mb-0 text-primary-color fw-bold">Bảng điểm học sinh
                            </h5>
                            <small class="text-muted">Dữ liệu được cập nhật theo thời gian thực</small>
                        </div>
                        <button type="button" class="btn btn-primary-color px-2" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-upload me-2"></i>Import điểm
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 600px;">
                            <table class="table table-hover table-striped mb-0" id="dataTable">
                                <thead class="bg-primary-color text-white sticky-top">
                                <tr>
                                    <th rowspan="2" class="text-center align-middle">
                                        Mã HS
                                    </th>
                                    <th rowspan="2" class="text-center align-middle">
                                        Họ và tên
                                    </th>
                                    @foreach($subjectsTaught as $subject)
                                        @if($selectedSemesterId == 0)
                                            <th colspan="2" class="text-center">
                                               {{ $subject->name }}
                                            </th>
                                        @else
                                            <th colspan="5" class="text-center">
                                                {{ $subject->name }}
                                            </th>
                                        @endif
                                    @endforeach
                                    <th rowspan="2" class="text-center align-middle">
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
                                                            {{ $subjectData['semester1_text'] ?? '-' }}
                                                        @else
                                                            {{ $subjectData['semester1_avg'] ?? '-' }}
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($isSpecialSubject)
                                                            {{ $subjectData['semester2_text'] ?? '-' }}
                                                        @else
                                                            {{ $subjectData['semester2_avg'] ?? '-' }}
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
                                        <td class="text-center">
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

                                                        if ($selectedSemesterId == 0) {
                                                            // Xem điểm cả năm: lấy kết quả HK2
                                                            $resultValue = $subjectData['semester2_text'] ?? 'Chưa đạt';
                                                        } else {
                                                            // Xem điểm học kỳ: lấy kết quả học kỳ hiện tại
                                                            $resultValue = $subjectData['semester']->text_value ?? '';
                                                        }

                                                        if ($resultValue !== '') {
                                                            $hasAnyGrade = true;
                                                        }
                                                        if ($resultValue !== 'Đạt') {
                                                            $allSubjectsPassed = false;
                                                        }
                                                    }
                                                    // Xử lý môn thường
                                                    else {
                                                        if ($selectedSemesterId == 0) {
                                                            // Điểm cả năm: (HK1 + 2*HK2)/3
                                                            $subjectAvg = $subjectData['average'] ?? null;
                                                        } else {
                                                            // Điểm học kỳ: lấy điểm TB học kỳ
                                                            $subjectAvg = $subjectData['average'] ?? null;
                                                        }

                                                        if ($subjectAvg !== null && $subjectAvg > 0) {
                                                            $numericAverage += $subjectAvg;
                                                            $numericCount++;
                                                            $hasAnyGrade = true;
                                                        }
                                                    }
                                                }

                                                // Hiển thị kết quả
                                                if (!$hasAnyGrade) {
                                                        echo '-';
                                                    } elseif ($hasSpecialSubjects) {
                                                        $result = $allSubjectsPassed ? 'Đạt' : 'Chưa đạt';
                                                        echo $result;
                                                    } elseif ($numericCount > 0) {
                                                        $avg = round($numericAverage / $numericCount, 1);
                                                        echo $avg;
                                                    } else {
                                                        echo '-';
                                                    }
                                            @endphp
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
                    <div class="alert alert-warning border-0 mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Lưu ý quan trọng:</strong> File Excel phải đúng với lớp, môn học và học kỳ đã chọn.
                        Hệ thống sẽ kiểm tra và từ chối nếu không khớp.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Thông tin import:</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p><strong>Lớp:</strong> <span id="import-class-name">Chưa chọn</span></p>
                                <p><strong>Môn học:</strong> <span id="import-subject-name">Chưa chọn</span></p>
                                <p><strong>Học kỳ:</strong> <span id="import-semester-name">Chưa chọn</span></p>
                                <p><strong>Năm học:</strong> <span id="import-year-name">Chưa chọn</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-2">
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
                        <div class="form-check mb-3 mt-3">
                            <input type="checkbox" id="forceUpdate" name="force_update">
                            <label class="form-check-label" for="forceUpdate">
                                Ghi đè dữ liệu đã tồn tại
                            </label>
                            <div class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Nếu chọn, hệ thống sẽ cập nhật điểm đã có thay vì bỏ qua.
                            </div>
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
        $(document).ready(function(){
            // Hàm cập nhật nút download template
            function updateDownloadButton() {
                const academicYear = $('#academic_year_id').val();
                const classId = $('#class_id').val();
                const semesterId = $('#semester_id').val();
                const $btnDownload = $('#btn-download');

                if (academicYear && classId && semesterId) {
                    // Cập nhật link download
                    const downloadUrl = "{{ route('grades.export.template') }}" +
                        `?academic_year_id=${academicYear}&class_id=${classId}&semester_id=${semesterId}`;

                    // Thay thế button bằng link download nếu chưa có
                    if ($btnDownload.is('button')) {
                        $btnDownload.replaceWith(`
                    <a href="${downloadUrl}" class="btn btn-primary-color px-2" id="btn-download">
                        <i class="fas fa-download me-2"></i>Tải file mẫu
                    </a>
                    `);
                    } else {
                        // Nếu đã là thẻ a thì chỉ cập nhật href
                        $btnDownload.attr('href', downloadUrl);
                    }
                } else {
                    // Đảm bảo luôn có nút disabled khi không đủ điều kiện
                    if ($btnDownload.is('a')) {
                        $btnDownload.replaceWith(`
                    <button class="btn btn-outline-secondary btn-lg px-4" id="btn-download" disabled
                            title="Vui lòng chọn đầy đủ năm học, lớp và học kỳ">
                        <i class="fas fa-download me-2"></i>Tải file mẫu
                    </button>
                    `);
                    } else {
                        $btnDownload.prop('disabled', true);
                    }
                }
            }

            // Xử lý khi năm học thay đổi
            $('#academic_year_id').change(function() {
                const yearId = $(this).val();

                // Reset các dropdown phụ thuộc
                $('#class_id').empty().append('<option value="">-- Chọn lớp --</option>');
                $('#semester_id').empty().append('<option value="">-- Chọn học kỳ --</option>');

                if (yearId) {
                    // Load danh sách lớp mà giáo viên được phân công
                    $.ajax({
                        url: '{{ route("grades.get-assigned-classes") }}',
                        type: 'GET',
                        data: {
                            academic_year_id: yearId,
                            teacher_id: {{ Auth::id() }}
                        },
                        success: function(response) {
                            const classSelect = $('#class_id');
                            response.forEach(function(classItem) {
                                classSelect.append(
                                    `<option value="${classItem.id}">${classItem.name}</option>`
                                );
                            });
                        }
                    });

                    // Load danh sách học kỳ
                    $.ajax({
                        url: '{{ route("grades.get-semesters") }}',
                        type: 'GET',
                        data: { academic_year_id: yearId },
                        success: function(response) {
                            const semesterSelect = $('#semester_id');
                            response.forEach(function(semester) {
                                semesterSelect.append(
                                    `<option value="${semester.id}">${semester.name}</option>`
                                );
                            });
                            // Thêm option "Cả năm"
                            semesterSelect.append('<option value="0">Cả năm</option>');
                        }
                    });
                }

                updateDownloadButton();
            });

            // Xử lý khi lớp hoặc học kỳ thay đổi
            $('#class_id, #semester_id').change(function() {
                updateDownloadButton();

                if ($('#academic_year_id').val() && $('#class_id').val() && $('#semester_id').val()) {
                    // Hiển thị loading indicator
                    $('#grades-container').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');

                    // Lấy dữ liệu bằng AJAX
                    $.ajax({
                        url: $('#filter-form').attr('action'),
                        method: 'GET',
                        data: $('#filter-form').serialize(),
                        success: function(response) {
                            // Tách và lấy phần nội dung cần thiết từ response
                            var newContent = $(response).find('#grades-container').html();
                            $('#grades-container').html(newContent);
                        },
                        error: function() {
                            $('#grades-container').html('<div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu</div>');
                        }
                    });
                }
                updateDownloadButton();
            });

            // Xử lý khi lớp hoặc học kỳ thay đổi
            $('#class_id, #semester_id').change(function() {
                updateDownloadButton();

                if ($('#academic_year_id').val() && $('#class_id').val() && $('#semester_id').val()) {
                    // Hiển thị loading indicator
                    $('#grades-container').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');

                    // Lấy dữ liệu bằng AJAX
                    $.ajax({
                        url: $('#filter-form').attr('action'),
                        method: 'GET',
                        data: $('#filter-form').serialize(),
                        success: function(response) {
                            // Tách và lấy phần nội dung cần thiết từ response
                            var newContent = $(response).find('#grades-container').html();
                            $('#grades-container').html(newContent);
                        },
                        error: function() {
                            $('#grades-container').html('<div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu</div>');
                        }
                    });
                }
            });

            // Gọi khi trang load lần đầu
            updateDownloadButton();

            // Cập nhật thông tin import modal
            function updateImportModalInfo() {
                const academicYear = $('#academic_year_id').val();
                const classId = $('#class_id').val();
                const semesterId = $('#semester_id').val();

                if (academicYear && classId && semesterId) {
                    // Lấy thông tin từ các dropdown
                    const className = $('#class_id option:selected').text();
                    const semesterName = $('#semester_id option:selected').text();
                    const academicYearName = $('#academic_year_id option:selected').text();

                    // Lấy tên môn học từ tiêu đề bảng điểm (lấy môn đầu tiên)
                    const subjectName = $('#dataTable thead th.text-center').eq(2).text().trim();

                    // Cập nhật thông tin trong modal
                    $('#import-class-name').text(className);
                    $('#import-subject-name').text(subjectName);
                    $('#import-semester-name').text(semesterName);
                    $('#import-year-name').text(academicYearName);
                } else {
                    $('#import-class-name').text('Chưa chọn');
                    $('#import-subject-name').text('Chưa chọn');
                    $('#import-semester-name').text('Chưa chọn');
                    $('#import-year-name').text('Chưa chọn');
                }
            }

            // Gọi khi mở modal import
            $('#importModal').on('show.bs.modal', function() {
                updateImportModalInfo();
            });

            // Xử lý hiển thị tên file khi chọn
            $('#fileInput').on('change', function() {
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
                const academicYearId = $('#academic_year_id').val();
                const classId = $('#class_id').val();
                const semesterId = $('#semester_id').val();
                const subjectName = $('#import-subject-name').text();
                const forceUpdate = $('#forceUpdate').is(':checked') ? 1 : 0;

                if (!academicYearId || !classId || !semesterId) {
                    showAlert('danger', 'Vui lòng chọn đầy đủ năm học, lớp và học kỳ trước khi import');
                    return;
                }

                if (!fileInput.files || !fileInput.files[0]) {
                    showAlert('danger', 'Vui lòng chọn file Excel để import');
                    return;
                }

                const formData = new FormData();
                formData.append('grades_file', fileInput.files[0]);
                formData.append('class_id', classId);
                formData.append('semester_id', semesterId);
                formData.append('academic_year_id', academicYearId);
                formData.append('subject_name', subjectName);
                formData.append('force_update', forceUpdate);
                formData.append("_token", "{{ csrf_token() }}");

                $.ajax({
                    url: '{{ route('grades.import') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('.btn-primary-color', $('#importModal')).prop('disabled', true)
                            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang import...');
                    },
                    success: function(response) {
                        showAlert('success', response.message);
                        $('#importModal').modal('hide');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    },
                    error: function(xhr) {
                        let errorMessage = 'Đã xảy ra lỗi không xác định.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            errorMessage = xhr.responseText;
                        }
                        showAlert('danger', errorMessage);
                    },
                    complete: function() {
                        $('.btn-primary-color', $('#importModal')).prop('disabled', false)
                            .html('<i class="fas fa-check me-2"></i>Xác nhận Import');
                    }
                });
            });

            // Hàm hiển thị thông báo
            function showAlert(type, message) {
                const alertContainer = $('#app-alert-container');
                if (alertContainer.length === 0) {
                    $('body').prepend('<div id="app-alert-container" class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1060;"></div>');
                }

                const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

                $('#app-alert-container').append(alertHtml);
                setTimeout(() => {
                    $('#app-alert-container').find('.alert').first().alert('close');
                }, 5000);
            }
        });
    </script>
@endpush
