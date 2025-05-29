@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2>Tạo mới đề thi</h2>
            </div>
        </div>

        <form id="examForm" action="{{route('exams.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card mb-4">
                <div class="card-header">Thông tin chung</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="title">Tên bài kiểm tra *</label>
                                <input type="text" class="form-control" id="title" name="title"
                                       value="{{ old('title', $exam->title ?? '') }}" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="subject_id">Môn học *</label>
                                <select name="subject_id" id="subject_id" class="form-control">
                                    <option value="">--Chọn môn học--</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="academic_year_id">Năm học *</label>
                                    <select name="academic_year_id" id="academic_year_id" class="form-control">
                                        <option value="">--Chọn năm học--</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="semester_id">Học kỳ *</label>
                                    <select name="semester_id" id="semester_id" class="form-control">
                                        <option value="">--Chọn học kỳ--</option>
                                        @foreach($semesters as $semester)
                                            <option value="{{ $semester->id }}">{{ $semester->name }}</option>
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
                                                {{ (old('grade_level_id', $exam->grade_level_id ?? '') == $grade->id ? 'selected' : '' )}}>
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
                                    <label for="exam_type_id">Loại đề thi *</label>
                                    <select class="form-control" id="test_type" name="test_type" required>
                                        <option value="">--Chọn loại đề thi--</option>

                                        <option value="fifteen_minutes">15 phút</option>
                                        <option value="one_period">1 tiết</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="duration_override">Thời gian làm bài (phút)</label>
                                <input type="number" class="form-control" id="duration_override" name="duration_override"
                                       value="{{ old('duration_override', $exam->duration_override ?? '') }}"
                                       min="1" placeholder="Để trống để sử dụng mặc định">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="total_marks">Tổng điểm *</label>
                                <input type="number" class="form-control" id="total_marks" name="total_marks"
                                       value="{{ old('total_marks', $exam->total_marks ?? 10) }}" min="1" required>
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

            <div class="d-flex justify-content-between">
                <a href="{{ route('exams.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <div>
                    <button type="submit" name="action" value="save" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu đề thi
                    </button>
                    <button type="submit" name="action" value="preview" class="btn btn-success ml-2">
                        <i class="fas fa-eye"></i> Xem trước
                    </button>
                </div>
            </div>
        </form>

        @include('exams.partials.question-bank-modal')
        @include('exams.partials.question-template')
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

                // Vô hiệu hóa nút và hiển thị trạng thái tải
                const $addButton = $(this);
                $addButton.prop('disabled', true).text('Đang thêm...');

                $.ajax({
                    url: '{{ route("question-bank.get-questions") }}', // Đảm bảo route này tồn tại và đúng
                    method: 'POST',
                    data: {
                        ids: selectedIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response && response.length > 0) {
                            response.forEach(function(question) {
                                const newIndex = questionCount; // Sử dụng questionCount hiện tại
                                const questionHtml = createQuestionHtml(newIndex, question);
                                $('#questionsContainer').append(questionHtml);
                                questionCount++; // Tăng questionCount sau khi thêm mỗi câu hỏi
                            });
                            $('#questionBankModal').modal('hide');
                            // Bỏ chọn tất cả checkbox sau khi thêm
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
                        // Kích hoạt lại nút sau khi hoàn thành
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
    </script>
@endpush
