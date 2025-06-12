@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        {{-- Tiêu đề và nút quay lại, giống form giáo viên --}}
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('exams.index') }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">Tạo mới đề thi</h3>
        </div>

        <form id="examForm" action="{{route('exams.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card mb-4 border-0 shadow-sm rounded-2"> {{-- Áp dụng style card --}}
                <div class="card-header bg-transparent text-primary-color fw-semibold">Thông tin chung</div> {{-- Sửa style header --}}
                <div class="card-body">
                    <div class="mb-3"> {{-- Thay thế .row và .col-md-12 bằng .mb-3 --}}
                        <label for="title" class="form-label">Tên bài kiểm tra <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title"
                               value="{{ old('title', $exam->title ?? '') }}" required maxlength="255">
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="subject_id" class="form-label">Môn học <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror"> {{-- Đổi sang form-select --}}
                            <option value="">--Chọn môn học--</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ (old('subject_id', $exam->subject_id ?? '') == $subject->id) ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="academic_year_id" class="form-label">Năm học <span class="text-danger">*</span></label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror"> {{-- Đổi sang form-select --}}
                            <option value="">--Chọn năm học--</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ (old('academic_year_id', $exam->academic_year_id ?? '') == $year->id) ? 'selected' : '' }}>{{ $year->year }}</option>
                            @endforeach
                        </select>
                        @error('academic_year_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="semester_id" class="form-label">Học kỳ <span class="text-danger">*</span></label>
                        <select name="semester_id" id="semester_id" class="form-select @error('semester_id') is-invalid @enderror"> {{-- Đổi sang form-select --}}
                            <option value="">--Chọn học kỳ--</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ (old('semester_id', $exam->semester_id ?? '') == $semester->id) ? 'selected' : '' }}>{{ $semester->name }}</option>
                            @endforeach
                        </select>
                        @error('semester_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="grade_level_id" class="form-label">Khối lớp <span class="text-danger">*</span></label>
                        <select class="form-select @error('grade_level_id') is-invalid @enderror" id="grade_level_id" name="grade_level_id" required> {{-- Đổi sang form-select --}}
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
                        <label for="test_type" class="form-label">Loại đề thi <span class="text-danger">*</span></label> {{-- Đổi id và name --}}
                        <select class="form-select @error('test_type') is-invalid @enderror" id="test_type" name="test_type" required> {{-- Đổi sang form-select --}}
                            <option value="">--Chọn loại đề thi--</option>
                            <option value="fifteen_minutes" {{ (old('test_type', $exam->test_type ?? '') == 'fifteen_minutes') ? 'selected' : '' }}>15 phút</option>
                            <option value="one_period" {{ (old('test_type', $exam->test_type ?? '') == 'one_period') ? 'selected' : '' }}>1 tiết</option>
                        </select>
                        @error('test_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="duration_override" class="form-label">Thời gian làm bài (phút)</label>
                        <input type="number" class="form-control @error('duration_override') is-invalid @enderror" id="duration_override" name="duration_override"
                               value="{{ old('duration_override', $exam->duration_override ?? '') }}"
                               min="1" placeholder="Để trống để sử dụng mặc định">
                        @error('duration_override')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="total_marks" class="form-label">Tổng điểm <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('total_marks') is-invalid @enderror" id="total_marks" name="total_marks"
                               value="{{ old('total_marks', $exam->total_marks ?? 10) }}" min="1" required>
                        @error('total_marks')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mb-4 border-0 shadow-sm rounded-2"> {{-- Áp dụng style card --}}
                <div class="card-header bg-transparent text-primary-color fw-semibold d-flex justify-content-between align-items-center"> {{-- Sửa style header --}}
                    <span>Nhập câu hỏi</span>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary-color" data-bs-toggle="modal" data-bs-target="#questionBankModal"> {{-- Đổi style button --}}Chọn từ ngân hàng câu hỏi
                        </button>
                        <button type="button" class="btn btn-sm btn-primary-color me-2" id="addQuestion"> {{-- Đổi style button --}}Thêm câu hỏi
                        </button>

                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3"> {{-- Thêm mb-3 --}}
                        <label class="form-label">Import từ file Word (DOCX)</label>
                        <div class="custom-file">
                            <input type="file" class="form-control @error('import_file') is-invalid @enderror" id="import_file" name="import_file" accept=".docx"> {{-- Đổi custom-file-input thành form-control --}}
                            {{-- <label class="custom-file-label" for="import_file">Chọn file</label> --}}
                        </div>
                        <small class="form-text text-muted">File phải có định dạng DOCX và kích thước tối đa 10MB</small>
                        @error('import_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if(isset($exam) && $exam->questions->isNotEmpty())
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="replace_questions" name="replace_questions" value="1">
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

            <div class="d-flex justify-content-end mt-4"> {{-- Đổi justify-content-between sang end và mt-4 --}}
                <div>
                    <button type="submit" name="action" value="preview" class="btn btn-outline-primary-color"> Xem trước
                    </button>
                    <button type="submit" name="action" value="save" class="btn btn-primary-color me-2">Lưu đề thi
                    </button>
                </div>
            </div>
        </form>

        {{-- Cập nhật data-bs-toggle và data-bs-target cho Bootstrap 5 --}}
        @include('exams.partials.question-bank-modal')
        @include('exams.partials.question-template')

        <div class="modal fade" id="mathModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Chèn công thức toán</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nhập công thức LaTeX:</label>
                            <input type="text" id="mathInput" class="form-control" placeholder="Ví dụ: x = \frac{-b \pm \sqrt{b^2-4ac}}{2a}">
                        </div>
                        <div class="mt-2">
                            <strong>Xem trước:</strong>
                            <div id="mathPreview" class="border p-2 mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="button" class="btn btn-primary" id="insertMath">Chèn</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- MathJax cho công thức toán học -->
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Khởi tạo số lượng câu hỏi hiện có trên trang
            let questionCount = $('#questionsContainer .question-item').length;

            // Hàm để tạo HTML cho một câu hỏi
            function createQuestionHtml(index, questionData = null) {
                // questionData sẽ có giá trị khi thêm từ ngân hàng câu hỏi
                const content = questionData ? questionData.content : '';
                const marks = questionData ? (questionData.marks || 1) : 1; // Mặc định điểm là 1 nếu không có
                const options = questionData ? questionData.options : Array.from({length: 4}, () => ({ content: '', is_correct: false }));

                let optionsHtml = '';
                options.forEach((option, optIdx) => {
                    const isChecked = option.is_correct ? 'checked' : '';
                    optionsHtml += `
            <div class="form-group">
                <label>Đáp án ${String.fromCharCode(65 + optIdx)} *</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="questions[${index}][options][${optIdx}][content]" value="${option.content}" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <input type="radio" name="questions[${index}][correct_option]" value="${optIdx}" ${isChecked} required>
                        </div>
                    </div>
                </div>
            </div>
            `;
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
                <textarea class="form-control question-content" name="questions[${index}][content]" required>${content}</textarea>
            </div>
            <div class="form-group">
                <label>Điểm *</label>
                <input type="number" step="0.1" min="0.1" class="form-control" name="questions[${index}][marks]" value="${marks}" required>
            </div>

            <div class="answers-container">
                ${optionsHtml}
            </div>
        </div>
        `;
            }

            // Thêm câu hỏi mới (thủ công)
            $('#addQuestion').click(function() {
                const questionHtml = createQuestionHtml(questionCount);
                $('#questionsContainer').append(questionHtml);
                questionCount++;
            });

            // Xóa câu hỏi
            $(document).on('click', '.remove-question', function() {
                $(this).closest('.question-item').remove();
                updateQuestionNumbers();
            });

            // Thêm câu hỏi từ ngân hàng
            $('#addSelectedQuestions').click(function() {
                const selectedIds = [];
                $('.question-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    alert('Vui lòng chọn ít nhất 1 câu hỏi từ ngân hàng!');
                    return;
                }

                const $addButton = $(this);
                $addButton.prop('disabled', true).text('Đang thêm...');

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
                                const newIndex = questionCount;
                                const questionHtml = createQuestionHtml(newIndex, question);
                                $('#questionsContainer').append(questionHtml);
                                questionCount++;
                            });
                            $('#questionBankModal').modal('hide');
                            $('.question-checkbox').prop('checked', false);
                        } else {
                            alert('Không tìm thấy câu hỏi nào được chọn từ ngân hàng.');
                        }
                    },
                    error: function(xhr) {
                        alert('Có lỗi xảy ra khi tải câu hỏi từ ngân hàng: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Lỗi không xác định.'));
                        console.error('AJAX error:', xhr);
                    },
                    complete: function() {
                        $addButton.prop('disabled', false).text('Thêm câu hỏi đã chọn');
                    }
                });
            });
            // Hàm cập nhật lại số thứ tự và thuộc tính name của các câu hỏi
            function updateQuestionNumbers() {
                questionCount = 0; // Reset lại questionCount
                $('.question-item').each(function(index) {
                    $(this).find('.question-number').text(index + 1);
                    $(this).attr('data-question-index', index);

                    // Cập nhật các thuộc tính name cho tất cả các input
                    // /textarea/radio bên trong
                    $(this).find('[name^="questions["]').each(function() {
                        const name = $(this).attr('name');
                        // Regex để thay thế chỉ số đầu tiên trong name, ví dụ: questions[0][content] -> questions[1][content]
                        const newName = name.replace(/questions\[\d+\]/, `questions[${index}]`);
                        $(this).attr('name', newName);
                    });
                });
                questionCount = $('.question-item').length; // Cập nhật lại questionCount sau khi sắp xếp
            }

            // Khi load trang, kiểm tra và cập nhật lại số thứ tự nếu có sẵn câu hỏi (ví dụ: ở trang edit)
            updateQuestionNumbers();
        });
        // Thêm vào phần scripts của form
        $('button[value="preview"]').click(function(e) {
            e.preventDefault();

            // Lấy dữ liệu từ form
            const formData = new FormData($('#examForm')[0]);

            // Gửi request AJAX để xem trước
            $.ajax({
                url: '{{ route("exams.preview") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Mở cửa sổ mới với nội dung xem trước
                    const previewWindow = window.open('', '_blank');
                    previewWindow.document.write(response);
                    previewWindow.document.close();
                },
                error: function(xhr) {
                    alert('Có lỗi xảy ra khi tạo bản xem trước: ' + xhr.responseText);
                }
            });
        });
        $(document).ready(function() {
            $('#academic_year_id').change(function() {
                var academicYearId = $(this).val();

                if (academicYearId) {
                    $.ajax({
                        url: '/get-semesters-by-year',
                        type: 'GET',
                        data: {
                            academic_year_id: academicYearId
                        },
                        success: function(data) {
                            $('#semester_id').empty();
                            $('#semester_id').append('<option value="">Chọn học kỳ</option>');

                            $.each(data, function(key, value) {
                                $('#semester_id').append('<option value="'+ value.id +'">'+ value.name +'</option>');
                            });
                        }
                    });
                } else {
                    $('#semester_id').empty();
                }
            });
        });
        // Thêm vào phần scripts của bạn
        {{--$('#import_file').change(function(e) {--}}
        {{--    const file = e.target.files[0];--}}
        {{--    if (!file) return;--}}

        {{--    // Kiểm tra định dạng file--}}
        {{--    if (!file.name.endsWith('.docx')) {--}}
        {{--        alert('Vui lòng chọn file Word (.docx)');--}}
        {{--        return;--}}
        {{--    }--}}

        {{--    // Hiển thị loading--}}
        {{--    $('#questionsContainer').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Đang đọc file Word...</div>');--}}

        {{--    // Tạo FormData để gửi file--}}
        {{--    const formData = new FormData();--}}
        {{--    formData.append('file', file);--}}
        {{--    formData.append('_token', '{{ csrf_token() }}');--}}

        {{--    // Gửi AJAX để đọc file Word--}}
        {{--    $.ajax({--}}
        {{--        url: '{{ route("exams.preview-word") }}', // Bạn cần tạo route này--}}
        {{--        type: 'POST',--}}
        {{--        data: formData,--}}
        {{--        processData: false,--}}
        {{--        contentType: false,--}}
        {{--        success: function(response) {--}}
        {{--            if (response.success && response.questions.length > 0) {--}}
        {{--                renderQuestionsFromWord(response.questions);--}}
        {{--            } else {--}}
        {{--                $('#questionsContainer').html('<div class="alert alert-warning">Không tìm thấy câu hỏi nào trong file.</div>');--}}
        {{--            }--}}
        {{--        },--}}
        {{--        error: function(xhr) {--}}
        {{--            $('#questionsContainer').html('<div class="alert alert-danger">Lỗi khi đọc file: ' + (xhr.responseJSON?.message || 'Lỗi không xác định') + '</div>');--}}
        {{--        }--}}
        {{--    });--}}
        {{--});--}}

        $('#import_file').change(function(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (!file.name.endsWith('.docx')) {
                alert('Vui lòng chọn file Word (.docx)');
                return;
            }

            $('#questionsContainer').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Đang đọc file Word...</div>');

            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '{{ route("exams.preview-word") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success && response.questions.length > 0) {
                        let html = '';
                        response.questions.forEach((question, index) => {
                            html += createQuestionHtml(index, question);
                        });
                        $('#questionsContainer').html(html);

                        // Khởi tạo trình soạn thảo cho các textarea mới
                        $('.math-content').each(function() {
                            if (!$(this).data('editor-initialized')) {
                                initMathEditor(this);
                                $(this).data('editor-initialized', true);
                            }
                        });

                        // Render lại MathJax
                        if (typeof MathJax !== 'undefined') {
                            MathJax.typesetPromise();
                        }
                    } else {
                        $('#questionsContainer').html('<div class="alert alert-warning">Không tìm thấy câu hỏi nào trong file.</div>');
                    }
                },
                error: function(xhr) {
                    $('#questionsContainer').html('<div class="alert alert-danger">Lỗi khi đọc file: ' + (xhr.responseJSON?.message || 'Lỗi không xác định') + '</div>');
                }
            });
        });


        // Hàm hiển thị câu hỏi từ file Word
        function renderQuestionsFromWord(questions) {
            let html = '';
            questions.forEach((question, index) => {
                html += `
        <div class="question-item mb-4 p-3 border rounded" data-question-index="${index}">
            <div class="d-flex justify-content-between mb-2">
                <h5 class="mb-0">Câu hỏi <span class="question-number">${index + 1}</span></h5>
            </div>
            <div class="form-group">
                <label>Nội dung câu hỏi *</label>
                <textarea class="form-control question-content" name="questions[${index}][content]" required>${question.content}</textarea>
            </div>
            <div class="form-group">
                <label>Điểm *</label>
                <input type="number" step="0.1" min="0.1" class="form-control" name="questions[${index}][marks]" value="${question.marks || 1}" required>
            </div>
            <div class="answers-container">`;

                question.options.forEach((option, optIdx) => {
                    const isChecked = optIdx == question.correct_option ? 'checked' : '';
                    html += `
                <div class="form-group">
                    <label>Đáp án ${String.fromCharCode(65 + optIdx)} *</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="questions[${index}][options][${optIdx}][content]" value="${option.content}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <input type="radio" name="questions[${index}][correct_option]" value="${optIdx}" ${isChecked} required>
                            </div>
                        </div>
                    </div>
                </div>`;
                });

                html += `</div></div>`;
            });

            $('#questionsContainer').html(html);
            questionCount = questions.length;
        }
        // Hàm tạo HTML cho câu hỏi (cập nhật để hỗ trợ MathJax)
        function createQuestionHtml(index, questionData = null) {
            const content = questionData ? questionData.content : '';
            const marks = questionData ? (questionData.marks || 1) : 1;
            const options = questionData ? questionData.options : Array.from({length: 4}, () => ({ content: '', is_correct: false }));

            let optionsHtml = '';
            options.forEach((option, optIdx) => {
                const isChecked = option.is_correct ? 'checked' : '';
                optionsHtml += `
            <div class="form-group">
                <label>Đáp án ${String.fromCharCode(65 + optIdx)} *</label>
                <div class="input-group">
                    <textarea class="form-control math-content" name="questions[${index}][options][${optIdx}][content]" required>${option.content}</textarea>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <input type="radio" name="questions[${index}][correct_option]" value="${optIdx}" ${isChecked} required>
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

        // Khởi tạo trình soạn thảo khi thêm câu hỏi mới
        $(document).on('focus', '.math-content', function() {
            if (!$(this).data('editor-initialized')) {
                initMathEditor(this);
                $(this).data('editor-initialized', true);
            }
        });
        // Thêm vào phần scripts
        function initMathEditor(textarea) {
            const editor = new EasyMDE({
                element: textarea,
                toolbar: ["bold", "italic", "heading", "|",
                    {
                        name: "math",
                        action: function(editor) {
                            $('#mathModal').modal('show');
                            $('#insertMath').off('click').on('click', function() {
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
            return editor;
        }

        // Modal chèn công thức toán
        $('#mathModal').on('shown.bs.modal', function() {
            $('#mathInput').focus();
            $('#mathInput').on('input', function() {
                $('#mathPreview').html(`$$${$(this).val()}$$`);
                if (typeof MathJax !== 'undefined') {
                    MathJax.typesetPromise();
                }
            });
        });
    </script>
@endpush
