@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4>Phân công học sinh tự động vào lớp</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('class_assignments.auto_assign') }}">
                    @csrf

                    <div class="row mb-3">
                        <label for="grade_level_id" class="col-md-3 col-form-label">Khối lớp</label>
                        <div class="col-md-9">
                            <select name="grade_level_id" id="grade_level_id" class="form-select" required>
                                <option value="">-- Chọn khối --</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}">Khối {{ $grade->grade_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="academic_year_id" class="col-md-3 col-form-label">Năm học</label>
                        <div class="col-md-9">
                            <select name="academic_year_id" id="academic_year_id" class="form-select" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $currentAcademicYear && $year->id == $currentAcademicYear->id ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-9 offset-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-magic me-2"></i>Phân công tự động
                            </button>
                            <a href="{{ route('class_assignments.index') }}" class="btn btn-secondary">
                                Hủy bỏ
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
