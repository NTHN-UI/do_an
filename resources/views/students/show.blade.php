<!-- resources/views/students/show.blade.php -->
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
            <a href="{{ route('students.index') }}" class="btn btn-back me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0"> Xem thông tin học sinh</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Họ và tên:</strong> {{ $student->full_name }}</p>
                    <p><strong>Email:</strong> {{ $student->email ?? 'N/A' }}</p>
                    <p><strong>SĐT:</strong> {{ $student->phone ?? 'N/A' }}</p>
                    <p><strong>Ngày
                            sinh:</strong> {{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}
                    </p>
                    <p><strong>Giới tính:</strong> {{ $student->gender ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Trường:</strong> {{ $student->school->name ?? 'N/A' }}</p>
                    <p><strong>Phụ huynh:</strong> {{ $student->guardian_name ?? 'N/A' }}</p>
                    <p><strong>SĐT phụ huynh:</strong> {{ $student->guardian_phone ?? 'N/A' }}</p>
                    <p><strong>Email phụ huynh:</strong> {{ $student->guardian_email ?? 'N/A' }}</p>
                    <p><strong>Trạng thái:</strong>
                        <span class="badge {{ $student->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $student->is_active ? 'Hoạt động' : 'Ngừng' }}
                        </span>
                    </p>
                </div>
            </div>
            <p><strong>Địa chỉ:</strong> {{ $student->address ?? 'N/A' }}</p>
            <div class="mt-3 d-flex justify-content-end">
                <a href="{{ route('students.index', $student->id) }}" class="btn fw-bold"
                   style="background-color: #E15336; border-color: #E15336; color: #fff; width: 90px"> Đóng
                </a>
            </div>
        </div>
    </div>
@endsection
