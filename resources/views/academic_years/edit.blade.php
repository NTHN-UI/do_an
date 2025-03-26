@extends('layouts.app')

@section('content')
    <h2>Sửa năm học</h2>
    <form action="{{ route('academic_years.update', $academicYear->id) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Năm học:</label>
        <input type="text" name="year" value="{{ $academicYear->year }}" required>

        <button type="submit">Cập nhật</button>
    </form>
@endsection
