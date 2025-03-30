@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Chỉnh Sửa Lớp Học: {{ $class->name }}</h3>
                            <a href="{{ route('classes.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('classes.update', $class->id) }}" id="classForm">
                            @csrf
                            @method('PUT')

                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Tên Lớp</label>
                                <div class="col-md-6">
                                    <input id="name" type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           name="name" value="{{ old('name', $class->name) }}"
                                           required autofocus>
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="school_id" class="col-md-4 col-form-label text-md-right">Trường</label>
                                <div class="col-md-6">
                                    <select id="school_id" name="school_id"
                                            class="form-control @error('school_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn trường --</option>
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}"
                                                {{ old('school_id', $class->school_id) == $school->id ? 'selected' : '' }}>
                                                {{ $school->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('school_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="grade_level_id" class="col-md-4 col-form-label text-md-right">Khối</label>
                                <div class="col-md-6">
                                    <select id="grade_level_id" name="grade_level_id"
                                            class="form-control @error('grade_level_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn khối --</option>
                                        @foreach($gradeLevels as $gradeLevel)
                                            <option value="{{ $gradeLevel->id }}"
                                                {{ old('grade_level_id', $class->grade_level_id) == $gradeLevel->id ? 'selected' : '' }}>
                                                Khối {{ $gradeLevel->grade_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('grade_level_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="academic_year_id" class="col-md-4 col-form-label text-md-right">Năm học</label>
                                <div class="col-md-6">
                                    <select id="academic_year_id" name="academic_year_id"
                                            class="form-control @error('academic_year_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn năm học --</option>
                                        @foreach($academicYears as $academicYear)
                                            <option value="{{ $academicYear->id }}"
                                                {{ old('academic_year_id', $class->academic_year_id) == $academicYear->id ? 'selected' : '' }}>
                                                {{ $academicYear->year }} ({{ $academicYear->start_date->format('d/m/Y') }} - {{ $academicYear->end_date->format('d/m/Y') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Lưu Thay Đổi
                                    </button>
                                    <a href="{{ route('classes.show', $class->id) }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Hủy
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
