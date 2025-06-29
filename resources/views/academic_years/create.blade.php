@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">

            <h3 class="mb-0 text-primary-color">Thêm năm học mới</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('academic_years.store') }}">
                @csrf

                <div class="form-group mt-2">
                    <label for="year"  class="mb-2">Năm học (VD: 2023-2024) <span class="text-danger">*</span></label>
                    <input type="text" name="year" class="form-control @error('year') is-invalid @enderror"
                           value="{{ old('year') }}" pattern="\d{4}-\d{4}">
                    @error('year')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="start_date"  class="mb-2">Ngày bắt đầu <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
                    @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="end_date"  class="mb-2">Ngày kết thúc <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{old('end_date')}}">
                    @error('end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('academic_years.index') }}" class="btn btn-outline-primary-color me-2">Đóng</a>
                    <button type="submit" class="btn btn-primary-color">
                        Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
