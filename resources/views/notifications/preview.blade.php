@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-eye me-2"></i> Xem Trước Thông Báo
                        </h4>
                    </div>

                    <div class="card-body">
                        <div class="mb-4">
                            <h5>Thông tin gửi:</h5>
                            <ul>
                                <li>Lớp: {{ $notification->class->name }}</li>
                                <li>Giáo viên: {{ $notification->sender->full_name }}</li>
                                <li>Mẫu thông báo: {{ $notification->template->name ?? 'Tự viết' }}</li>
                                {{-- Đã sửa lỗi: Đảm bảo $notification->priority không phải là null khi truyền vào ucfirst() --}}
                                <li>Mức độ ưu tiên: {{ ucfirst($notification->priority ?? '') }}</li>
                            </ul>
                        </div>

                        <div class="mb-4 bg-light p-4 rounded">
                            <h5 class="mb-3">Xem trước email:</h5>
                            <div class="border p-4 bg-white">
                                <h4 class="fw-bold">{{ $notification->subject }}</h4>
                                <div class="mt-3 whitespace-pre-line">
                                    {!! nl2br(e($notification->content)) !!}
                                </div>

                                @if($notification->attachments->count() > 0)
                                    <div class="mt-4 pt-3 border-top">
                                        <h6>File đính kèm:</h6>
                                        <ul>
                                            @foreach($notification->attachments as $attachment)
                                                <li>{{ $attachment->file_name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($studentsWithoutEmail->count() > 0)
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i> Cảnh báo</h5>
                                <p>Có {{ $studentsWithoutEmail->count() }} học sinh chưa có email phụ huynh:</p>
                                <ul>
                                    @foreach($studentsWithoutEmail as $student)
                                        <li>{{ $student->full_name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('notifications.create') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-edit me-1"></i> Chỉnh sửa lại
                            </a>
                            <form action="{{ route('notifications.send', $notification) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-1"></i> Gửi Thông Báo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
