@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-2 text-gray-800">Điểm học sinh: {{ $student->full_name }}</h1>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Bảng điểm chi tiết</h6>
            </div>
            <div class="card-body">
                @foreach($grades as $semesterId => $semesterGrades)
                    <div class="mb-4">
                        <h5>Học kỳ {{ $semesterId }}</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Môn học</th>
                                    <th>Điểm 15 phút</th>
                                    <th>Điểm 1 tiết</th>
                                    <th>Điểm học kỳ</th>
                                    <th>Điểm TB môn</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($semesterGrades as $subjectId => $subjectGrades)
                                    <tr>
                                        <td>{{ $subjectGrades->first()->subject->name }}</td>
                                        <td>
                                            @foreach(($subjectGrades['fifteen_minutes'] ?? []) as $grade)
                                                {{ $grade->score }}<br>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach(($subjectGrades['one_period'] ?? []) as $grade)
                                                {{ $grade->score }}<br>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach(($subjectGrades['semester'] ?? []) as $grade)
                                                {{ $grade->score }}
                                            @endforeach
                                        </td>
                                        <td>
                                            {{ $subjectAverages[$semesterId][$subjectId] ?? '' }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <a href="{{ route('grades.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
@endsection
