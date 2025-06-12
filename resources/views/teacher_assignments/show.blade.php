@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('teacher_assignments.index') }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">Chi tiết phân công giảng dạy</h3>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-12">
                    <dl class="row">
                        <dt class="col-sm-4">Giáo viên:</dt>
                        <dd class="col-sm-8">{{ $teacherAssignment->teacher->full_name }}</dd>

                        <dt class="col-sm-4">Trường:</dt>
                        <dd class="col-sm-8">{{ $teacherAssignment->teacher->school->name }}</dd>

                        <dt class="col-sm-4">Lớp học:</dt>
                        <dd class="col-sm-8">{{ $teacherAssignment->class->gradeLevel->grade_number }} - {{ $teacherAssignment->class->name }}</dd>

                        <dt class="col-sm-4">Môn học:</dt>
                        <dd class="col-sm-8">{{ $teacherAssignment->subject->name }}</dd>

                        <dt class="col-sm-4">Vai trò:</dt>
                        <dd class="col-sm-8">{{ $teacherAssignment->is_homeroom ? 'Giáo viên chủ nhiệm' : 'Giáo viên bộ môn' }}</dd>

                        <dt class="col-sm-4">Năm học:</dt>
                        <dd class="col-sm-8">{{ $teacherAssignment->academicYear->year }}</dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                <a href="{{ route('teacher_assignments.index') }}" class="btn btn-primary-color ms-2">
                    Đóng
                </a>
            </div>
        </div>
    </div>
@endsection
