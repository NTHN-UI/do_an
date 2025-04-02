@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Chỉnh Sửa Khối Học</h3>
                        <div class="card-tools">
                            <a href="{{ route('grade_levels.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('grade_levels.update', $gradeLevel->id) }}" id="gradeLevelForm">
                            @csrf
                            @method('PUT')

                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">Trường học</label>
                                <input type="hidden" name="school_id" value="{{ Auth::user()->school_id  }}">
                                <div class="col-md-6">
                                    <div class="form-control bg-light">
                                        {{ Auth::user()->school->name }}
                                        ({{ Auth::user()->school->education_level == 'primary' ? 'Tiểu học' :
                                          (Auth::user()->school->education_level == 'secondary' ? 'THCS' : 'THPT') }})
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="grade_number" class="col-md-4 col-form-label text-md-right">Khối học</label>
                                <div class="col-md-6">
                                    <input type="number" id="grade_number" name="grade_number"
                                           class="form-control @error('grade_number') is-invalid @enderror"
                                           value="{{ old('grade_number') }}"
                                           min="1" max="12"
                                           required>

                                    @error('grade_number')
                                    <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
                                    @enderror

                                    <small class="form-text text-muted">
                                        @if(old('school_id'))
                                            @php
                                                $school = $schools->firstWhere('id', old('school_id'));
                                                echo $school ? match($school->education_level) {
                                                    'primary' => 'Nhập khối từ 1 đến 5',
                                                    'secondary' => 'Nhập khối từ 6 đến 9',
                                                    'high' => 'Nhập khối từ 10 đến 12',
                                                } : 'Vui lòng chọn trường trước';
                                            @endphp
                                        @else
                                            Vui lòng chọn trường trước
                                        @endif
                                    </small>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập Nhật
                                    </button>
                                    <a href="{{ route('grade_levels.show', $gradeLevel->id) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Hủy
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('#school_id').change(function() {
                const schoolId = $(this).val();
                const gradeInput = $('#grade_number');
                const hintText = gradeInput.next('.form-text');

                if (!schoolId) {
                    gradeInput.attr('min', 1).attr('max', 12);
                    hintText.text('Vui lòng chọn trường trước');
                    return;
                }

                // Lấy thông tin trường từ danh sách đã load
                const school = @json($schools->keyBy('id'));
                const selectedSchool = school[schoolId];

                // Đặt giới hạn khối học theo cấp
                switch(selectedSchool.education_level) {
                    case 'primary':
                        gradeInput.attr('min', 1).attr('max', 5);
                        hintText.text('Nhập khối từ 1 đến 5');
                        break;
                    case 'secondary':
                        gradeInput.attr('min', 6).attr('max', 9);
                        hintText.text('Nhập khối từ 6 đến 9');
                        break;
                    case 'high':
                        gradeInput.attr('min', 10).attr('max', 12);
                        hintText.text('Nhập khối từ 10 đến 12');
                        break;
                }

                // Reset giá trị nếu vượt quá phạm vi mới
                const currentValue = parseInt(gradeInput.val());
                if (currentValue < gradeInput.attr('min') || currentValue > gradeInput.attr('max')) {
                    gradeInput.val('');
                }
            });

            // Tự động kích hoạt khi có lỗi validation
            @if(old('school_id'))
            $('#school_id').trigger('change');
            @endif
        });
    </script>
@endsection
