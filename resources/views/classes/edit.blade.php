@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0">Sửa thông tin lớp học</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('classes.update', $class->id) }}" id="classForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">

                <div class="form-group">
                    <label for="name">Tên Lớp *</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                           name="name" value="{{ old('name', $class->name) }}" autofocus>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Trường</label>
                    <input type="hidden" name="school_id" value="{{ Auth::user()->school->id }}">
                    <div class="form-control bg-light">
                        {{ Auth::user()->school->name }}
                    </div>
                </div>

                <div class="form-group">
                    <label for="grade_level_id">Khối *</label>
                    <select id="grade_level_id" name="grade_level_id" class="form-control @error('grade_level_id') is-invalid @enderror" >
                        <option value="">-- Chọn khối --</option>
                        @foreach($gradeLevels as $gradeLevel)
                            <option value="{{ $gradeLevel->id }}" {{ old('grade_level_id', $class->grade_level_id) == $gradeLevel->id ? 'selected' : '' }}>
                                Khối {{ $gradeLevel->grade_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('grade_level_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end align-items-center mt-3">
                    <a href="{{ route('classes.index', $class->id) }}" class="btn btn-outline-primary-color me-2">
                        Đóng
                    </a>
                    <button type="submit" class="btn btn-primary-color">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Dynamic load grade levels when school changes
            $('#school_id').change(function() {
                const schoolId = $(this).val();
                const gradeSelect = $('#grade_level_id');

                if (!schoolId) {
                    gradeSelect.empty().append('<option value="">-- Chọn trường trước --</option>');
                    return;
                }

                // Filter grade levels by selected school
                gradeSelect.empty().append('<option value="">-- Đang tải --</option>');

                // Get grade levels via AJAX or use preloaded data
                $.get(`/api/schools/${schoolId}/grade-levels`, function(data) {
                    gradeSelect.empty().append('<option value="">-- Chọn khối --</option>');
                    data.forEach(grade => {
                        gradeSelect.append(`<option value="${grade.id}">Khối ${grade.grade_number}</option>`);
                    });

                    // Select previously selected grade level
                    const oldGradeId = "{{ old('grade_level_id', $class->grade_level_id) }}";
                    if (oldGradeId) {
                        gradeSelect.val(oldGradeId);
                    }
                });
            });

            // Trigger change if school is already selected
            @if(old('school_id', $class->school_id))
            $('#school_id').trigger('change');
            @endif
        });
    </script>
@endpush
