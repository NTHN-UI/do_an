@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <h3 class="mb-0">Chỉnh sửa tài liệu</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('documents.update', $document) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group mt-2">
                    <label for="title" class="mb-2">Tiêu đề <span class="text-danger">*</span></label>
                    <input id="title" type="text" name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $document->title) }}">
                    @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="description" class="mb-2">Mô tả</label>
                    <textarea id="description" name="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="3">{{ old('description', $document->description) }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="subject_id" class="mb-2">Môn học <span class="text-danger">*</span></label>
                    <select id="subject_id" name="subject_id"
                            class="form-select @error('subject_id') is-invalid @enderror" required>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}"
                                {{ $document->subject_id == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="file" class="mb-2">File tài liệu</label>
                    <div class="card bg-light p-3 mb-2">
                        <i class="fas fa-file-alt me-2"></i>
                        {{ basename($document->file_path) }}
                    </div>
                    <input id="file" type="file" name="file"
                           class="form-control @error('file') is-invalid @enderror">
                    <small class="text-muted">Để trống nếu không muốn thay đổi file</small>
                    @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end align-items-center mt-3">
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-primary-color me-2">
                        Đóng
                    </a>
                    <button type="submit" class="btn btn-primary-color">Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
