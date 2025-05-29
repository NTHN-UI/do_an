@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="main-card card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-primary-color text-white border-0 p-0">
                        <div class="header-content p-4 pb-0">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb p-0 mb-3">
                                    <li class="breadcrumb-item ">
                                        <a href="{{ route('documents.index') }}" class="text-white text-decoration-none fw-medium hover-link">
                                            <i class="fas fa-archive me-1"></i>Thư viện Tài liệu
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Chi tiết tài liệu</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="title-section p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h1 class="display-5 fw-bold mb-3 text-white lh-base">{{ $document->title }}</h1>

                                    <div class="document-meta d-flex flex-wrap align-items-center gap-3 mb-3">
                                    <span class="badge bg-primary-color text-white px-3 py-2 fw-semibold rounded-pill"> {{-- Changed bg-primary to bg-primary-color and ensured text-white --}}
                                        <i class="fas fa-bookmark me-1"></i>{{ $document->subject->name }}
                                    </span>
                                        <span class="text-white-75 fw-medium">
                                        <i class="fas fa-user-tie me-2"></i>{{ $document->teacher->full_name }}
                                    </span>
                                        <span class="text-white-75 fw-medium">
                                        <i class="fas fa-calendar-alt me-2"></i>{{ $document->created_at->format('d/m/Y H:i') }}
                                    </span>
                                    </div>
                                </div>

                                @if(Auth::user()->isStudent())
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-lg rounded-circle p-3 shadow-sm" type="button"
                                                id="dropdownMenuButton" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-h text-primary-color"></i> {{-- Changed text-primary to text-primary-color --}}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 py-2">
                                            <li>
                                                <a class="dropdown-item px-3 py-2 fw-medium" href="{{ route('documents.download', $document) }}">
                                                    <i class="fas fa-download text-primary-color me-2"></i>Tải xuống tài liệu {{-- Changed text-primary to text-primary-color --}}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="preview-section p-4 p-md-5 bg-light-subtle">
                            @php
                                $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
                                $previewAvailable = in_array($extension, ['pdf']);
                            @endphp

                            <div class="preview-container rounded-4 overflow-hidden shadow-sm bg-white">
                                @if($previewAvailable && Storage::exists($document->file_path))
                                    <div class="pdf-preview-header bg-gradient-light p-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0 fw-bold text-secondary">
                                                <i class="fas fa-file-pdf text-danger me-2"></i>Xem trước tài liệu
                                            </h5>
                                            <span class="badge bg-primary-subtle text-primary-color px-3 py-2 rounded-pill"> {{-- Changed text-primary to text-primary-color --}}
                                        PDF Document
                                    </span>
                                        </div>
                                    </div>
                                    <div class="pdf-viewer p-3">
                                        <iframe src="{{ Storage::url($document->file_path) }}"
                                                width="100%" height="600px"
                                                style="border: none;"
                                                class="rounded-3 shadow-sm">
                                        </iframe>
                                        <div class="text-center mt-3">
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Bạn đang xem trước tài liệu. Để xem đầy đủ, vui lòng tải xuống.
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <div class="no-preview text-center py-5">
                                        <div class="preview-icon mb-4">
                                            <div class="bg-primary-subtle text-primary-color rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" {{-- Changed text-primary to text-primary-color --}}
                                            style="width: 100px; height: 100px;">
                                                <i class="fas fa-file-alt fa-3x"></i>
                                            </div>
                                        </div>
                                        <h4 class="mb-3 fw-bold text-secondary">Không có bản xem trước</h4>
                                        <p class="text-muted mb-4 lead">Loại tệp này không hỗ trợ xem trước trực tiếp.</p>
                                        <div class="file-type-info mb-4">
                                    <span class="badge bg-secondary-subtle text-secondary px-4 py-3 rounded-pill fs-6">
                                        <i class="fas fa-file me-2"></i>
                                        Định dạng: {{ strtoupper($extension) }}
                                    </span>
                                        </div>
                                        @if(Auth::user()->isStudent())
                                            <a href="{{ route('documents.download', $document) }}"
                                               class="btn btn-primary-color btn-lg px-5 py-3 rounded-pill shadow-sm hover-lift"> {{-- Changed btn-primary to btn-primary-color --}}
                                                <i class="fas fa-download me-2"></i>Tải xuống tài liệu
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($document->description)
                            <div class="description-section p-4 p-md-5 border-bottom">
                                <div class="row">
                                    <div class="col-12">
                                        <h4 class="mb-4 fw-bold text-secondary">
                                            <i class="fas fa-align-left text-primary-color me-2"></i>Mô tả chi tiết {{-- Changed text-primary to text-primary-color --}}
                                        </h4>
                                        <div class="description-content p-4 bg-light-subtle rounded-4 border">
                                            <div class="text-secondary lh-lg">
                                                {!! nl2br(e($document->description)) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="info-section p-4 p-md-5">
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="info-card h-100 p-4 bg-white border rounded-4 shadow-sm">
                                        <h5 class="mb-4 fw-bold text-secondary">
                                            <i class="fas fa-info-circle text-primary-color me-2"></i>Thông tin chung {{-- Changed text-primary to text-primary-color --}}
                                        </h5>
                                        <div class="info-list">
                                            <div class="info-item d-flex align-items-center mb-3 pb-3 border-bottom">
                                                <div class="icon-wrapper bg-primary-subtle text-primary-color rounded-circle p-2 me-3"> {{-- Changed text-primary to text-primary-color --}}
                                                    <i class="fas fa-calendar-plus"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Ngày tải lên</small>
                                                    <span class="fw-semibold">{{ $document->created_at->format('d/m/Y H:i') }}</span>
                                                </div>
                                            </div>
                                            <div class="info-item d-flex align-items-center mb-3 pb-3 border-bottom">
                                                <div class="icon-wrapper bg-success-subtle text-success rounded-circle p-2 me-3">
                                                    <i class="fas fa-sync-alt"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Cập nhật gần nhất</small>
                                                    <span class="fw-semibold">{{ $document->updated_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                            <div class="info-item d-flex align-items-center mb-3 pb-3 border-bottom">
                                                <div class="icon-wrapper bg-warning-subtle text-warning rounded-circle p-2 me-3">
                                                    <i class="fas fa-file-code"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Định dạng tệp</small>
                                                    <span class="fw-semibold text-uppercase">.{{ $extension }}</span>
                                                </div>
                                            </div>
                                            @if(Storage::exists($document->file_path))
                                                <div class="info-item d-flex align-items-center">
                                                    <div class="icon-wrapper bg-info-subtle text-info rounded-circle p-2 me-3">
                                                        <i class="fas fa-hdd"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Kích thước tệp</small>
                                                        <span class="fw-semibold">{{ number_format(Storage::size($document->file_path) / 1024, 2) }} KB</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="teacher-card h-100 p-4 bg-white border rounded-4 shadow-sm">
                                        <h5 class="mb-4 fw-bold text-secondary">
                                            <i class="fas fa-user-graduate text-primary-color me-2"></i>Thông tin giáo viên {{-- Changed text-primary to text-primary-color --}}
                                        </h5>
                                        <div class="teacher-profile">
                                            <div class="d-flex align-items-center mb-4">
                                                <div class="teacher-avatar bg-primary-subtle text-primary-color rounded-circle d-flex align-items-center justify-content-center me-3" {{-- Changed text-primary to text-primary-color --}}
                                                style="width: 60px; height: 60px;">
                                                    <i class="fas fa-user-tie fa-2x"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-bold text-primary-color">{{ $document->teacher->full_name }}</h6> {{-- Changed text-primary to text-primary-color --}}
                                                    <p class="text-muted mb-0">{{ $document->teacher->email }}</p>
                                                </div>
                                            </div>
                                            <div class="teacher-stats">
                                                <div class="stat-item bg-light-subtle p-3 rounded-3 text-center">
                                                    <div class="stat-number h4 mb-1 fw-bold text-primary-color"> {{-- Changed text-primary to text-primary-color --}}
                                                        {{ $document->teacher->documents()->count() }}
                                                    </div>
                                                    <small class="text-muted">Tài liệu đã chia sẻ</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="action-section p-4 p-md-5 bg-light-subtle border-top">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <a href="{{ route('documents.index') }}"
                                   class="btn btn-outline-secondary btn-lg px-4 py-2 rounded-pill shadow-sm">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                                </a>

                                <div class="action-buttons d-flex flex-wrap gap-2">
                                    @if(Auth::user()->isTeacher() && Auth::user()->id === $document->teacher_id)
                                        <a href="{{ route('documents.edit', $document) }}"
                                           class="btn btn-warning btn-lg px-4 py-2 rounded-pill shadow-sm">
                                            <i class="fas fa-edit me-2"></i>Chỉnh sửa
                                        </a>
                                    @endif

                                    @if(Auth::user()->isStudent())
                                        <a href="{{ route('documents.download', $document) }}"
                                           class="btn btn-primary-color btn-lg px-4 py-2 rounded-pill shadow-sm"> {{-- Changed btn-primary to btn-primary-color --}}
                                            <i class="fas fa-download me-2"></i>Tải xuống
                                        </a>
                                    @endif

                                    <button type="button"
                                            class="btn btn-light btn-lg rounded-circle p-3 shadow-sm"
                                            title="Chia sẻ tài liệu"
                                            data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="fas fa-share-alt text-secondary"></i>
                                    </button>

                                    <button type="button"
                                            class="btn btn-light btn-lg rounded-circle p-3 shadow-sm"
                                            title="Lưu tài liệu"
                                            data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="fas fa-bookmark text-secondary"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Initialize tooltips
            document.addEventListener('DOMContentLoaded', function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });

            // Add smooth scrolling for better UX
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        </script>
    @endpush
@endsection
