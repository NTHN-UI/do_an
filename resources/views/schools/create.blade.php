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
            <a href="{{ route('schools.index') }}" class="btn btn-sm btn-primary-color me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0 text-primary-color">Thêm mới trường học</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('schools.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Tên trường <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" >
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">Địa chỉ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                           id="address" name="address" value="{{ old('address') }}" >
                    @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="province">Tỉnh/Thành <span class="text-danger">*</span></label>
                    <select class="form-select @error('province') is-invalid @enderror"
                            id="province" name="province">
                        <option value="">-- Chọn tỉnh/thành --</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province['code'] }}" {{ old('province') == $province['code'] ? 'selected' : '' }}>
                                {{ $province['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('province')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="district">Quận/Huyện <span class="text-danger">*</span></label>
                    <select class="form-select @error('district') is-invalid @enderror"
                            id="district" name="district" disabled>
                        <option value="">-- Chọn quận/huyện --</option>
                    </select>
                    @error('district')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('schools.index') }}" class="btn btn-secondary me-2">Đóng</a>
                    <button type="submit" class="btn btn-primary-color">Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Phần script trong blade
        $(document).ready(function() {
            $('#province').change(function() {
                var provinceCode = $(this).val();
                console.log('Selected province code:', provinceCode);

                if (provinceCode) {
                    $('#district').prop('disabled', false);

                    $.ajax({
                        url: '/districts/' + provinceCode, // Đã sửa thành /districts/
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(data) {
                            console.log('Received districts data:', data);
                            $('#district').empty().append('<option value="">-- Chọn quận/huyện --</option>');

                            $.each(data, function(key, district) {
                                $('#district').append($('<option>', {
                                    value: district.code,
                                    text: district.name
                                }));
                            });

                            @if(old('district'))
                            $('#district').val('{{ old('district') }}');
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

            @if(old('province'))
            $('#province').val('{{ old('province') }}').trigger('change');
            @endif
        });
    </script>
@endpush
