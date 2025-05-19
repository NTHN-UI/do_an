@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Quản lý Phân công giảng dạy</h2>
        <div class="d-flex justify-content-end align-items-center mb-4">
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
                                    <span
                                        class="badge bg-primary-color">{{ $assignment->is_homeroom ? "Giáo viên chủ nhiệm" : "Giáo viên bộ môn" }}</span>
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

@endsection

@push('scripts')
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
            // Tự động submit form khi có thay đổi filter
            $('#academic-year-filter, #class-filter, [name="subject_id"], [name="is_homeroom"]').change(function () {
                $('#filter-form').submit();
            });

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
        {{--// Xử lý khi năm học thay đổi--}}
        {{--$('#academic-year-filter').change(function() {--}}
        {{--    const academicYearId = $(this).val();--}}

        {{--    // Gọi API lấy danh sách lớp theo năm học--}}
        {{--    if (academicYearId) {--}}
        {{--        $.ajax({--}}
        {{--            url: '{{ route("teacher_assignments.getClassesByAcademicYear") }}',--}}
        {{--            method: 'GET',--}}
        {{--            data: {--}}
        {{--                academic_year_id: academicYearId--}}
        {{--            },--}}
        {{--            success: function(response) {--}}
        {{--                // Cập nhật dropdown lớp--}}
        {{--                const classFilter = $('#class-filter');--}}
        {{--                classFilter.empty();--}}
        {{--                classFilter.append('<option value="">-- Tất cả lớp --</option>');--}}

        {{--                response.forEach(function(classItem) {--}}
        {{--                    classFilter.append(--}}
        {{--                        `<option value="${classItem.id}">--}}
        {{--                            ${classItem.grade_level.grade_number} - ${classItem.name}--}}
        {{--                        </option>`--}}
        {{--                    );--}}
        {{--                });--}}
        {{--            },--}}
        {{--            error: function(xhr) {--}}
        {{--                console.error('Error fetching classes:', xhr.responseText);--}}
        {{--            }--}}
        {{--        });--}}
        {{--    } else {--}}
        {{--        // Nếu chọn "Tất cả năm" thì reset dropdown lớp--}}
        {{--        const classFilter = $('#class-filter');--}}
        {{--        classFilter.empty();--}}
        {{--        classFilter.append('<option value="">-- Tất cả lớp --</option>');--}}

        {{--        // Thêm tất cả lớp (nếu cần)--}}
        {{--        @foreach($allClasses as $class)--}}
        {{--        classFilter.append(--}}
        {{--            `<option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>--}}
        {{--                    {{ $class->gradeLevel->grade_number }} - {{ $class->name }}--}}
        {{--            </option>`--}}
        {{--        );--}}
        {{--        @endforeach--}}
        {{--    }--}}
        {{--});--}}


    </script>
@endpush

