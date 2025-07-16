@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <h3 class="mb-0 text-primary-color">Phân lớp tự động</h3>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('class_assignments.auto_assign') }}">
                    @csrf

                    <div class="row mb-3">
                        <div>
                            <label for="academic_year_id" class="form-label">Năm học <span class="text-danger">*</span></label>
                            <select name="academic_year_id" id="academic_year_id"
                                    class="form-select @error('academic_year_id') is-invalid @enderror" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}"
                                        {{ ($currentAcademicYear && $year->id == $currentAcademicYear->id) || old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_year_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div>
                            <label for="grade_level_id" class="form-label">Khối lớp <span class="text-danger">*</span></label>
                            <select name="grade_level_id" id="grade_level_id"
                                    class="form-select @error('grade_level_id') is-invalid @enderror" required>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}"
                                        {{ ($grade->grade_number == 10) || old('grade_level_id') == $grade->id ? 'selected' : '' }}>
                                        Khối {{ $grade->grade_number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grade_level_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('class_assignments.index') }}" class="btn btn-outline-primary-color me-2">Đóng
                        </a>
                        <button type="submit" class="btn btn-primary-color">Phân lớp tự động
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
