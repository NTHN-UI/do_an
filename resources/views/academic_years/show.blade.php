@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Thông tin năm học</h2>

        <div class="card">
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>ID:</th>
                        <td>{{ $academicYear->id }}</td>
                    </tr>
                    <tr>
                        <th>Năm học:</th>
                        <td>{{ $academicYear->year }}</td>
                    </tr>
                    <tr>
                        <th>Ngày bắt đầu:</th>
                        <td>{{ $academicYear->start_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Ngày kết thúc:</th>
                        <td>{{ $academicYear->end_date->format('d/m/Y') }}</td>
                    </tr>
                </table>

                <div class="mt-3">
                    <a href="{{ route('academic_years.edit', $academicYear->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Sửa
                    </a>
                    <a href="{{ route('academic_years.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
