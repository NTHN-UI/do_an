@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2>Sửa đề thi</h2>
            </div>
        </div>

        <form id="examForm" action="{{ route('exams.update', $exam->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card mb-4">
                <div class="card-header">Thông tin chung</div>
                <div class="card-body">
                    {{-- General Information Fields --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="title">Tên bài kiểm tra *</label>
                                <input type="text" class="form-control" id="title" name="title"
                                       value="{{ old('title', $exam->title) }}" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="subject_id">Môn học *</label>
                                <select name="subject_id" id="subject_id" class="form-control" required>
                                    <option value="">--Chọn môn học--</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}"
                                            {{ (old('subject_id', $exam->subject_id) == $subject->id ? 'selected' : '') }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="academic_year_id">Năm học *</label>
                                <select name="academic_year_id" id="academic_year_id" class="form-control" required>
                                    <option value="">--Chọn năm học--</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}"
                                            {{ (old('academic_year_id', $exam->academic_year_id) == $year->id ? 'selected' : '') }}>
                                            {{ $year->year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="semester_id">Học kỳ *</label>
                                <select name="semester_id" id="semester_id" class="form-control" required>
                                    <option value="">--Chọn học kỳ--</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}"
                                            {{ (old('semester_id', $exam->semester_id) == $semester->id ? 'selected' : '') }}>
                                            {{ $semester->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="grade_level_id">Khối lớp *</label>
                                <select class="form-control" id="grade_level_id" name="grade_level_id" required>
                                    <option value="">--Chọn khối--</option>
                                    @foreach($gradeLevels as $grade)
                                        <option value="{{ $grade->id }}"
                                            {{ (old('grade_level_id', $exam->grade_level_id) == $grade->id ? 'selected' : '' )}}>
                                            Khối {{ $grade->grade_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="test_type">Loại đề thi *</label>
                                <select class="form-control" id="test_type" name="test_type" required>
                                    <option value="">--Chọn loại đề thi--</option>
                                    <option value="fifteen_minutes" {{ (old('test_type', $exam->test_type) == 'fifteen_minutes' ? 'selected' : '') }}>15 phút</option>
                                    <option value="one_period" {{ (old('test_type', $exam->test_type) == 'one_period' ? 'selected' : '') }}>1 tiết</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="duration_override">Thời gian làm bài (phút)</label>
                                <input type="number" class="form-control" id="duration_override" name="duration_override"
                                       value="{{ old('duration_override', $exam->duration_override) }}"
                                       min="1" placeholder="Để trống để sử dụng mặc định">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="total_marks">Tổng điểm *</label>
                                <input type="number" class="form-control" id="total_marks" name="total_marks"
                                       value="{{ old('total_marks', $exam->total_marks) }}" min="1" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Nhập câu hỏi</span>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary mr-2" id="addQuestion">
                            <i class="fas fa-plus"></i> Thêm câu hỏi
                        </button>
                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#questionBankModal">
                            <i class="fas fa-database"></i> Chọn từ ngân hàng câu hỏi
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Import từ file Word (DOCX)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="import_file" name="import_file" accept=".docx">
                            <label class="custom-file-label" for="import_file">Chọn file</label>
                        </div>
                        <small class="form-text text-muted">File phải có định dạng DOCX và kích thước tối đa 10MB</small>
                    </div>

                    @if($exam->questions->isNotEmpty())
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="replace_questions" name="replace_questions" value="1" {{ old('replace_questions') ? 'checked' : '' }}>
                            <label class="form-check-label" for="replace_questions">
                                Thay thế toàn bộ câu hỏi hiện có
                            </label>
                        </div>
                    @endif

                    <div id="questionsContainer">
                        {{-- This section remains complex due to old() data handling --}}
                        @if(old('replace_questions', false) && old('questions'))
                            @foreach(old('questions') as $index => $question)
                                @include('exams.partials.question-item', [
                                    'index' => $index,
                                    'question' => (object)[
                                        'id' => $question['id'] ?? null,
                                        'content' => $question['content'] ?? '',
                                        'marks' => $question['marks'] ?? 1,
                                        'options' => array_map(function($opt) {
                                            return (object)[
                                                'id' => $opt['id'] ?? null,
                                                'content' => $opt['content'] ?? '',
                                                'is_correct' => isset($question['correct_option'])
                                                    ? ($opt['content'] == $question['options'][$question['correct_option']]['content'])
                                                    : false
                                            ];
                                        }, $question['options'] ?? [])
                                    ],
                                    'correctOption' => $question['correct_option'] ?? 0
                                ])
                            @endforeach
                        @else
                            @foreach($exam->questions as $index => $question)
                                @include('exams.partials.question-item', [
                                    'index' => $index,
                                    'question' => $question,
                                    'correctOption' => $question->options->search(function($option) {
                                        return $option->is_correct;
                                    })
                                ])
                            @endforeach
                        @endif
                    </div>

                    <div class="alert alert-info mt-3">
                        <strong>Lưu ý:</strong> Mỗi câu hỏi phải có ít nhất 2 đáp án và 1 đáp án đúng.
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('exams.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <div>
                    <button type="submit" name="action" value="update" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật đề thi
                    </button>
                    <button type="button" name="action" value="preview" class="btn btn-success ml-2" id="previewBtn">
                        <i class="fas fa-eye"></i> Xem trước
                    </button>
                </div>
            </div>
        </form>

        @include('exams.partials.question-bank-modal')
    </div>
@endsection

@push('styles')
    <style>
        .question-item {
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }
        .question-item:hover {
            background-color: #e9ecef;
        }
        .answers-container {
            padding: 10px;
            background-color: white;
            border-radius: 5px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
    <script>
        $(document).ready(function() {
            let questionCount = $('#questionsContainer .question-item').length;

            // Template cho câu hỏi mới
            function getQuestionTemplate(index, question = null) {
                const questionId = question ? question.id : '';
                const content = question ? question.content : '';
                const marks = question ? question.marks : 1;
                const options = question && question.options ? question.options : Array(4).fill().map(() => ({ content: '', is_correct: false }));
                const correctOption = question ? question.correctOption : 0; // This might be an index

                let optionsHtml = '';
                options.forEach((option, optIdx) => {
                    const isChecked = (question ? (optIdx === correctOption) : (optIdx === 0)) ? 'checked' : '';
                    // If option has an ID (meaning it's an existing option), include a hidden input for it
                    const optionIdInput = option.id ? `<input type="hidden" name="questions[${index}][options][${optIdx}][id]" value="${option.id}">` : '';

                    optionsHtml += `
                        <div class="form-group">
                            <label>Đáp án ${String.fromCharCode(65 + optIdx)} *</label>
                            <div class="input-group">
                                ${optionIdInput}
                                <input type="text" class="form-control"
                                       name="questions[${index}][options][${optIdx}][content]"
                                       value="${option.content || ''}" required>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <input type="radio"
                                               name="questions[${index}][correct_option]"
                                               value="${optIdx}" ${isChecked} required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                return `
                    <div class="question-item mb-4 p-3 border rounded" data-question-index="${index}">
                        <input type="hidden" name="questions[${index}][id]" value="${questionId}">
                        <div class="d-flex justify-content-between mb-2">
                            <h5 class="mb-0">Câu hỏi <span class="question-number">${index + 1}</span></h5>
                            <button type="button" class="btn btn-sm btn-danger remove-question">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                        <div class="form-group">
                            <label>Nội dung câu hỏi *</label>
                            <textarea class="form-control question-content"
                                      name="questions[${index}][content]" required>${content}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Điểm *</label>
                            <input type="number" step="0.1" min="0.1" class="form-control"
                                   name="questions[${index}][marks]" value="${marks}" required>
                        </div>
                        <div class="answers-container">
                            ${optionsHtml}
                        </div>
                    </div>
                `;
            }

            // Thêm câu hỏi mới
            $('#addQuestion').click(function() {
                const questionHtml = getQuestionTemplate(questionCount);
                $('#questionsContainer').append(questionHtml);
                questionCount++;
                updateQuestionNumbers();
            });

            // Xóa câu hỏi
            $(document).on('click', '.remove-question', function() {
                $(this).closest('.question-item').remove();
                updateQuestionNumbers();
            });

            // Cập nhật số thứ tự câu hỏi và thuộc tính 'name'
            function updateQuestionNumbers() {
                questionCount = 0; // Reset questionCount to recount accurately
                $('.question-item').each(function(index) {
                    $(this).find('.question-number').text(index + 1);
                    $(this).attr('data-question-index', index);

                    // Update name attributes for all inputs, textareas, and radios
                    $(this).find('[name^="questions["]').each(function() {
                        const name = $(this).attr('name');
                        // Use a regex to replace the question index, preserving option index if present
                        const newName = name.replace(/questions\[\d+\]/, `questions[${index}]`);
                        $(this).attr('name', newName);
                    });
                });
                questionCount = $('.question-item').length; // Update total count after re-indexing
            }

            // Xử lý khi chọn file
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            // Xử lý xem trước
            $('#previewBtn').click(function(e) {
                e.preventDefault();

                // Lấy dữ liệu từ form
                const formData = new FormData($('#examForm')[0]);

                // Thêm action=preview và exam_id vào formData
                formData.append('action', 'preview');
                formData.append('exam_id', '{{ $exam->id }}'); // Thêm ID đề thi đang edit

                // Gửi request AJAX để xem trước
                $.ajax({
                    url: '{{ route("exams.preview") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        const previewWindow = window.open('', '_blank');
                        previewWindow.document.write(response);
                        previewWindow.document.close();
                    },
                    error: function(xhr) {
                        alert('Có lỗi xảy ra khi tạo bản xem trước: ' + xhr.responseText);
                    }
                });
            });

            // Xử lý thêm câu hỏi từ ngân hàng
            $('#addSelectedQuestions').click(function() {
                const selectedIds = $('.question-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) {
                    alert('Vui lòng chọn ít nhất 1 câu hỏi từ ngân hàng!');
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang thêm...');

                $.ajax({
                    url: '{{ route("question-bank.get-questions") }}',
                    method: 'POST',
                    data: {
                        ids: selectedIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response && response.length > 0) {
                            response.forEach(function(question) {
                                const newIndex = questionCount; // Use current questionCount
                                const questionHtml = getQuestionTemplate(newIndex, {
                                    id: question.id,
                                    content: question.content,
                                    marks: question.marks || 1,
                                    options: question.options,
                                    correctOption: question.options.findIndex(opt => opt.is_correct)
                                });
                                $('#questionsContainer').append(questionHtml);
                                questionCount++; // Increment after adding each question
                            });
                            $('#questionBankModal').modal('hide');
                            $('.question-checkbox').prop('checked', false);
                            updateQuestionNumbers(); // Re-index all questions after adding
                        } else {
                            alert('Không tìm thấy câu hỏi nào được chọn từ ngân hàng.');
                        }
                    },
                    error: function(xhr) {
                        alert('Có lỗi xảy ra khi tải câu hỏi từ ngân hàng: ' +
                            (xhr.responseJSON ? xhr.responseJSON.message : 'Lỗi không xác định.'));
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-database"></i> Thêm câu hỏi đã chọn');
                    }
                });
            });

            // Initialize question numbers on page load
            updateQuestionNumbers();
        });
    </script>
@endpush
