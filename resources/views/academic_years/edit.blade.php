@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Chỉnh sửa năm học</h2>

        <form action="{{ route('academic_years.update', $academicYear->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Năm học (VD: 2023-2024)</label>
                <input type="text" name="year" class="form-control" value="{{ $academicYear->year }}" required>
            </div>

            <div class="form-group">
                <label>Ngày bắt đầu</label>
                <input type="date" name="start_date" class="form-control"
                       value="{{ $academicYear->start_date->format('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label>Ngày kết thúc</label>
                <input type="date" name="end_date" class="form-control"
                       value="{{ $academicYear->end_date->format('Y-m-d') }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="{{ route('academic_years.index') }}" class="btn btn-secondary">Hủy bỏ</a>
        </form>
    </div>
@endsection
