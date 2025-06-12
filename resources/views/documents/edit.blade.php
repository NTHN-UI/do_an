@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0">
                        <h3 class="fw-bold mb-0">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa tài liệu
                        </h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('documents.update', $document) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="title" class="form-label">Tiêu đề</label>
                                <input type="text" class="form-control" id="title" name="title"
                                       value="{{ old('title', $document->title) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả</label>
                                <textarea class="form-control" id="description" name="description"
                                          rows="3">{{ old('description', $document->description) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="subject_id" class="form-label">Môn học</label>
                                <select class="form-select" id="subject_id" name="subject_id" required>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}"
                                            {{ $document->subject_id == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="file" class="form-label">File tài liệu (Để trống nếu không đổi)</label>
                                <div class="card bg-light p-3 mb-2">
                                    <i class="fas fa-file-alt me-2"></i>
                                    {{ basename($document->file_path) }}
                                </div>
                                <input type="file" class="form-control" id="file" name="file">
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('documents.index') }}"
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Hủy bỏ
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
