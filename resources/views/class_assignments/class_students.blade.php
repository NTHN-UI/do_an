@php use App\Models\ClassModel; @endphp
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Danh sách học sinh lớp {{ $class->name }} - Năm học {{ $academicYear->year }}</h3>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#moveStudentModal">
                    <i class="fas fa-exchange-alt me-2"></i>Chuyển lớp học sinh
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ và tên</th>
                            <th>Ngày sinh</th>
                            <th>Giới tính</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>{{ $student->id }}</td>
                                <td>{{ $student->full_name }}</td>
                                <td>{{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $student->gender ?? 'N/A' }}</td>
                                <td>
                                <span class="badge {{ $student->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $student->is_active ? 'Hoạt động' : 'Ngừng' }}
                                </span>
                                </td>
                                <td>
                                    <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-outline-primary">
                                        Xem
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $students->links() }}
            </div>
        </div>
    </div>

    <!-- Modal chuyển lớp -->
    <div class="modal fade" id="moveStudentModal" tabindex="-1" aria-labelledby="moveStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('class_assignments.move_student') }}">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                    <input type="hidden" name="current_class_id" value="{{ $class->id }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="moveStudentModalLabel">Chuyển học sinh sang lớp khác</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="student_id" class="form-label">Học sinh</label>
                            <select name="user_id" id="student_id" class="form-select" required>
                                <option value="">-- Chọn học sinh --</option>
                                @foreach($class->students as $student)
                                    <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="new_class_id" class="form-label">Lớp đích</label>
                            <select name="new_class_id" id="new_class_id" class="form-select" required>
                                <option value="">-- Chọn lớp --</option>
                                @foreach(ClassModel::where('grade_level_id', $class->grade_level_id)
                                    ->where('academic_year_id', $academicYear->id)
                                    ->where('id', '!=', $class->id)
                                    ->get() as $targetClass)
                                    <option value="{{ $targetClass->id }}">{{ $targetClass->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Xác nhận chuyển</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
