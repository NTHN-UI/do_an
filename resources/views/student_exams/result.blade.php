@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <h3 class="m-0 text-primary-color">Kết quả bài thi: {{ $exam->title }}</h3>
            </div>
            <div class="text-end">
                <span class="me-2 fw-bold">Điểm số:</span>
                <strong class="text-primary-color">{{ number_format($result->score, 1) }}
                    /{{ $exam->total_marks }}</strong>
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-4">
                <div class="row mb-4 border-bottom pb-3">
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Môn học:</strong> {{ $exam->subject->name }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Lớp:</strong> {{ $assignment->class->name }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Thời gian còn lại:</strong> {{ gmdate("i:s", $result->time_taken) }}
                            phút</p>
                    </div>
                </div>
                <h5 class="mb-3 mt-4">Chi tiết bài làm</h5>
                @foreach($questions as $index => $question)
                    @php
                        $studentAnswer = $result->answers[$question->id] ?? null;
                        $selectedOption = $question->options->where('id', $studentAnswer)->first();
                        $isCorrectAnswer = ($studentAnswer && $selectedOption && $selectedOption->is_correct);
                        $isIncorrectAnswer = ($studentAnswer && $selectedOption && !$selectedOption->is_correct);
                        $isUnanswered = ($studentAnswer === null);
                    @endphp
                    <div class="question mb-4 p-3 border rounded-3 shadow-sm
                    @if($isCorrectAnswer) border-success bg-success-subtle
                    @elseif($isIncorrectAnswer) border-danger bg-danger-subtle
                    @else border-secondary bg-light
                    @endif">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6>Câu {{ $index + 1 }} ({{ $question->marks }} điểm)</h6>
                            @if($isCorrectAnswer)
                                <span class="badge bg-success fw-normal">Đúng</span>
                            @elseif($isIncorrectAnswer)
                                <span class="badge bg-danger fw-normal">Sai</span>
                            @else
                                <span class="badge bg-secondary fw-normal">Không trả lời</span>
                            @endif
                        </div>
                        <p class="fw-bold mb-3">{!! nl2br(e($question->content)) !!}</p>
                        <div class="options">
                            @foreach($question->options as $option)
                                <div class="form-check mb-1">
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
            </div>
        </div>
    </div>
@endsection
