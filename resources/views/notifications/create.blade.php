@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">

            <h3 class="mb-0 text-primary-color">Thông báo đến phụ huynh</h3>
        </div>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body">
                <form id="notificationForm" action="{{ route('notifications.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <!-- Template Selection -->
                    <div class="mb-4 rounded-3 info-box-yellow">
                        <label for="template_id" class="form-label">Chọn Mẫu Thông Báo <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('template_id') is-invalid @enderror" id="template_id" name="template_id" required>
                            <option value="">-- Chọn mẫu thông báo --</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}"
                                        data-subject="{{ $template->subject_template }}"
                                        data-content="{{ $template->body_template }}">
                                    {{ $template->name }} ({{ $template->type }})
                                </option>
                            @endforeach
                        </select>
                        @error('template_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Class Selection -->
                    <div class="mb-4">
                        <label class="form-label">Lớp Chủ Nhiệm</label>
                        <div class="form-control bg-light rounded-3 shadow-sm py-2 px-3">
                            {{ $className }}
                        </div>
                        <input type="hidden" name="class_id" value="{{ $classId }}">
                    </div>

                    <!-- Priority -->
                    <div class="mb-4">
                        <label for="priority" class="form-label">Mức Độ Ưu Tiên <span class="text-danger">*</span></label>
                        <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                            <option value="">-- Chọn mức độ ưu tiên --</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority['value'] }}">{{ $priority['label'] }}</option>
                            @endforeach
                        </select>
                        @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Subject -->
                    <div class="mb-4 rounded-3">
                        <label for="subject" class="form-label">Tiêu Đề Thông Báo <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror"
                               id="subject" name="subject" value="{{ old('subject') }}" required maxlength="255">
                        @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Content -->
                    <div class="mb-4 rounded-3 ">
                        <label for="content" class="form-label fw-semibold">
                            <i class="fas fa-align-left me-1"></i> Nội Dung Thông Báo <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="10" required>{{ old('content') }}</textarea>
                        @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="attachments" class="form-label fw-semibold">
                            <i class="fas fa-paperclip me-1"></i> File Đính Kèm (Tối đa 3 file)
                        </label>
                        <input type="file" class="form-control @error('attachments') is-invalid @enderror"
                               id="attachments" name="attachments[]" multiple>
                        @error('attachments')
                        <div class="invalid-feedback">{{ $message }}
                        </div>
                        @enderror
                        <small class="text-muted">Định dạng: PDF, Word, Excel, hình ảnh. Tối đa 5MB/file.</small>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary-color">
                            <i class="fas fa-eye me-1"></i> Xem Trước
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        $(document).ready(function() {
            $('#template_id').change(function() {
                var templateId = $(this).val();

                if (templateId) {
                    var selectedOption = $(this).find('option:selected');
                    $('#subject').val(selectedOption.data('subject'));
                    $('#content').val(selectedOption.data('content'));
                }
            });

            // Phiên bản gọi API (nếu cần)
            $('#template_id').on('change', function() {
                var templateId = $(this).val();
                if (!templateId) return;

                $.get('/notifications/templates/' + templateId)
                    .done(function(data) {
                        $('#subject').val(data.subject);
                        $('#content').val(data.content);

                        // Hiển thị các biến có thể sử dụng
                        if (data.variables && data.variables.length > 0) {
                            $('#template-variables').remove();
                            $('<div>', {
                                id: 'template-variables',
                                class: 'alert alert-info mt-3',
                                html: '<strong>Các biến có thể sử dụng:</strong> ' + data.variables.join(', ')
                            }).insertAfter('#content');
                        }
                    })
                    .fail(function() {
                        alert('Lỗi khi tải nội dung mẫu');
                    })
                    .always(function() {
                        loadingIndicator.remove();
                    });
            });
        });
    </script>
@endpush
