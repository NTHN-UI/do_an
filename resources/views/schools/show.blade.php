@extends('layouts.app')

@section('content')
    <style>
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow-x: auto;
        }
    </style>
    <div class="container">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('schools.index') }}" class="btn btn-sm btn-primary-color me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0 text-primary-color"> Xem thông tin trường học</h4>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">Tên trường:</dt>
                        <dd class="col-sm-8">{{ $school->name }}</dd>

                        <dt class="col-sm-4">Địa chỉ:</dt>
                        <dd class="col-sm-8">{{ $school->address }}</dd>

                        <dt class="col-sm-4">Quận/Huyện:</dt>
                        <dd class="col-sm-8">{{ $school->district }}</dd>

                        <dt class="col-sm-4">Tỉnh/Thành:</dt>
                        <dd class="col-sm-8">{{ $school->province }}</dd>

                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">{{ $school->id }}</dd>

                        <dt class="col-sm-4">Ngày tạo:</dt>
                        <dd class="col-sm-8">{{ $school->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Lần cuối cập nhật:</dt>
                        <dd class="col-sm-8">{{ $school->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                <a href="{{ route('schools.email-settings', $school) }}" class="btn btn-primary-color fw-bold me-2">
                    <i class="fas fa-envelope"></i> Cấu hình Email
                </a>
                <a href="{{ route('schools.index', $school->id) }}" class="btn btn-primary-color fw-bold"> Đóng
                </a>

            </div>


        </div>
    </div>
@endsection
