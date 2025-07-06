@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="m-0 text-primary-color">Đề thi: {{ $exam->title }}</h3>
                <div class="text-muted mt-1">
                    <span class="fw-bold me-2">Thời gian:</span>
                    {{ $assignment->start_time->format('d/m/Y H:i') }} -
                    {{ $assignment->end_time->format('d/m/Y H:i') }}
                </div>
            </div>
            <div id="timer" class="exam-timer"></div>
        </div>

        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-4">
                <form id="examForm" action="{{ route('student_exams.submit', $assignment->id) }}" method="POST">
                    @csrf

                    @foreach($questions as $index => $question)
                        <div class="question mb-4 p-3 border rounded-3 shadow-sm bg-light">
                            <h5 class="fw-bold mb-3">Câu {{ $index + 1 }} ({{ $question->marks }} điểm)</h5>
                            <p class="mb-3">{!! nl2br(e($question->content)) !!}</p>

                            <div class="options">
                                @foreach($question->options as $option)
                                    <div class="form-check mb-2">
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
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary-color px-4">Nộp bài
                        </button>
                    </div>
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
