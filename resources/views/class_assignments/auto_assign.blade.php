@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h3 class="mb-0 text-primary-color">Phân công học sinh tự động vào lớp</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('class_assignments.auto_assign') }}">
                    @csrf

                    <div class="form-group">
                        <label for="grade_level_id" class="col-md-3 col-form-label">Khối lớp</label>
                            <select name="grade_level_id" id="grade_level_id" class="form-select @error('grade_level_id') is-invalid @enderror" >
                                <option value="">-- Chọn khối --</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}">Khối {{ $grade->grade_number }}</option>
                                @endforeach
                            </select>
                        @error('grade_level_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                        </div>


                    <div class="form-group">
                        <label for="academic_year_id" class="col-md-3 col-form-label">Năm học</label>
                            <select name="academic_year_id" id="academic_year_id" class="form-select  @error('academic_year_id') is-invalid @enderror" >
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $currentAcademicYear && $year->id == $currentAcademicYear->id ? 'selected' : '' }}>
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
                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('class_assignments.index') }}" class="btn btn-outline-primary-color me-2">Đóng</a>
                        <button type="submit" class="btn btn-primary-color">
                            Phân công tự động
                        </button>
                    </div>
                </form>
            </div>
        </div>

@endsection
