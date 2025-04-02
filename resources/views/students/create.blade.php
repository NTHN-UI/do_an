<!-- resources/views/students/create.blade.php -->
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

        .form-check-input:checked {
            background-color: #E15336;
            border-color: #E15336;
        }


    </style>

    <div class="container">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('students.index') }}" class="btn btn-back me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0">Thêm mới Học sinh</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('students.store') }}" method="POST">
                @csrf
                <div class="mb-3 d-flex align-items-center">
                    <span class="me-2">Trạng thái</span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               onchange="document.getElementById('statusText').textContent = this.checked ? 'Hoạt động' : 'Ngừng'">
                        <label class="form-check-label ms-1" for="is_active">
                            <span id="statusText">{{ old('is_active', true) ? 'Hoạt động' : 'Ngừng' }}</span>
                        </label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Họ và tên <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                   id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                            @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                   name="email" value="{{ old('email') }}">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                   name="phone" value="{{ old('phone') }}">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                   id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                            @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address"
                                      name="address" rows="3">{{ old('address') }}</textarea>
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="gender" class="form-label">Giới tính</label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                <option value="">Chọn giới tính</option>
                                <option value="Nam" {{ old('gender') == 'Nam' ? 'selected' : '' }}>Nam</option>
                                <option value="Nữ" {{ old('gender') == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                <option value="Khác" {{ old('gender') == 'Khác' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trường học</label>
                            <input type="hidden" name="school_id" value="{{ Auth::user()->school->id }}">
                            <div class="form-control bg-light">
                                {{ Auth::user()->school->name }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="guardian_name" class="form-label">Tên phụ huynh</label>
                            <input type="text" class="form-control @error('guardian_name') is-invalid @enderror"
                                   id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}">
                            @error('guardian_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="guardian_email" class="form-label">Email phụ huynh</label>
                            <input type="email" class="form-control @error('guardian_email') is-invalid @enderror"
                                   id="guardian_email" name="guardian_email" value="{{ old('guardian_email') }}">
                            @error('guardian_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="guardian_phone" class="form-label">SĐT phụ huynh</label>
                            <input type="text" class="form-control @error('guardian_phone') is-invalid @enderror"
                                   id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}">
                            @error('guardian_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="{{ route('students.index') }}" class="btn btn-secondary me-2"
                       style="background-color: #ffffff; border-color: #E15336; color: #E15336 ;">Đóng</a>
                    <button type="submit" class="btn btn-primary "
                            style="background-color: #E15336; border-color: #E15336; color: #fff;">Lưu
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkbox = document.getElementById('is_active');
            const statusText = document.getElementById('statusText');
            statusText.textContent = checkbox.checked ? 'Hoạt động' : 'Ngừng';
        });

        function toggleStatusText(checkbox) {
            document.getElementById('statusText').textContent = checkbox.checked ? 'Hoạt động' : 'Ngừng';
        }

    </script>

@endsection

