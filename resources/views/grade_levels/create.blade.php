@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Thêm khối mới</h2>
        <form action="{{ route('grade_levels.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="grade_number" class="form-label">Nhập số khối:</label>
                <input type="number" name="grade_number" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Thêm</button>
            <a href="{{ route('grade_levels.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
@endsection

