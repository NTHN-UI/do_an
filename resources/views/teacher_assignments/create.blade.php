@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">Phân công giảng dạy cho: {{ $teacher->full_name }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('teacher_assignments.store', $teacher) }}" method="POST" id="assignment-form">
                @csrf
                    <div class="col-md-12">
                        <label class="form-label">Giáo viên</label>
                        <input type="text" class="form-control" value="{{ $teacher->full_name }}" readonly>
                    </div>
                <div class="col-md-12">
                        <label class="form-label">Trường</label>
                        <input type="text" class="form-control" value="{{ $teacher->school->name }}" readonly>
                    </div>


                <div class="col-md-12">
                        <label class="form-label">Môn học <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">-- Chọn môn học --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                <div class="col-md-12">
                        <label class="form-label">Năm học</label>
                        <select name="academic_year_id" class="form-select" id="academic-year-filter">
                            <option value="">-- Tất cả năm --</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                <div class="col-md-12">

                        <label class="form-label">Lớp học (Chọn nhiều lớp) <span class="text-danger">*</span></label>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" id="classDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Chọn lớp học
                            </button>
                            <div class="dropdown-menu w-100 p-3" aria-labelledby="classDropdown" id="classes-container">
                                @if($classes->count() > 0)
                                    <div class="row">
                                        @foreach($classes as $class)
                                            <div class="col-12 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input class-checkbox"
                                                           type="checkbox"
                                                           name="class_ids[]"
                                                           value="{{ $class->id }}"
                                                        {{ request('class_id') == $class->id ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="class_{{ $class->id }}">
                                                        {{ $class->gradeLevel->grade_number }} - {{ $class->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning">Không có lớp học nào trong năm học này</div>
                                @endif
                            </div>
                        </div>
                        <div id="selected-classes" class="mt-2"></div>
                    </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_homeroom" id="is_homeroom"
                               class="form-check-input" value="1" {{ old('is_homeroom') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_homeroom">
                            Giáo viên chủ nhiệm
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary-color">
                         Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Function to update selected classes display
            function updateSelectedClasses() {
                let selected = [];
                $('.class-checkbox:checked').each(function() {
                    selected.push($(this).next('label').text().trim());
                });

                if (selected.length > 0) {
                    $('#selected-classes').html('<strong>Lớp đã chọn:</strong> ' + selected.join(', '));
                } else {
                    $('#selected-classes').html('');
                }
            }

            // Initialize selected classes display
            updateSelectedClasses();

            // Update when checkbox changes
            $(document).on('change', '.class-checkbox', function() {
                updateSelectedClasses();
            });

            // Xử lý khi thay đổi năm học
            $('#academic-year-filter').change(function() {
                const yearId = $(this).val();

                if (yearId) {
                    // Gọi AJAX để lấy danh sách lớp theo năm học
                    $.ajax({
                        url: '{{ route("teacher_assignments.getClassesByAcademicYear") }}',
                        method: 'GET',
                        data: { academic_year_id: yearId },
                        success: function(response) {
                            let html = '<div class="row">';

                            if (response.length > 0) {
                                response.forEach(function(classItem) {
                                    html += `
                                <div class="col-12 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input class-checkbox"
                                               type="checkbox"
                                               name="class_ids[]"
                                               value="${classItem.id}">
                                        <label class="form-check-label">
                                            ${classItem.grade_level.grade_number} - ${classItem.name}
                                        </label>
                                    </div>
                                </div>
                            `;
                                });
                            } else {
                                html += '<div class="alert alert-warning">Không có lớp học nào trong năm học này</div>';
                            }

                            html += '</div>';
                            $('#classes-container').html(html);
                            updateSelectedClasses();
                        },
                        error: function() {
                            alert('Đã xảy ra lỗi khi tải danh sách lớp học');
                        }
                    });
                } else {
                    // Nếu không chọn năm học thì hiển thị tất cả lớp
                    $('#classes-container').html(`
                <div class="alert alert-info">Vui lòng chọn năm học để xem danh sách lớp</div>
            `);
                    $('#selected-classes').html('');
                }
            });

            // Kích hoạt sự kiện change ngay khi trang load nếu đã có năm học được chọn
            @if($currentAcademicYear)
            $('#academic-year-filter').trigger('change');
            @endif

            // Xử lý submit form
            $('#assignment-form').on('submit', function(e) {
                e.preventDefault();

                const isHomeroom = $('#is_homeroom').is(':checked');
                const checkedClasses = $('.class-checkbox:checked').length;
                const subjectSelected = $('select[name="subject_id"]').val();
                const academicYearSelected = $('select[name="academic_year_id"]').val();

                // Validate trước khi gửi
                if (!subjectSelected) {
                    alert('Vui lòng chọn môn học!');
                    return false;
                }

                if (!academicYearSelected) {
                    alert('Vui lòng chọn năm học!');
                    return false;
                }

                if (checkedClasses === 0) {
                    alert('Vui lòng chọn ít nhất một lớp học!');
                    return false;
                }

                if (isHomeroom && checkedClasses > 1) {
                    alert('Giáo viên chủ nhiệm chỉ được phân công 1 lớp!');
                    return false;
                }

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const formData = form.serialize();

                submitBtn.prop('disabled', true);
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect;
                        } else {
                            alert(response.message);
                            submitBtn.prop('disabled', false);
                            submitBtn.html('<i class="fas fa-save"></i> Lưu phân công');
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false);
                        submitBtn.html('<i class="fas fa-save"></i> Lưu phân công');

                        let errorMessage = 'Đã xảy ra lỗi khi lưu phân công';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            if (xhr.responseJSON.errors) {
                                errorMessage = Object.values(xhr.responseJSON.errors).join('\n');
                            }
                        }

                        alert(errorMessage);
                    }
                });
            });
        });
    </script>
@endpush
