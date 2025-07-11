<div class="question-item mb-4 p-3 border rounded" data-question-index="{{ $index }}">
    <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $question->id ?? '' }}">
    <div class="d-flex justify-content-between mb-2">
        <h5 class="mb-0">Câu hỏi <span class="question-number">{{ $index + 1 }}</span></h5>
        <button type="button" class="btn btn-sm btn-danger remove-question">
            <i class="fas fa-trash"></i>
        </button>
    </div>
    <div class="form-group mt-2">
        <label>Nội dung câu hỏi *</label>
        <textarea class="form-control question-content" name="questions[{{ $index }}][content]"
                  required>{{ old("questions.$index.content", $question->content ?? '') }}</textarea>
    </div>
    <div class="form-group mt-2">
        <label>Điểm *</label>
        <input type="number" step="0.1" min="0.1" class="form-control"
               name="questions[{{ $index }}][marks]"
               value="{{ old("questions.$index.marks", $question->marks ?? 1) }}" required>
    </div>

    <div class="answers-container">
        @php
            $options = $question->options ?? [];
            while(count($options) < 4) {
                $options[] = (object)['content' => '', 'is_correct' => false, 'id' => null];
            }
        @endphp

        @foreach($options as $optIdx => $option)
            <div class="form-group mt-2">
                <input type="hidden" name="questions[{{ $index }}][options][{{ $optIdx }}][id]"
                       value="{{ $option->id ?? '' }}">
                <label>Đáp án {{ chr(65 + $optIdx) }} *</label>
                <div class="input-group">
                    <input type="text" class="form-control"
                           name="questions[{{ $index }}][options][{{ $optIdx }}][content]"
                           value="{{ old("questions.$index.options.$optIdx.content", $option->content ?? '') }}"
                           required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <input type="radio"
                                   name="questions[{{ $index }}][correct_option]"
                                   value="{{ $optIdx }}"
                                   {{ (old("questions.$index.correct_option", $correctOption ?? 0) == $optIdx) ? 'checked' : '' }} required>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
