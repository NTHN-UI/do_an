@extends('layouts.app')

@section('content')
    <style>
        .dropdown-menu {
            min-width: 80px;
        }

        .dropdown-item:active,
        .dropdown-item:focus {
            background-color: #013066 !important;
            color: white !important;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: var(--bs-white);
            border-color: var(--primary-color);
        }
    </style>
    <div class="container rounded-3 shadow p-4">
        <h3 class="mb-3 text-primary-color">Quản lý đề thi</h3>
        <div class="mb-3 d-flex justify-content-end align-items-center">
            <form method="GET" class="me-1">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                           placeholder="Tìm kiếm..."
                           value="{{ request('search') }}"
                           id="search-input-exam">
                    <button type="submit" class="btn btn-primary-color px-2">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <a href="{{ route('exams.create') }}" class="btn btn-primary-color ms-1">Tạo mới đề thi</a>
        </div>

        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th>ID</th>
                            <th>Tên đề thi</th>
                            <th>Môn học</th>
                            <th>Khối lớp</th>
                            <th>Loại đề</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4" style="width: 50px;"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($exams as $exam)
                            <tr class="text-center">
                                <td>{{ $exam->id }}</td>
                                <td>{{ Str::limit($exam->title, 30) }}</td>
                                <td>{{ $exam->subject->name }}</td>
                                <td>Khối {{ $exam->gradeLevel->grade_number }}</td>
                                <td>
                                    @if($exam->test_type === 'fifteen_minutes')
                                        15 phút
                                    @elseif($exam->test_type === 'one_period')
                                        1 tiết
                                    @endif
                                </td>
                                <td>
                                    @if($exam->is_published)
                                        <span class="badge bg-primary-color">Đã xuất bản</span>
                                    @else
                                        <span class="badge bg-secondary">Bản nháp</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('exams.show', $exam->id) }}">Xem</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('exams.edit', $exam->id) }}">Sửa</a>
                                            </li>
                                            @if($exam->is_published)
                                                <li>
                                                    <a href="{{ route('exam_assignments.create', ['exam' => $exam->id]) }}"
                                                       class="dropdown-item px-3 py-2">
                                                        Giao đề
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <form action="{{ route('exams.destroy', $exam->id) }}" method="POST"
                                                      onsubmit="return confirm('Bạn chắc chắn muốn xóa đề thi này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item px-3 py-2">
                                                        Xóa
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Không có đề thi nào</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($exams->lastPage() > 1)
                <div class="card-footer border-0 bg-transparent" id="pagination-container">
                    <nav aria-label="page navigation">
                        {{ $exams->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
@endsection
