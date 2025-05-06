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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
        }

        .btn-back {
            background: none;
            border: none;
            font-size: 1.2rem;
        }

        .btn-back:hover {
            color: #011a4d !important;
        }
    </style>

    <div class="container">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('school_admins.index') }}" class="btn btn-back me-3" style="color: #013066;" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0" style="color: #013066;">Thêm mới Admin Trường</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('school_admins.store') }}">
                @csrf

                <div class="form-group">
                    <label for="full_name">Tên đầy đủ <span class="text-danger">*</span></label>
                    <input id="full_name" type="text" class="form-control @error('full_name') is-invalid @enderror"
                           name="full_name" value="{{ old('full_name') }}" autocomplete="name" autofocus>
                    @error('full_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" autocomplete="email">
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="school_id">Trường <span class="text-danger">*</span></label>
                    <select id="school_id" class="form-control @error('school_id') is-invalid @enderror"
                            name="school_id" >
                        <option value="">-- Chọn trường --</option>
                        @forelse($schools as $school)
                            <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }} ({{ $school->district }})
                            </option>
                        @empty
                            <option value="">Không có trường nào</option>
                        @endforelse
                    </select>
                    @error('school_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                           name="password"  autocomplete="new-password">
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password-confirm">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                    <input id="password-confirm" type="password" class="form-control"
                           name="password_confirmation"  autocomplete="new-password">
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('school_admins.index') }}" class="btn btn-secondary me-2"
                       style="background-color: #ffffff; border-color: #013066; color: #013066;">Đóng</a>
                    <button type="submit" class="btn btn-primary"
                            style="background-color: #013066; border-color: #013066; color: #fff;">Lưu</button>
                </div>
            </form>
        </div>
    </div>
@endsection
