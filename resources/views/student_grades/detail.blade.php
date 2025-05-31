@extends('layouts.app')

@section('title', 'Chi tiết điểm học kỳ')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Chi tiết điểm học kỳ {{ $semester->name }} - Năm học {{ $academicYear->name }}</h2>            <a href="{{ route('student_grades') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        @foreach($grades as $subjectId => $subjectGrades)
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5>{{ $subjectGrades->first()->first()->subject->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Điểm 15 phút:</h6>
                            <ul>
                                @foreach($subjectGrades['fifteen_minutes'] ?? [] as $grade)
                                    <li>Lần {{ $loop->iteration }}:
                                        @if($grade->text_value)
                                            {{ $grade->text_value }}
                                        @else
                                            {{ $grade->score }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Điểm 1 tiết:</h6>
                            @if(isset($subjectGrades['one_period']))
                                <p>
                                    @if($subjectGrades['one_period'][0]->text_value)
                                        {{ $subjectGrades['one_period'][0]->text_value }}
                                    @else
                                        {{ $subjectGrades['one_period'][0]->score }}
                                    @endif
                                </p>
                            @else
                                <p>Chưa có điểm</p>
                            @endif

                            <h6>Điểm cuối kỳ:</h6>
                            @if(isset($subjectGrades['semester']))
                                <p>
                                    @if($subjectGrades['semester'][0]->text_value)
                                        {{ $subjectGrades['semester'][0]->text_value }}
                                    @else
                                        {{ $subjectGrades['semester'][0]->score }}
                                    @endif
                                </p>
                            @else
                                <p>Chưa có điểm</p>
                            @endif
                        </div>
                    </div>

                    <div class="alert alert-secondary mt-3">
                        <strong>Điểm trung bình môn:</strong>
                        {{ $subjectAverages[$subjectId] ?? 'Chưa tính được' }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
