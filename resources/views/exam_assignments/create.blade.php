@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4>Giao đề thi: {{ $exam->title }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('exam_assignments.store', $exam->id) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Lớp học *</label>
                        <select name="class_id" class="form-control" required>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Thời gian bắt đầu *</label>
                                <input type="datetime-local" name="start_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Thời gian kết thúc *</label>
                                <input type="datetime-local" name="end_time" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="shuffle_questions" id="shuffle_questions" class="form-check-input">
                        <label for="shuffle_questions" class="form-check-label">Xáo trộn câu hỏi</label>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="shuffle_options" id="shuffle_options" class="form-check-input">
                        <label for="shuffle_options" class="form-check-label">Xáo trộn đáp án</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Giao đề thi</button>
                    <a href="{{ route('exams.show', $exam->id) }}" class="btn btn-secondary">Hủy</a>
                </form>
            </div>
        </div>
    </div>
@endsection
