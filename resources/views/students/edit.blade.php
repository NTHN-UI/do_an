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
            <h3 class="mb-0 text-primary-color">Chỉnh sửa thông tin học sinh</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('students.update', $student->id) }}" method="POST" id="edit-student-form">
                @csrf
                @method('PUT')
                <div class="col-md-12 d-flex align-items-center mb-3">
                    <span class="me-2">Trạng thái</span>
                    <div class="form-check form-switch">
                        <input class="form-check-input " type="checkbox" id="is_active" name="is_active"
                               {{ old('is_active', $student->is_active) ? 'checked' : '' }}
                               onchange="toggleStatusText(this)">
                        <label class="form-check-label ms-1" for="is_active">
                            <span id="statusText">{{ old('is_active', $student->is_active) ? 'Hoạt động' : 'Ngừng' }}</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" id="full_name"
                           class="form-control @error('full_name') is-invalid @enderror"
                           value="{{ old('full_name', $student->full_name) }}" required>
                    @error('full_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="hidden" name="email" value="{{ $student->email }}">
                    <div class="form-control bg-light">
                        {{ $student->email }}
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="text" name="phone" id="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $student->phone) }}" required>
                    @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label for="gender" class="form-label">Giới tính <span class="text-danger">*</span></label>
                    <select name="gender" id="gender"
                            class="form-select @error('gender') is-invalid @enderror" required>
                        <option value="">-- Chọn --</option>
                        <option value="Nam" {{ old('gender', $student->gender) == 'Nam' ? 'selected' : '' }}>Nam</option>
                        <option value="Nữ" {{ old('gender', $student->gender) == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                        <option value="Khác" {{ old('gender', $student->gender) == 'Khác' ? 'selected' : '' }}>Khác</option>
                    </select>
                    @error('gender')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label for="date_of_birth" class="form-label">Ngày sinh <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_birth" id="date_of_birth"
                           class="form-control @error('date_of_birth') is-invalid @enderror"
                           value="{{ old('date_of_birth', $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '') }}" required>
                    @error('date_of_birth')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Trường</label>
                    <input type="hidden" name="school_id" value="{{ Auth::user()->school->id }}">
                    <div class="form-control bg-light">
                        {{ Auth::user()->school->name }}
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="address" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                    <input type="text" name="address" id="address"
                           class="form-control @error('address') is-invalid @enderror"
                           value="{{ old('address', $student->address) }}" required>
                    @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label for="entry_score" class="form-label">Điểm đầu vào</label>
                    <input type="hidden" name="entry_score" value="{{ $student->entry_score }}">
                    <div class="form-control bg-light">
                        {{ $student->entry_score }}
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <h5 class="text-primary-color">Thông tin phụ huynh</h5>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="guardian_name" class="form-label">Tên phụ huynh</label>
                    <input type="text" class="form-control @error('guardian_name') is-invalid @enderror"
                           id="guardian_name" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}">
                    @error('guardian_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label for="guardian_email" class="form-label">Email phụ huynh</label>
                    <input type="email" class="form-control @error('guardian_email') is-invalid @enderror"
                           id="guardian_email" name="guardian_email" value="{{ old('guardian_email', $student->guardian_email) }}">
                    @error('guardian_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label for="guardian_phone" class="form-label">Số điện thoại phụ huynh</label>
                    <input type="text" class="form-control @error('guardian_phone') is-invalid @enderror"
                           id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone) }}">
                    @error('guardian_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex justify-content-end align-items-center mt-3">
                    <a href="{{ route('students.index') }}" class="btn btn-outline-primary-color me-2">
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
