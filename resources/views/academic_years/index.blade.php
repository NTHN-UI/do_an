@extends('layouts.app')

@section('content')
    <style>
        .table-responsive .dropdown-menu {
            position: fixed !important;
            z-index: 1000 !important;
            min-width: 90px;
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
        <h3 class="mb-3 text-primary-color">Danh sách năm học</h3>
        <div class="mb-3 d-flex justify-content-end align-items-center">
            <a href="{{ route('academic_years.create') }}" class="btn btn-primary-color">
                Thêm mới
            </a>
        </div>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th>ID</th>
                            <th>Năm học</th>
                            <th>Ngày bắt đầu</th>
                            <th>Ngày kết thúc</th>
                            <th>Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($academicYears as $index => $year)
                            <tr class="text-center">
                                <td>{{ $index }}</td>
                                <td>{{ $year->year }}</td>
                                <td>{{ $year->start_date->format('d/m/Y') }}</td>
                                <td>{{ $year->end_date->format('d/m/Y') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                                            <li>
                                                <a class="dropdown-item px-3 py-2 "
                                                   href="{{ route('academic_years.show', $year->id) }}">
                                                    Xem
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('academic_years.edit', $year->id) }}">
                                                    Sửa
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($academicYears->lastPage() > 1)
                <div class="card-footer border-0 bg-transparent" id="pagination-container">
                    <nav aria-label="page navigation">
                        {{ $academicYears->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
@endsection
