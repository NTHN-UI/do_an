@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <h4 class="mb-0">Quản lý điểm lớp chủ nhiệm</h4>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('grades.homeroom') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="academic_year_id">Năm học</label>
                                <select class="form-control" id="academic_year_id" name="academic_year_id">
                                    @foreach($homeroomClasses->unique('academic_year_id') as $assignment)
                                        <option value="{{ $assignment->academic_year_id }}"
                                            {{ $selectedAcademicYearId == $assignment->academic_year_id ? 'selected' : '' }}>
                                            {{ $assignment->academicYear->year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="class_id">Lớp chủ nhiệm</label>
                                <select class="form-control" id="class_id" name="class_id">
                                    @foreach($homeroomClasses->where('academic_year_id', $selectedAcademicYearId) as $assignment)
                                        <option value="{{ $assignment->class_id }}"
                                            {{ $selectedClassId == $assignment->class_id ? 'selected' : '' }}>
                                            {{ $assignment->class->name }} ({{ $assignment->class->gradeLevel->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Lọc dữ liệu
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($students->isNotEmpty())
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Bảng điểm lớp {{ $class->name }} - Năm học {{ $homeroomClasses->firstWhere('academic_year_id', $selectedAcademicYearId)->academicYear->year }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th rowspan="2">Mã HS</th>
                                <th rowspan="2">Họ và tên</th>
                                @foreach($subjects as $subject)
                                    <th colspan="2" class="text-center">{{ $subject->name }}</th>
                                @endforeach
                                <th colspan="3" class="text-center">Tổng hợp</th>
                            </tr>
                            <tr>
                                @foreach($subjects as $subject)
                                    <th>HK1</th>
                                    <th>HK2</th>
                                @endforeach
                                <th>ĐTB HK1</th>
                                <th>ĐTB HK2</th>
                                <th>ĐTB CN</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($students as $index => $student)
                                @php
                                    $result = $studentResults[$student->id] ?? [];
                                @endphp
                                <tr>
                                    <td>{{ $student -> id }}</td>
                                    <td>{{ $student->full_name }}</td>

                                    @foreach($subjects as $subject)
                                        <td>{{ $result['semester1']['subjects'][$subject->id] ?? '' }}</td>
                                        <td>{{ $result['semester2']['subjects'][$subject->id] ?? '' }}</td>
                                    @endforeach

                                    <td class="font-weight-bold">
                                        {{ $result['semester1']['average'] ?? '' }}
                                        @if(isset($result['semester1']['classification']))
                                            <br><small class="text-muted">({{ $result['semester1']['classification'] }})</small>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold">
                                        {{ $result['semester2']['average'] ?? '' }}
                                        @if(isset($result['semester2']['classification']))
                                            <br><small class="text-muted">({{ $result['semester2']['classification'] }})</small>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold">
                                        {{ $result['yearly']['average'] ?? '' }}
                                        @if(isset($result['yearly']['classification']))
                                            <br><small class="text-muted">({{ $result['yearly']['classification'] }})</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                Không có học sinh nào trong lớp này hoặc chưa có dữ liệu điểm.
            </div>
        @endif
    </div>
@endsection
