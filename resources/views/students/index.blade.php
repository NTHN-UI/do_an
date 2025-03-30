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

        .table thead {
            background: linear-gradient(45deg, #f1f3f5, #e9ecef);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .table th {
            font-weight: 500;
            padding: 12px;
        }

        .table td {
            vertical-align: middle;
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.08);
            transition: background-color 0.3s ease-in-out;
        }

        .badge {
            font-size: 0.85rem;
            padding: 0.4em 0.75em;
            border-radius: 0.5rem;
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

        .input-group .btn {
            border: 0.5rem;
        }

    </style>
    <div class="container">
        <h3 class="mb-3">Danh sách học sinh</h3>

        <!-- Search and Add Button -->
        <div class="d-flex justify-content-end align-items-center mb-3">
            <form method="GET" action="{{ route('students.index') }}" class="d-flex me-3">
                <div class="input-group">
                    <input type="text"
                           name="search"
                           class="form-control" style="min-width: 300px"
                           placeholder="Tìm kiếm học sinh theo họ tên, email, số điện thoại..."
                           value="{{ $search }}"
                           id="search-input">
                    <button type="submit" class="btn me-2" style="background-color: #E15336;">
                        <i class="fas fa-search" style="color: white"></i>
                    </button>
                    @if($search)
                        <a href="{{ route('students.index') }}" class="btn"
                           style="background-color: #E15336;color: white">
                            <i class="fas fa-times"></i> Xóa
                        </a>
                    @endif
                </div>
            </form>
            <a href="{{ route('students.create') }}" class="btn shadow"
               style="background-color: #E15336; border-color: #E15336; color: #fff;">
                Thêm mới
            </a>


        </div>

        <!-- Student list -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th class="ps-4" style="width: 50px;">ID</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Trường</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end pe-4" style="width: 50px;"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($students as $student)
                            <tr class="text-center">
                                <td class="ps-4">{{ $student->id }}</td>
                                <td>{{ $student->full_name }}</td>
                                <td>{{ $student->email }}</td>
                                <td>{{ $student->phone }}</td>
                                <td>{{ $student->school->name ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <span
                                        class="badge rounded-pill {{ $student->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $student->is_active ? 'Hoạt động' : 'Ngừng' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0"
                                            data-bs-popper="static">
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('students.show', $student->id) }}">
                                                    Xem
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('students.edit', $student->id) }}">
                                                    Sửa
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('students.destroy', $student->id) }}"
                                                      method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item px-3 py-2 shadow"
                                                            onclick="return confirm('Bạn có chắc chắn muốn xóa học sinh này?')">
                                                        Xóa
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>

                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination and info -->
        <div class="d-flex justify-content-end align-items-center mt-2">
            <div class="text-muted me-3">
                {{ $students->firstItem() }}-{{ $students->lastItem() }} của {{ $students->total() }} bản ghi
            </div>
            <ul class="pagination pagination-sm mb-0">
                <!-- Nút "Trang Trước" -->
                <li class="page-item {{ $students->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $students->previousPageUrl() }}">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>

                <!-- Số trang -->
                @for ($i = 1; $i <= $students->lastPage(); $i++)
                    <li class="page-item {{ $students->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $students->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                <!-- Nút "Trang Kế" -->
                <li class="page-item {{ $students->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $students->nextPageUrl() }}">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Tìm kiếm khi nhập (debounce 300ms)
            $('#search-input').on('keyup', _.debounce(function () {
                if ($(this).val().length === 0 || $(this).val().length > 2) {
                    $(this).closest('form').submit();
                }
            }, 300));
        });
    </script>
@endsection
