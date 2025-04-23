@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Phân công giảng dạy cho: {{ $teacher->full_name }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('teacher_assignments.store', $teacher) }}" method="POST" id="assignment-form">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Giáo viên</label>
                            <input type="text" class="form-control" value="{{ $teacher->full_name }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trường</label>
                            <input type="text" class="form-control" value="{{ $teacher->school->name }}" readonly>
                        </div>
                    </div>

                    <input type="hidden" name="academic_year_id" value="{{ $currentAcademicYear->id }}">

                    <div class="row mb-3">
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <label class="form-label">Năm học</label>
                            <input type="text" class="form-control" value="{{ $currentAcademicYear->year }}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Lớp học (Chọn nhiều lớp) <span class="text-danger">*</span></label>
                            <select name="class_ids[]" class="form-select select2-multiple" multiple="multiple" required>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ in_array($class->id, old('class_ids', [])) ? 'selected' : '' }}>
                                        {{ $class->gradeLevel->grade_number }} - {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
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

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('teacher_assignments.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu phân công
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

            $('#assignment-form').on('submit', function(e) {
                e.preventDefault();

                const isHomeroom = $('#is_homeroom').is(':checked');
                const classCount = $('.select2-multiple').val()?.length || 0;

                if (isHomeroom && classCount > 1) {
                    alert('Giáo viên chủ nhiệm chỉ được phân công 1 lớp!');
                    return false;
                }

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');

                submitBtn.prop('disabled', true);
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    success: function(response) {
                        window.location.href = "{{ route('teacher_assignments.index') }}";
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false);
                        submitBtn.html('<i class="fas fa-save"></i> Lưu phân công');

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            alert(xhr.responseJSON.message);
                        } else {
                            alert('Đã xảy ra lỗi khi lưu phân công. Vui lòng thử lại.');
                        }
                    }
                });
            });
        });

    </script>
@endpush
