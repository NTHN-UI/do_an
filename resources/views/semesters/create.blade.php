@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Thêm mới học kỳ</h2>

        <form action="{{ route('semesters.store') }}" method="POST">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">

            <div class="form-group">
                <label>Tên học kỳ *</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}">
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Ngày bắt đầu *</label>
                        <input type="date" name="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date') }}" >
                        @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Ngày kết thúc *</label>
                        <input type="date" name="end_date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date') }}" >
                        @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group form-check">
                <input type="hidden" name="is_current" value="0">
                <input type="checkbox" name="is_current" id="is_current"
                       class="form-check-input @error('is_current') is-invalid @enderror" value="1"
                    {{ old('is_current') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_current">
                    Học kỳ hiện tại
                </label>
                @error('is_current')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Thêm mới</button>
            <a href="{{ route('semesters.index') }}" class="btn btn-secondary">Hủy bỏ</a>
        </form>
    </div>
@endsection
