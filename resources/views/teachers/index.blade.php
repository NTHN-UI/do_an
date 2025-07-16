@extends('layouts.app')

@section('content')
    <style>
        .table-responsive .dropdown-menu {
            position: fixed !important;
            z-index: 1000 ;
            min-width: 90px;
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
        <h3 class="mb-3 text-primary-color">Quản lý giáo viên</h3>
        <div class="mb-3 d-flex justify-content-end align-items-center">
            <form id="search-form" method="GET" action="{{ route('teachers.index') }}" class="me-1">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                           placeholder="Tìm kiếm giáo viên..."
                           value="{{ request('search') }}"
                           id="search-input">
                    <button type="submit" class="btn btn-primary-color px-2">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <a href="{{ route('teachers.create') }}" class="btn btn-primary-color ms-1">Thêm mới</a>
        </div>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary">
                        <tr>
                            <th class=" text-center ps-4">STT</th>
                            <th>Họ tên</th>
                            <th>Trường</th>
                            <th>Email</th>
                            <th class="text-center">Điện thoại</th>
                            <th class="text-center">Môn dạy</th>
                            <th class="text-center">Trạng thái</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($teachers as $index => $teacher)
                            <tr>
                                <td class="text-center ps-4">{{ $index + 1 }}</td>
                                <td>{{ $teacher->full_name }}</td>
                                <td>{{ $teacher->school->name ?? 'N/A' }}</td>
                                <td>{{ $teacher->email }}</td>
                                <td class="text-center">{{ $teacher->phone }}</td>
                                <td class="text-center">{{ $teacher->subject->name ?? "Chưa được phân công" }}</td>
                                <td class="text-center">
                                    @if($teacher->is_active)
                                        <span class="badge bg-primary-color">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Ngừng</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false"  data-bs-container="body">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('teachers.show', $teacher) }}">Xem</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('teachers.edit', $teacher) }}">
                                                    Sửa
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('teacher_assignments.create', $teacher) }}"
                                                   title="Phân công">
                                                    Phân công
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($teachers->lastPage() > 1)
                <div class="card-footer border-0 bg-transparent" id="pagination-container">
                    <nav aria-label="page navigation">
                        {{ $teachers->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let timer;
            $('#search-input').on('keyup', function () {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    $('#search-form').submit();
                }, 500);
            });

            @if(request('search'))
            $('.btn-outline-secondary').click(function () {
                $('#search-input').val('');
                $('#search-form').submit();
            });
            @endif
        });
    </script>
@endpush
