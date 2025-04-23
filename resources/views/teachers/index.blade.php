@extends('layouts.app')

@section('content')
    <div class="container">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" style="border-left: 4px solid #dc3545;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2" style="font-size: 1.5rem;"></i>
                    <div>
                        {!! session('error') !!}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" style="border-left: 4px solid #28a745;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2" style="font-size: 1.5rem;"></i>
                    <div>
                        {!! session('success') !!}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        @endif
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quản lý Giáo viên</h2>
            <a href="{{ route('teachers.create') }}" class="btn" style="background-color:#013066; color:#ffffff">Thêm mới
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <form id="search-form" method="GET" action="{{ route('teachers.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Tìm kiếm giáo viên..."
                                   value="{{ request('search') }}"
                                   id="search-input">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search'))
                                <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Họ tên</th>
                            <th>Trường</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($teachers as $teacher)
                            <tr>
                                <td>{{ $teacher->full_name }}</td>
                                <td>{{ $teacher->school->name ?? 'N/A' }}</td>
                                <td>{{ $teacher->email }}</td>
                                <td>{{ $teacher->phone }}</td>
                                <td>
                                    @if($teacher->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-danger">Không hoạt động</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('teachers.show', $teacher) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('teachers.edit', $teacher) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('teacher_assignments.create', $teacher) }}"
                                           class="btn btn-sm btn-success" title="Phân công">
                                            <i class="fas fa-tasks"></i>
                                        </a>
                                        <form action="{{ route('teachers.destroy', $teacher) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Bạn chắc chắn muốn xóa giáo viên này?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $teachers->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Tìm kiếm real-time với debounce
            let timer;
            $('#search-input').on('keyup', function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    $('#search-form').submit();
                }, 500);
            });

            // Reset về trạng thái ban đầu khi xóa search
            @if(request('search'))
            $('.btn-outline-secondary').click(function() {
                $('#search-input').val('');
                $('#search-form').submit();
            });
            @endif
        });
    </script>
@endpush
