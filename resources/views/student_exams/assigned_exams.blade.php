@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <h3 class="mb-3 text-primary-color">Danh sách đề thi được giao</h3>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                @if($assignments->isEmpty())
                    <div class="alert alert-info border-info rounded-2 shadow-sm m-3" role="alert">
                        Hiện tại không có đề thi nào được giao cho bạn.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                            <thead class="table-secondary text-center">
                            <tr>
                                <th>STT</th>
                                <th>Tên đề thi</th>
                                <th>Môn học</th>
                                <th>Lớp</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($assignments as $index => $assignment)
                                <tr class="text-center">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $assignment->exam->title }}</td>
                                    <td>{{ $assignment->exam->subject->name }}</td>
                                    <td>{{ $assignment->class->name }}</td>
                                    <td>
                                        {{ $assignment->start_time->format('d/m/Y H:i') }} -
                                        {{ $assignment->end_time->format('d/m/Y H:i') }}
                                    </td>
                                    <td class = "text-center">
                                        @php
                                            $now = now();
                                            $start = $assignment->start_time;
                                            $end = $assignment->end_time;
                                        @endphp
                                        @if($now->lt($start))
                                            <span class="badge bg-primary-color fw-normal">Chưa mở</span>
                                            <div class="small text-muted mt-1">
                                                Mở sau: {{ $start->diffForHumans() }}
                                            </div>
                                        @elseif($now->gt($end))
                                            <span class="badge bg-primary-color fw-normal">Đã hết hạn</span>
                                            <div class="small text-muted mt-1">
                                                Đã hết hạn: {{ $end->diffForHumans() }}
                                            </div>
                                        @else
                                            <span class="badge bg-primary-color fw-normal">Đang mở</span>
                                            <div class="small text-muted mt-1">
                                                Kết thúc: {{ $end->diffForHumans() }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class = "text-center">
                                        @php
                                            $now = now();
                                            $start = $assignment->start_time;
                                            $end = $assignment->end_time;
                                            $hasResult = $assignment->results()->where('student_id', auth()->id())->exists();
                                        @endphp

                                        @if($now->between($start, $end))
                                            @if($hasResult)
                                                <a href="{{ route('student_exams.result', $assignment->id) }}"
                                                   class="btn btn-sm btn-primary-color">
                                                    Xem kết quả
                                                </a>
                                            @else
                                                <a href="{{ route('student_exams.show', $assignment->id) }}"
                                                   class="btn btn-sm btn-primary-color"> Làm bài
                                                </a>
                                            @endif
                                        @elseif($now->lt($start))
                                            <span class="text-muted">Chưa đến giờ làm bài</span>
                                        @elseif($now->gt($end))
                                            @if($hasResult)
                                                <a href="{{ route('student_exams.result', $assignment->id) }}"
                                                   class="btn btn-sm btn-outline-primary-color">
                                                    Xem lại
                                                </a>
                                            @else
                                                <span class="badge bg-danger fw-normal">Hết thời hạn làm bài</span>
                                            @endif
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
