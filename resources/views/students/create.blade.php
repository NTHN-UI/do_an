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
                            <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                   id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                            @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Đã ẩn trường email -->
                        <input type="hidden" name="email" value="">

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

                        <!-- Thêm trường chọn năm học -->
                        <div class="mb-3">
                            <label for="academic_year_id" class="form-label">Năm học <span class="text-danger">*</span></label>
                            <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                <option value="">-- Chọn năm học --</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}"
                                        {{ old('academic_year_id', $selectedAcademicYearId ?? null) == $year->id ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_year_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Thêm trường chọn khối học -->
                        <div class="mb-3">
                            <label for="grade_level_id" class="form-label">Khối học <span class="text-danger">*</span></label>
                            <select class="form-select @error('grade_level_id') is-invalid @enderror" id="grade_level_id" name="grade_level_id" required>
                                <option value="">-- Chọn khối --</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}"
                                        {{ old('grade_level_id', $selectedGradeLevelId ?? null) == $grade->id ? 'selected' : '' }}>
                                        Khối {{ $grade->grade_number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grade_level_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Đã ẩn trường password và set giá trị mặc định -->
                        <input type="hidden" name="password" value="12345678">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Địa chỉ</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address"
                              name="address" rows="3">{{ old('address') }}</textarea>
                    @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
