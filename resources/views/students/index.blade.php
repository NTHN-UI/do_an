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
        <h3 class="mb-3 text-primary-color">Danh sách học sinh</h3>

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
        <!-- Filter Form -->
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

                            <!-- Export Dropdown -->
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
                                        <a class="dropdown-item"
                                           href="{{ $academicYearId ? route('students.export', ['academic_year_id' => $academicYearId, 'grade_level_id' => $gradeLevelId]) : '#' }}">
                                            <i class="fas fa-file-export me-1"></i> Xuất danh sách
                                        </a>
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
                            <th>ID</th>
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
            $(document).ready(function () {
                let timer;
                $('#search-input').on('keyup', function () {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        $('#search-form').submit();
                    }, 500);
                });

                // Reset search input if needed (add a clear button if you want)
                @if(request('search'))
                $('.btn-outline-secondary').click(function () {
                    $('#search-input').val('');
                    $('#search-form').submit();
                });
                @endif
                const academicYearSelect = $('#academic_year_id');
                const gradeLevelSelect = $('#grade_level_id');
                const inputFile = $("#real-import-btn");

                function toggleActionButtons() {
                    const yearSelected = academicYearSelect.val();
                    const gradeSelected = gradeLevelSelect.val();
                    const shouldEnable = yearSelected && gradeSelected;
                    $('.action-btn').prop('disabled', !shouldEnable);
                }

                // Gọi kiểm tra ban đầu
                toggleActionButtons();

                // Gọi lại khi người dùng chọn lại năm học hoặc khối
                academicYearSelect.on('change', function () {
                    toggleActionButtons();

                    const academic_year_id = $(this).val();
                    const grade_id = gradeLevelSelect.val();

                    if (!grade_id) return;

                    render(grade_id, academic_year_id);
                });

                gradeLevelSelect.on('change', function () {
                    toggleActionButtons();
                    const academic_year_id = academicYearSelect.val();
                    const grade_id = $(this).val();

                    if (!academic_year_id) return;

                    render(grade_id, academic_year_id);
                });

                const getData = (grade_id, academic_year_id) => {
                    return $.ajax({
                        url: '{{ route("students.index") }}',
                        type: 'GET',
                        data: {
                            _token: '{{ csrf_token() }}',
                            grade_id: grade_id,
                            academic_year_id: academic_year_id
                        },
                        error: function (xhr) {
                            alert('Lỗi: ' + (xhr.responseText || 'Vui lòng thử lại'));
                            console.log("Error: ", xhr.responseText);
                        }
                    });
                }

                const render = async (grade_id, academic_year_id) => {
                    try {
                        const response = await getData(grade_id, academic_year_id);
                        const studentContainer = $("#students-container");
                        const paginationContainer = $("#pagination-container");

                        studentContainer.html(response.data);
                        paginationContainer.html(response.pagination);

                        // Thêm sự kiện click cho phân trang AJAX
                        $(document).off('click', '.pagination a').on('click', '.pagination a', function (e) {
                            e.preventDefault();
                            const url = $(this).attr('href');
                            loadPage(url, grade_id, academic_year_id);
                        });
                    } catch (error) {
                        console.error("Failed to fetch data: ", error);
                        alert("Có lỗi xảy ra khi tải dữ liệu, vui lòng thử lại");
                    }
                }

                const loadPage = async (url, grade_id, academic_year_id) => {
                    try {
                        const fullUrl = new URL(url, window.location.origin);
                        fullUrl.searchParams.set('grade_id', grade_id);
                        fullUrl.searchParams.set('academic_year_id', academic_year_id);

                        const response = await $.ajax({
                            url: fullUrl.href,
                            type: 'GET',
                            data: {
                                _token: '{{ csrf_token() }}',
                                grade_id: grade_id,
                                academic_year_id: academic_year_id,
                                ajax: true
                            }
                        });

                        $("#students-container").html(response.data);
                        $("#pagination-container").html(response.pagination);
                    } catch (error) {
                        console.error("Failed to load page: ", error);
                    }
                }

                // Xử lý khi người dùng chọn file
                inputFile.on('change', function () {
                    if (!this.files.length) return;

                    const academicYearId = $('#academic_year_id').val();
                    const gradeLevelId = $('#grade_level_id').val();
                    const file = this.files[0];

                    if (!academicYearId || !gradeLevelId) {
                        alert("Vui lòng chọn năm học và khối học trước khi import");
                        this.value = '';
                        return;
                    }

                    const formData = new FormData();
                    formData.append('file', this.files[0]);
                    formData.append('academic_year_id', $('#academic_year_id').val());
                    formData.append('grade_level_id', $('#grade_level_id').val());
                    formData.append('_token', '{{ csrf_token() }}');

                    $.ajax({
                        url: "{{ route('students.import') }}",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            let html = `<div class="text-start"><p>${response.message}</p>`;

                            if (response.errors && response.errors.length > 0) {
                                html += `<div class="mt-3">
                    <h6 class="text-danger">Chi tiết lỗi:</h6>
                    <div class="text-danger" style="max-height: 200px; overflow-y: auto;">
                        <ul class="mb-0">`;

                                response.errors.forEach(error => {
                                    html += `<li>${error}</li>`;
                                });

                                html += `</ul></div></div>`;
                            }

                            html += `</div>`;
                        },
                        error: function (xhr) {
                            alert("Có lỗi khi import");
                            console.error("Import error: ", xhr.responseText);
                        },
                        complete: function () {
                            $('#real-import-btn').val('');
                        }
                    });
                });


                $('#academic_year_id, #grade_level_id').change(function() {
                    const academicYearId = $('#academic_year_id').val();
                    const gradeLevelId = $('#grade_level_id').val();

                    if (academicYearId && gradeLevelId) {
                        const url = "{{ route('students.export.template') }}" +
                            "?academic_year_id=" + academicYearId +
                            "&grade_level_id=" + gradeLevelId;
                        $('#export-template-link').attr('href', url);
                    }
                });
            });
    </script>
@endpush
