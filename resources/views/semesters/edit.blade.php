@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0">Sửa thông tin học kỳ</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('semesters.update', $semester->id) }}" id="semesterForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">

                <div class="form-group">
                    <label for="name">Tên học kỳ *</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                           name="name" value="{{ old('name', $semester->name) }}" autofocus>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="start_date">Ngày bắt đầu *</label>
                    <input id="start_date" type="date" class="form-control @error('start_date') is-invalid @enderror"
                           name="start_date" value="{{ old('start_date', $semester->start_date ? $semester->start_date->format('Y-m-d') : '') }}">
                    @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="end_date">Ngày kết thúc *</label>
                    <input id="end_date" type="date" class="form-control @error('end_date') is-invalid @enderror"
                           name="end_date" value="{{ old('end_date', $semester->end_date ? $semester->end_date->format('Y-m-d') : '') }}">
                    @error('end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end align-items-center mt-3">
                    <a href="{{ route('semesters.index', $selectedYearId) }}" class="btn btn-outline-primary-color me-2">
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
