@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0 text-primary-color">Sửa phân công giảng dạy: {{ $teacher->full_name }}</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('teacher_assignments.update', $teacherAssignment) }}" method="POST">
                @csrf
                @method('PUT')
                <!-- Giáo viên - chỉ hiển thị -->
                <div class="col-md-12">
                    <label class="form-label">Giáo viên</label>
                    <input type="text" class="form-control" value="{{ $teacher->full_name }}" disabled>
                    <input type="hidden" name="school_name" value="{{ $teacher->full_name }}">

                </div>
                <div class="col-md-12">
                    <label class="form-label">Trường</label>
                    <input type="text" class="form-control" value="{{ $teacher->school->name }}" disabled>
                    <input type="hidden" name="school_name" value="{{ $teacher->school->name }}">
                </div>



                <div class="col-md-12">
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

                <!-- Môn học - chỉ hiển thị, không cho sửa, nhưng vẫn gửi giá trị -->
                <div class="col-md-12">
                    <label class="form-label">Môn học <span class="text-danger">*</span></label>

                    <select class="form-select" disabled>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}"
                                {{ $teacherAssignment->subject_id == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="subject_id" value="{{ $teacherAssignment->subject_id }}">
                </div>


                <div class="mb-12">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_homeroom" id="is_homeroom"
                               class="form-check-input" value="1"
                            {{ $teacherAssignment->is_homeroom ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_homeroom">
                            Giáo viên chủ nhiệm
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end align-items-center mt-3">
                    <a href="{{ route('teacher_assignments.index') }}" class="btn btn-outline-primary-color me-2">
                        Đóng
                    </a>
                    <button type="submit" class="btn btn-primary-color">
                        Cập nhật
                    </button>
                </div>
            </form>

@endsection
