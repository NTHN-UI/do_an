<!-- resources/views/students/edit.blade.php -->
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
            <h4 class="mb-0"> Sửa thông tin Học sinh</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('students.update', $student->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3 d-flex align-items-center">
                    <span class="me-2">Trạng thái</span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               {{ old('is_active', $student->is_active) ? 'checked' : '' }}
                               onchange="toggleStatusText(this)">
                        <label class="form-check-label ms-1" for="is_active">
                            <span
                                id="statusText">{{ old('is_active', $student->is_active) ? 'Hoạt động' : 'Ngừng' }}</span>
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   value="{{ $student->full_name }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="hidden" name="email" value="{{ $student->email }}">

                            <div class="form-control bg-light">
                                {{ $student->email }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   value="{{ $student->phone }}">
                        </div>

                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                   value="{{ $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '' }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="gender" class="form-label">Giới tính</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Chọn giới tính</option>
                                <option value="Nam" {{ $student->gender == 'Nam' ? 'selected' : '' }}>Nam</option>
                                <option value="Nữ" {{ $student->gender == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                <option value="Khác" {{ $student->gender == 'Khác' ? 'selected' : '' }}>Khác</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trường học</label>
                            <input type="hidden" name="school_id" value="{{ Auth::user()->school->id }}">
                            <div class="form-control bg-light">
                                {{ Auth::user()->school->name }}
                            </div>
                        </div>

                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Địa chỉ</label>
                    <textarea class="form-control" id="address" name="address"
                              rows="3">{{ $student->address }}</textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('students.index', $student->id) }}" class="btn me-2"
                       style="background-color: #ffffff; border-color: #E15336; color: #E15336 ;">Đóng</a>

                    <button type="submit" class="btn"
                            style="background-color: #E15336; border-color: #E15336; color: #fff;">Cập nhật
                    </button>
                </div>

            </form>
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
