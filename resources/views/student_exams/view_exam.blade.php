@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $exam->title }}</h4>
                <span class="badge bg-info">{{ $exam->subject->name }}</span>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <p><strong>Thời gian làm bài:</strong> {{ $assignment->duration }} phút</p>
                    <p><strong>Thời gian bắt đầu:</strong> {{ $assignment->start_time->format('H:i d/m/Y') }}</p>
                    <p><strong>Thời gian kết thúc:</strong> {{ $assignment->end_time->format('H:i d/m/Y') }}</p>
                </div>

                @if($hasSubmitted)
                    <div class="alert alert-success">
                        Bạn đã nộp bài thi này
                    </div>
                @elseif(!$canStart)
                    <div class="alert alert-warning">
                        @if(now() < $assignment->start_time)
                            Chưa đến thời gian làm bài
                        @else
                            Đã hết thời gian làm bài
                        @endif
                    </div>
                @endif

                <div class="exam-content">
                    {!! $exam->content !!}
                </div>

                @if($canStart && !$hasSubmitted)
                    <form action="{{ route('exams.submit', $exam->id) }}" method="POST" class="mt-4">
                        @csrf
                        @foreach($exam->questions as $question)
                            <div class="card mb-3">
                                <div class="card-header">
                                    <strong>Câu {{ $loop->iteration }}:</strong> {{ $question->content }}
                                </div>
                                <div class="card-body">
                                    @foreach($question->answers as $answer)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                   name="answers[{{ $question->id }}]"
                                                   id="answer_{{ $answer->id }}"
                                                   value="{{ $answer->id }}">
                                            <label class="form-check-label" for="answer_{{ $answer->id }}">
                                                {{ $answer->content }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary">Nộp bài</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
