@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Chỉnh sửa hồ sơ</h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-4">
                                <div class="col-md-4 text-center">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px;">
                                            <i class="fas fa-user fa-4x text-white"></i>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control" id="avatar" name="avatar">
                                    <small class="text-muted">Ảnh JPG, PNG tối đa 2MB</small>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">Họ và tên</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Số điện thoại</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('profile.show') }}" class="btn btn-secondary">Hủy bỏ</a>
                                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
