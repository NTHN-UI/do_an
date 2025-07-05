@extends('layouts.app')

@section('content')
    <style>
        .table-responsive .dropdown-menu {
            position: fixed !important;
            z-index: 1000 !important;
            min-width: 90px;
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
        <h3 class="mb-3 text-primary-color">Danh sách học si1nh</h3>

        <!-- Search and Add Button -->
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
        <div class="card mb-3 ">
            <div class="card-body">
                <form method="GET" action="{{ route('students.index') }}" id="filter-form">
                    <div class="row g-3">
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <label for="grade_level_id" class="form-label">Khối học</label>
                            <select class="form-select" id="grade_level_id" name="grade_level_id">
                                <option value="">-- Chọn khối --</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}"
                                        {{ old('grade_level_id', $gradeLevelId ?? null) == $grade->id ? 'selected' : '' }}>
                                        {{ $grade->grade_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2 justify-content-end">
                            <!-- Nút Import -->
                            <div class="flex-shrink-0">
                            <button type="button" class="btn btn-primary-color me-2 action-btn" onclick="$('#real-import-btn').click()"
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
                                        <a class="dropdown-item" href="{{ route('students.export.template', [
    'academic_year_id' => $academicYearId ?? null,
    'grade_level_id' => $gradeLevelId ?? null
]) }}" id="export-template-link">
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


        <!-- Student list -->
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th>STT</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th >SĐT</th>
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
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#search-input').on('keyup', function() {
                clearTimeout($(this).data('timer'));
                $(this).data('timer', setTimeout(() => $('#search-form').submit(), 500));
            });


            @if(request('search'))
            $('.btn-outline-secondary').click(function() {
                $('#search-input').val('');
                $('#search-form').submit();
            });
            @endif

            const academicYearSelect = $('#academic_year_id');
            const gradeLevelSelect = $('#grade_level_id');
            const inputFile = $("#real-import-btn");

            // Bật/tắt nút action
            function toggleActionButtons() {
                const enable = academicYearSelect.val() && gradeLevelSelect.val();
                $('.action-btn').prop('disabled', !enable);
            }

            // khi thay đổi năm học/khối
            function handleFilterChange() {
                toggleActionButtons();
                if (academicYearSelect.val() && gradeLevelSelect.val()) {
                    loadStudents();
                }
                updateExportLink();
            }

            // Load danh sách học sinh
            function loadStudents(url = null) {
                const params = {
                    grade_id: gradeLevelSelect.val(),
                    academic_year_id: academicYearSelect.val(),
                    _token: '{{ csrf_token() }}'
                };

                $.get(url || '{{ route("students.index") }}', params)
                    .done(function(response) {
                        $("#students-container").html(response.data);
                        $("#pagination-container").html(response.pagination);
                        setupPagination();
                    })
                    .fail(function(xhr) {
                        alert('Lỗi: ' + (xhr.responseText || 'Vui lòng thử lại'));
                    });
            }


            function setupPagination() {
                $(document).off('click', '.pagination a').on('click', '.pagination a', function(e) {
                    e.preventDefault();
                    loadStudents($(this).attr('href'));
                });
            }

            // Xử lý import file
            function handleFileImport() {
                if (!this.files.length) return;

                const academicYearId = academicYearSelect.val();
                const gradeLevelId = gradeLevelSelect.val();

                if (!academicYearId || !gradeLevelId) {
                    alert("Vui lòng chọn năm học và khối học trước khi import");
                    this.value = '';
                    return;
                }

                const formData = new FormData();
                formData.append('file', this.files[0]);
                formData.append('academic_year_id', academicYearId);
                formData.append('grade_level_id', gradeLevelId);
                formData.append('_token', '{{ csrf_token() }}');

                $('#loading-spinner').removeClass('d-none');

                $.ajax({
                    url: "{{ route('students.import') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            if (response.new_student) {
                                const newStudentRow = `
                            <tr>
                                <td>${response.new_student.id}</td>
                                <td>${response.new_student.full_name}</td>
                                <td>${response.new_student.email}</td>
                                <td>${response.new_student.phone || ''}</td>
                                <td>${response.new_student.school.name}</td>
                                <td>${response.new_student.is_active ? 'Hoạt động' : 'Không hoạt động'}</td>
                                <td></td>
                            </tr>
                        `;
                                $('#students-container').prepend(newStudentRow);
                            }
                            if (response.reload) loadStudents();
                            showAlert('success', response.message);
                        } else {
                            showAlert('danger', response.message);
                        }
                    },
                    error: function(xhr) {
                        showAlert('danger', xhr.responseJSON?.message || 'Lỗi khi import file');
                    },
                    complete: function() {
                        $('#loading-spinner').addClass('d-none');
                        inputFile.val('');
                    }
                });
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
                const academicYearId = academicYearSelect.val();
                const gradeLevelId = gradeLevelSelect.val();

                if (academicYearId && gradeLevelId) {
                    const url = "{{ route('students.export.template') }}?academic_year_id=" + academicYearId + "&grade_level_id=" + gradeLevelId;
                    $('#export-template-link').attr('href', url);
                }
            }

            academicYearSelect.on('change', handleFilterChange);
            gradeLevelSelect.on('change', handleFilterChange);
            inputFile.on('change', handleFileImport);

            toggleActionButtons();
            updateExportLink();
        });
    </script>
@endpush
