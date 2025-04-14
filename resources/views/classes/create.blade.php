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
            <a href="{{ route('classes.index') }}" class="btn btn-back me-3" style="color: #013066;" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0" style="color: #013066;">Thêm mới lớp học</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('classes.store') }}">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">

                <div class="form-group">
                    <label for="name">Tên Lớp <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" >
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
                    <label for="grade_level_id">Khối <span class="text-danger">*</span></label>
                    <select class="form-control @error('grade_level_id') is-invalid @enderror"
                            id="grade_level_id" name="grade_level_id" >
                        <option value="">-- Chọn Khối --</option>
                        @foreach($gradeLevels as $gradeLevel)
                            <option value="{{ $gradeLevel->id }}" {{ old('grade_level_id') == $gradeLevel->id ? 'selected' : '' }}>
                                Khối {{ $gradeLevel->grade_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('grade_level_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>



                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('classes.index') }}" class="btn btn-secondary me-2"
                       style="background-color: #ffffff; border-color: #013066; color: #013066 ;">Đóng</a>
                    <button type="submit" class="btn btn-primary "
                            style="background-color: #013066; border-color: #013066; color: #fff;">
                        Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
