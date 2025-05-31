@extends('layouts.app')

@section('content')

    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0">Chỉnh sửa năm học</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('academic_years.update', $academicYear->id) }}" method="POST" id="academicYearForm">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="year">Năm học (VD: 2023-2024) *</label>
                    <input id="year" type="text" name="year" class="form-control @error('year') is-invalid @enderror"
                           value="{{ old('year', $academicYear->year) }}" required pattern="\d{4}-\d{4}">
                    @error('year')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="start_date">Ngày bắt đầu *</label>
                    <input id="start_date" type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                           value="{{ old('start_date', $academicYear->start_date->format('Y-m-d')) }}" required>
                    @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="end_date">Ngày kết thúc *</label>
                    <input id="end_date" type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                           value="{{ old('end_date', $academicYear->end_date->format('Y-m-d')) }}" required>
                    @error('end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end align-items-center mt-3">
                    <a href="{{ route('academic_years.index') }}" class="btn btn-primary-color me-2">
                        Đóng
                    </a>
                    <button type="submit" class="btn btn-primary-color">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
