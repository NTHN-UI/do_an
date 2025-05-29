<div class="modal fade" id="questionBankModal" tabindex="-1" role="dialog" aria-labelledby="questionBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionBankModalLabel">Chọn câu hỏi từ ngân hàng</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchQuestionBank" placeholder="Tìm kiếm câu hỏi...">
                </div>
                <div class="question-bank-list">
                    @foreach($questionBanks as $question)
                        <div class="card mb-2">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input question-checkbox" type="checkbox"
                                           value="{{ $question->id }}"
                                           id="qb_{{ $question->id }}">
                                    <label class="form-check-label" for="qb_{{ $question->id }}">
                                        {{ $question->content }}
                                    </label>
                                </div>
                                <div class="options ml-4 mt-2">
                                    @foreach($question->options as $option)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" disabled {{ $option->is_correct ? 'checked' : '' }}>
                                            <label class="form-check-label {{ $option->is_correct ? 'text-success font-weight-bold' : '' }}">
                                                {{ chr(64 + $loop->iteration) }}. {{ $option->content }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Mức độ: {{ ucfirst($question->difficulty) }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="addSelectedQuestions">Thêm vào đề thi</button>
            </div>
        </div>
    </div>
</div>
