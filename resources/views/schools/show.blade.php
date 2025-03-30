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
            <a href="{{ route('schools.index') }}" class="btn btn-back me-3" style = "color:#013066" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0" style = "color:#013066"> Xem thông tin trường học</h4>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">Tên trường:</dt>
                        <dd class="col-sm-8">{{ $school->name }}</dd>

                        <dt class="col-sm-4">Quận/Huyện:</dt>
                        <dd class="col-sm-8">{{ $school->district }}</dd>

                        <dt class="col-sm-4">Tỉnh/Thành:</dt>
                        <dd class="col-sm-8">{{ $school->province }}</dd>

                        <dt class="col-sm-4">Cấp học:</dt>
                        <dd class="col-sm-8">{{ $school->education_level_name }}</dd>
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
                <a href="{{ route('schools.index', $school->id) }}" class="btn fw-bold"
                   style="background-color: #013066; border-color: #013066; color: #fff; width: 90px"> Đóng
                </a>
            </div>
        </div>
    </div>
@endsection
