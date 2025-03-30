@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông Tin Lớp Học: {{ $class->name }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Trường:</strong> {{ $class->school->name }}</p>
                        <p><strong>Khối:</strong> Khối {{ $class->gradeLevel->grade_number }}</p>
                        <p><strong>Năm học:</strong> {{ $class->academicYear->year }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Ngày tạo:</strong> {{ $class->created_at->format('d/m/Y H:i') }}</p>
                        <p><strong>Cập nhật cuối:</strong> {{ $class->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <div class="mt-4 d-flex">
                    <a href="{{ route('classes.edit', $class->id) }}" class="btn btn-primary mr-2">
                        <i class="fas fa-edit"></i> Chỉnh Sửa
                    </a>
                    <form action="{{ route('classes.destroy', $class->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
