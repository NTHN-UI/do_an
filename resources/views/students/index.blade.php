@extends('layouts.app')

@section('content')
    <style>
        .dropdown-menu {
            min-width: 80px;
        }

        .dropdown-item:active,
        .dropdown-item:focus {
            background-color: #013066 !important;
            color: white !important;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: var(--bs-white);
            border-color: var(--primary-color);
        }
    </style>
    <div class="container rounded-3 shadow p-4">
        <h3 class="mb-3 text-primary-color">Danh sách học sinh</h3>

        <div class="d-flex flex-column flex-md-row justify-content-end align-items-md-center mb-4 gap-3 header-actions">

            <div class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap">
                <form id="search-form" method="GET" action="{{ route('students.index') }}" class="search-form">
                    <div class="input-group shadow-sm">
                        <input type="text" name="search" class="form-control border-end-0"
                               placeholder="Tìm kiếm học sinh..."
                               value="{{ request('search') }}"
                               id="search-input">
                        <button type="submit" class="btn btn-primary-color px-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                <div class="d-flex gap-2">
                    <a href="{{ route('students.create') }}" class="btn btn-primary-color">
                        Thêm mới
                    </a>
                    <a href="{{ route('class_assignments.index') }}" class="btn btn-primary-color">Phân lớp
                    </a>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('students.index') }}" id="filter-form">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="academic_year_id" class="form-label">Năm học</label>
                            <select class="form-select" id="academic_year_id" name="academic_year_id">
                                <option value="">-- Chọn năm học --</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}"
                                        {{ old('academic_year_id', $academicYearId ?? null) == $year->id ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2 justify-content-end">
                            <div class="flex-shrink-0">
                                <button type="button" class="btn btn-primary-color me-2 action-btn"
                                        onclick="$('#real-import-btn').click()"
                                        {{ !$academicYearId ? 'disabled' : '' }}
                                        title="{{ !$academicYearId ? 'Vui lòng chọn năm học trước' : 'Import học sinh' }}">
                                    <i class="fas fa-file-import me-1"></i> Import
                                </button>
                                <input type="file" id="real-import-btn" accept=".xlsx,.xls" class="d-none">

                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-secondary dropdown-toggle action-btn" type="button"
                                            id="exportDropdown" data-bs-toggle="dropdown"
                                            {{ !$academicYearId ? 'disabled' : '' }}
                                            @if(!$academicYearId) title="Vui lòng chọn năm học trước" @endif>
                                        <i class="fas fa-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('students.export.template', ['academic_year_id' => $academicYearId ?? '']) }}"
                                               id="export-template-link" target="_blank">
                                                <i class="fas fa-file-excel me-1"></i> Tải file mẫu
                                            </a>
                                        </li>
                                        <li>

                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th>STT</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Trường</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                        </thead>
                        <tbody id="students-container">
                        @include('students.partials.results', ['students' => $students])
                        </tbody>
                    </table>
                </div>
            </div>
            @include('students.partials.pagination', ['students' => $students])
        </div>
    </div>
    <div class="modal fade" id="importErrorsModal" tabindex="-1" aria-labelledby="importErrorsModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary-color text-white">
                    <h5 class="modal-title" id="importErrorsModalLabel">Lỗi khi import dữ liệu</h5>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Vui lòng kiểm tra lại các lỗi bên dưới và thử lại
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                            <tr>
                                <th width="100">Dòng số</th>
                                <th>Lỗi</th>
                                <th>Giá trị nhập</th>
                            </tr>
                            </thead>
                            <tbody id="importErrorsList">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary-color" data-bs-dismiss="modal">Đóng</button>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            $('#search-input').on('keyup', function () {
                clearTimeout($(this).data('timer'));
                $(this).data('timer', setTimeout(() => $('#search-form').submit(), 500));
            });

            @if(request('search'))
            $('.btn-outline-secondary').click(function () {
                $('#search-input').val('');
                $('#search-form').submit();
            });
            @endif

            const academicYearSelect = $('#academic_year_id');
            const inputFile = $("#real-import-btn");

            // Bật/tắt nút action chỉ dựa trên năm học
            function toggleActionButtons() {
                const enable = academicYearSelect.val();
                $('.action-btn').prop('disabled', !enable);
            }

            // Khi thay đổi năm học
            function handleFilterChange() {
                toggleActionButtons();
                if (academicYearSelect.val()) {
                    loadStudents();
                }
                updateExportLink();
                $('#filter-form').submit();

            }

            // Load danh sách học sinh
            function loadStudents(url = null) {
                const params = {
                    academic_year_id: academicYearSelect.val(),
                    _token: '{{ csrf_token() }}'
                };

                $.get(url || '{{ route("students.index") }}', params)
                    .done(function (response) {
                        $("#students-container").html(response.data);
                        $("#pagination-container").html(response.pagination);
                        setupPagination();
                    })
                    .fail(function (xhr) {
                        alert('Lỗi: ' + (xhr.responseText || 'Vui lòng thử lại'));
                    });
            }

            function setupPagination() {
                $(document).off('click', '.pagination a').on('click', '.pagination a', function (e) {
                    e.preventDefault();
                    loadStudents($(this).attr('href'));
                });
            }

            // Xử lý import file
            function handleFileImport() {
                if (!this.files.length) return;

                const academicYearId = academicYearSelect.val();

                if (!academicYearId) {
                    alert("Vui lòng chọn năm học trước khi import");
                    this.value = '';
                    return;
                }

                const formData = new FormData();
                formData.append('file', this.files[0]);
                formData.append('academic_year_id', academicYearId);
                formData.append('_token', '{{ csrf_token() }}');

                $('#loading-spinner').removeClass('d-none');

                $.ajax({
                    url: "{{ route('students.import') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Thành công!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#013066',
                            });

                            if (response.new_student) {
                                const newStudentRow = `
                <tr>
                    <td>${response.new_student.id}</td>
                    <td>${response.new_student.full_name}</td>
                    <td>${response.new_student.email}</td>
                    <td>${response.new_student.phone || ''}</td>
                    <td>${response.new_student.school.name}</td>
                    <td>${response.new_student.is_active ? 'Hoạt động' : 'Không hoạt động'}</td>
                    <td class="text-center">

                    </td>
                </tr>
            `;
                                $('#students-container').prepend(newStudentRow);
                            }

                            if (response.reload) {
                                loadStudents();
                            }
                        } else {
                            Swal.fire({
                                title: 'Thông báo',
                                text: response.message,
                                icon: 'info',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#013066',
                            });
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            // Xóa nội dung cũ
                            $('#importErrorsList').empty();

                            // Thêm từng lỗi vào bảng
                            xhr.responseJSON.errors.forEach(error => {
                                const rowError = `
                        <tr>
                            <td>${error.row}</td>
                            <td class="text-danger">${error.error}</td>
                            <td>${error.values ? formatErrorValues(error.values) : 'N/A'}</td>
                        </tr>
                    `;
                                $('#importErrorsList').append(rowError);
                            });

                            // Cập nhật link tải file mẫu
                            $('#downloadTemplateBtn').attr('href',
                                `{{ route('students.export.template') }}?academic_year_id=${academicYearId}`
                            );

                            // Hiển thị modal
                            $('#importErrorsModal').modal('show');
                        } else {
                            Swal.fire({
                                title: 'Lỗi',
                                text: xhr.responseJSON?.message || 'Có lỗi xảy ra khi import',
                                icon: 'error',
                                confirmButtonText: 'Đóng'
                            });
                        }
                    },
                    complete: function () {
                        $('#loading-spinner').addClass('d-none');
                        $('#real-import-btn').val('');
                    }
                });
            }

            function formatErrorValues(values) {
                let html = '';
                for (const key in values) {
                    if (values[key]) {
                        html += `<div><strong>${key}:</strong> ${values[key]}</div>`;
                    }
                }
                return html || 'Không có dữ liệu';
            }

            function showAlert(type, message) {
                const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
                $('#alert-container').html(alertHtml).delay(5000).fadeOut();
            }

            function updateExportLink() {
                const academicYearId = $('#academic_year_id').val();
                if (academicYearId) {
                    const url = "{{ route('students.export.template') }}?academic_year_id=" + academicYearId;
                    $('#export-template-link').attr('href', url);
                }
            }

            academicYearSelect.on('change', handleFilterChange);
            inputFile.on('change', handleFileImport);

            toggleActionButtons();
            updateExportLink();
        });

    </script>
@endpush
