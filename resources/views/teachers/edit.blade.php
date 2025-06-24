@extends('layouts.app')

@section('content')
    <style>
        .form-check-input:checked {
            background-color: #013066;
            border-color: #013066;
        }
    </style>
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">Chỉnh sửa thông tin giáo viên
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('teachers.update', $teacher->id) }}" method="POST" id="edit-teacher-form">
                @csrf
                @method('PUT')
                <div class="col-md-12 d-flex align-items-center mb-3">
                    <span class="me-2">Trạng thái</span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               value="1" {{ $teacher->is_active ? 'checked' : '' }}>
                        <label class="form-check-label ms-1" for="is_active">
                            <span id="statusText">{{ old('is_active', $teacher->is_active) ? 'Hoạt động' : 'Ngừng' }}</span>
                        </label>
                    </div>
                </div>
                    <div class="col-md-12">
                        <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="full_name"
                               class="form-control @error('full_name') is-invalid @enderror"
                               value="{{ old('full_name', $teacher->full_name) }}" >
                        @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="email" class="form-label">Email</label>
                        <input type="hidden" name="email" value="{{ $teacher->email }}">
                        <div class="form-control bg-light">
                            {{ $teacher->email }}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label for="subject_id" class="form-label">Môn dạy <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id"
                                class="form-select @error('subject_id') is-invalid @enderror">
                            <option value="">-- Chọn môn học --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}"
                                    {{ old('subject_id', $teacher->subject_id) == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" id="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $teacher->phone) }}" >
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="gender" class="form-label">Giới tính <span class="text-danger">*</span></label>
                        <select name="gender" id="gender"
                                class="form-select @error('gender') is-invalid @enderror" >
                            <option value="">-- Chọn --</option>
                            <option value="Nam" {{ old('gender', $teacher->gender) == 'Nam' ? 'selected' : '' }}>Nam</option>
                            <option value="Nữ" {{ old('gender', $teacher->gender) == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                            <option value="Khác" {{ old('gender', $teacher->gender) == 'Khác' ? 'selected' : '' }}>Khác</option>
                        </select>
                        @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="date_of_birth" class="form-label">Ngày sinh <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" id="date_of_birth"
                               class="form-control @error('date_of_birth') is-invalid @enderror"
                               value="{{ old('date_of_birth', $teacher->date_of_birth->format('Y-m-d')) }}" >
                        @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Trường</label>
                        <input type="hidden" name="school_id" value="{{ Auth::user()->school->id }}">
                        <div class="form-control bg-light">
                            {{ Auth::user()->school->name }}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label for="address" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                        <input type="text" name="address" id="address"
                               class="form-control @error('address') is-invalid @enderror"
                               value="{{ old('address', $teacher->address) }}" >
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                <div class="d-flex justify-content-end align-items-center mt-3">
                    <a href="{{ route('teachers.index') }}" class="btn btn-outline-primary-color me-2">
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
            document.addEventListener('DOMContentLoaded', function() {
                const checkbox = document.getElementById('is_active');
                const statusText = document.getElementById('statusText');

                if (checkbox) {
                    // Cập nhật trạng thái ban đầu
                    statusText.textContent = checkbox.checked ? 'Hoạt động' : 'Ngừng';

                    // Xử lý sự kiện change
                    checkbox.addEventListener('change', function() {
                        statusText.textContent = this.checked ? 'Hoạt động' : 'Ngừng';
                    });
                }
            });


        </script>
    @endpush
