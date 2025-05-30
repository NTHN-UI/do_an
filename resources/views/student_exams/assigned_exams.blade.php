@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Danh sách đề thi được giao</h4>
            </div>

            <div class="card-body">
                @if($assignments->isEmpty())
                    <div class="alert alert-info">
                        Hiện tại không có đề thi nào được giao cho bạn.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên đề thi</th>
                                <th>Môn học</th>
                                <th>Lớp</th>
                                <th>Thời gian làm bài</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($assignments as $index => $assignment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $assignment->exam->title }}</td>
                                    <td>{{ $assignment->exam->subject->name }}</td>
                                    <td>{{ $assignment->class->name }}</td>
                                    <td>
                                        {{ $assignment->start_time->format('d/m/Y H:i') }} -
                                        {{ $assignment->end_time->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
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
                                    <td>
                                        @php
                                            $now = now();
                                            $start = $assignment->start_time;
                                            $end = $assignment->end_time;
                                            $hasResult = $assignment->results()->where('student_id', auth()->id())->exists();
                                        @endphp

                                        @if($now->between($start, $end))
                                            @if($hasResult)
                                                <a href="{{ route('student_exams.result', $assignment->id) }}"
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Xem kết quả
                                                </a>
                                            @else
                                                <a href="{{ route('student_exams.show', $assignment->id) }}"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-pen"></i> Làm bài
                                                </a>
                                            @endif
                                        @elseif($now->gt($end) && $hasResult)
                                            <a href="{{ route('student_exams.result', $assignment->id) }}"
                                               class="btn btn-sm btn-secondary">
                                                <i class="fas fa-file-alt"></i> Xem lại
                                            </a>
                                        @else
                                            <span class="text-muted">Không có hành động</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
