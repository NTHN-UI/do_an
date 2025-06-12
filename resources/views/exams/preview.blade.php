@extends('layouts.app')

@section('content')
    <div class="container exam-preview">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary-color text-white">
                        <h3 class="text-center mb-0">BẢN XEM TRƯỚC ĐỀ THI</h3>
                    </div>

                    <div class="card-body">
                        <!-- Header Information -->
                        <div class="exam-header text-center mb-4">
                            <h4 class="school-name">{{ auth()->user()->school->name }}</h4>
                            <h5 class="exam-title">{{ $examData['title'] }}</h5>
                            <div class="exam-meta">
                                <span class="badge badge-info">{{ strtoupper($examData['test_type']) }}</span>
                                <span class="mx-2">|</span>
                                <span>Môn: {{ $subject->name }}</span>
                                <span class="mx-2">|</span>
                                <span>Khối: {{ $gradeLevel->grade_number }}</span>
                                <span class="mx-2">|</span>
                                <span>Năm học: {{ $academicYear->year }}</span>
                                <span class="mx-2">|</span>
                                <span>{{ $semester->name }}</span>
                            </div>
                            @if($examData['duration_override'])
                                <div class="exam-duration mt-2">
                                    <strong>Thời gian làm bài:</strong> {{ $examData['duration_override'] }} phút
                                </div>
                            @endif
                        </div>

                        <!-- Instructions -->
                        <div class="exam-instructions alert alert-secondary p-3 mb-4">
                            <h6 class="font-weight-bold">Hướng dẫn làm bài:</h6>
                            <ol class="mb-0 pl-3">
                                <li>Đề thi gồm có {{ count($questions) }} câu hỏi</li>
                                <li>Tổng điểm: {{ $examData['total_marks'] }} điểm</li>
                                <li>Thí sinh chọn một đáp án đúng nhất cho mỗi câu hỏi</li>
                            </ol>
                        </div>

                        <!-- Questions -->
                        <div class="exam-questions">
                            @foreach($questions as $question)
                                <div class="question-item mb-4">
                                    <div class="question-content d-flex">
                                        <strong class="mr-2">Câu {{ $question['number'] }}.</strong>
                                        <div class="flex-grow-1">{!! nl2br(e($question['content'])) !!}</div>
                                        <span class="ml-2">({{ $question['marks'] }} điểm)</span>
                                    </div>

                                    <div class="question-options pl-4 mt-2">
                                        @foreach($question['options'] as $option)
                                            <div class="form-check {{ $option['is_correct'] ? 'correct-answer' : '' }}">
                                                <label class="form-check-label">
                                                    <span class="option-letter">{{ $option['letter'] }}.</span>
                                                    {{ $option['content'] }}
                                                    @if($option['is_correct'])
                                                        <span class="badge badge-success ml-2">Đáp án đúng</span>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Footer -->
                        <div class="exam-footer text-center mt-4 pt-3 border-top">
                            <div class="row">
                                <div class="col-md-6 text-start">
                                    <strong>Người tạo:</strong> {{ auth()->user()->full_name }}
                                </div>
                                <div class="col-md-6 text-right">
                                    <strong>Ngày tạo:</strong> {{ now()->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Actions -->
                    @if($isPreview)
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between">
                                <form action="{{ isset($exam) ? route('exams.update', $exam->id) : route('exams.store') }}" method="POST" class="d-inline">
                                    @csrf
                                    @if(isset($exam))
                                        @method('PUT')
                                    @endif

                                    <!-- Truyền tất cả dữ liệu từ form xem trước -->
                                    <input type="hidden" name="title" value="{{ $examData['title'] }}">
                                    <input type="hidden" name="subject_id" value="{{ $examData['subject_id'] }}">
                                    <input type="hidden" name="grade_level_id" value="{{ $examData['grade_level_id'] }}">
                                    <input type="hidden" name="academic_year_id" value="{{ $examData['academic_year_id'] }}">
                                    <input type="hidden" name="semester_id" value="{{ $examData['semester_id'] }}">
                                    <input type="hidden" name="test_type" value="{{ $examData['test_type'] }}">
                                    <input type="hidden" name="total_marks" value="{{ $examData['total_marks'] }}">
                                    <input type="hidden" name="duration_override" value="{{ $examData['duration_override'] ?? '' }}">

                                    @foreach($questions as $qIndex => $question)
                                        <input type="hidden" name="questions[{{ $qIndex }}][content]" value="{{ $question['content'] }}">
                                        <input type="hidden" name="questions[{{ $qIndex }}][marks]" value="{{ $question['marks'] }}">
                                        <input type="hidden" name="questions[{{ $qIndex }}][correct_option]" value="{{ $question['correct_option'] }}">

                                        @foreach($question['options'] as $oIndex => $option)
                                            <input type="hidden"
                                                   name="questions[{{ $qIndex }}][options][{{ $oIndex }}][content]"
                                                   value="{{ $option['content'] }}">
                                            <input type="hidden"
                                                   name="questions[{{ $qIndex }}][options][{{ $oIndex }}][is_correct]"
                                                   value="{{ $option['is_correct'] ? '1' : '0' }}">
                                        @endforeach
                                    @endforeach

                                    <button type="submit" name="action" value="{{ isset($exam) ? 'update' : 'save' }}" class="btn btn-primary-color ml-2">{{ isset($exam) ? 'Cập nhật' : 'Lưu' }} đề thi
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .exam-preview {
            font-size: 1.1rem;
        }
        .exam-header {
            margin-bottom: 2rem;
        }
        .school-name {
            font-weight: bold;
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }
        .exam-title {
            font-weight: bold;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .exam-meta {
            font-size: 1rem;
            color: #555;
        }
        .question-item {
            page-break-inside: avoid;
        }
        .question-content {
            font-weight: 500;
        }
        .correct-answer {
            background-color: #e8f5e9;
            padding: 5px;
            border-radius: 4px;
        }
        .option-letter {
            font-weight: bold;
            margin-right: 5px;
        }
        @media print {
            body {
                background: white;
                font-size: 12pt;
            }
            .container {
                width: auto;
                max-width: 100%;
                padding: 0;
            }
            .card-header, .exam-instructions {
                background-color: white !important;
                color: black !important;
            }
            .card-footer {
                display: none;
            }
            .correct-answer .badge {
                display: none;
            }
        }
    </style>
@endpush
