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
    </style>
    <div class="container">
        <div class="d-flex align-items-center mb-4">
            <h3 class="mb-0 text-primary-color">Sửa thông tin trường học</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('schools.update', $school->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mt-2">
                    <label for="name">Tên trường <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $school->name) }}">
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="address">Địa chỉ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                           id="address" name="address" value="{{ old('address', $school->address) }}">
                    @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="province">Tỉnh/Thành <span class="text-danger">*</span></label>
                    <select class="form-select @error('province') is-invalid @enderror"
                            id="province" name="province">
                        <option value="">-- Chọn tỉnh/thành --</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province['code'] }}"
                                {{ old('province', $school->province_code) == $province['code'] ? 'selected' : '' }}>
                                {{ $province['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('province')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="district">Quận/Huyện <span class="text-danger">*</span></label>
                    <select class="form-select @error('district') is-invalid @enderror"
                            id="district" name="district" {{ !$school->province_code ? 'disabled' : '' }}>
                        <option value="">-- Chọn quận/huyện --</option>
                        @if($school->province_code)
                            @foreach($districts as $district)
                                <option value="{{ $district['code'] }}"
                                    {{ old('district', $school->district_code) == $district['code'] ? 'selected' : '' }}>
                                    {{ $district['name'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('district')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('schools.index') }}" class="btn btn-outline-primary-color me-2">Đóng</a>
                    <button type="submit" class="btn btn-primary-color">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#province').change(function() {
                var provinceCode = $(this).val();

                if (provinceCode) {
                    $('#district').prop('disabled', false);

                    $.ajax({
                        url: '/districts/' + provinceCode,
                        type: 'GET',
                        success: function(data) {
                            $('#district').empty().append('<option value="">-- Chọn quận/huyện --</option>');

                            $.each(data, function(key, district) {
                                $('#district').append($('<option>', {
                                    value: district.code,
                                    text: district.name
                                }));
                            });

                            // Set lại giá trị đã chọn nếu có
                            @if(old('district', $school->district_code))
                            $('#district').val('{{ old('district', $school->district_code) }}');
                            @endif
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr.responseText);
                            $('#district').empty().append('<option value="">-- Lỗi tải dữ liệu --</option>');
                        }
                    });
                } else {
                    $('#district').prop('disabled', true)
                        .empty()
                        .append('<option value="">-- Chọn quận/huyện --</option>');
                }
            });

            @if(old('province', $school->province_code))
            $('#province').val('{{ old('province', $school->province_code) }}').trigger('change');
            @endif
        });
    </script>
@endpush
