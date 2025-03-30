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
    </style>

    <div class="container">
        <h3 class="mb-0">Danh sách lớp học</h3>
        <div class="d-flex justify-content-end align-items-center mb-3">
            <a href="{{ route('classes.create') }}" class="btn btn-primary">
               Thêm Lớp
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th>ID</th>
                            <th>Tên Lớp</th>
                            <th>Trường</th>
                            <th>Khối</th>
                            <th>Năm Học</th>
                            <th class="text-end pe-4" style="width: 50px;"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($classes as $class)
                            <tr class="text-center">
                                <td>{{ $class->id }}</td>
                                <td>{{ $class->name }}</td>
                                <td>{{ $class->school->name }}</td>
                                <td>Khối {{ $class->gradeLevel->grade_number }}</td>
                                <td>{{ $class->academicYear->year }}</td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                                            <li>
                                                <a class="dropdown-item px-3 py-2" href="{{ route('classes.show', $class->id) }}">
                                                    <i class="fas fa-eye me-2"></i>Xem
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item px-3 py-2" href="{{ route('classes.edit', $class->id) }}">
                                                    <i class="fas fa-edit me-2"></i>Sửa
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('classes.destroy', $class->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item px-3 py-2 text-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">
                                                        <i class="fas fa-trash me-2"></i>Xóa
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end align-items-center mt-2">
            <div class="text-muted me-3">
                {{ $classes->firstItem() }}-{{ $classes->lastItem() }} của {{ $classes->total() }} bản ghi
            </div>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ $classes->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $classes->previousPageUrl() }}">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>
                @for ($i = 1; $i <= $classes->lastPage(); $i++)
                    <li class="page-item {{ $classes->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $classes->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="page-item {{ $classes->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $classes->nextPageUrl() }}">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
@endsection
