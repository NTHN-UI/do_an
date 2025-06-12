@extends('layouts.app')

@section('content')
    <style>
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: var(--bs-white);
            border-color: var(--primary-color);
        }
        .table-responsive .dropdown-menu {
            min-width: 90px;
        }
        .dropdown-item:active,
        .dropdown-item:focus {
            background-color: var(--primary-color) !important;
            color: white !important;
        }
    </style>
    <div class="container rounded-3 shadow p-4">
        <h3 class="mb-3 text-primary-color">Danh sách đề thi đã giao</h3>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th>ID</th>
                            <th>Tên đề thi</th>
                            <th>Môn học</th>
                            <th>Lớp</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($assignments as $assignment)
                            <tr class="text-center">
                                <td>{{ $assignment->id }}</td>
                                <td>{{ $assignment->exam->title }}</td>
                                <td>{{ $assignment->exam->subject->name }}</td>
                                <td>{{ $assignment->class->name }}</td>
                                <td>
                                    {{ $assignment->start_time->format('d/m/Y H:i') }} -
                                    {{ $assignment->end_time->format('d/m/Y H:i') }}
                                </td>
                                <td class="text-center">
                                    @php
                                        $now = now();
                                        $start = $assignment->start_time;
                                        $end = $assignment->end_time;
                                    @endphp
                                    @if($now->lt($start))
                                        <span class="badge bg-warning">Chưa mở</span>
                                        <div class="small text-muted">
                                            Mở sau: {{ $start->diffForHumans() }}
                                        </div>
                                    @elseif($now->gt($end))
                                        <span class="badge bg-danger">Đã hết hạn</span>
                                        <div class="small text-muted">
                                            Đã hết hạn: {{ $end->diffForHumans() }}
                                        </div>
                                    @else
                                        <span class="badge bg-success">Đang mở</span>
                                        <div class="small text-muted">
                                            Kết thúc: {{ $end->diffForHumans() }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Không có đề thi nào được giao</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($assignments->lastPage() > 1)
                    <div class="card-footer border-0 bg-transparent" id="pagination-container">
                        <nav aria-label="page navigation">
                            {{ $assignments->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
