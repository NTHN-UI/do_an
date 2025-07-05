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
        <h3 class="mb-3 text-primary-color">Danh sách phân công</h3>
        <div class="mb-3 d-flex justify-content-end align-items-center">
            <div>
                <a href="{{ route('teachers.index') }}?assign=1" class="btn btn-primary-color">Thêm phân công
                </a>
            </div>
        </div>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Bộ lọc -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('teacher_assignments.index') }}" id="filter-form">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Năm học</label>
                            <select name="academic_year_id" class="form-select" id="academic-year-filter">
                                <option value="">-- Tất cả năm --</option>
                                @foreach($academicYears as $year)
                                    <option
                                        value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Lớp học</label>
                            <select name="class_id" class="form-select" id="class-filter">
                                <option value="">-- Tất cả lớp --</option>
                                @foreach($classes as $class)
                                    <option
                                        value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->gradeLevel->grade_number }} - {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Môn học</label>
                            <select name="subject_id" class="form-select">
                                <option value="">-- Tất cả môn --</option>
                                @foreach($subjects as $subject)
                                    <option
                                        value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vai trò</label>
                            <select name="is_homeroom" class="form-select">
                                <option value="">-- Tất cả --</option>
                                <option value="1" {{ request('is_homeroom') === '1' ? 'selected' : '' }}>Giáo viên chủ
                                    nhiệm
                                </option>
                                <option value="0" {{ request('is_homeroom') === '0' ? 'selected' : '' }}>Giáo viên bộ
                                    môn
                                </option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danh sách phân công -->
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th>STT</th>
                            <th>Giáo viên</th>
                            <th>Lớp</th>
                            <th>Môn học</th>
                            <th>Vai trò</th>
                            <th>Năm học</th>
                            <th>Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($assignments as $index => $assignment)
                            <tr class="text-center">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $assignment->teacher->full_name }}</td>
                                <td>{{ $assignment->class->gradeLevel->grade_number }}
                                    - {{ $assignment->class->name }}</td>
                                <td>{{ $assignment->subject->name }}</td>
                                <td>
                                    <span
                                        class="badge bg-primary-color">{{ $assignment->is_homeroom ? "Giáo viên chủ nhiệm" : "Giáo viên bộ môn" }}</span>
                                </td>
                                <td>{{ $assignment->academicYear->year }}</td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('teacher_assignments.show', $assignment->id) }}">
                                                    Xem
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('teacher_assignments.edit', $assignment->id) }}">
                                                    Sửa
                                                </a>
                                            </li>
                                            <li>
                                                <button class="dropdown-item px-3 py-2 delete-btn"
                                                        data-id="{{ $assignment->id }}"
                                                        data-url="{{ route('teacher_assignments.destroy', $assignment->id) }}">
                                                    Xóa
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if($assignments->isEmpty())
                    <div class="alert alert-info text-center">
                        Không có phân công nào được tìm thấy.
                    </div>
                @endif
                @if($assignments->lastPage() > 1)
                    <div class="card-footer border-0 bg-transparent" id="pagination-container">
                        <nav aria-label="page navigation">
                            {{ $assignments->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
        <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteConfirmationModalLabel">Xác nhận xóa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Bạn có chắc chắn muốn xóa phân công này không?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary-color" data-bs-dismiss="modal">Hủy</button>
                        <form id="deleteForm" method="POST" action="">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-primary-color">Xác nhận xóa</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $(document).on('click', '.delete-btn', function() {
                const url = $(this).data('url');
                $('#deleteForm').attr('action', url);
                $('#deleteConfirmationModal').modal('show');
            });

            // Xử lý submit form xóa
            $('#deleteForm').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xóa...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: {
                        _token: form.find('input[name="_token"]').val(),
                        _method: 'DELETE'
                    },
                    success: function() {
                        $('#deleteConfirmationModal').modal('hide');
                        window.location.reload();
                    },
                    error: function(xhr) {
                        $('#deleteConfirmationModal').modal('hide');
                        alert('Đã xảy ra lỗi khi xóa phân công');
                        submitBtn.prop('disabled', false).html('Xác nhận xóa');
                    }
                });
            });

            // Tự động submit form khi có thay đổi filter
            $('#academic-year-filter, #class-filter, [name="subject_id"], [name="is_homeroom"]').change(function () {
                $('#filter-form').submit();
            });

            // Xử lý form thêm phân công
            $('#assignment-form').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    success: function (response) {
                        window.location.href = "{{ route('teacher_assignments.index') }}";
                    },
                    error: function (xhr) {
                        submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Lưu phân công');

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            alert(xhr.responseJSON.message);
                        } else {
                            alert('Đã xảy ra lỗi khi lưu phân công');
                        }
                    }
                });
            });
        });
    </script>
@endpush

