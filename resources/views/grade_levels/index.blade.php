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
            background-color: #013066 !important;
            color: white !important;
        }
    </style>
    <div class="container rounded-3 shadow p-4">
        <h3 class="mb-3 text-primary-color">Danh sách khối học</h3>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th>ID</th>
                            <th>Khối</th>
                            <th>Trường</th>
                            <th>Địa chỉ</th>
                            <th>Số lớp</th>
                            <th class="text-end pe-4" style="width: 50px;"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($gradeLevels as $gradeLevel)
                            <tr class="text-center">
                                <td>{{ $gradeLevel->id }}</td>
                                <td>
                                    <span class="badge text-dark p-2">
                                        Khối {{ $gradeLevel->grade_number }}
                                    </span>
                                </td>
                                <td>{{ $gradeLevel->school->name }}</td>
                                <td>{{ $gradeLevel->school->district }}, {{ $gradeLevel->school->province }}</td>
                                <td class="text-center">
                                    <span class="badge badge-pill text-dark">
                                        {{ $gradeLevel->classes_count ?? $gradeLevel->classes->count() }}
                                    </span>
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
                                                   href="{{ route('grade_levels.show', $gradeLevel->id) }}">
                                                    Xem
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Không có dữ liệu khối học</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($gradeLevels->hasPages())
                <div class="card-footer border-0 bg-transparent" id="pagination-container">
                    <nav aria-label="page navigation">
                        {{ $gradeLevels->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
@endsection
