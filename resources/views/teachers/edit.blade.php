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
                    <div class="mb-3 d-flex align-items-center">
                        <span class="me-2">Trạng thái</span>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                   {{ old('is_active', $teacher->is_active) ? 'checked' : '' }}
                                   onchange="toggleStatusText(this)">
                            <label class="form-check-label ms-1" for="is_active">
                            <span
                                id="statusText">{{ old('is_active', $teacher->is_active) ? 'Hoạt động' : 'Ngừng' }}</span>
                            </label>
                        </div>
                    </div>
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
                            <label for="email" class="form-label">Email </label>
                            <input type="hidden" name="email" value="{{ $teacher->email }}">
                            <div class="form-control bg-light">
                                {{ $teacher->email }}
                            </div>
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
@endsection

    @push('scripts')
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
            document.addEventListener('DOMContentLoaded', function () {
                const checkbox = document.getElementById('is_active');
                const statusText = document.getElementById('statusText');
                // Sử dụng giá trị từ database thay vì mặc định true
                statusText.textContent = checkbox.checked ? 'Hoạt động' : 'Ngừng';
            });

            function toggleStatusText(checkbox) {
                document.getElementById('statusText').textContent = checkbox.checked ? 'Hoạt động' : 'Ngừng';
            }

      
        </script>
    @endpush
