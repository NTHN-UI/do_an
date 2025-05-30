@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4>Kết quả bài thi: {{ $exam->title }}</h4>
                <div class="text-muted">
                    Điểm số: <strong>{{ number_format($result->score, 1) }}/{{ $exam->total_marks }}</strong>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <p><strong>Môn học:</strong> {{ $exam->subject->name }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Lớp:</strong> {{ $assignment->class->name }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Thời gian làm bài:</strong> {{ gmdate("i:s", $result->time_taken) }} phút</p>
                    </div>
                </div>

                <h5 class="mb-3">Chi tiết bài làm</h5>

                @foreach($questions as $index => $question)
                    @php
                        $studentAnswer = $result->answers[$question->id] ?? null;
                        $selectedOption = $question->options->where('id', $studentAnswer)->first();
                    @endphp

                    <div class="question mb-4 p-3 border rounded
                    @if($studentAnswer && $selectedOption && $selectedOption->is_correct) bg-light border-success
                    @elseif($studentAnswer) bg-light border-danger
                    @else bg-light
                    @endif">

                        <div class="d-flex justify-content-between">
                            <h6>Câu {{ $index + 1 }} ({{ $question->marks }} điểm)</h6>
                            @if($studentAnswer)
                                @if($selectedOption && $selectedOption->is_correct)
                                    <span class="badge bg-success">Đúng</span>
                                @else
                                    <span class="badge bg-danger">Sai</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">Không trả lời</span>
                            @endif
                        </div>

                        <p class="fw-bold">{!! nl2br(e($question->content)) !!}</p>

                        <div class="options">
                            @foreach($question->options as $option)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" disabled
                                           @if($studentAnswer == $option->id) checked @endif>

                                    <label class="form-check-label
                                    @if($option->is_correct) text-success fw-bold @endif
                                    @if($studentAnswer == $option->id && !$option->is_correct) text-danger @endif">
                                        {{ $option->content }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <a href="{{ route('student_exams.assigned_exams') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>
@endsection
