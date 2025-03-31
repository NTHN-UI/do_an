@extends('layouts.app')

@section('content')
    <style>
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow-x: auto;
        }
    </style>
    <div class="container">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('classes.index') }}" class="btn btn-back me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0">Sửa thông tin lớp học</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('classes.update', $class->id) }}" id="classForm">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Tên Lớp *</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                           name="name" value="{{ old('name', $class->name) }}" required autofocus>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="school_id">Trường *</label>
                    <select id="school_id" name="school_id" class="form-control @error('school_id') is-invalid @enderror" required>
                        <option value="">-- Chọn trường --</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ old('school_id', $class->school_id) == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('school_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="grade_level_id">Khối *</label>
                    <select id="grade_level_id" name="grade_level_id" class="form-control @error('grade_level_id') is-invalid @enderror" required>
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

                <div class="form-group">
                    <label for="academic_year_id">Năm học *</label>
                    <select id="academic_year_id" name="academic_year_id" class="form-control @error('academic_year_id') is-invalid @enderror" required>
                        <option value="">-- Chọn năm học --</option>
                        @foreach($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}" {{ old('academic_year_id', $class->academic_year_id) == $academicYear->id ? 'selected' : '' }}>
                                {{ $academicYear->year }} ({{ $academicYear->start_date->format('d/m/Y') }} - {{ $academicYear->end_date->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('academic_year_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end align-items-center mt-3">
                    <a href="{{ route('classes.show', $class->id) }}" class="btn me-2"
                       style="background-color: #ffffff; border-color: #013066; color: #013066;">
                        Đóng
                    </a>
                    <button type="submit" class="btn"
                            style="background-color: #013066; border-color: #013066; color: #fff;">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
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
@endsection
