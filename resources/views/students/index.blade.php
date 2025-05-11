@extends('layouts.app')

@section('content')
    <style>
        .container {
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow-x: auto;
        }

        .card {
            border-radius: 0.5rem;
        }

        .card-body {
            position: relative;
            overflow: visible !important;
        }

        .table thead {
            background: linear-gradient(45deg, #f1f3f5, #e9ecef);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .table th {
            font-weight: 500;
            padding: 12px;
        }

        .table td {
            vertical-align: middle;
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.08);
            transition: background-color 0.3s ease-in-out;
        }

        .badge {
            font-size: 0.85rem;
            padding: 0.4em 0.75em;
            border-radius: 0.5rem;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .input-group .btn {
            border: 0.5rem;
        }

    </style>
    <div class="container">
        <h3 class="mb-3">Danh sách học sinh</h3>

        <!-- Search and Add Button -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('students.index') }}" id="search-form">
                    <div class="row g-3">
                        <!-- Search Input -->
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Tìm kiếm học sinh..." value="{{ $search }}">
                                <button type="submit" class="btn" style="background-color:#013066; color:#ffffff">
                                    <i class="fas fa-search text-white"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Academic Year Filter -->
                        <div class="col-md-3">
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

                        <!-- Grade Level Filter -->
                        <div class="col-md-3">
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
                    </div>
                </form>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mb-3">
            <div>
                <a href="{{ route('students.create') }}" class="btn shadow me-2"
                   style="background-color:#013066; color:#ffffff">
                    Thêm mới
                </a>
                <a href="{{ route('class_assignments.index') }}" class="btn me-2"
                   style="background-color:#013066; color:#ffffff">Phân lớp
                </a>
            </div>

            <div>
                <!-- Import Button -->
                <button class="btn btn-success me-2 action-btn" onclick="$('#real-import-btn').click()"
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

        <!-- Student list -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-secondary">
                        <tr>
                            <th class="ps-4" style="width: 50px;">ID</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th class="text-center">SĐT</th>
                            <th>Trường</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end pe-4" style="width: 50px;"></th>
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
            $(document).ready(function () {
                const academicYearSelect = $('#academic_year_id');
                const gradeLevelSelect = $('#grade_level_id');
                const searchForm = $('#search-form');
                const inputFile = $("#real-import-btn");

                // Tìm kiếm khi nhập (debounce)
                const $searchInput = $('input[name="search"]');

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
                        $(document).off('click', '.pagination a').on('click', '.pagination a', function(e) {
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
                // Xử lý import file
                inputFile.on("change", function() {
                    if (!this.files.length) return;

                    const academicYearId = academicYearSelect.val();
                    const gradeLevelId = gradeLevelSelect.val();

                    if (!academicYearId) {
                        alert('Vui lòng chọn năm học trước khi import');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('file', this.files[0]);
                    formData.append('academic_year_id', academicYearId);
                    formData.append('grade_level_id', gradeLevelId || '');
                    formData.append('_token', '{{ csrf_token() }}');

                    $.ajax({
                        url: '{{ route("students.import") }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                render(gradeLevelSelect.val(), academicYearSelect.val())
                                /*// Thêm các tham số filter vào URL khi reload
                                let reloadUrl = window.location.pathname;
                                const params = new URLSearchParams();

                                if (academicYearId) params.append('academic_year_id', academicYearId);
                                if (gradeLevelId) params.append('grade_level_id', gradeLevelId);
                                if ($searchInput.val()) params.append('search', $searchInput.val());

                                if (params.toString()) {
                                    reloadUrl += '?' + params.toString();
                                }*/

                            } else {
                                alert('Lỗi: ' + response.message);
                            }
                        },
                        error: function(xhr) {
                            alert('Lỗi: ' + (xhr.responseJSON?.message || 'Vui lòng thử lại'));
                        },
                        complete: function() {
                            inputFile.val(''); // Reset input file
                        }
                    });
                });
            });
            // Cập nhật link export template khi thay đổi select
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
