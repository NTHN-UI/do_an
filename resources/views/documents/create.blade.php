@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">Tải lên tài liệu mới</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group mb-3">
                    <label for="title">Tiêu đề tài liệu <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                           id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="description">Mô tả</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="subject_id">Môn học <span class="text-danger">*</span></label>
                    <select class="form-control @error('subject_id') is-invalid @enderror"
                            id="subject_id" name="subject_id" required>
                        <option value="">-- Chọn môn học --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-4">
                    <label for="file">File tài liệu <span class="text-danger">*</span></label>
                    <input class="form-control @error('file') is-invalid @enderror"
                           type="file" id="file" name="file" required
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                    <small class="form-text text-muted">
                        Chấp nhận file: PDF, Word, PowerPoint, Excel (tối đa 10MB)
                    </small>
                    @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-primary-color me-2">Đóng</a>
                    <button type="submit" class="btn btn-primary-color">
                        Tải lên
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview file name script -->
    <script>
        document.getElementById('file').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Chưa chọn file';
            document.querySelector('.form-text').textContent = `File đã chọn: ${fileName}`;
        });
    </script>
@endsection
