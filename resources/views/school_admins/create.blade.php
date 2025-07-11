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
            <h3 class="mb-0 text-primary-color">Thêm mới Admin Trường</h3>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('school_admins.store') }}">
                @csrf

                <div class="form-group mt-2">
                    <label for="full_name">Tên đầy đủ <span class="text-danger">*</span></label>
                    <input id="full_name" type="text" class="form-control @error('full_name') is-invalid @enderror"
                           name="full_name" value="{{ old('full_name') }}" autocomplete="name" autofocus>
                    @error('full_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" autocomplete="email">
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="school_id">Trường <span class="text-danger">*</span></label>
                    <select id="school_id" class="form-control @error('school_id') is-invalid @enderror"
                            name="school_id">
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

                <div class="form-group mt-2">
                    <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                           name="password" autocomplete="new-password">
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="password-confirm">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                    <input id="password-confirm" type="password" class="form-control"
                           name="password_confirmation" autocomplete="new-password">
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('school_admins.index') }}" class="btn btn-outline-primary-color me-2">Đóng</a>
                    <button type="submit" class="btn btn-primary-color">Lưu</button>
                </div>
            </form>
        </div>
    </div>
@endsection
