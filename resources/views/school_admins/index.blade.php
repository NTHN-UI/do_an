@extends('layouts.app')

@section('content')
    <style>
        .dropdown-menu {
            min-width: 80px;
            z-index: 1050;
        }

        .dropdown-item:active,
        .dropdown-item:focus {
            background-color: #013066;
            color: white;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: var(--bs-white);
            border-color: var(--primary-color);
        }
    </style>
    <div class="container  rounded-3 shadow p-4">
        <h3 class="mb-3 text-primary-color">Danh sách Admin trường</h3>
        <div class="d-flex justify-content-end align-items-center mb-3">
            <form method="GET" action="{{ route('school_admins.index') }}" class="d-flex me-2" id="search-form">
                <div class="input-group">
                    <input type="text" name="search" id="search-input" class="form-control" style="min-width: 300px"
                           placeholder="Tìm theo tên, email hoặc tên trường..."
                           value="{{ request('search') }}"
                           autocomplete="off">
                    <button type="submit" class="btn btn-primary-color me-0">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <a href="{{ route('school_admins.create') }}" class="btn btn-primary-color">
                Thêm mới
            </a>
        </div>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary">
                        <tr>
                            <th class="ps-4">STT</th>
                            <th>Tên Admin</th>
                            <th>Email</th>
                            <th>Trường</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($schoolAdmins as $index => $admin)
                            <tr>
                                <td class="ps-4">{{ $index + $schoolAdmins->firstItem() }}</td>
                                <td>{{ $admin->full_name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ $admin->school->name ?? 'N/A' }}</td>
                                <td class="text-center ">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                                            <li><a class="dropdown-item"
                                                   href="{{ route('school_admins.show', $admin->id) }}">Xem</a></li>
                                            <li><a class="dropdown-item"
                                                   href="{{ route('school_admins.edit', $admin->id) }}">Sửa</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Không tìm thấy kết quả</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($schoolAdmins->lastPage() > 1)
                <div class="card-footer border-0 bg-transparent" id="pagination-container">
                    <nav aria-label="page navigation">
                        {{ $schoolAdmins->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let timeout = null;

            $('#search-input').on('input', function () {
                clearTimeout(timeout);
                let $this = $(this);
                timeout = setTimeout(function () {
                    if ($this.val().trim() === '') {
                        $('#search-form').submit();
                    }
                }, 300);
            });

            $('#search-input').on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#search-form').submit();
                }
            });

            $('#search-form button[type="submit"]').on('click', function (e) {
                e.preventDefault();
                $('#search-form').submit();
            });
        });
    </script>
@endpush
