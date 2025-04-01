@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="display-6 fw-bold text-primary">Tài liệu Tham khảo</h1>
                <p class="text-muted">Tổng hợp tài liệu học tập từ các môn học và giáo viên</p>
            </div>
            <a href="{{ route('materials.create') }}" class="btn btn-primary">
                <i class="fas fa-upload"></i> Tải lên tài liệu
            </a>
        </div>

        <!-- Bộ lọc tìm kiếm -->
        <div class="card shadow-sm mb-5 border-0">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('materials.index') }}">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0"
                                       placeholder="Tìm kiếm tài liệu..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="subject_id" class="form-select">
                                <option value="">Tất cả môn học</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="teacher_id" class="form-select">
                                <option value="">Tất cả giáo viên</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danh sách tài liệu dạng card -->
        <div class="row g-4">
            @forelse($materials as $material)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 hover-shadow transition-all">
                        <!-- Thêm dropdown menu ở góc phải card header -->
                        <div class="card-header bg-white border-0 pb-0 position-relative">
                            <div class="dropdown position-absolute top-0 end-0 mt-2 me-2">
                                <button class="btn btn-sm btn-light rounded-circle" type="button"
                                        id="dropdownMenuButton{{ $material->id }}" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul  class="dropdown-menu dropdown-menu-end"  style="min-width: 70px;"
                                    aria-labelledby="dropdownMenuButton{{ $material->id }}" >
                                    <!-- Chỉ hiển thị sửa/xóa trong dropdown -->
                                    <li>
                                        <a class="dropdown-item"
                                           href="{{ route('materials.edit', $material) }}">
                                            Sửa
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('materials.destroy', $material) }}"
                                              method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item"
                                                    onclick="return confirm('Bạn chắc chắn muốn xóa tài liệu này?')">
                                                Xóa
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>

                            <!-- Phần header giữ nguyên -->
                            <div class="d-flex justify-content-between align-items-center pt-2 pe-4">
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">
                        {{ $material->subject->name }}
                    </span>
                                <span class="text-muted small">{{ $material->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <!-- Phần body giữ nguyên -->
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">{{ $material->title }}</h5>
                            <p class="card-text text-muted mb-4">
                                @if($material->description)
                                    {{ Str::limit($material->description, 120) }}
                                @else
                                    <span class="fst-italic">Không có mô tả</span>
                                @endif
                            </p>
                            <div class="d-flex align-items-center text-muted small mb-3">
                                <i class="fas fa-user-tie me-2"></i>
                                <span>{{ $material->teacher->full_name }}</span>
                            </div>
                        </div>

                        <!-- Phần footer giữ nguyên các nút hiện có -->
                        <div class="card-footer bg-white border-0 pt-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('materials.download', $material) }}"
                                   class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="fas fa-download me-1"></i> Tải xuống
                                </a>
                                <a href="{{ route('materials.show', $material) }}"
                                   class="text-primary small">
                                    Xem chi tiết <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 text-center py-5">
                        <div class="card-body">
                            <img src="{{ asset('images/empty-docs.svg') }}" alt="No documents" style="height: 150px;" class="mb-4">
                            <h5 class="text-muted">Không tìm thấy tài liệu nào</h5>
                            <p class="text-muted">Hãy thử thay đổi tiêu chí tìm kiếm của bạn</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Phân trang -->
        <div class="d-flex justify-content-center mt-5">
            {{ $materials->links() }}
        </div>
    </div>
@endsection
