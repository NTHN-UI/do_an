@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Thêm Lớp Học</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('classes.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Tên Lớp</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="mb-3">
                <label for="grade_level_id" class="form-label">Chọn Khối</label>
                <select class="form-control" id="grade_level_id" name="grade_level_id" required>
                    <option value="">-- Chọn Khối --</option>
                    @foreach($grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->grade_number }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="academic_year_id" class="form-label">Chọn Năm Học</label>
                <select class="form-control" id="academic_year_id" name="academic_year_id" required>
                    <option value="">-- Chọn Năm Học --</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->year }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Thêm Lớp</button>
            <a href="{{ route('classes.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
@endsection
