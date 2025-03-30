@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Thông tin Học kỳ</h2>

        <div class="card">
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>ID:</th>
                        <td>{{ $semester->id }}</td>
                    </tr>
                    <tr>
                        <th>Tên học kỳ:</th>
                        <td>{{ $semester->name }}</td>
                    </tr>
                    <tr>
                        <th>Năm học:</th>
                        <td>{{ $semester->academicYear->year }}</td>
                    </tr>
                    <tr>
                        <th>Ngày bắt đầu:</th>
                        <td>{{ $semester->start_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Ngày kết thúc:</th>
                        <td>{{ $semester->end_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Học kỳ hiện tại:</th>
                        <td>{{ $semester->is_current ? '✓' : '✗' }}</td>
                    </tr>
                </table>

                <div class="mt-3">
                    <a href="{{ route('semesters.edit', $semester->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Sửa
                    </a>
                    <a href="{{ route('semesters.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
