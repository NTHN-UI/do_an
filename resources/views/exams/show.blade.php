@extends('layouts.app')

@section('content')
    <style>
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .info-item strong {
            color: var(--primary-color);
        }
    </style>
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <h3 class="mb-0 text-primary-color">Chi tiết đề thi</h3>
            <div class="ms-auto">
                @if(!$exam->is_published)
                    <form action="{{ route('exams.publish', $exam->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Xuất bản
                        </button>
                    </form>
                @endif

            </div>
        </div>

        <div class="card mb-4 border-0 shadow-sm rounded-2">

            <div class="card-header bg-transparent text-primary-color fw-semibold">Thông tin đề thi</div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Tên đề thi:</strong> {{ $exam->title }}</p>
                        <p class="mb-2"><strong>Môn học:</strong> {{ $exam->subject->name }}</p>
                        <p class="mb-2"><strong>Khối lớp:</strong> Khối {{ $exam->gradeLevel->grade_number }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Loại đề thi:</strong>
                            @if($exam->test_type === 'fifteen_minutes')
                                15 phút
                            @elseif($exam->test_type === 'one_period')
                                1 tiết
                            @endif
                        </p>
                        <p class="mb-2">
                            <strong>Thời gian làm bài:</strong>
                            {{ $exam->test_type == 'fifteen_minutes' ? '15 phút' : '45 phút' }}
                        </p>
                        <p class="mb-2"><strong>Tổng điểm:</strong> {{ $exam->total_marks }}</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Năm học:</strong> {{ $exam->academicYear->year }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Học kỳ:</strong> {{ $exam->semester->name }}</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Người tạo:</strong> {{ $exam->teacher->full_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Trạng thái:</strong>
                            @if($exam->is_published)
                                <span class="badge bg-primary-color">Đã xuất bản</span>
                            @else
                                <span class="badge bg-secondary">Bản nháp</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-2">

            <div class="card-header bg-transparent text-primary-color fw-semibold">Nội dung đề thi</div>
            <div class="card-body">
                @foreach($exam->questions as $index => $question)
                    <div class="question-item mb-4 p-3 border rounded-3 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0 text-primary-color">Câu {{ $index + 1 }} <small>({{ $question->marks }}
                                    điểm)</small></h5>
                        </div>
                        <div class="question-content mb-3">
                            {!! $question->content !!}
                        </div>

                        <div class="options">
                            @foreach($question->options as $optionIndex => $option)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio"
                                           name="question_{{ $question->id }}"
                                           id="option_{{ $option->id }}"
                                           disabled
                                        {{ $option->is_correct ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label {{ $option->is_correct ? 'text-primary-color fw-bold' : '' }}"
                                        for="option_{{ $option->id }}">
                                        {{ chr(65 + $optionIndex) }}. {!! $option->content !!}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <p class="fw-bold mb-0">Tổng cộng: {{ $exam->questions->count() }} câu
                        - {{ $exam->questions->sum('marks') }} điểm</p>
                    <div>

                        <a href="{{ route('exams.index') }}" class="btn btn-primary-color">
                            Đóng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
