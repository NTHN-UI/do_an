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
            <a href="{{ route('schools.index') }}" class="btn btn-back me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0" style = "color:#013066"> Sửa thông tin trường học </h4>
        </div>
        <div class="card-body">
            <form action="{{ route('schools.update', $school->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Tên trường *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $school->name) }}" >
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="district">Quận/Huyện *</label>
                    <input type="text" class="form-control @error('district') is-invalid @enderror"
                           id="district" name="district" value="{{ old('district', $school->district) }}" >
                    @error('district')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="province">Tỉnh/Thành *</label>
                    <input type="text" class="form-control @error('province') is-invalid @enderror"
                           id="province" name="province" value="{{ old('province', $school->province) }}" >
                    @error('province')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="education_level">Cấp học *</label>
                    <select class="form-control @error('education_level') is-invalid @enderror"
                            id="education_level" name="education_level" >
                        <option
                            value="secondary" {{ old('education_level', $school->education_level) == 'secondary' ? 'selected' : '' }}>
                            THCS
                        </option>
                        <option
                            value="high" {{ old('education_level', $school->education_level) == 'high' ? 'selected' : '' }}>
                            THPT
                        </option>
                    </select>
                    @error('education_level')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex justify-content-end align-items-center mt-3">
                    <a href="{{ route('schools.index', $school->id) }}" class="btn me-2"
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
