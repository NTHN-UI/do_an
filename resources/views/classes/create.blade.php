@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ isset($class) ? 'Chỉnh Sửa' : 'Thêm Mới' }} Lớp Học</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($class) ? route('classes.update', $class->id) : route('classes.store') }}">
                    @csrf
                    @if(isset($class))
                        @method('PUT')
                    @endif

                    <div class="form-group">
                        <label for="name">Tên Lớp</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="{{ old('name', $class->name ?? '') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="school_id">Trường</label>
                        <select class="form-control" id="school_id" name="school_id" required>
                            <option value="">-- Chọn Trường --</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}"
                                    {{ old('school_id', $class->school_id ?? '') == $school->id ? 'selected' : '' }}>
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="grade_level_id">Khối</label>
                        <select class="form-control" id="grade_level_id" name="grade_level_id" required>
                            <option value="">-- Chọn Khối --</option>
                            @foreach($gradeLevels as $gradeLevel)
                                <option value="{{ $gradeLevel->id }}"
                                    {{ old('grade_level_id', $class->grade_level_id ?? '') == $gradeLevel->id ? 'selected' : '' }}>
                                    Khối {{ $gradeLevel->grade_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="academic_year_id">Năm Học</label>
                        <select class="form-control" id="academic_year_id" name="academic_year_id" required>
                            <option value="">-- Chọn Năm Học --</option>
                            @foreach($academicYears as $academicYear)
                                <option value="{{ $academicYear->id }}"
                                    {{ old('academic_year_id', $class->academic_year_id ?? '') == $academicYear->id ? 'selected' : '' }}>
                                    {{ $academicYear->year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        {{ isset($class) ? 'Cập Nhật' : 'Thêm Mới' }}
                    </button>
                    <a href="{{ route('classes.index') }}" class="btn btn-secondary">Hủy</a>
                </form>
            </div>
        </div>
    </div>
@endsection
