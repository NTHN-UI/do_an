@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        {{-- Header Section --}}
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('exams.index') }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">Tạo mới đề thi</h3>
        </div>

        <form id="examForm" action="{{ route('exams.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- General Information Card --}}
            <div class="card mb-4 border-0 shadow-sm rounded-2">
                <div class="card-header bg-transparent text-primary-color fw-semibold">Thông tin chung</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Tên bài kiểm tra <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                               name="title"
                               value="{{ old('title', $exam->title ?? '') }}" required maxlength="255">
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="subject_id" class="form-label">Môn học <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id"
                                class="form-select @error('subject_id') is-invalid @enderror">
                            <option value="">--Chọn môn học--</option>
                            @foreach($subjects as $subject)
                                <option
                                    value="{{ $subject->id }}" {{ (old('subject_id', $exam->subject_id ?? '') == $subject->id) ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="grade_level_id" class="form-label">Khối lớp <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('grade_level_id') is-invalid @enderror" id="grade_level_id"
                                name="grade_level_id" required>
                            <option value="">--Chọn khối--</option>
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade->id }}"
                                    {{ (old('grade_level_id', $exam->grade_level_id ?? '') == $grade->id ? 'selected' : '' )}}>
                                    Khối {{ $grade->grade_number }}
                                </option>
                            @endforeach
                        </select>
                        @error('grade_level_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="mb-3">
                        <label for="academic_year_id" class="form-label">Năm học <span
                                class="text-danger">*</span></label>
                        <select name="academic_year_id" id="academic_year_id"
                                class="form-select @error('academic_year_id') is-invalid @enderror">
                            <option value="">--Chọn năm học--</option>
                            @foreach($academicYears as $year)
                                <option
                                    value="{{ $year->id }}" {{ (old('academic_year_id', $exam->academic_year_id ?? '') == $year->id) ? 'selected' : '' }}>{{ $year->year }}</option>
                            @endforeach
                        </select>
                        @error('academic_year_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="semester_id" class="form-label">Học kỳ <span class="text-danger">*</span></label>
                        <select name="semester_id" id="semester_id"
                                class="form-select @error('semester_id') is-invalid @enderror">
                            <option value="">--Chọn học kỳ--</option>
                            @foreach($semesters as $semester)
                                <option
                                    value="{{ $semester->id }}" {{ (old('semester_id', $exam->semester_id ?? '') == $semester->id) ? 'selected' : '' }}>{{ $semester->name }}</option>
                            @endforeach
                        </select>
                        @error('semester_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="mb-3">
                        <label for="test_type" class="form-label">Loại đề thi <span class="text-danger">*</span></label>
                        <select class="form-select @error('test_type') is-invalid @enderror" id="test_type"
                                name="test_type" required>
                            <option value="">--Chọn loại đề thi--</option>
                            <option
                                value="fifteen_minutes" {{ (old('test_type', $exam->test_type ?? '') == 'fifteen_minutes') ? 'selected' : '' }}>
                                15 phút
                            </option>
                            <option
                                value="one_period" {{ (old('test_type', $exam->test_type ?? '') == 'one_period') ? 'selected' : '' }}>
                                1 tiết
                            </option>
                        </select>
                        @error('test_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="duration_override" class="form-label">Thời gian làm bài (phút)</label>
                        <input type="number" class="form-control @error('duration_override') is-invalid @enderror"
                               id="duration_override" name="duration_override"
                               value="{{ old('duration_override', $exam->duration_override ?? '') }}"
                               min="1" placeholder="Để trống để sử dụng mặc định">
                        @error('duration_override')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="total_marks" class="form-label">Tổng điểm <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('total_marks') is-invalid @enderror"
                               id="total_marks" name="total_marks"
                               value="{{ old('total_marks', $exam->total_marks ?? 10) }}" min="1" required>
                        @error('total_marks')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Questions Section --}}
            <div class="card mb-4 border-0 shadow-sm rounded-2">
                <div
                    class="card-header bg-transparent text-primary-color fw-semibold d-flex justify-content-between align-items-center">
                    <span>Nhập câu hỏi</span>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary-color" data-bs-toggle="modal"
                                data-bs-target="#questionBankModal">
                            Chọn từ ngân hàng câu hỏi
                        </button>
                        <button type="button" class="btn btn-sm btn-primary-color me-2" id="addQuestion">
                            Thêm câu hỏi
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Import từ file Word (DOCX)</label>
                        <div class="custom-file">
                            <input type="file" class="form-control @error('import_file') is-invalid @enderror"
                                   id="import_file" name="import_file" accept=".docx">
                        </div>
                        <small class="form-text text-muted">File phải có định dạng DOCX và kích thước tối đa
                            10MB</small>
                        @error('import_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if(isset($exam) && $exam->questions->isNotEmpty())
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="replace_questions"
                                   name="replace_questions" value="1">
                            <label class="form-check-label" for="replace_questions">
                                Thay thế toàn bộ câu hỏi hiện có
                            </label>
                        </div>
                    @endif

                    <div id="questionsContainer">
                        @if(isset($exam) && $exam->questions->isNotEmpty() && !old('replace_questions'))
                            @foreach($exam->questions as $index => $question)
                                @include('exams.partials.question-template', [
                                    'questionIndex' => $index,
                                    'question' => $question
                                ])
                            @endforeach
                        @elseif(old('questions'))
                            @foreach(old('questions') as $index => $question)
                                @include('exams.partials.question-template', [
                                    'questionIndex' => $index,
                                    'question' => (object)$question
                                ])
                            @endforeach
                        @endif
                    </div>

                    <div class="alert alert-info mt-3">
                        <strong>Lưu ý:</strong> Mỗi câu hỏi phải có ít nhất 2 đáp án và 1 đáp án đúng.
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="d-flex justify-content-end mt-4">
                <div>
                    <button type="submit" name="action" value="preview" class="btn btn-outline-primary-color">Xem
                        trước
                    </button>
                    <button type="submit" name="action" value="save" class="btn btn-primary-color me-2">Lưu đề thi
                    </button>
                </div>
            </div>
        </form>

        {{-- Modals --}}
        @include('exams.partials.question-bank-modal')
        @include('exams.partials.question-template')


    </div>
@endsection

@push('scripts')
    <!-- External Libraries -->
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function () {
            // Global question counter
            let questionCount = $('#questionsContainer .question-item').length;

            // Initialize form controls
            initFormControls();

            // Event handlers
            setupEventHandlers();

            // Initialize question numbering
            updateQuestionNumbers();

            @if(session('open_question_bank'))
                @if(session('success'))
                    alert('{{ session('success') }}');
                @endif
                    $('#questionBankModal').modal('show');
                    loadQuestionBank();
            @endif

            // Function to initialize form controls
            function initFormControls() {
                // Disable semester dropdown initially
                $('#semester_id').prop('disabled', true);

                // Set test type duration if already selected
                if ($('#test_type').val()) {
                    $('#test_type').trigger('change');
                }
            }

            // Function to setup all event handlers
            function setupEventHandlers() {
                // Academic year change handler
                $('#academic_year_id').change(handleAcademicYearChange);

                // Test type change handler
                $('#test_type').change(handleTestTypeChange);

                // Add question button
                $('#addQuestion').click(addNewQuestion);

                // Remove question button
                $(document).on('click', '.remove-question', removeQuestion);

                // Add selected questions from bank
                $('#addSelectedQuestions').click(addQuestionsFromBank);

                // File import handler
                $('#import_file').change(handleFileImport);

                // Total marks validation
                $('#total_marks').on('change', validateTotalMarks);

                // Form submission
                $('#examForm').submit(handleFormSubmission);

                // Preview button
                $('button[value="preview"]').click(handlePreview);

                // Question bank modal
                $('#questionBankModal').on('show.bs.modal', loadQuestionBank);

                // Question bank filters
                $('#filterSubject, #filterGrade, #searchQuestion').on('change keyup', loadQuestionBank);

                // Math content focus
                $(document).on('focus', '.math-content', initMathEditorOnFocus);
            }

            // Academic year change handler
            function handleAcademicYearChange() {
                const academicYearId = $(this).val();

                if (academicYearId) {
                    $('#semester_id').prop('disabled', false);

                    $.ajax({
                        url: '/get-semesters-by-year',
                        type: 'GET',
                        data: {academic_year_id: academicYearId},
                        success: function (data) {
                            $('#semester_id').empty().append('<option value="">--Chọn học kỳ--</option>');
                            $.each(data, function (key, value) {
                                $('#semester_id').append(`<option value="${value.id}">${value.name}</option>`);
                            });
                        }
                    });
                } else {
                    $('#semester_id').empty()
                        .append('<option value="">--Chọn học kỳ--</option>')
                        .prop('disabled', true);
                }
            }

            // Test type change handler
            function handleTestTypeChange() {
                const testType = $(this).val();
                let defaultDuration = '';

                if (testType === 'fifteen_minutes') {
                    defaultDuration = 15;
                } else if (testType === 'one_period') {
                    defaultDuration = 45;
                }

                if (defaultDuration) {
                    $('#duration_override').val(defaultDuration).prop('disabled', true);
                } else {
                    $('#duration_override').val('').prop('disabled', false);
                }
            }

            // Create question HTML template
            function createQuestionHtml(index, questionData = null) {
                const content = questionData ? questionData.content : '';
                const marks = questionData ? (questionData.marks || 1) : 1;
                const options = questionData ? questionData.options : Array.from({length: 4}, () => ({
                    content: '',
                    is_correct: false
                }));
                const correctOption = questionData ? questionData.correct_option : null;

                let optionsHtml = '';
                options.forEach((option, optIdx) => {
                    // Kiểm tra đáp án đúng từ server hoặc từ dấu (*)
                    const isChecked = (correctOption !== null && optIdx === correctOption) ||
                        (option.is_correct === true);

                    optionsHtml += `
            <div class="form-group">
                <label>Đáp án ${String.fromCharCode(65 + optIdx)} *</label>
                <div class="input-group">
                    <textarea class="form-control math-content" name="questions[${index}][options][${optIdx}][content]" required>${option.content || ''}</textarea>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <input type="radio" name="questions[${index}][correct_option]" value="${optIdx}" ${isChecked ? 'checked' : ''} required>
                        </div>
                    </div>
                </div>
            </div>`;
                });

                return `
        <div class="question-item mb-4 p-3 border rounded" data-question-index="${index}">
            <div class="d-flex justify-content-between mb-2">
                <h5 class="mb-0">Câu hỏi <span class="question-number">${index + 1}</span></h5>
                <button type="button" class="btn btn-sm btn-danger remove-question">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            </div>
            <div class="form-group">
                <label>Nội dung câu hỏi *</label>
                <textarea class="form-control question-content math-content" name="questions[${index}][content]" required>${content}</textarea>
            </div>
            <div class="form-group">
                <label>Điểm *</label>
                <input type="number" step="0.1" min="0.1" class="form-control" name="questions[${index}][marks]" value="${marks}" required>
            </div>
            <div class="answers-container">
                ${optionsHtml}
            </div>
        </div>`;
            }

            // Add new question
            function addNewQuestion() {
                const questionHtml = createQuestionHtml(questionCount);
                $('#questionsContainer').append(questionHtml);

                // Initialize editor for new question
                $('.question-item').last().find('.math-content').each(function () {
                    if (!$(this).data('editor-initialized')) {
                        initMathEditor(this);
                        $(this).data('editor-initialized', true);
                    }
                });

                questionCount++;
                validateTotalMarks();
                $(document).trigger('questionAdded');
            }

            // Remove question
            function removeQuestion() {
                $(this).closest('.question-item').remove();
                updateQuestionNumbers();
                validateTotalMarks();
                $(document).trigger('questionRemoved');
            }

            // Update question numbers
            function updateQuestionNumbers() {
                $('.question-item').each(function (index) {
                    // Update displayed number
                    $(this).find('.question-number').text(index + 1);

                    // Update data attribute
                    $(this).attr('data-question-index', index);

                    // Update all input names
                    $(this).find('[name^="questions["]').each(function () {
                        const name = $(this).attr('name');
                        const newName = name.replace(/questions\[\d+\]/, `questions[${index}]`);
                        $(this).attr('name', newName);
                    });
                });

                // Update global counter
                questionCount = $('.question-item').length;
            }




            // Add questions from question bank
            function addQuestionsFromBank() {
                const selectedIds = $('.question-checkbox:checked').map(function () {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) {
                    alert('Vui lòng chọn ít nhất 1 câu hỏi từ ngân hàng!');
                    return;
                }

                // Đóng modal ngay lập tức khi bắt đầu thêm
                $('#questionBankModal').modal('hide');

                $.ajax({
                    url: '{{ route("question_bank.get_questions") }}',
                    method: 'POST',
                    data: {
                        ids: selectedIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response && response.length > 0) {
                            let questionsHtml = '';

                            response.forEach(function (question) {
                                const newIndex = questionCount;
                                questionsHtml += createQuestionHtml(newIndex, question);
                                questionCount++;
                            });

                            $('#questionsContainer').append(questionsHtml);

                            // Initialize editors for new questions
                            $('.question-item').slice(-response.length).find('.math-content').each(function () {
                                if (!$(this).data('editor-initialized')) {
                                    initMathEditor(this);
                                    $(this).data('editor-initialized', true);
                                }
                            });

                            $('.question-checkbox').prop('checked', false);
                            validateTotalMarks();
                        }
                    },
                    error: function (xhr) {
                        alert('Có lỗi xảy ra khi tải câu hỏi từ ngân hàng');
                        console.error('AJAX error:', xhr);
                    }
                });

                $(document).trigger('questionAdded');
            }

            // Load question bank
            function loadQuestionBank() {
                $.ajax({
                    url: '/question-bank/filter',
                    method: 'GET',
                    data: {
                        subject_id: $('#filterSubject').val(),
                        grade_level_id: $('#filterGrade').val(),
                        search: $('#searchQuestion').val()
                    },
                    success: function (response) {
                        $('#questionBankTable tbody').html(response.html);
                    }
                });
            }

            // Handle file import
            function handleFileImport(e) {
                const file = e.target.files[0];
                if (!file) return;

                if (!file.name.endsWith('.docx')) {
                    alert('Vui lòng chọn file Word (.docx)');
                    return;
                }

                // Kiểm tra xem có checkbox "Thay thế toàn bộ câu hỏi" không
                const replaceAll = $('#replace_questions').is(':checked');

                // Nếu không thay thế toàn bộ, hiển thị thông báo đang thêm
                if (!replaceAll) {
                    $('#questionsContainer').append('<div class="text-center py-4" id="import-loading"><i class="fas fa-spinner fa-spin"></i> Đang thêm câu hỏi từ file...</div>');
                } else {
                    $('#questionsContainer').html('<div class="text-center py-4" id="import-loading"><i class="fas fa-spinner fa-spin"></i> Đang đọc file Word...</div>');
                }

                const formData = new FormData();
                formData.append('file', file);
                formData.append('replace_all', replaceAll ? '1' : '0');
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route("exams.preview-word") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#import-loading').remove();

                        if (response.success && response.questions.length > 0) {
                            let html = '';

                            // Nếu là thêm mới, giữ nguyên câu hỏi cũ
                            if (!replaceAll) {
                                // Giữ nguyên các câu hỏi hiện có
                                html = $('#questionsContainer').html();

                                // Thêm câu hỏi mới vào cuối
                                response.questions.forEach((question, index) => {
                                    const newIndex = questionCount + index;
                                    html += createQuestionHtml(newIndex, question);
                                });

                                questionCount += response.questions.length;
                            }
                            // Nếu thay thế toàn bộ
                            else {
                                response.questions.forEach((question, index) => {
                                    html += createQuestionHtml(index, question);
                                });
                                questionCount = response.questions.length;
                            }

                            $('#questionsContainer').html(html);

                            // Initialize editors for all questions
                            $('.math-content').each(function () {
                                if (!$(this).data('editor-initialized')) {
                                    initMathEditor(this);
                                    $(this).data('editor-initialized', true);
                                }
                            });

                            // Render MathJax
                            if (typeof MathJax !== 'undefined') {
                                MathJax.typesetPromise();
                            }

                            validateTotalMarks();

                        } else {
                            const message = replaceAll
                                ? '<div class="alert alert-warning">Không tìm thấy câu hỏi nào trong file.</div>'
                                : '<div class="alert alert-warning">Không có câu hỏi mới nào được thêm từ file.</div>';

                            $('#questionsContainer').append(message);
                        }
                    },
                    error: function (xhr) {
                        $('#import-loading').remove();
                        $('#questionsContainer').append(`<div class="alert alert-danger">Lỗi khi đọc file: ${xhr.responseJSON?.message || 'Lỗi không xác định'}</div>`);
                    }
                });

                $(document).trigger('questionAdded');
            }

            // Validate total marks
            function validateTotalMarks() {
                const totalMarks = parseFloat($('#total_marks').val()) || 0;
                let sumQuestionMarks = 0;

                $('input[name^="questions["][name$="[marks]"]').each(function () {
                    sumQuestionMarks += parseFloat($(this).val()) || 0;
                });

                const difference = Math.abs(sumQuestionMarks - totalMarks);
                const isValid = difference < 0.01;

                if (!isValid) {
                    $('#marks-warning').remove();
                    $('#total_marks').after(`
                        <div id="marks-warning" class="text-danger small mt-1">
                            Tổng điểm câu hỏi hiện tại: ${sumQuestionMarks.toFixed(1)}
                            (Chênh lệch: ${(sumQuestionMarks - totalMarks).toFixed(1)})
                        </div>
                    `);
                } else {
                    $('#marks-warning').remove();
                }

                return isValid;
            }

            // Auto adjust question marks
            function autoAdjustQuestionMarks() {
                const totalMarks = parseFloat($('#total_marks').val()) || 0;
                const questionCount = $('.question-item').length;

                if (questionCount === 0) return;

                const marksPerQuestion = totalMarks / questionCount;
                const roundedMarks = Math.round(marksPerQuestion * 10) / 10;

                $('input[name^="questions["][name$="[marks]"]').val(roundedMarks);
                validateTotalMarks();
            }

            // Handle form submission
            function handleFormSubmission(e) {
                if (!validateTotalMarks()) {
                    e.preventDefault();

                    if (confirm('Tổng điểm không khớp. Bạn có muốn tự động điều chỉnh điểm các câu hỏi?')) {
                        autoAdjustQuestionMarks();
                        $(this).submit();
                    }

                    return false;
                }
            }

            // Handle preview
            function handlePreview(e) {
                if (!validateTotalMarks()) {
                    e.preventDefault();
                    alert('Vui lòng điều chỉnh điểm các câu hỏi để tổng điểm khớp với điểm bài kiểm tra');
                    return false;
                }

                e.preventDefault();

                const formData = new FormData($('#examForm')[0]);

                $.ajax({
                    url: '{{ route("exams.preview") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        const previewWindow = window.open('', '_blank');
                        previewWindow.document.write(response);
                        previewWindow.document.close();
                    },
                    error: function (xhr) {
                        alert('Có lỗi xảy ra khi tạo bản xem trước: ' + xhr.responseText);
                    }
                });
            }

            // Initialize math editor on focus
            function initMathEditorOnFocus() {
                if (!$(this).data('editor-initialized')) {
                    initMathEditor(this);
                    $(this).data('editor-initialized', true);
                }
            }

            // Initialize math editor
            function initMathEditor(textarea) {
                return new EasyMDE({
                    element: textarea,
                    toolbar: ["bold", "italic", "heading", "|",
                        {
                            name: "math",
                            action: function (editor) {
                                $('#mathModal').modal('show');
                                $('#insertMath').off('click').on('click', function () {
                                    const tex = $('#mathInput').val();
                                    if (tex) {
                                        editor.codemirror.replaceSelection(`\\(${tex}\\)`);
                                    }
                                    $('#mathModal').modal('hide');
                                    $('#mathInput').val('');
                                });
                            },
                            className: "fa fa-square-root-alt",
                            title: "Chèn công thức toán",
                        },
                        "|", "unordered-list", "ordered-list", "|", "preview", "guide"
                    ],
                    spellChecker: false,
                    status: false
                });
            }

            // Math modal handler
            $('#mathModal').on('shown.bs.modal', function () {
                $('#mathInput').focus().on('input', function () {
                    $('#mathPreview').html(`$$${$(this).val()}$$`);
                    if (typeof MathJax !== 'undefined') {
                        MathJax.typesetPromise();
                    }
                });
            });

            // Auto adjust marks when questions are added/removed
            $(document).on('questionAdded questionRemoved', autoAdjustQuestionMarks);
        });
        $(document).ready(function() {
            // Kiểm tra nếu có yêu cầu mở modal
            @if(session('open_question_bank'))
            // Hiển thị thông báo thành công
            @if(session('success'))
            alert('{{ session('success') }}');
            @endif

            // Mở modal ngân hàng câu hỏi
            $('#questionBankModal').modal('show');

            // Load lại danh sách câu hỏi
            loadQuestionBank();
            @endif
        });

        function loadQuestionBank() {
            $.ajax({
                url: '{{ route("question_bank.filter") }}',
                method: 'GET',
                data: {
                    subject_id: $('#filterSubject').val(),
                    grade_level_id: $('#filterGrade').val(),
                    search: $('#searchQuestion').val()
                },
                success: function(response) {
                    $('#questionBankTable tbody').html(response.html);
                }
            });
        }

    </script>
@endpush
