@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <h3 class="mb-0 text-primary-color">Chi tiết tài liệu</h3>
        </div>

        <div class="card mb-4 border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold">{{ $document->title }}</h4>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-primary-subtle text-primary-color px-3 py-1 rounded-pill">
                            <i class="fas fa-bookmark me-1"></i>{{ $document->subject->name }}
                        </span>
                            <span class="text-muted small">
                            <i class="fas fa-user-tie me-1"></i>{{ $document->teacher->full_name }}
                        </span>
                            <span class="text-muted small">
                            <i class="fas fa-clock me-1"></i>{{ $document->created_at->format('d/m/Y H:i') }}
                        </span>
                        </div>
                    </div>
                    @if(Auth::user()->isStudent())
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary-color rounded-circle" type="button"
                                    data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="{{ route('documents.download', $document) }}">
                                        <i class="fas fa-download me-2 text-primary-color"></i>Tải xuống
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card-body p-0">
                @php
                    $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
                    $previewAvailable = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png']);
                @endphp

                @if($previewAvailable && Storage::exists($document->file_path))
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-file-alt me-2 text-primary-color"></i>Xem trước
                            </h5>
                            <span class="badge bg-primary-color text-white px-2 py-1 rounded-pill">
                            .{{ strtoupper($extension) }}
                        </span>
                        </div>

                        @if($extension === 'pdf')
                            <div class="ratio ratio-16x9 bg-light rounded-2">
                                <iframe src="{{ Storage::url($document->file_path) }}"
                                        style="border: none;"
                                        class="rounded-2"></iframe>
                            </div>
                        @else
                            <div class="text-center">
                                <img src="{{ Storage::url($document->file_path) }}"
                                     alt="Preview"
                                     class="img-fluid rounded-2 shadow-sm"
                                     style="max-height: 500px;">
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-file-alt fa-4x text-primary-subtle"></i>
                        </div>
                        <h5 class="mb-3">Không có bản xem trước</h5>
                        <p class="text-muted mb-4">Định dạng .{{ $extension }} không hỗ trợ xem trước</p>
                        <div class="mb-4">
                        <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                            <i class="fas fa-file me-1"></i>
                            Định dạng: .{{ strtoupper($extension) }}
                        </span>
                        </div>
                        @if(Auth::user()->isStudent())
                            <a href="{{ route('documents.download', $document) }}"
                               class="btn btn-primary-color px-4 py-2 rounded-pill">
                                <i class="fas fa-download me-2"></i>Tải xuống
                            </a>
                        @endif
                    </div>
                @endif

                @if($document->description)
                    <div class="p-4 border-bottom">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-align-left me-2 text-primary-color"></i>Mô tả
                        </h5>
                        <div class="p-3 bg-light rounded-3">
                            {!! nl2br(e($document->description)) !!}
                        </div>
                    </div>
                @endif

                <div class="p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0 pe-md-4 border-end border-primary-subtle"><h5
                                class="fw-bold mb-3">
                                <i class="fas fa-info-circle me-2 text-primary-color"></i>Thông tin tệp
                            </h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between border-0">
                                    <span class="text-muted">Ngày tải lên</span>
                                    <span class="fw-medium">{{ $document->created_at->format('d/m/Y') }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between border-0">
                                    <span class="text-muted">Cập nhật gần nhất</span>
                                    <span class="fw-medium">{{ $document->updated_at->diffForHumans() }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between border-0">
                                    <span class="text-muted">Định dạng</span>
                                    <span class="fw-medium text-uppercase">.{{ $extension }}</span>
                                </li>
                                @if(Storage::exists($document->file_path))
                                    <li class="list-group-item px-0 d-flex justify-content-between border-0">
                                        <span class="text-muted">Kích thước</span>
                                        <span class="fw-medium">
                                    {{ number_format(Storage::size($document->file_path) / 1024, 1) }} KB
                                </span>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-user-tie me-2 text-primary-color"></i>Giáo viên
                            </h5>
                            <div class="d-flex align-items-center mb-3">
                                <div
                                    class="avatar bg-primary-subtle text-primary-color rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 50px; height: 50px;">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $document->teacher->full_name }}</h6>
                                    <small class="text-muted">{{ $document->teacher->email }}</small>
                                </div>
                            </div>
                            <div class="bg-light p-3 rounded-3">
                                <div class="text-center">
                                    <div class="h4 fw-bold mb-1">{{ $document->teacher->documents()->count() }}</div>
                                    <small class="text-muted">Tài liệu đã chia sẻ</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <div class="d-flex gap-2">
                <a href="{{ route('documents.index') }}"
                   class="btn btn-outline-primary-color">
                    Đóng
                </a>
                @if(Auth::user()->isStudent())
                    <a href="{{ route('documents.download', $document) }}"
                       class="btn btn-primary-color">
                        <i class="fas fa-download me-2"></i>Tải xuống
                    </a>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('[data-bs-toggle="tooltip"]').tooltip();
            });
        </script>
    @endpush

@endsection
