@extends('layouts.app')

@section('content')
    <style>
        .dropdown-menu {
            min-width: 80px;
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
        <h3 class="mb-3 text-primary-color">Danh sách lớp học</h3>
        <div class="mb-3 d-flex justify-content-end align-items-center">
            <form method="GET" action="{{ route('classes.index') }}">
                <div class="input-group">
                    <select name="academic_year_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Chọn năm học --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->year }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
            <a href="{{ route('classes.create') }}" class="btn btn-primary-color">
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
                            <th>Tên Lớp</th>
                            <th>Trường</th>
                            <th>Khối</th>
                            <th class="text-end pe-4" style="width: 50px;"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($classes as $index => $class)
                            <tr class="text-center">
                                <td>{{ $index }}</td>
                                <td>{{ $class->name }}</td>
                                <td>{{ $class->school->name }}</td>
                                <td>Khối {{ $class->gradeLevel->grade_number }}</td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('classes.show', $class->id) }}">
                                                    Xem
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('classes.edit', $class->id) }}">
                                                    Sửa
                                                </a>
                                            </li>
                                            <li>
                                                <button class="dropdown-item"
                                                        onclick="showDeleteModal('{{ route('classes.destroy', $class->id) }}')">
                                                    Xóa
                                                </button>
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
            @if($classes->lastPage() > 1)
                <div class="card-footer border-0 bg-transparent" id="pagination-container">
                    <nav aria-label="page navigation">
                        {{ $classes->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa lớp học này?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary-color" data-bs-dismiss="modal">Hủy</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-primary-color">Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showDeleteModal(url) {
            $('#deleteForm').attr('action', url);
            $('#deleteModal').modal('show');
        }
    </script>
@endpush
