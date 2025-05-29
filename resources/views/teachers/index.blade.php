@extends('layouts.app')

@section('content')
    <style>
        .container {
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow-x: auto;
        }

        .card {
            border-radius: 0.5rem;
        }

        .card-body {
            position: relative;
            overflow: visible !important;
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.08);
            transition: background-color 0.3s ease-in-out;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .table-responsive .dropdown-menu {
            position: fixed !important;
            z-index: 1000 !important;
            min-width: 90px;
        }

        .table-responsive .show > .dropdown-menu {
            display: block !important;
        }

        th {
            font-weight: 500;
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
    <div class="container">
        <h3 class="mb-3 text-primary-color">Quản lý giáo viên</h3>
        <div class="mb-3 d-flex justify-content-end align-items-center">
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
            <a href="{{ route('teachers.create') }}" class="btn btn-primary-color ms-2">Thêm mới
            </a>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th>Họ tên</th>
                            <th>Trường</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Môn dạy</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4" style="width: 50px;"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($teachers as $teacher)
                            <tr class="text-center">
                                <td>{{ $teacher->full_name }}</td>
                                <td>{{ $teacher->school->name ?? 'N/A' }}</td>
                                <td>{{ $teacher->email }}</td>
                                <td>{{ $teacher->phone }}</td>
                                <td>{{ $teacher->subject->name ?? "Chưa được phân công" }}</td>
                                <td></td>
                                <td>
                                    @if($teacher->is_active)
                                        <span class="badge bg-primary-color">Hoạt động</span>
                                    @else
                                        <span class="badge bg-primary-color">Không hoạt động</span>
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
                                                   href="{{ route('teachers.show', $teacher) }}" >Xem
                                                </a>
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
                        @endforeach
                        </tbody>
                    </table>
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
