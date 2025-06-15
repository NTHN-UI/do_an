@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">Giao đề thi: {{ $exam->title }}</h3>
        </div>
        <div> {{-- This div replaces card-body --}}
            <form action="{{ route('exam_assignments.store', $exam->id) }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label for="class_id">Lớp học <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id" class="form-control @error('class_id') is-invalid @enderror" required>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="start_time">Thời gian bắt đầu <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" required>
                            @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="end_time">Thời gian kết thúc <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}" required>
                            @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="shuffle_questions" id="shuffle_questions"
                           class="form-check-input" value="1" {{ old('shuffle_questions') ? 'checked' : '' }}>
                    <label for="shuffle_questions" class="form-check-label">Xáo trộn câu hỏi</label>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="shuffle_options" id="shuffle_options"
                           class="form-check-input" value="1" {{ old('shuffle_options') ? 'checked' : '' }}>
                    <label for="shuffle_options" class="form-check-label">Xáo trộn đáp án</label>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('exams.show', $exam->id) }}" class="btn btn-outline-primary-color me-2">Đóng</a>
                    <button type="submit" class="btn btn-primary-color">
                        Giao đề thi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
