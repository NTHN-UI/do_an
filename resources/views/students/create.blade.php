@extends('layouts.app')

@section('content')
    <style>
        .form-check-input:checked {
            background-color: #013066;
            border-color: #013066;
        }
    </style>
    <div class="container rounded-3 shadow p-4" style="background: #fff;">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('students.index') }}" class="btn btn-back me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0 text-primary-color">Thêm mới Học sinh</h4>
        </div>
        <div class="card border-0 shadow-sm rounded-2">
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
                        <div class="col-md-12">
                                <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                       id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                                @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <input type="hidden" name="email" value="">
                    <div class="col-md-12">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                       name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                    <div class="col-md-12">
                                <label for="date_of_birth" class="form-label">Ngày sinh</label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                       id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                    <div class="col-md-12">

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
                    <div class="col-md-12">
                        <label class="form-label">Trường học</label>
                        <input type="hidden" name="school_id" value="{{ Auth::user()->school->id }}">
                        <div class="form-control bg-light">
                            {{ Auth::user()->school->name }}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label for="academic_year_id" class="form-label">Năm học <span class="text-danger">*</span></label>
                        <select class="form-select @error('academic_year_id') is-invalid @enderror"
                                id="academic_year_id" name="academic_year_id" required>
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
                    <div class="col-md-12">
                        <label for="grade_level_id" class="form-label">Khối học <span class="text-danger">*</span></label>
                        <select class="form-select @error('grade_level_id') is-invalid @enderror"
                                id="grade_level_id" name="grade_level_id" required>
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
                    <div class="col-md-12">
                        <label for="entry_score" class="form-label">Điểm đầu vào</label>
                        <input type="number" step="0.01"
                               class="form-control @error('entry_score') is-invalid @enderror"
                               id="entry_score" name="entry_score" value="{{ old('entry_score') }}">
                        @error('entry_score')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <input type="hidden" name="password" value="12345678">
                    <div class="col-md-12">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror"
                               id="address" name="address" value="{{ old('address') }}">
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                            <h5 class="text-primary-color mt-2">Thông tin phụ huynh</h5>
                    <div class="col-md-12">
                                <label for="guardian_name" class="form-label">Tên phụ huynh</label>
                                <input type="text" class="form-control" id="guardian_name" name="guardian_name"
                                       value="{{ old('guardian_name') }}">
                            </div>
                    <div class="col-md-12">
                                <label for="guardian_email" class="form-label">Email phụ huynh</label>
                                <input type="email" class="form-control" id="guardian_email" name="guardian_email"
                                       value="{{ old('guardian_email') }}">
                            </div>
                    <div class="col-md-12">
                                <label for="guardian_phone" class="form-label">Số điện thoại phụ huynh</label>
                                <input type="text" class="form-control" id="guardian_phone" name="guardian_phone"
                                       value="{{ old('guardian_phone') }}">
                            </div>

                    <div class="d-flex justify-content-end mt-2">
                        <a href="{{ route('students.index') }}" class="btn btn-outline-primary-color me-2">Đóng</a>
                        <button type="submit" class="btn btn-primary-color">Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const gradeLevelSelect = document.querySelector('select[name="grade_level_id"]');
            const entryScoreField = document.getElementById('entry_score');

            if (!gradeLevelSelect || !entryScoreField) return;

            // Hàm xử lý hiển thị/ẩn trường điểm
            const toggleEntryScoreField = () => {
                const isGrade10 = gradeLevelSelect.value === 10;
                const formGroup = entryScoreField.closest('.form-group');

                if (formGroup) {
                    formGroup.style.display = isGrade10 ? 'block' : 'none';
                    entryScoreField.required = isGrade10;

                    // Reset giá trị khi ẩn đi
                    if (!isGrade10) entryScoreField.value = '';
                }
            };

            // Lắng nghe sự kiện thay đổi
            gradeLevelSelect.addEventListener('change', toggleEntryScoreField);

            // Khởi tạo trạng thái ban đầu
            toggleEntryScoreField();
        });
    </script>
@endpush
