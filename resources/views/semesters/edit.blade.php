@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Chỉnh sửa Học kỳ</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('semesters.update', $semester->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label for="name" class="form-label">Tên học kỳ *</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $semester->name) }}" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="academic_year_id" class="form-label">Năm học *</label>
                <select name="academic_year_id" id="academic_year_id"
                        class="form-control @error('academic_year_id') is-invalid @enderror" required>
                    <option value="">-- Chọn năm học --</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}"
                            {{ old('academic_year_id', $semester->academic_year_id) == $year->id ? 'selected' : '' }}>
                            {{ $year->year }}
                        </option>
                    @endforeach
                </select>
                @error('academic_year_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_date" class="form-label">Ngày bắt đầu *</label>
                        <input type="date" name="start_date" id="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date', $semester->start_date->format('Y-m-d')) }}" required>
                        @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_date" class="form-label">Ngày kết thúc *</label>
                        <input type="date" name="end_date" id="end_date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date', $semester->end_date->format('Y-m-d')) }}" required>
                        @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
                <a href="{{ route('semesters.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </form>
    </div>

    <script>
        // Client-side validation for date range
        document.addEventListener('DOMContentLoaded', function() {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');

            endDate.addEventListener('change', function() {
                if (new Date(startDate.value) >= new Date(endDate.value)) {
                    endDate.setCustomValidity('Ngày kết thúc phải sau ngày bắt đầu');
                } else {
                    endDate.setCustomValidity('');
                }
            });
        });
    </script>

    <style>
        .form-group {
            margin-bottom: 1rem;
        }
        .invalid-feedback {
            display: block;
            color: #dc3545;
        }
    </style>
@endsection
