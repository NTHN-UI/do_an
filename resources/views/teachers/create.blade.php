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

            <h3 class="mb-0 text-primary-color">Thêm mới giáo viên</h3>
        </div>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body">
                <form action="{{ route('teachers.store') }}" method="POST" id="teacher-form">
                    @csrf
                    <div class="mb-3 d-flex align-items-center">
                        <span class="me-2">Trạng thái</span>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label ms-1" for="is_active">
                                <span id="statusText">{{ old('is_active', true) ? 'Hoạt động' : 'Ngừng' }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="full_name"
                               class="form-control @error('full_name') is-invalid @enderror"
                               value="{{ old('full_name') }}" >
                        @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                            <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" id="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}" >
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    <div class="col-md-12">
                            <label for="gender" class="form-label">Giới tính <span class="text-danger">*</span></label>
                            <select name="gender" id="gender"
                                    class="form-select @error('gender') is-invalid @enderror" >
                                <option value="">-- Chọn --</option>
                                <option value="Nam" {{ old('gender') == 'Nam' ? 'selected' : '' }}>Nam</option>
                                <option value="Nữ" {{ old('gender') == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                <option value="Khác" {{ old('gender') == 'Khác' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label for="date_of_birth" class="form-label">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" id="date_of_birth"
                                   class="form-control @error('date_of_birth') is-invalid @enderror"
                                   value="{{ old('date_of_birth') }}">
                            @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    <div class="col-md-12">
                        <label for="subject_id" class="form-label">Môn học giảng dạy <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror">
                            <option value="">-- Chọn môn học --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')
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
                               value="{{ old('address') }}" >
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('teachers.index') }}" class="btn btn-outline-primary-color me-2">Đóng</a>
                        <button type="submit" class="btn btn-primary-color">
                            Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#is_active').on('change', function() {
                $('#statusText').text($(this).is(':checked') ? 'Hoạt động' : 'Ngừng');
            });

            const initialIsActive = $('#is_active').is(':checked');
            $('#statusText').text(initialIsActive ? 'Hoạt động' : 'Ngừng');
        });
    </script>
@endpush
