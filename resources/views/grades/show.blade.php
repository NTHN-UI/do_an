
@php use App\Models\Grade; @endphp
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Bảng điểm {{ $subject->name }} - Lớp {{ $class->name }} (HK{{ $semester->name }})
                </h4>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                        <tr>
                            <th rowspan="2" >STT</th>
                            <th rowspan="2" >Họ tên</th>
                            @foreach(Grade::TEST_TYPES as $type => $label)
                                <th class="text-center">{{ $label }}</th>
                            @endforeach
                            <th class="text-center">Trung bình</th>
                            <th class="text-center">Điểm chữ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($students as $index => $student)
                            @php
                                $studentGrades = $grades[$student->id] ?? [];
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $student->full_name }}</td>
                                @foreach(Grade::TEST_TYPES as $type => $label)
                                    <td class="text-center">
                                        {{ $studentGrades[$type][0]->score ?? '-' }}
                                    </td>
                                @endforeach
                                <td class="text-center fw-bold">
                                    {{ $averages[$student->id] ?? '-' }}
                                </td>
                                <td class="text-center">
                                    @if(isset($averages[$student->id]))
                                        @php
                                            $avg = $averages[$student->id];
                                            $letter = $avg >= 8.5 ? 'A' :
                                                     ($avg >= 7 ? 'B' :
                                                     ($avg >= 5.5 ? 'C' :
                                                     ($avg >= 4 ? 'D' : 'F')));
                                        @endphp
                                        <span class="badge bg-{{ $letter == 'F' ? 'danger' : 'success' }}">
                                            {{ $letter }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="{{ route('grades.create', [
                    'class' => $class,
                    'subject' => $subject,
                    'semester' => $semester
                ]) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa điểm
                    </a>
                    <button class="btn btn-success ms-2">
                        <i class="fas fa-file-export me-2"></i>Xuất bảng điểm
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
