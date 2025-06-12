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

        dl.row dt {
            font-weight: 500;
            color: #495057;
        }

        dl.row dd {
            color: #212529;
        }
    </style>

    <div class="container">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn btn-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">Thông tin Admin Trường</h3>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">Tên đầy đủ:</dt>
                        <dd class="col-sm-8">{{ $admin->full_name }}</dd>

                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $admin->email }}</dd>
                    </dl>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <dl class="row">
                        <dt class="col-sm-2">Trường:</dt>
                        <dd class="col-sm-10">{{ $admin->school->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-2">Địa chỉ trường:</dt>
                        <dd class="col-sm-10">{{ $admin->school->address ?? '' }},{{ $admin->school->district ?? '' }}, {{ $admin->school->province ?? '' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-end">
                <a href="{{ route('school_admins.index') }}" class="btn btn-primary-color fw-bold">Đóng
                </a>
            </div>
        </div>
    </div>
@endsection
