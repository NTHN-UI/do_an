@php use App\Models\Grade; @endphp

@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>Quản lý điểm - {{ $currentSemester->name }}
                </h4>
            </div>

            <div class="card-body">
                @if($assignments->isEmpty())
                    <div class="alert alert-info">Bạn chưa được phân công giảng dạy môn nào trong học kỳ này</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>Môn học</th>
                                <th>Lớp</th>
                                <th>Sĩ số</th>
                                <th>Đã nhập điểm</th>
                                <th>Thao tác</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($assignments as $subjectId => $subjectAssignments)
                                @foreach($subjectAssignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->subject->name }}</td>
                                        <td>{{ $assignment->class->name }}</td>
                                        <td>{{ $assignment->class->students->count() }}</td>
                                        <td>
                                            @php
                                                $gradedCount = Grade::where('class_id', $assignment->class_id)
                                                    ->where('subject_id', $assignment->subject_id)
                                                    ->where('semester_id', $currentSemester->id)
                                                    ->distinct('student_id')
                                                    ->count('student_id');
                                            @endphp
                                            {{ $gradedCount }}/{{ $assignment->class->students->count() }}
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('grades.create', [
                                                    'class' => $assignment->class,
                                                    'subject' => $assignment->subject,
                                                    'semester' => $currentSemester
                                                ]) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Nhập điểm
                                                </a>
                                                <a href="{{ route('grades.show', [
                                                    'class' => $assignment->class,
                                                    'subject' => $assignment->subject,
                                                    'semester' => $currentSemester
                                                ]) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-chart-bar"></i> Xem bảng điểm
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
