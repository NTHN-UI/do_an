@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">Thêm mới lớp học</h3>
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
                    <a href="{{ route('classes.index') }}" class="btn btn-secondary me-2">Đóng</a>
                    <button type="submit" class="btn btn-primary-color">
                        Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
