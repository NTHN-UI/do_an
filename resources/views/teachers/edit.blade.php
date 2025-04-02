<!-- resources/views/teachers/edit.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>Chỉnh sửa thông tin giáo viên
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('teachers.update', $teacher->id) }}" method="POST" id="edit-teacher-form">
                    @csrf
                    @method('PUT')

                    <!-- Thông tin cơ bản -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="full_name"
                                   class="form-control @error('full_name') is-invalid @enderror"
                                   value="{{ old('full_name', $teacher->full_name) }}" required>
                            @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $teacher->email) }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Thông tin liên hệ -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" id="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $teacher->phone) }}" required>
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="gender" class="form-label">Giới tính <span class="text-danger">*</span></label>
                            <select name="gender" id="gender"
                                    class="form-select @error('gender') is-invalid @enderror" required>
                                <option value="">-- Chọn --</option>
                                <option value="Nam" {{ old('gender', $teacher->gender) == 'Nam' ? 'selected' : '' }}>Nam</option>
                                <option value="Nữ" {{ old('gender', $teacher->gender) == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                <option value="Khác" {{ old('gender', $teacher->gender) == 'Khác' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="date_of_birth" class="form-label">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" id="date_of_birth"
                                   class="form-control @error('date_of_birth') is-invalid @enderror"
                                   value="{{ old('date_of_birth', $teacher->date_of_birth->format('Y-m-d')) }}" required>
                            @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Trường học & Địa chỉ -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trường</label>
                            <input type="hidden" name="school_id" value="{{ Auth::user()->school->id }}">
                            <div class="form-control bg-light">
                                {{ Auth::user()->school->name }} ({{ Auth::user()->school->education_level_name }})
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                            <input type="text" name="address" id="address"
                                   class="form-control @error('address') is-invalid @enderror"
                                   value="{{ old('address', $teacher->address) }}" required>
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Mật khẩu -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Mật khẩu mới</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Để trống nếu không đổi">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Chỉ điền khi muốn thay đổi mật khẩu</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control"
                                   placeholder="Xác nhận mật khẩu mới">
                        </div>
                    </div>

                    <!-- Trạng thái hoạt động -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="is_active" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select name="is_active" id="is_active"
                                    class="form-select @error('is_active') is-invalid @enderror" required>
                                <option value="1" {{ old('is_active', $teacher->is_active) == 1 ? 'selected' : '' }}>Hoạt động</option>
                                <option value="0" {{ old('is_active', $teacher->is_active) == 0 ? 'selected' : '' }}>Không hoạt động</option>
                            </select>
                            @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Nút submit -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('teachers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Hủy bỏ
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Validate form trước khi submit
                document.getElementById('edit-teacher-form').addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('password_confirmation').value;

                    if (password && password !== confirmPassword) {
                        e.preventDefault();
                        alert('Mật khẩu xác nhận không khớp!');
                        document.getElementById('password_confirmation').focus();
                    }
                });

                // Format số điện thoại
                document.getElementById('phone').addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            });
        </script>
    @endsection
@endsection
