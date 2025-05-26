@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-paper-plane me-2"></i> Gửi Thông Báo Đến Phụ Huynh
                        </h4>
                    </div>

                    <div class="card-body">
                        <form id="notificationForm" action="{{ route('notifications.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <!-- Template Selection -->
                            <!-- Template Selection -->
                            <div class="mb-4 bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                                <label for="template_id" class="form-label">
                                    <i class="fas fa-file-alt me-1"></i> Chọn Mẫu Thông Báo *
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
                            <!-- Class Selection -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-users me-1"></i> Lớp Chủ Nhiệm
                                </label>
                                <div class="form-control bg-light">
                                    {{ $className }} <!-- Hiển thị tên lớp không có khối -->
                                </div>
                                <input type="hidden" name="class_id" value="{{ $classId }}">
                            </div>

                            <!-- Priority -->
                            <div class="mb-4">
                                <label for="priority" class="form-label">
                                    <i class="fas fa-exclamation-circle me-1"></i> Mức Độ Ưu Tiên *
                                </label>
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
                            <div class="mb-4 bg-green-50 p-4 rounded-lg border border-green-200">
                                <label for="subject" class="form-label">
                                    <i class="fas fa-heading me-1"></i> Tiêu Đề Thông Báo *
                                </label>
                                <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                       id="subject" name="subject" value="{{ old('subject') }}" required maxlength="255">
                                @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Có thể sử dụng các biến: {TEN_HOC_SINH}, {LOP}, {GIAO_VIEN}, {NGAY_THANG}</small>
                            </div>

                            <!-- Content -->
                            <div class="mb-4 bg-purple-50 p-4 rounded-lg border border-purple-200">
                                <label for="content" class="form-label">
                                    <i class="fas fa-align-left me-1"></i> Nội Dung Thông Báo *
                                </label>
                                <textarea class="form-control @error('content') is-invalid @enderror"
                                          id="content" name="content" rows="10" required>{{ old('content') }}</textarea>
                                @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Có thể sử dụng các biến: {TEN_HOC_SINH}, {LOP}, {GIAO_VIEN}, {NGAY_THANG}, {THANG}, {MON_THI}, {GIO_THI}, {NGAY_THI}, {PHONG_THI}</small>
                            </div>

                            <!-- Attachments -->
                            <div class="mb-4">
                                <label for="attachments" class="form-label">
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

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('notifications.history') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Quay Lại
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i> Xem Trước
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load template content when selected
            document.getElementById('template_id').addEventListener('change', function() {
                const templateId = this.value;
                if (templateId) {
                    const selectedOption = this.options[this.selectedIndex];
                    document.getElementById('subject').value = selectedOption.dataset.subject;
                    document.getElementById('content').value = selectedOption.dataset.content;
                }
            });
        });
        $('#template_id').change(function() {
            const templateId = $(this).val();
            if (!templateId) return;

            // Hiển thị loading
            $('#template-loading').removeClass('d-none');

            $.get(`/notifications/templates/${templateId}`, function(data) {
                $('#subject').val(data.subject);
                $('#content').val(data.content);

                // Hiển thị các biến có thể sử dụng
                if (data.variables && data.variables.length > 0) {
                    $('#template-variables').html(`
                    <div class="alert alert-info mt-3">
                        <strong>Các biến có thể sử dụng:</strong>
                        ${data.variables.join(', ')}
                    </div>
                `);
                }

                // Ẩn loading
                $('#template-loading').addClass('d-none');
            }).fail(function() {
                $('#template-loading').addClass('d-none');
                alert('Lỗi khi tải nội dung mẫu');
            });
        });
    </script>
@endpush
