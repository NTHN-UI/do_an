@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Chỉnh sửa khối</h2>
        <form action="{{ route('grade_levels.update', $grade->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="grade_number" class="form-label">Số khối:</label>
                <input type="number" name="grade_number" class="form-control" value="{{ $grade->grade_number }}" required>
            </div>
            <button type="submit" class="btn btn-success">Cập nhật</button>
            <a href="{{ route('grade_levels.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
@endsection
@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Chỉnh sửa khối</h2>
        <form action="{{ route('grade_levels.update', $grade->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="grade_number" class="form-label">Số khối:</label>
                <input type="number" name="grade_number" class="form-control" value="{{ $grade->grade_number }}" required>
            </div>
            <button type="submit" class="btn btn-success">Cập nhật</button>
            <a href="{{ route('grade_levels.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
@endsection
