@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pb-0">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent p-0 mb-3">
                                <li class="breadcrumb-item"><a href="{{ route('materials.index') }}">Tài liệu</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h2 class="fw-bold mb-2">{{ $material->title }}</h2>
                                <div class="d-flex align-items-center text-muted mb-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill me-3">
                                        {{ $material->subject->name }}
                                    </span>
                                    <span><i class="fas fa-user-tie me-1"></i> {{ $material->teacher->full_name }}</span>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light rounded-circle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('materials.download', $material) }}">
                                            <i class="fas fa-download me-2"></i>Tải xuống
                                        </a></li>
                                    @auth
                                        @if(auth()->user()->id === $material->teacher_id || auth()->user()->role === 'admin')
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-warning" href="#">
                                                    <i class="fas fa-edit me-2"></i>Chỉnh sửa
                                                </a></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('delete-form').submit()">
                                                    <i class="fas fa-trash me-2"></i>Xóa
                                                </a></li>
                                            <form id="delete-form" action="{{ route('materials.destroy', $material) }}" method="POST" class="d-none">
                                                @csrf @method('DELETE')
                                            </form>
                                        @endif
                                    @endauth
                                </ul>
                            </div>
                        </div>

                        <!-- Thông tin file -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                                        <i class="fas fa-file-alt text-primary fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ pathinfo($material->file_path, PATHINFO_BASENAME) }}</h6>
                                        <p class="text-muted small mb-0">
                                            Định dạng: .{{ pathinfo($material->file_path, PATHINFO_EXTENSION) }} |
                                            Tải lên: {{ $material->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                    <a href="{{ route('materials.download', $material) }}"
                                       class="btn btn-primary rounded-pill px-3">
                                        <i class="fas fa-download me-1"></i> Tải xuống
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Mô tả -->
                        @if($material->description)
                            <div class="mb-4">
                                <h5 class="mb-3">Mô tả tài liệu</h5>
                                <div class="p-3 bg-light rounded">
                                    {!! nl2br(e($material->description)) !!}
                                </div>
                            </div>
                        @endif

                        <!-- Thông tin bổ sung -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">
                                            <i class="fas fa-info-circle me-2"></i>Thông tin
                                        </h6>
                                        <ul class="list-unstyled small">
                                            <li class="mb-2">
                                                <i class="fas fa-calendar-alt me-2 text-muted"></i>
                                                <strong>Ngày tải lên:</strong>
                                                {{ $material->created_at->format('d/m/Y') }}
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-user-edit me-2 text-muted"></i>
                                                <strong>Cập nhật lần cuối:</strong>
                                                {{ $material->updated_at->diffForHumans() }}
                                            </li>
                                            <li>
                                                <i class="fas fa-file-signature me-2 text-muted"></i>
                                                <strong>Định dạng:</strong>
                                                .{{ pathinfo($material->file_path, PATHINFO_EXTENSION) }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">
                                            <i class="fas fa-user-tie me-2"></i>Giáo viên
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $material->teacher->full_name }}</h6>
                                                <p class="text-muted small mb-0">{{ $material->teacher->email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-0 d-flex justify-content-between pt-0">
                        <a href="{{ route('materials.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                        </a>
                        <div class="btn-group">
                            <button type="button" class="btn btn-light rounded-pill">
                                <i class="fas fa-share-alt"></i>
                            </button>
                            <button type="button" class="btn btn-light rounded-pill">
                                <i class="fas fa-bookmark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
