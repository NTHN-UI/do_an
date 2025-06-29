@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">

            <h3 class="mb-0 text-primary-color">Phân công giảng dạy cho: {{ $teacher->full_name }}</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('teacher_assignments.store', $teacher) }}" method="POST" id="assignment-form">
                @csrf
                <div class="col-md-12 mb-3">
                    <label class="form-label">Giáo viên</label>
                    <input type="text" class="form-control" value="{{ $teacher->full_name }}" readonly>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Trường</label>
                    <input type="text" class="form-control" value="{{ $teacher->school->name }}" readonly>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Môn học <span class="text-danger">*</span></label>
                    <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" id="subject-select">
                        <option value="">-- Chọn môn học --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Năm học <span class="text-danger">*</span></label>
                    <select name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" id="academic-year-filter">
                        <option value="">-- Chọn năm học --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->year }}
                            </option>
                        @endforeach
                    </select>
                    @error('academic_year_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
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
                    @error('class_ids')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_homeroom" id="is_homeroom"
                               class="form-check-input @error('is_homeroom') is-invalid @enderror" value="1" {{ old('is_homeroom') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_homeroom">
                            Giáo viên chủ nhiệm
                        </label>
                    </div>
                    @error('is_homeroom')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('teachers.index') }}" class="btn btn-outline-primary-color me-2">Đóng</a>

                    <button type="submit" class="btn btn-primary-color">
                        Lưu phân công
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
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
        });
    </script>
@endpush
