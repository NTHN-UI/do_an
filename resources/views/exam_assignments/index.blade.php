@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4>Danh sách đề thi đã giao</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đề thi</th>
                            <th>Môn học</th>
                            <th>Lớp</th>
                            <th>Thời gian</th>
                            <th>Hành động</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->id }}</td>
                                <td>{{ $assignment->exam->title }}</td>
                                <td>{{ $assignment->exam->subject->name }}</td>
                                <td>{{ $assignment->class->name }}</td>
                                <td>
                                    {{ $assignment->start_time->format('d/m/Y H:i') }} -
                                    {{ $assignment->end_time->format('d/m/Y H:i') }}
                                </td>
{{--                                <td>--}}
{{--                                    <a href="{{ route('exam-assignments.edit', $assignment->id) }}"--}}
{{--                                       class="btn btn-sm btn-primary">--}}
{{--                                        <i class="fas fa-edit"></i>--}}
{{--                                    </a>--}}
{{--                                </td>--}}
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $assignments->links() }}
            </div>
        </div>
    </div>
@endsection
