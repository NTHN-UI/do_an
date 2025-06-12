@extends('layouts.app')

@section('content')
    <style>
        .table-responsive .dropdown-menu {
            position: fixed !important;
            z-index: 1000 !important;
            min-width: 90px;
        }
        .dropdown-item:active,
        .dropdown-item:focus {
            background-color: #013066 !important;
            color: white !important;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: var(--bs-white);
            border-color: var(--primary-color);
        }
    </style>
    <div class="container rounded-3 shadow p-4">
        <h3 class="mb-3 text-primary-color">Lịch sử Thông báo</h3>
        <div class="mb-3 d-flex justify-content-end align-items-center">
            <a href="{{ route('notifications.create') }}" class="btn btn-primary-color ms-2">
                Thông báo mới
            </a>
        </div>

        {{-- Bảng hiển thị lịch sử thông báo --}}
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary">
                        <tr>
                            <th>Tiêu đề</th>
                            <th class="text-center">Lớp</th>
                            <th class="text-center">Ưu tiên</th>
                            <th class="text-center" >Ngày gửi</th>
                            <th class="text-center">Người gửi</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{-- Giả định biến $notifications chứa danh sách thông báo --}}
                        @forelse($notifications as $notification)
                            <tr class="text-center">
                                <td class="text-start">{{ $notification->subject }}</td>
                                <td>{{ $notification->class->name ?? 'N/A' }}</td>
                                <td>
                                    {{-- Hiển thị mức độ ưu tiên --}}
                                    @switch($notification->priority)
                                        @case('high')
                                            <span class="badge bg-primary-color">Cao</span>
                                            @break
                                        @case('medium')
                                            <span class="badge bg-primary-color">Trung bình</span>
                                            @break
                                        @default
                                            <span class="badge bg-primary-color">Thấp</span>
                                    @endswitch
                                </td>
                                <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $notification->sender->full_name ?? 'N/A' }}</td>
                                <td>
                                    {{-- Giả định có trường 'status' trong model Notification --}}
                                    @if($notification->status === 'sent')
                                        <span class="badge bg-primary-color">Đã gửi</span>
                                    @elseif($notification->status === 'draft')
                                        <span class="badge bg-primary-color">Bản nháp</span>
                                    @else
                                        <span class="badge bg-primary-color">Đang xử lý</span>
                                    @endif
                                </td>
                                <td class="text-end text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('notifications.preview', $notification->id) }}">Xem</a>
                                            </li>
                                            {{-- Các nút thao tác khác nếu cần (ví dụ: Sửa, Xóa) --}}
                                            {{-- @if($notification->status === 'draft')
                                            <li>
                                                <a class="dropdown-item px-3 py-2"
                                                   href="{{ route('notifications.edit', $notification->id) }}">Sửa</a>
                                            </li>
                                            @endif --}}
                                            {{-- <li>
                                                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item px-3 py-2 text-danger">Xóa</button>
                                                </form>
                                            </li> --}}
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Không có thông báo nào.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Phân trang --}}
            @if($notifications->lastPage() > 1)
                <div class="card-footer border-0 bg-transparent" id="pagination-container">
                    <nav aria-label="page navigation">
                        {{ $notifications->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
@endsection
