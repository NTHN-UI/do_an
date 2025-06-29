@extends('layouts.app')

@section('content')
    <style>
        .table-responsive .dropdown-menu {
            position: fixed !important;
            z-index: 1000 !important;
            min-width: 60px;
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
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <h3 class="text-primary-color">Danh sách học sinh {{ $class->name }}</h3>
        </div>

        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary">
                        <tr>
                            <th>Họ và tên</th>
                            <th class="text-center">Ngày sinh</th>
                            <th class="text-center">Giới tính</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>{{ $student->full_name }}</td>
                                <td class="text-center">{{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}</td>
                                <td class="text-center">{{ $student->gender ?? 'N/A' }}</td>
                                <td class="text-center">
                                <span class="badge {{ $student->is_active ? 'bg-primary-color' : 'bg-secondary' }}">
                                    {{ $student->is_active ? 'Hoạt động' : 'Ngừng' }}
                                </span>
                                </td>
                                <td>
                                    <div class="dropdown text-center">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                                            <li>
                                                <a href="{{ route('students.show', $student) }}"
                                                   class="btn btn-sm text-primary-color">
                                                    Xem
                                                </a>
                                            </li>

                                            <li>
                                                <a href="{{ route('students.edit', $student) }}"
                                                   class="btn btn-sm text-primary-color">
                                                    Sửa
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

                @if($students->lastPage() > 1)
                    <div class="card-footer border-0 bg-transparent">
                        <nav aria-label="page navigation">
                            {{ $students->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
