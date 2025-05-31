@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0 text-primary-color">Thêm năm học mới</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('academic_years.store') }}">
                @csrf

                <div class="form-group">
                    <label for="year">Năm học (VD: 2023-2024) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('year') is-invalid @enderror"
                           id="year" name="year" value="{{ old('year') }}" pattern="\d{4}-\d{4}">
                    @error('year')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="start_date">Ngày bắt đầu <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                           id="start_date" name="start_date" value="{{ old('start_date') }}">
                    @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="end_date">Ngày kết thúc <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                           id="end_date" name="end_date" value="{{ old('end_date') }}">
                    @error('end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('academic_years.index') }}" class="btn btn-secondary me-2">Đóng</a>
                    <button type="submit" class="btn btn-primary-color">
                        Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
