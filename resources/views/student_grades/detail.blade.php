@extends('layouts.app')

@section('title', 'Chi tiết điểm học kỳ')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">
                Chi tiết điểm {{ $semester->name }} - Năm học {{ $academicYear->year }}
            </h3>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th >Môn học</th>
                        <th>Điểm 15 phút</th>
                        <th class="text-center">Điểm 1 tiết</th>
                        <th class="text-center">Điểm cuối kỳ</th>
                        <th class="text-center">Kết quả</th>
                     </tr>
                    </thead>
                    <tbody>
                    @foreach($grades as $subjectId => $subjectGrades)
                        @php
                            $subject = $subjectGrades->first()->first()->subject;
                            $isSpecialSubject = in_array($subject->name, [
                                'Giáo dục quốc phòng và an ninh',
                                'Giáo dục thể chất',
                                'Nghệ thuật'
                            ]);
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $subject->name }}</td>
                            <td>
                                @if(isset($subjectGrades['fifteen_minutes']) && count($subjectGrades['fifteen_minutes']) > 0)
                                    <div class="d-flex gap-2">
                                        @foreach($subjectGrades['fifteen_minutes'] as $grade)
                                            @if($isSpecialSubject)
                                                <span >
                                                        {{ $grade->text_value ?? ($grade->score >= 5 ? 'Đạt' : 'Chưa đạt') }}
                                                    </span>
                                            @else
                                                <span >
                                                        {{ $grade->score }}
                                                    </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if(isset($subjectGrades['one_period']))
                                    @if($isSpecialSubject)
                                        <span >
                                                {{ $subjectGrades['one_period'][0]->text_value ?? ($subjectGrades['one_period'][0]->score >= 5 ? 'Đạt' : 'Chưa đạt') }}
                                            </span>
                                    @else
                                        <span >
                                                {{ $subjectGrades['one_period'][0]->score }}
                                            </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <!-- Điểm cuối kỳ -->
                            <td class="text-center">

                            @if(isset($subjectGrades['semester']))
                                    @if($isSpecialSubject)
                                        <span>
                                                {{ $subjectGrades['semester'][0]->text_value ?? ($subjectGrades['semester'][0]->score >= 5 ? 'Đạt' : 'Chưa đạt') }}
                                            </span>
                                    @else
                                        <span >
                                                {{ $subjectGrades['semester'][0]->score }}
                                            </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <!-- Kết quả -->
                            <td class="text-center">
                                @if(isset($subjectAverages[$subjectId]))
                                    @if($isSpecialSubject)
                                        @php
                                            $result = $subjectGrades['semester'][0]->text_value ??
                                                     ($subjectGrades['semester'][0]->score >= 5 ? 'Đạt' : 'Chưa đạt');
                                        @endphp
                                        <span>
                                                {{ $result }}
                                            </span>
                                    @else
                                        <div class="text-center">
                                                <span>
                                                    {{ number_format($subjectAverages[$subjectId], 1) }}
                                                </span>
                                        </div>
                                    @endif
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

    <style>
        .table th {
            border-top: none;
            border-bottom: 2px solid #e9ecef;
        }
        .table td {
            vertical-align: middle;
        }
        .badge.rounded-circle {
            min-width: 32px;
        }
    </style>
@endsection
