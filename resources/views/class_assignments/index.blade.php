@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Quản lý phân lớp học sinh</h3>
            <div>
                <a href="{{ route('class_assignments.auto_assign') }}" class="btn btn-primary">
                    <i class="fas fa-magic me-2"></i>Phân công tự động
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label for="academic_year_id" class="form-label">Năm học</label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $selectedAcademicYear == $year->id ? 'selected' : '' }}>
                                    {{ $year->year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Lọc</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Tên lớp</th>
                            <th>Khối</th>
                            <th>Giáo viên chủ nhiệm</th>
                            <th>Số học sinh</th>
                            <th>Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($classes as $class)
                            <tr>
                                <td>{{ $class->name }}</td>
                                <td>Khối {{ $class->gradeLevel->grade_number }}</td>
                                <td>
                                    @if($class->homeroomTeacher)
                                        {{ $class->homeroomTeacher->full_name }}
                                    @else
                                        <span class="text-muted">Chưa có</span>
                                    @endif
                                </td>
                                <td>{{ $class->students_count }}</td>
                                <td>
                                    <a href="{{ route('class_assignments.show', $class) }}" class="btn btn-sm btn-outline-primary">
                                        Xem học sinh
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
