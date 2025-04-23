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


        .table {
            border-radius: 10px;
            overflow: hidden;
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

        .table-responsive .dropdown-menu {
            position: fixed;
            z-index: 1000 ;
            min-width: 90px;
        }

        .table-responsive .show > .dropdown-menu {
            display: block !important;
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
                            <div class="input-group" >
                                <input type="text" name="search" class="form-control"
                                       placeholder="Tìm kiếm học sinh..." value="{{ $search }}">
                                <button type="submit" class="btn" style="background-color:#013066; color:#ffffff">
                                    <i class="fas fa-search text-white" ></i>
                                </button>
                            </div>
                        </div>

                        <!-- Academic Year Filter -->
                        <div class="col-md-3">
                            <label for="academic_year_id" class="form-label">Năm học</label>
                            <select class="form-select" id="academic_year_id" name="academic_year_id">
                                <option value="">-- Chọn năm học --</option>
                                @foreach($academicYears as $year)
                                    <option
                                        value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                        {{ $year->year }} <!-- Đảm bảo đây là trường hiển thị -->
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
                                    <option
                                        value="{{ $grade->id }}" {{ $gradeLevelId == $grade->id ? 'selected' : '' }}>
                                        {{ $grade->grade_number }} <!-- Đảm bảo đây là trường hiển thị -->
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
                <a href="{{ route('class_assignments.index') }}" class="btn me-2" style="background-color:#013066; color:#ffffff">Phân lớp
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
                            <a class="dropdown-item" href="{{ route('students.export.template') }}">
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
                        <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td class="ps-4">{{ $student->school_auto_id }}</td>
                                <td>{{ $student->full_name }}</td>
                                <td>{{ $student->email }}</td>
                                <td class="text-center">{{ $student->phone }}</td>
                                <td>{{ $student->school->name ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <span
                                        class="badge rounded-pill {{ $student->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $student->is_active ? 'Hoạt động' : 'Ngừng' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0"
                                            data-bs-popper="static">
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('students.show', $student->id) }}">
                                                    Xem
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('students.edit', $student->id) }}">
                                                    Sửa
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('students.destroy', $student->id) }}"
                                                      method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item px-3 py-2 shadow"
                                                            onclick="return confirm('Bạn có chắc chắn muốn xóa học sinh này?')">
                                                        Xóa
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>

                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination and info -->
        <div class="d-flex justify-content-end align-items-center mt-2">
            <div class="text-muted me-3">
                {{ $students->firstItem() }}-{{ $students->lastItem() }} của {{ $students->total() }} bản ghi
            </div>
            <ul class="pagination pagination-sm mb-0">
                <!-- Nút "Trang Trước" -->
                <li class="page-item {{ $students->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $students->previousPageUrl() }}">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>

                <!-- Số trang -->
                @for ($i = 1; $i <= $students->lastPage(); $i++)
                    <li class="page-item {{ $students->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $students->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                <!-- Nút "Trang Kế" -->
                <li class="page-item {{ $students->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $students->nextPageUrl() }}">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
            </ul>
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

                /*if ($searchInput.length) {
                    let debounceTimer;
                    $searchInput.on('keyup', function () {
                        clearTimeout(debounceTimer);
                        const $input = $(this);
                        debounceTimer = setTimeout(function () {
                            const val = $input.val();
                            if (val.length === 0 || val.length > 2) {
                                searchForm.submit();
                            }
                        }, 300);
                    });
                }*/

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
                });

                gradeLevelSelect.on('change', function () {
                    toggleActionButtons();
                });

                // Xử lý import file
                inputFile.on("change", function () {
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

                    // Trong file blade, sửa phần AJAX thành:
                    $.ajax({
                        url: '{{ route("students.import") }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                // Hiển thị thông báo thành công
                                alert(response.message);
                                // Tự động tải lại trang để xem kết quả
                                window.location.reload();
                            } else {
                                alert('Lỗi: ' + response.message);
                            }
                        },
                        error: function(xhr) {
                            alert('Lỗi: ' + (xhr.responseJSON?.message || 'Vui lòng thử lại'));
                        },
                        complete: function() {
                            btn.prop('disabled', false).html('<i class="fas fa-file-import me-1"></i> Import');
                            inputFile.val(''); // Reset input file
                        }
                    });
                });
            });
        });
    </script>
@endpush
