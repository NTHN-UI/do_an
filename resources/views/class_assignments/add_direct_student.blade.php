@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
                <h3 class="text-primary-color">Thêm học sinh mới vào {{ $class->name }}</h3>
        </div>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body">
                <form method="POST" action="{{ route('class_assignments.store_direct_student', $class->id) }}">                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">

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
                            <h5 class="text-primary-color mb-3">Thông tin học sinh</h5>

                            <div class="mb-3">
                                <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                       id="full_name" name="full_name" value="{{ old('full_name') }}" >
                                @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label">Ngày sinh <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                       id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" >
                                @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="gender" class="form-label">Giới tính <span class="text-danger">*</span></label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" >
                                    <option value="">-- Chọn giới tính --</option>
                                    <option value="Nam" {{ old('gender') == 'Nam' ? 'selected' : '' }}>Nam</option>
                                    <option value="Nữ" {{ old('gender') == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                    <option value="Khác" {{ old('gender') == 'Khác' ? 'selected' : '' }}>Khác</option>
                                </select>
                                @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        <div class="col-md-12">
                            <label class="form-label">Trường học</label>
                            <input type="hidden" name="school_id" value="{{ Auth::user()->school->id }}">
                            <div class="form-control bg-light">
                                {{ Auth::user()->school->name }}
                            </div>
                        </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Địa chỉ</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                       id="address" name="address" value="{{ old('address') }}">
                                @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                            <h5 class="text-primary-color mb-3">Thông tin phụ huynh</h5>

                            <div class="mb-3">
                                <label for="guardian_name" class="form-label">Tên phụ huynh</label>
                                <input type="text" class="form-control @error('guardian_name') is-invalid @enderror"
                                       id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}">
                                @error('guardian_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                    <div class="mb-3">
                        <label for="guardian_email" class="form-label">Email phụ huynh <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('guardian_email') is-invalid @enderror"
                               id="guardian_email" name="guardian_email" value="{{ old('guardian_email') }}" >
                        @error('guardian_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                            <div class="mb-3">
                                <label for="guardian_phone" class="form-label">Số điện thoại phụ huynh</label>
                                <input type="text" class="form-control @error('guardian_phone') is-invalid @enderror"
                                       id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}">
                                @error('guardian_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>




                    <div class="d-flex justify-content-end">
                        <a href="{{ route('class_assignments.show', $class->id) }}" class="btn btn-outline-primary-color me-2">Đóng</a>
                        <button type="submit" class="btn btn-primary-color">Lưu</button>
                    </div>
                </form>
            </div>
        </div>


@endsection
