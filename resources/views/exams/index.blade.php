@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Quản lý đề thi</h1>
            <div>
                <a href="{{ route('exams.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tạo đề thi mới
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="mb-0">Danh sách đề thi</h4>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" class="form-inline float-right">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đề thi</th>
                            <th>Môn học</th>
                            <th>Khối lớp</th>
                            <th>Loại đề</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($exams as $exam)
                            <tr>
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
                                        <span class="badge bg-primary-color">Bản nháp</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('exams.show', $exam->id) }}" class="btn btn-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('exams.edit', $exam->id) }}" class="btn btn-primary" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('exams.destroy', $exam->id) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa đề thi này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Không có đề thi nào</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
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
    </div>
@endsection
