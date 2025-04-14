@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Thêm năm học mới</h2>

        <form action="{{ route('academic_years.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Năm học (VD: 2023-2024)</label>
                <input type="text" name="year" class="form-control @error('year') is-invalid @enderror"
                       value="{{ old('year') }}" pattern="\d{4}-\d{4}">
                @error('year')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Ngày bắt đầu</label>
                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
                @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Ngày kết thúc</label>
                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{old('end_date')}}">
                @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Lưu lại</button>
            <a href="{{ route('academic_years.index') }}" class="btn btn-secondary">Hủy bỏ</a>
        </form>
    </div>
@endsection
