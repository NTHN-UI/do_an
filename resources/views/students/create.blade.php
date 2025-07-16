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
            <h3 class="mb-0 text-primary-color">Thêm mới Học sinh</h3>
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
                               id="full_name" name="full_name" value="{{ old('full_name') }}">
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
                        <label for="date_of_birth" class="form-label">Ngày sinh <span
                                class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                               id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                        @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">

                        <label for="gender" class="form-label">Giới tính <span class="text-danger">*</span></label>
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
                        @if($currentAcademicYear)
                            <input type="hidden" name="academic_year_id" value="{{ $currentAcademicYear->id }}">
                            <div class="form-control bg-light">
                                {{ $currentAcademicYear->year }}
                            </div>
                        @else
                            <select class="form-select @error('academic_year_id') is-invalid @enderror"
                                    id="academic_year_id" name="academic_year_id" >
                                <option value="">-- Chọn năm học --</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_year_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Khối học</label>
                        <input type="hidden" name="grade_level_id" value="{{ $grade10->id }}">
                        <div class="form-control bg-light">
                            Khối 10
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label for="entry_score" class="form-label">Điểm đầu vào <span class="text-danger">*</span></label>
                        <input type="number" step="0.1"
                               class="form-control @error('entry_score') is-invalid @enderror"
                               id="entry_score" name="entry_score"
                               value="{{ old('entry_score') }}" min="0" max="50" >
                        @error('entry_score')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="exam_block" class="form-label">Khối đăng ký <span class="text-danger">*</span></label>
                        <select name="exam_block" class="form-select" id="exam_block" >
                            <option value="">-- Chọn khối thi --</option>
                            <option value="A" {{ old('exam_block') == 'A' ? 'selected' : '' }}>Khối A</option>
                            <option value="A1" {{ old('exam_block') == 'A1' ? 'selected' : '' }}>Khối A1</option>
                            <option value="B" {{ old('exam_block') == 'B' ? 'selected' : '' }}>Khối B</option>
                            <option value="C" {{ old('exam_block') == 'C' ? 'selected' : '' }}>Khối C</option>
                            <option value="D" {{ old('exam_block') == 'D' ? 'selected' : '' }}>Khối D</option>
                        </select>
                        @error('exam_block')
                        <div class="text-danger">{{ $message }}</div>
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
                        <input type="text" class="form-control @error('guardian_name') is-invalid @enderror"
                               id="guardian_name" name="guardian_name"
                               value="{{ old('guardian_name') }}">
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="guardian_email" class="form-label">Email phụ huynh <span
                                class="text-danger">*</span></label>
                        <input type="email" class="form-control  @error('guardian_email') is-invalid @enderror"
                               id="guardian_email" name="guardian_email"
                               value="{{ old('guardian_email') }}">
                        @error('guardian_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="guardian_phone" class="form-label">Số điện thoại phụ huynh</label>
                        <input type="text" class="form-control  @error('guardian_phone') is-invalid @enderror"
                               id="guardian_phone" name="guardian_phone"
                               value="{{ old('guardian_phone') }}">
                        @error('guardian_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
@push('scripts')
    <script>
        $(document).ready(function () {
            $('#grade_level_id').change(function () {
                const gradeId = $(this).val();
                const entryScoreField = $('#entry_score_field');

                if (!gradeId) {
                    entryScoreField.hide();
                    return;
                }

                const isGrade10 = $(this).find('option:selected').text().includes('10');
                $('#entry_score_field').toggle(isGrade10);

                if (isGrade10) {
                    entryScoreField.show();
                    $('#entry_score').prop('required', true);
                } else {
                    entryScoreField.hide();
                    $('#entry_score').prop('required', false).val('');
                }
            });

            @if(old('grade_level_id'))
            $('#grade_level_id').val('{{ old('grade_level_id') }}').trigger('change');
            @endif
        });
    </script>
@endpush
