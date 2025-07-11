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
        <h3 class="mb-3 text-primary-color">Quản lý phân lớp học sinh</h3>
        <div class="mb-3 d-flex justify-content-end align-items-center">
            <a href="{{ route('class_assignments.auto_assign') }}" class="btn btn-primary-color me-2">
                Phân công tự động
            </a>
        </div>
        <div class="card border-0 shadow-sm rounded-2 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('class_assignments.index') }}" class="row g-3">
                    <div class="col-md-5">
                        <label for="academic_year_id" class="form-label">Năm học</label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select"
                                onchange="this.form.submit()">
                            <option value="">-- Chọn năm học --</option>
                            @foreach($academicYears as $year)
                                <option
                                    value="{{ $year->id }}" {{ $selectedAcademicYear == $year->id ? 'selected' : '' }}>
                                    {{ $year->year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @foreach(request()->except('academic_year_id') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th>Tên lớp</th>
                            <th>Khối</th>
                            <th>Giáo viên chủ nhiệm</th>
                            <th>Số học sinh</th>
                            <th>Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($classes as $class)
                            <tr class="text-center">
                                <td>{{ $class->name }}</td>
                                <td>Khối {{ $class->gradeLevel->grade_number }}</td>
                                <td>
                                    @if($class->homeroomAssignment)
                                        {{ $class->homeroomAssignment->teacher->full_name }}
                                    @else
                                        <span class="text-muted">Chưa có</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge text-primary-color rounded-pill">
                                        {{ $class->students_count ?: '0' }}
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <a href="{{ route('class_assignments.show', ['class' => $class, 'academic_year_id' => $selectedAcademicYear]) }}"
                                       class="btn btn-sm text-primary-color" title="Xem học sinh">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Không có lớp học nào</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
