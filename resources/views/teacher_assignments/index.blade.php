@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quản lý Phân công giảng dạy</h2>
            <div>
                <a href="{{ route('teachers.index') }}?assign=1" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm phân công
                </a>
                <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-users"></i> Quản lý Giáo viên
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
                <form method="GET" action="{{ route('teacher_assignments.index') }}">
                    <div class="row g-3">
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
                            <label class="form-label">Lớp học</label>
                            <select name="class_id" class="form-select">
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
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Lọc
                            </button>
                            <a href="{{ route('teacher_assignments.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-sync"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danh sách phân công -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
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
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $assignment->teacher->full_name }}</td>
                                <td>{{ $assignment->class->gradeLevel->grade_number }}
                                    - {{ $assignment->class->name }}</td>
                                <td>{{ $assignment->subject->name }}</td>
                                <td>
                                    @if($assignment->is_homeroom)
                                        <span class="badge bg-primary">Giáo viên chủ nhiệm</span>
                                    @else
                                        <span class="badge bg-info">Giáo viên bộ môn</span>
                                    @endif
                                </td>
                                <td>{{ $assignment->academicYear->year }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('teacher_assignments.show', $assignment) }}"
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('teacher_assignments.edit', $assignment) }}"
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('teacher_assignments.destroy', $assignment) }}"
                                              method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Bạn chắc chắn muốn xóa giáo viên này?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

                <div class="d-flex justify-content-center mt-3">
                    {{ $assignments->links() }}
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            // Xử lý form xóa phân công
            $('.delete-assignment-form').on('submit', function (e) {
                e.preventDefault();

                if (confirm('Bạn chắc chắn muốn xóa phân công này?')) {
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: {
                            _token: $(this).find('input[name="_token"]').val(),
                            _method: 'DELETE'
                        },
                        success: function () {
                            window.location.reload();
                        },
                        error: function () {
                            alert('Đã xảy ra lỗi khi xóa phân công');
                        }
                    });
                }
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
@endsection
