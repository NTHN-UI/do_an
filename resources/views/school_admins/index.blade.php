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

        .table-responsive .dropdown-menu  {
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
    </style>
    <div class="container">
        <h3 class="mb-3" style="color:#013066">Danh sách Admin Trường</h3>
        <div class="d-flex justify-content-end align-items-center mb-3">
            <form method="GET" action="{{ route('school_admins.index') }}" class="d-flex me-2" id="search-form">
                <div class="input-group">
                    <input type="text" name="search" id="search-input" class="form-control" style="min-width: 300px"
                           placeholder="Tìm theo tên, email hoặc tên trường..."
                           value="{{ request('search') }}"
                           autocomplete="off">
                    <button type="submit" class="btn me-0" style="background-color: #013066;">
                        <i class="fas fa-search" style="color: white"></i>
                    </button>
                </div>
            </form>
            <a href="{{ route('school_admins.create') }}" class="btn shadow"
               style="background-color: #013066; border-color: #013066; color: #fff;">
                Thêm mới
            </a>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th class="ps-4">STT</th>
                            <th>Tên Admin</th>
                            <th>Email</th>
                            <th>Trường</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($schoolAdmins as $index => $admin)
                            <tr class="text-center">
                                <td class="ps-4">{{ $index + $schoolAdmins->firstItem() }}</td>
                                <td>{{ $admin->full_name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ $admin->school->name ?? 'N/A' }}</td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                                            <li><a class="dropdown-item px-3 py-2"
                                                   href="{{ route('school_admins.show', $admin->id) }}">Xem</a></li>
                                            <li><a class="dropdown-item px-3 py-2"
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
        </div>

        <div class="d-flex justify-content-end align-items-center mt-2">
            <div class="text-muted me-3">
                {{ $schoolAdmins->firstItem() }}-{{ $schoolAdmins->lastItem() }} của {{ $schoolAdmins->total() }} bản ghi
            </div>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ $schoolAdmins->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link"href="{{ $schoolAdmins->previousPageUrl() }}">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>
                @for ($i = 1; $i <= $schoolAdmins->lastPage(); $i++)
                    <li class="page-item {{ $schoolAdmins->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link" style="background-color: #013066; border-color: #013066; color: #fff;" href="{{ $schoolAdmins->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="page-item {{ $schoolAdmins->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $schoolAdmins->nextPageUrl() }}">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let timeout = null;

            $('#search-input').on('input', function() {
                clearTimeout(timeout);
                let $this = $(this);
                timeout = setTimeout(function () {
                    if ($this.val().trim() === '') {
                        $('#search-form').submit();
                    }
                }, 300);
            });

            $('#search-input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#search-form').submit();
                }
            });

            $('#search-form button[type="submit"]').on('click', function(e) {
                e.preventDefault();
                $('#search-form').submit();
            });
        });
    </script>
@endpush
