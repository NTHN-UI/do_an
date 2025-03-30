@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Sửa phân công giảng dạy: {{ $teacher->full_name }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('teacher_assignments.update', $teacherAssignment) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Giáo viên</label>
                            <input type="text" class="form-control" value="{{ $teacher->full_name }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trường</label>
                            <input type="text" class="form-control" value="{{ $teacher->school->name }}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Lớp học <span class="text-danger">*</span></label>
                            <select name="class_id" class="form-select" required>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}"
                                        {{ $teacherAssignment->class_id == $class->id ? 'selected' : '' }}>
                                        {{ $class->gradeLevel->grade_number }} - {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Môn học <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select" required>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}"
                                        {{ $teacherAssignment->subject_id == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_homeroom" id="is_homeroom"
                                   class="form-check-input" value="1"
                                {{ $teacherAssignment->is_homeroom ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_homeroom">
                                Giáo viên chủ nhiệm
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('teacher_assignments.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
