@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        {{-- Phần tiêu đề --}}
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('notifications.create') }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">Xem trước thông báo
            </h3>
        </div>

        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body">
                <div class="mb-4 p-4 rounded-3 border border-secondary-subtle info-box-general">
                    <h5 class="fw-semibold text-primary-color mb-3">Thông tin gửi:
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <li><strong> {{ $notification->class->name }}</strong></li>
                        <li><strong>Giáo viên:</strong> {{ $notification->sender->full_name }}</li>
                        <li><strong>Mẫu thông báo:</strong> {{ $notification->template->name ?? 'Tự viết' }}</li>
                        <li><strong>Mức độ ưu tiên:</strong>
                            @switch($notification->priority)
                                @case('high')
                                    <span class="badge bg-danger">Cao</span>
                                    @break
                                @case('medium')
                                    <span class="badge bg-warning text-dark">Trung bình</span>
                                    @break
                                @default
                                    <span class="badge bg-info text-dark">Thấp</span>
                            @endswitch
                        </li>
                    </ul>
                </div>

                <div class="mb-4 p-4 rounded-3 border border-secondary-subtle info-box-preview">
                    <h5 class="fw-semibold text-primary-color mb-3">Xem trước email:
                    </h5>
                    <div class="border p-4 bg-white rounded-3 shadow-sm">
                        <h4 class="fw-bold mb-3 text-primary-color">{{ $notification->subject }}</h4>
                        <div class="mt-3 whitespace-pre-line">
                            {!! nl2br(e($notification->content)) !!}
                        </div>

                        @if($notification->attachments->count() > 0)
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="fw-semibold text-muted">File đính kèm:</h6>
                                <ul class="list-unstyled mb-0">
                                    @foreach($notification->attachments as $attachment)
                                        <li><i class="fas fa-paperclip me-2 text-muted"></i>{{ $attachment->file_name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Nút hành động --}}
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('notifications.edit', $notification->id) }}" class="btn btn-outline-primary-color me-2">Chỉnh sửa</a>
                    <form action="{{ route('notifications.send', $notification) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary-color">Gửi thông báo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
