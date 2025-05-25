@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-history me-2"></i> Lịch sử thông báo
                </h4>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('failed') && count(session('failed')) > 0)
                    <div class="alert alert-warning">
                        <h5>Gửi thất bại cho {{ count(session('failed')) }} phụ huynh:</h5>
                        <ul>
                            @foreach(session('failed') as $fail)
                                <li>{{ $fail['student'] }} ({{ $fail['email'] }}): {{ $fail['error'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tiêu đề</th>
                            <th>Lớp</th>
                            <th>Ngày gửi</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($notifications as $notification)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $notification->subject }}</td>
                                <td>{{ $notification->class->name ?? 'N/A' }}</td>
                                <td>{{ $notification->sent_at ? $notification->sent_at->format('d/m/Y H:i') : 'Chưa gửi' }}</td>
                                <td>
                                <span class="badge bg-{{ $notification->status == 'sent' ? 'success' : 'warning' }}">
                                    {{ $notification->status == 'sent' ? 'Đã gửi' : 'Bản nháp' }}
                                </span>
                                </td>
                                <td>
                                    <a href="{{ route('notifications.preview', $notification) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                    @if($notifications->lastPage() > 1)
                        <div class="card-footer border-0 bg-transparent" id="pagination-container">
                            <nav aria-label="page navigation">
                                {{ $notifications->links('pagination::bootstrap-5') }}
                            </nav>
                        </div>
                    @endif
            </div>
        </div>
    </div>
@endsection
