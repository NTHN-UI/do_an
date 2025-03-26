@extends('layouts.app')

@section('content')
    <style>
        .container {
            background: linear-gradient(0deg, #ffedea, #ffffff);
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

        .dropdown-menu {
            position: fixed !important;
            z-index: 1000 !important;
            min-width: 90px;
        }

        .show > .dropdown-menu {
            display: block !important;
        }

    </style>
    <div class="container">
        <h3 class="mb-3">Danh sách trường học</h3>

        <div class="d-flex justify-content-end align-items-center mb-3">
            <form method="GET" action="{{ route('schools.index') }}" class="d-flex me-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" style="min-width: 300px"
                           placeholder="Tìm kiếm trường học..." value="{{ request('search') }}">
                    <button type="submit" class="btn me-2" style="background-color: #E15336;">
                        <i class="fas fa-search" style="color: white"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('schools.index') }}" class="btn"
                           style="background-color: #E15336;color: white">
                            <i class="fas fa-times"></i> Xóa
                        </a>
                    @endif
                </div>
            </form>
            <a href="{{ route('schools.create') }}" class="btn shadow"
               style="background-color: #E15336; border-color: #E15336; color: #fff;">
                Thêm mới
            </a>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Tên trường</th>
                            <th>Quận/Huyện</th>
                            <th>Tỉnh/Thành</th>
                            <th>Cấp học</th>
                            <th class="text-end pe-4"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($schools as $school)
                            <tr class="text-center">
                                <td class="ps-4">{{ $school->id }}</td>
                                <td>{{ $school->name }}</td>
                                <td>{{ $school->district }}</td>
                                <td>{{ $school->province }}</td>
                                <td>
                                    @if($school->education_level === 'primary')
                                        Tiểu học
                                    @elseif($school->education_level === 'secondary')
                                        THCS
                                    @else
                                        THPT
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                                            <li><a class="dropdown-item px-3 py-2"
                                                   href="{{ route('schools.show', $school->id) }}">Xem</a></li>
                                            <li><a class="dropdown-item px-3 py-2"
                                                   href="{{ route('schools.edit', $school->id) }}">Sửa</a></li>
                                            <li>
                                                <form action="{{ route('schools.destroy', $school->id) }}"
                                                      method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item px-3 py-2"
                                                            onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa
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

        <div class="d-flex justify-content-end align-items-center mt-2">
            <div class="text-muted me-3">
                {{ $schools->firstItem() }}-{{ $schools->lastItem() }} của {{ $schools->total() }} bản ghi
            </div>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ $schools->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $schools->previousPageUrl() }}">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>
                @for ($i = 1; $i <= $schools->lastPage(); $i++)
                    <li class="page-item {{ $schools->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $schools->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="page-item {{ $schools->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $schools->nextPageUrl() }}">
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
