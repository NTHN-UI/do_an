@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4>{{ $assignment->exam->title }}</h4>
                <div class="text-muted">
                    Thời gian làm bài: {{ $assignment->start_time->format('d/m/Y H:i') }} -
                    {{ $assignment->end_time->format('d/m/Y H:i') }}
                </div>
                <div id="timer" class="text-danger font-weight-bold"></div>
            </div>

            <div class="card-body">
                <form id="examForm" action="{{ route('student_exams.submit', $assignment->id) }}" method="POST">
                    @csrf

                    @foreach($questions as $index => $question)
                        <div class="question mb-4">
                            <h5>Câu {{ $index + 1 }} ({{ $question->marks }} điểm)</h5>
                            <p>{!! nl2br(e($question->content)) !!}</p>

                            <div class="options">
                                @foreach($question->options as $option)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="answers[{{ $question->id }}]"
                                               id="option_{{ $option->id }}"
                                               value="{{ $option->id }}">
                                        <label class="form-check-label" for="option_{{ $option->id }}">
                                            {{ $option->content }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <input type="hidden" name="time_taken" id="timeTaken">
                    <button type="submit" class="btn btn-primary">Nộp bài</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Tính thời gian làm bài
        const endTime = new Date('{{ $assignment->end_time->toIso8601String() }}').getTime();

        function updateTimer() {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                document.getElementById('timer').innerHTML = "ĐÃ HẾT GIỜ";
                document.getElementById('examForm').submit();
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('timer').innerHTML = `Thời gian còn lại: ${hours}h ${minutes}m ${seconds}s`;
            document.getElementById('timeTaken').value = Math.floor((endTime - now) / 1000);
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    </script>
@endpush
