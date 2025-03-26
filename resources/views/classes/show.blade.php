@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Thông Tin Lớp Học</h2>

        <table class="table table-bordered">
            <tr>
                <th>ID</th>
                <td>{{ $class->id }}</td>
            </tr>
            <tr>
                <th>Tên Lớp</th>
                <td>{{ $class->name }}</td>
            </tr>
            <tr>
                <th>Khối</th>
                <td>{{ $class->gradeLevel->grade_number }}</td>
            </tr>
            <tr>
                <th>Năm Học</th>
                <td>{{ $class->academicYear->year }}</td>
            </tr>
        </table>

        <a href="{{ route('classes.index') }}" class="btn btn-secondary">Quay lại</a>
    </div>
@endsection
