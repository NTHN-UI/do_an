@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0 text-primary-color">Xem thông tin lớp học</h4>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-12">
                    <dl class="row">
                        <dt class="col-sm-4">Tên lớp:</dt>
                        <dd class="col-sm-8">{{ $class->name }}</dd>

                        <dt class="col-sm-4">Trường:</dt>
                        <dd class="col-sm-8">{{ $class->school->name }}</dd>

                        <dt class="col-sm-4">Khối:</dt>
                        <dd class="col-sm-8">Khối {{ $class->gradeLevel->grade_number }}</dd>

                        <dt class="col-sm-4">Năm học:</dt>
                        <dd class="col-sm-8">{{ $class->academicYear->year }}</dd>

                        <dt class="col-sm-4">Ngày tạo:</dt>
                        <dd class="col-sm-8">{{ $class->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Lần cuối cập nhật:</dt>
                        <dd class="col-sm-8">{{ $class->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                <a href="{{ route('classes.index', $class->id) }}" class="btn btn-primary-color me-2">
                     Đóng
                </a>
            </div>
        </div>
    </div>
@endsection
