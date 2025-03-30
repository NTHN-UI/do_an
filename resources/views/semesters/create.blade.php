@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ isset($semester) ? 'Chỉnh sửa' : 'Thêm mới' }} Học kỳ</h2>

        <form action="{{ isset($semester) ? route('semesters.update', $semester->id) : route('semesters.store') }}" method="POST">
            @csrf
            @if(isset($semester)) @method('PUT') @endif

            <div class="form-group">
                <label>Tên học kỳ *</label>
                <input type="text" name="name" class="form-control"
                       value="{{ $semester->name ?? old('name') }}" required>
            </div>

            <div class="form-group">
                <label>Năm học *</label>
                <select name="academic_year_id" class="form-control" required>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}"
                            {{ (isset($semester) && $semester->academic_year_id == $year->id) || old('academic_year_id') == $year->id ? 'selected' : '' }}>
                            {{ $year->year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Ngày bắt đầu *</label>
                        <input type="date" name="start_date" class="form-control"
                               value="{{ isset($semester) ? $semester->start_date->format('Y-m-d') : old('start_date') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Ngày kết thúc *</label>
                        <input type="date" name="end_date" class="form-control"
                               value="{{ isset($semester) ? $semester->end_date->format('Y-m-d') : old('end_date') }}" required>
                    </div>
                </div>
            </div>

            <div class="form-group form-check">
                <input type="hidden" name="is_current" value="0"> <!-- Quan trọng -->
                <input type="checkbox" name="is_current" id="is_current"
                       class="form-check-input" value="1"
                    {{ old('is_current', $semester->is_current ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_current">
                    Học kỳ hiện tại
                </label>
            </div>

            <button type="submit" class="btn btn-primary">
                {{ isset($semester) ? 'Cập nhật' : 'Thêm mới' }}
            </button>
            <a href="{{ route('semesters.index') }}" class="btn btn-secondary">Hủy bỏ</a>
        </form>
    </div>
@endsection
