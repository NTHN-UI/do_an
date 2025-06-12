@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">Chỉnh sửa thông báo</h3>
        </div>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body">
                <form id="notificationForm"
                      action="{{ route('notifications.update', $notification->id) }}" {{-- Luôn trỏ đến update --}}
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-4 rounded-3 info-box-yellow">
                        <label for="template_id" class="form-label">Chọn Mẫu Thông Báo <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('template_id') is-invalid @enderror" id="template_id" name="template_id" required>
                            <option value="">-- Chọn mẫu thông báo --</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}"
                                        data-subject="{{ $template->subject_template }}"
                                        data-content="{{ $template->body_template }}"
                                    {{ ($notification->template_id == $template->id) ? 'selected' : '' }}> {{-- Điền dữ liệu cũ --}}
                                    {{ $template->name }} ({{ $template->type }})
                                </option>
                            @endforeach
                        </select>
                        @error('template_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Lớp Chủ Nhiệm</label>
                        <div class="form-control bg-light rounded-3 shadow-sm py-2 px-3">
                            {{ $className }}
                        </div>
                        <input type="hidden" name="class_id" value="{{ $classId }}">
                    </div>

                    <div class="mb-4">
                        <label for="priority" class="form-label">Mức Độ Ưu Tiên <span class="text-danger">*</span></label>
                        <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                            <option value="">-- Chọn mức độ ưu tiên --</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority['value'] }}"
                                    {{ ($notification->priority == $priority['value']) ? 'selected' : '' }}> {{-- Điền dữ liệu cũ --}}
                                    {{ $priority['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 rounded-3">
                        <label for="subject" class="form-label">Tiêu Đề Thông Báo <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror"
                               id="subject" name="subject"
                               value="{{ old('subject', $notification->subject) }}" required maxlength="255"> {{-- Điền dữ liệu cũ --}}
                        @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 rounded-3 ">
                        <label for="content" class="form-label fw-semibold">
                            <i class="fas fa-align-left me-1"></i> Nội Dung Thông Báo <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="10" required>{{ old('content', $notification->content) }}</textarea> {{-- Điền dữ liệu cũ --}}
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
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Định dạng: PDF, Word, Excel, hình ảnh. Tối đa 5MB/file.</small>

                        @if($notification->attachments->count() > 0) {{-- Kiểm tra có file đính kèm --}}
                        <div class="mt-2">
                            <h6>File đính kèm hiện có:</h6>
                            <ul class="list-unstyled">
                                @foreach($notification->attachments as $attachment)
                                    <li><i class="fas fa-file-alt me-1"></i> {{ $attachment->file_name }}</li>
                                @endforeach
                            </ul>
                            <small class="text-info">Để xóa file cũ, bạn cần triển khai logic riêng trong phương thức update.</small>
                        </div>
                        @endif
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load template content when selected (chỉ cho form tạo mới)
            document.getElementById('template_id').addEventListener('change', function() {
                const templateId = this.value;
                if (templateId) {
                    const selectedOption = this.options[this.selectedIndex];
                    document.getElementById('subject').value = selectedOption.dataset.subject;
                    document.getElementById('content').value = selectedOption.dataset.content;
                }
            });
            // Đoạn script jQuery của bạn (chỉ chạy khi chọn template)
            $('#template_id').change(function() {
                const templateId = $(this).val();
                if (!templateId) return;

                // Hiển thị loading (nếu có)
                // $('#template-loading').removeClass('d-none'); // Bỏ comment nếu bạn có element này

                $.get(`/notifications/templates/${templateId}`, function(data) {
                    $('#subject').val(data.subject);
                    $('#content').val(data.content);

                    // Hiển thị các biến (nếu có)
                    // if (data.variables && data.variables.length > 0) {
                    //     $('#template-variables').html(` // Bỏ comment nếu bạn có element này
                    //     <div class="alert alert-info mt-3">
                    //         <strong>Các biến có thể sử dụng:</strong>
                    //         ${data.variables.join(', ')}
                    //     </div>
                    // `);
                    // }

                    // Ẩn loading
                    // $('#template-loading').addClass('d-none'); // Bỏ comment nếu bạn có element này
                }).fail(function() {
                    // $('#template-loading').addClass('d-none'); // Bỏ comment nếu bạn có element này
                    alert('Lỗi khi tải nội dung mẫu');
                });
            });
        });
    </script>
@endpush
