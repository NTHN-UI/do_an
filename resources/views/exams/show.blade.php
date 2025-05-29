@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Chi tiết đề thi</h1>
            <div>
                @if(!$exam->is_published)
                    <form action="{{ route('exams.publish', $exam->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Xuất bản
                        </button>
                    </form>
                @endif
                <a href="{{ route('exams.edit', $exam->id) }}" class="btn btn-primary ml-2">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
                <a href="{{ route('exams.index') }}" class="btn btn-secondary ml-2">
                    <i class="fas fa-list"></i> Danh sách
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0">Thông tin đề thi</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tên đề thi:</strong> {{ $exam->title }}</p>
                        <p><strong>Môn học:</strong> {{ $exam->subject->name }}</p>
                        <p><strong>Khối lớp:</strong> Khối {{ $exam->gradeLevel->grade_number }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Loại đề thi:</strong>
                            @if($exam->test_type === 'fifteen_minutes')
                                15 phút
                            @elseif($exam->test_type === 'one_period')
                                1 tiết
                            @endif
                        </p>
                        <p><strong>Thời gian làm bài:</strong> {{ $exam->duration_override ?? 'Mặc định' }} phút</p>
                        <p><strong>Tổng điểm:</strong> {{ $exam->total_marks }}</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <p><strong>Năm học:</strong> {{ $exam->academicYear->year }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Học kỳ:</strong> {{ $exam->semester->name }}</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <p><strong>Người tạo:</strong> {{ $exam->teacher->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Trạng thái:</strong>
                            @if($exam->is_published)
                                <span class="badge badge-success">Đã xuất bản</span>
                            @else
                                <span class="badge badge-secondary">Bản nháp</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Nội dung đề thi</h3>
            </div>
            <div class="card-body">
                @foreach($exam->questions as $index => $question)
                    <div class="question-item mb-4 p-3 border rounded">
                        <div class="d-flex justify-content-between mb-2">
                            <h5 class="mb-0">Câu {{ $index + 1 }} <small>({{ $question->marks }} điểm)</small></h5>
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
                                    <label class="form-check-label {{ $option->is_correct ? 'text-success font-weight-bold' : '' }}"
                                           for="option_{{ $option->id }}">
                                        {{ chr(65 + $optionIndex) }}. {!! $option->content !!}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="text-right mt-4">
                    <p class="font-weight-bold">Tổng cộng: {{ $exam->questions->count() }} câu - {{ $exam->questions->sum('marks') }} điểm</p>
                </div>
            </div>
        </div>
    </div>
@endsection
