@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Chỉnh sửa Admin Trường</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('school_admins.update', $admin->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group row mb-3">
                                <label for="full_name" class="col-md-4 col-form-label text-md-right">Tên đầy đủ</label>

                                <div class="col-md-6">
                                    <input id="full_name" type="text" class="form-control @error('full_name') is-invalid @enderror"
                                           name="full_name" value="{{ old('full_name', $admin->full_name) }}" required>

                                    @error('full_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="email" class="col-md-4 col-form-label text-md-right">Email</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                           name="email" value="{{ old('email', $admin->email) }}" required>

                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="school_id" class="col-md-4 col-form-label text-md-right">Trường</label>

                                <div class="col-md-6">
                                    <select id="school_id" class="form-control @error('school_id') is-invalid @enderror"
                                            name="school_id" required>
                                        <option value="">-- Chọn trường --</option>
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}"
                                                {{ old('school_id', $admin->school_id) == $school->id ? 'selected' : '' }}>
                                                {{ $school->name }} ({{ $school->education_level_name }})
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('school_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="password" class="col-md-4 col-form-label text-md-right">Mật khẩu mới</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                           name="password" autocomplete="new-password">
                                    <small class="text-muted">Để trống nếu không đổi mật khẩu</small>

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="password-confirm" class="col-md-4 col-form-label text-md-right">Xác nhận mật khẩu</label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control"
                                           name="password_confirmation" autocomplete="new-password">
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Cập nhật
                                    </button>
                                    <a href="{{ route('school_admins.index') }}" class="btn btn-secondary">
                                        Hủy bỏ
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
