@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center mb-4">
                <h3 class="text-primary-color">Kết quả {{ $assignment->exam->title }} {{ $assignment->class->name }}</h3>

            </div>

        </div>

        <!-- Thống kê nhanh -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 bg-light p-3 text-center">
                    <h6 class="text-muted">Sĩ số lớp</h6>
                    <h4>{{ $assignment->class->students->count() }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light p-3 text-center">
                    <h6 class="text-muted">Đã làm bài</h6>
                    <h4>{{ $results->count() }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light p-3 text-center">
                    <h6 class="text-muted">Điểm TB</h6>
                    <h4>{{ number_format($results->avg('score'), 1) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light p-3 text-center">
                    <h6 class="text-muted">Tỉ lệ hoàn thành</h6>
                    <h4>{{ round($results->count()/$assignment->class->students->count()*100) }}%</h4>
                </div>
            </div>
        </div>

        <!-- Bảng kết quả -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>STT</th>
                        <th>Học sinh</th>
                        <th class="text-center">Điểm</th>
                        <th class="text-center">Thời gian</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($students as $index => $student)
                        @php
                            $result = $student->examResults->first();
                            $score = $result ? number_format($result->score, 1) : '0';
                            $statusText = $result ? 'Đã hoàn thành' : 'Chưa làm';
                            $statusClass = $result ? 'bg-primary-color' : 'bg-secondary';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $student->full_name ?? $student->name }}</td>
                            <td class="text-center font-weight-bold">
                                {{ $score }}/{{ $assignment->exam->total_marks }}
                            </td>
                            <td class="text-center">
                                {{ $result ? gmdate("i:s", $result->time_taken) : '-' }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $statusClass }} text-white px-3 py-1">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($result)
                                    <a href="{{ route('student_exams.result', ['assignment' => $assignment->id, 'student' => $student->id]) }}"
                                       class="text-primary-color">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
