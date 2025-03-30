@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Chi tiết phân công giảng dạy</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Giáo viên</label>
                        <input type="text" class="form-control" value="{{ $teacherAssignment->teacher->full_name }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trường</label>
                        <input type="text" class="form-control" value="{{ $teacherAssignment->teacher->school->name }}" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Lớp học</label>
                        <input type="text" class="form-control"
                               value="{{ $teacherAssignment->class->gradeLevel->grade_number }} - {{ $teacherAssignment->class->name }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Môn học</label>
                        <input type="text" class="form-control" value="{{ $teacherAssignment->subject->name }}" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Vai trò</label>
                        <input type="text" class="form-control"
                               value="{{ $teacherAssignment->is_homeroom ? 'Giáo viên chủ nhiệm' : 'Giáo viên bộ môn' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Năm học</label>
                        <input type="text" class="form-control" value="{{ $teacherAssignment->academicYear->year }}" readonly>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('teacher_assignments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                    <div>
                        <a href="{{ route('teacher_assignments.edit', $teacherAssignment) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <form action="{{ route('teacher_assignments.destroy', $teacherAssignment) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Bạn chắc chắn muốn xóa phân công này?')">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
