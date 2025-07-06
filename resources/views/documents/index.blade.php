@extends('layouts.app')

@section('content')
    <div class="container-fluid rounded-3 shadow p-4">
        <div class="d-flex align-items-center justify-content-between mb-4 p-3">
            <div class="d-flex align-items-center ms-2">
                <div>
                    <h3 class="mb-0 text-primary-color fw-bold">Tài liệu học tập</h3>
                    <small class="text-muted d-block">Quản lý tài liệu từ các môn học, dễ dàng tìm kiếm và tải về.</small>
                    <span class="badge bg-primary-color text-white px-3 py-2 rounded-pill mt-2">
                    {{ $documents->total() ?? 0 }} tài liệu
                </span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                @if(Auth::user()->isTeacher())
                    <a href="{{ route('documents.create') }}" class="btn btn-primary-color text-white px-3 py-2 rounded-pill shadow-sm hover-lift">
                       Tải lên tài liệu
                    </a>
                @endif
            </div>
        </div>

        <div class="card mb-3 ">
            <div class="card-body">
                <form method="GET" action="{{ route('documents.index') }}" id="searchForm">
                    <div class="row g-4 align-items-end">
                        <div class="col-md-3">
                            <div class="input-group">
                            <span class="input-group-text bg-white border-primary-subtle rounded-start-3">
                                <i class="fas fa-search text-primary-color opacity-75"></i>
                            </span>
                                <input type="text" name="search" id="search"
                                       class="form-control border-primary-subtle rounded-end-3 shadow-sm"
                                       placeholder="Tìm theo tên tài liệu..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <select name="subject_id" id="subject_id" class="form-select border-primary-subtle shadow-sm rounded-3">
                                <option value="">--Tất cả môn học--</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if(Auth::user()->isStudent())
                            <div class="col-md-3">
                                <label for="teacher_id" class="form-label fw-semibold text-secondary">Giáo viên</label>
                                <select name="teacher_id" id="teacher_id" class="form-select border-primary-subtle shadow-sm rounded-3">
                                    <option value="">--Tất cả giáo viên--</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="text-muted fw-semibold me-2">Lọc nhanh:</span>
                                <a href="{{ route('documents.index') }}"
                                   class="badge bg-light text-dark text-decoration-none px-3 py-2 rounded-pill hover-shadow">
                                    <i class="fas fa-times me-1"></i>Xóa bộ lọc
                                </a>
                                @foreach($subjects->take(3) as $subject)
                                    <a href="{{ route('documents.index', ['subject_id' => $subject->id]) }}"
                                       class="badge bg-primary-subtle text-primary-color text-decoration-none px-3 py-2 rounded-pill hover-shadow">
                                        {{ $subject->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(request()->hasAny(['search', 'subject_id', 'teacher_id']))
            <div class="results-info mb-4">
                <div class="alert alert-info border-0 rounded-3 bg-info-subtle shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-3 fs-5 text-primary-color"></i>
                        <div>
                            <strong>Kết quả tìm kiếm:</strong> Tìm thấy {{ $documents->total() }} tài liệu
                            @if(request('search'))
                                cho từ khóa "<strong class="text-primary-color">{{ request('search') }}</strong>"
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="documents-container">
            <div class="row g-4">
                @forelse($documents as $document)
                    <div class="col-md-6 col-lg-4">
                        <div class="document-card card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift-card">
                            <div class="card-header bg-white border-0 pb-0 position-relative">
                                <div class="dropdown position-absolute top-0 end-0 mt-3 me-3 z-3">
                                    @if(Auth::user()->isTeacher())
                                        <button class="btn btn-sm mt-2" type="button"
                                                id="dropdownMenuButton{{ $document->id }}" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 py-2">
                                            <li>
                                                <a class="dropdown-item px-3 py-2 rounded-2" href="{{ route('documents.edit', $document) }}">
                                                    Sửa
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item px-3 py-2 rounded-2 delete-btn"
                                                   href="#"
                                                   data-id="{{ $document->id }}"
                                                   data-title="{{ $document->title }}"
                                                   data-url="{{ route('documents.destroy', $document) }}">Xóa
                                                </a>
                                            </li>
                                        </ul>
                                    @endif
                                </div>

                                <div class="card-header-content pt-3 pe-5">
                                    <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary-color rounded-pill px-3 py-2 fw-semibold">
                                        <i class="fas fa-bookmark me-1"></i>{{ $document->subject->name }}
                                    </span>
                                        <span class="text-muted small fw-medium">
                                        <i class="far fa-clock me-1"></i>{{ $document->created_at->diffForHumans() }}
                                    </span>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body d-flex flex-column p-4">
                                <h5 class="card-title fw-bold mb-3 text-primary-color lh-base">{{ $document->title }}</h5>
                                <p class="card-text text-muted mb-4 flex-grow-1 lh-relaxed">
                                    @if($document->description)
                                        {{ Str::limit($document->description, 120, '...') }}
                                    @else
                                        <span class="fst-italic text-black-50">
                                        <i class="fas fa-info-circle me-1"></i>Không có mô tả chi tiết.
                                    </span>
                                    @endif
                                </p>
                                <div class="teacher-info d-flex align-items-center p-3 bg-light rounded-3 mb-3">
                                    <div class="teacher-avatar bg-primary bg-opacity-10 text-primary-color rounded-circle d-flex align-items-center justify-content-center me-3"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $document->teacher->full_name }}</div>
                                        <div class="text-muted small">Giáo viên</div>
                                    </div>
                                </div>
                                <div class="file-info d-flex align-items-center justify-content-between text-muted small mb-3 p-2 bg-primary-subtle rounded-2">
                                <span>
                                    <i class="fas fa-file-alt me-1"></i>
                                    {{ strtoupper(pathinfo($document->file_path, PATHINFO_EXTENSION)) }}
                                </span>
                                    <span class="fw-medium">Tệp đính kèm</span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-4 px-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    @if(Auth::user()->isStudent())
                                        <a href="{{ route('documents.download', $document) }}"
                                           class="btn btn-primary-color rounded-pill px-4 py-2 fw-semibold hover-lift">
                                            <i class="fas fa-download me-2"></i>Tải xuống
                                        </a>
                                    @else
                                        <div></div>
                                    @endif
                                    <a href="{{ route('documents.show', $document) }}"
                                       class="btn btn-outline-primary-color rounded-pill px-4 py-2 fw-semibold hover-lift">
                                        Xem chi tiết <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state text-center py-5">
                            <div class="empty-illustration mb-4">
                                <div class="bg-light border rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm"
                                     style="width: 120px; height: 120px;">
                                    <i class="fas fa-search text-muted opacity-50" style="font-size: 3rem;"></i>
                                </div>
                            </div>
                            <h4 class="text-muted fw-bold mb-3">Không tìm thấy tài liệu nào!</h4>
                            <p class="text-muted mb-4">Hãy thử thay đổi tiêu chí tìm kiếm của bạn hoặc quay lại sau.</p>
                            <div class="d-flex gap-3 justify-content-center">
                                <a href="{{ route('documents.index') }}" class="btn btn-primary-color rounded-pill px-4 hover-lift">
                                    <i class="fas fa-refresh me-2"></i>Xóa bộ lọc
                                </a>
                                @if(Auth::user()->isTeacher())
                                    <a href="{{ route('documents.create') }}" class="btn btn-outline-primary-color rounded-pill px-4 hover-lift">
                                        <i class="fas fa-plus me-2"></i>Thêm tài liệu mới
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        @if($documents->hasPages())
            <div class="pagination-wrapper d-flex justify-content-center mt-5">
                <div class="custom-pagination">
                    {{ $documents->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa tài liệu "<span id="documentTitle"></span>" không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-color" data-bs-dismiss="modal">Đóng</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-primary-color">
                            Xác nhận xóa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#searchForm select').change(function() {
                $('#searchForm').submit();
            });

            var searchTimeout;
            $('input[name="search"]').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if ($(this).val().length >= 3 || $(this).val().length === 0) {
                        $('#searchForm').submit();
                    }
                }, 800);
            });

            $('.delete-btn').click(function(e) {
                e.preventDefault();
                var documentId = $(this).data('id');
                var documentTitle = $(this).data('title');
                var deleteUrl = $(this).data('url');

                $('#documentTitle').text(documentTitle);
                $('#deleteForm').attr('action', deleteUrl);
                $('#deleteModal').modal('show');
            });

            $('.document-card').each(function(index) {
                $(this).css('animation-delay', index * 0.1 + 's');
            });
        });
    </script>
@endpush

