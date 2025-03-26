@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Thêm Học kỳ</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('semesters.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Tên Học kỳ</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="academic_year_id" class="form-label">Năm học</label>
                <select class="form-control" id="academic_year_id" name="academic_year_id" required>
                    <option value="">-- Chọn năm học --</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->year }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Thêm</button>
        </form>
    </div>
@endsection
