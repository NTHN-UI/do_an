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
            <h3 class="mb-0 text-primary-color">Sửa thông tin Admin Trường</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('school_admins.update', $admin->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group mt-2">
                    <label for="full_name">Tên đầy đủ *</label>
                    <input id="full_name" type="text" class="form-control @error('full_name') is-invalid @enderror"
                           name="full_name" value="{{ old('full_name', $admin->full_name) }}" >
                    @error('full_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="email">Email *</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email', $admin->email) }}" >
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="school_id">Trường *</label>
                    <select id="school_id" class="form-control @error('school_id') is-invalid @enderror"
                            name="school_id" >
                        <option value="">-- Chọn trường --</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}"
                                {{ old('school_id', $admin->school_id) == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('school_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="password">Mật khẩu mới</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                           name="password" autocomplete="new-password">
                    <small class="text-muted">Để trống nếu không đổi mật khẩu</small>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="password-confirm">Xác nhận mật khẩu</label>
                    <input id="password-confirm" type="password" class="form-control"
                           name="password_confirmation" autocomplete="new-password">
                </div>

                <div class="d-flex justify-content-end align-items-center mt-3">
                    <a href="{{ route('school_admins.index') }}" class="btn btn-outline-primary-color me-2">
                        Đóng
                    </a>
                    <button type="submit" class="btn btn-primary-color">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
