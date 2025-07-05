@extends('layouts.app')

@section('content')
    <style>
        .container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0px 4px 16px rgba(0, 0, 0, 0.08);
            padding: 32px;
            overflow-x: auto;
        }

    </style>
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <h3 class="mb-0 text-primary-color">Thông tin học sinh</h3>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3 text-center">
                    <div class="avatar-lg mx-auto bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; overflow: hidden;">
                        @if($student->avatar_url)
                            <img src="{{ $student->avatar_url }}" alt="Avatar" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <i class="fas fa-user-graduate fa-2x text-secondary"></i>
                        @endif
                    </div>

                    <h4>{{ $student->full_name }}</h4>
                    <p class="text-muted mb-0">{{ $student->school->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-9">
                    <dl class="row">
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $student->email ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">SĐT:</dt>
                        <dd class="col-sm-8">{{ $student->phone ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Ngày sinh:</dt>
                        <dd class="col-sm-8">{{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}</dd>

                        <dt class="col-sm-4">Giới tính:</dt>
                        <dd class="col-sm-8">{{ $student->gender ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Điểm đầu vào:</dt>
                        <dd class="col-sm-8">{{ $student->entry_score ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Trạng thái:</dt>
                        <dd class="col-sm-8">
                            @if($student->is_active)
                                <span class="badge bg-primary-color">Hoạt động</span>
                            @else
                                <span class="badge bg-primary-color">Ngừng</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Địa chỉ:</dt>
                        <dd class="col-sm-8">{{ $student->address ?? 'N/A' }}</dd>
                    </dl>
                </div>
            </div>
            <div class="info-section mb-4">
                <h5 class="text-primary-color">Thông tin phụ huynh</h5>
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Tên phụ huynh:</dt>
                            <dd class="col-sm-7">{{ $student->guardian_name ?? 'N/A' }}</dd>

                            <dt class="col-sm-5">Email phụ huynh:</dt>
                            <dd class="col-sm-7">{{ $student->guardian_email ?? 'N/A' }}</dd>

                            <dt class="col-sm-5">SĐT phụ huynh:</dt>
                            <dd class="col-sm-7">{{ $student->guardian_phone ?? 'N/A' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                <a href="{{ route('students.index', $student->id) }}" class="btn btn-primary-color fw-bold" style="width: 90px">
                    Đóng
                </a>
            </div>
        </div>
    </div>
@endsection
