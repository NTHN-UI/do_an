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
        <h3 class="mb-3 text-primary-color">Danh sách học kỳ</h3>
        <div class="row mb-3 justify-content-end">
            <div class="col-md-4">
                <form method="GET" action="{{ route('semesters.index') }}">
                    <div class="input-group">
                        <select name="academic_year_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Chọn năm học --</option>
                        @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                                    {{ $year->year }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <a href="{{ route('semesters.create') }}"
                               class="btn btn-primary-color">
                                Thêm mới
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary text-center">
                <tr>
                    <th>ID</th>
                    <th>Tên học kỳ</th>
                    <th>Ngày bắt đầu</th>
                    <th>Ngày kết thúc</th>
                    <th>Hiện tại</th>
                    <th class="text-end pe-4" style="width: 50px;"></th>
                </tr>
                </thead>
                <tbody>
                @foreach($semesters as $index => $semester)
                    <tr class="text-center">
                        <td>{{ $index }}</td>
                        <td>{{ $semester->name }}</td>
                        <td>{{ $semester->start_date->format('d/m/Y') }}</td>
                        <td>{{ $semester->end_date->format('d/m/Y') }}</td>
                        <td>{{ $semester->is_current ? '✓' : '' }}</td>
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
                                           href="{{ route('semesters.show', $semester->id) }}">
                                            Xem
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item px-3 py-2"
                                           href="{{ route('semesters.edit', $semester->id) }}">
                                            Sửa
                                        </a>
                                    </li>
                                </ul>
                            </div>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

                @if($semesters->lastPage() > 1)
                    <div class="card-footer border-0 bg-transparent" id="pagination-container">
                        <nav aria-label="page navigation">
                            {{$semesters->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif    </div>
@endsection
