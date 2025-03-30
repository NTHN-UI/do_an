@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Thông tin Admin Trường</div>

                    <div class="card-body">
                        <div class="row mb-3">
                            <label class="col-md-4 text-end fw-bold">Tên đầy đủ:</label>
                            <div class="col-md-8">
                                {{ $admin->full_name }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 text-end fw-bold">Email:</label>
                            <div class="col-md-8">
                                {{ $admin->email }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 text-end fw-bold">Trường:</label>
                            <div class="col-md-8">
                                {{ $admin->school->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 text-end fw-bold">Cấp học:</label>
                            <div class="col-md-8">
                                {{ $admin->school->education_level_name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 text-end fw-bold">Địa chỉ trường:</label>
                            <div class="col-md-8">
                                {{ $admin->school->district ?? '' }}, {{ $admin->school->province ?? '' }}
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <a href="{{ route('school_admins.edit', $admin->id) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="{{ route('school_admins.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
