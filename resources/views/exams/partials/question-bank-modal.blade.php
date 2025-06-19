<!-- resources/views/exams/partials/question-bank-modal.blade.php -->
<div class="modal fade" id="questionBankModal" tabindex="-1" aria-labelledby="questionBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionBankModalLabel">Ngân hàng câu hỏi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select id="filterSubject" class="form-select">
                            <option value="">Lọc theo môn học</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="filterGrade" class="form-select">
                            <option value="">Lọc theo khối</option>
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade->id }}">Khối {{ $grade->grade_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" id="searchQuestion" class="form-control" placeholder="Tìm kiếm câu hỏi...">
                    </div>
                    <div class="col-md-3 text-end">
                        <a href="{{ route('question_bank.import') }}?return_to=exam" class="btn btn-primary-color">
                            <i class="fas fa-file-import"></i> Import câu Hỏi
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Chọn</th>
                            <th>Câu hỏi</th>
                            <th>Đáp án</th>
                            <th>Môn</th>
                            <th>Khối</th>
                            <th>Điểm</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($questionBanks as $question)
                            <tr>
                                <td>
                                    <input type="checkbox" class="question-checkbox" value="{{ $question->id }}">
                                </td>
                                <td>{!! Str::limit($question->content, 150) !!}</td>
                                <td>
                                    @if($question->options->isNotEmpty())
                                        <div class="answer-container">
                                            @foreach($question->options as $option)
                                                <div class="answer-option @if($option->is_correct) text-success fw-bold @endif">
                                                    <span class="option-letter">{{ chr(65 + $option->order) }}.</span>
                                                    <span class="option-content">{{ $option->content }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">không có đáp án </span>
                                    @endif
                                </td>
                                <td>{{ $question->subject->name ?? '' }}</td>
                                <td>Khối {{ $question->gradeLevel->grade_number ?? '' }}</td>
                                <td>{{ $question->default_marks ?? 1 }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    @if($questionBanks instanceof \Illuminate\Pagination\AbstractPaginator)
                        {{ $questionBanks->links('pagination::bootstrap-5') }}
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary-color" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary-color" id="addSelectedQuestions">Thêm câu hỏi đã chọn</button>
            </div>
        </div>
    </div>
</div>
