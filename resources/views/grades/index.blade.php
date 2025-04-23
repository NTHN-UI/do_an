@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-2 text-gray-800">Quản lý điểm học sinh</h1>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lọc dữ liệu</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('grades.index') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="academic_year_id">Năm học</label>
                                <select class="form-control" id="academic_year_id" name="academic_year_id" required>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ $selectedAcademicYearId == $year->id ? 'selected' : '' }}>
                                            {{ $year->year }} ({{ $year->start_date->format('d/m/Y') }} - {{ $year->end_date->format('d/m/Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="class_id">Lớp học</label>
                                <select class="form-control" id="class_id" name="class_id" required>
                                    <option value="">-- Chọn lớp --</option>
                                    @foreach($assignedClasses as $classId => $assignments)
                                        @php $class = $assignments->first()->class; @endphp
                                        <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }} - {{ $class->gradeLevel->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="semester_id">Học kỳ</label>
                                <select class="form-control" id="semester_id" name="semester_id" required>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}" {{ $selectedSemesterId == $semester->id ? 'selected' : '' }}>
                                            {{ $semester->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Lọc dữ liệu</button>

                    @if($selectedClassId && $selectedSemesterId)
                        <a href="{{ route('grades.export', [
                        'class_id' => $selectedClassId,
                        'semester_id' => $selectedSemesterId,
                        'academic_year_id' => $selectedAcademicYearId
                    ]) }}" class="btn btn-success ml-2">
                            <i class="fas fa-download"></i> Tải file mẫu
                        </a>
                    @endif
                </form>
            </div>
        </div>

        @if($selectedClassId && $selectedSemesterId)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Danh sách điểm học sinh</h6>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importModal">
                        <i class="fas fa-upload"></i> Import điểm
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                            <tr>
                                <th rowspan="2">STT</th>
                                <th rowspan="2">Mã HS</th>
                                <th rowspan="2">Họ và tên</th>
                                @foreach($subjectsTaught as $subject)
                                    <th colspan="3" class="text-center">{{ $subject->name }}</th>
                                @endforeach
                                <th rowspan="2">Điểm TB HK</th>
                            </tr>
                            <tr>
                                @foreach($subjectsTaught as $subject)
                                    <th>15 phút</th>
                                    <th>1 tiết</th>
                                    <th>HK</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($students as $index => $student)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $student->student_code }}</td>
                                    <td>{{ $student->full_name }}</td>

                                    @foreach($subjectsTaught as $subject)
                                        @php
                                            $subjectGrades = $grades[$student->id][$subject->id] ?? [];
                                            $fifteenMinutes = $subjectGrades['fifteen_minutes'] ?? [];
                                            $onePeriod = $subjectGrades['one_period'] ?? [];
                                            $semester = $subjectGrades['semester'] ?? [];

                                            // Tính điểm trung bình
                                            $avgFifteen = count($fifteenMinutes) > 0 ? round(array_sum(array_column($fifteenMinutes->toArray(), 'score')) / count($fifteenMinutes), 1) : '';
                                            $avgOnePeriod = count($onePeriod) > 0 ? round(array_sum(array_column($onePeriod->toArray(), 'score')) / count($onePeriod), 1) : '';
                                            $semesterScore = count($semester) > 0 ? $semester->first()->score : '';

                                            // Tính điểm TB môn (nếu đủ dữ liệu)
                                            $subjectAverage = '';
                                            if ($avgFifteen !== '' && $avgOnePeriod !== '' && $semesterScore !== '') {
                                                $subjectAverage = round(($avgFifteen + $avgOnePeriod * 2 + $semesterScore * 3) / 6, 1);
                                            }
                                        @endphp

                                        <td>{{ $avgFifteen }}</td>
                                        <td>{{ $avgOnePeriod }}</td>
                                        <td>{{ $semesterScore }}</td>
                                    @endforeach

                                    <td>
                                        @php
                                            // Tính điểm TB học kỳ (trung bình các môn)
                                            $count = 0;
                                            $total = 0;

                                            foreach ($subjectsTaught as $subject) {
                                                $subjectGrades = $grades[$student->id][$subject->id] ?? [];
                                                $fifteenMinutes = $subjectGrades['fifteen_minutes'] ?? [];
                                                $onePeriod = $subjectGrades['one_period'] ?? [];
                                                $semester = $subjectGrades['semester'] ?? [];

                                                if (count($fifteenMinutes) > 0 && count($onePeriod) > 0 && count($semester) > 0) {
                                                    $avgFifteen = array_sum(array_column($fifteenMinutes->toArray(), 'score')) / count($fifteenMinutes);
                                                    $avgOnePeriod = array_sum(array_column($onePeriod->toArray(), 'score')) / count($onePeriod);
                                                    $semesterScore = $semester->first()->score;

                                                    $total += ($avgFifteen + $avgOnePeriod * 2 + $semesterScore * 3) / 6;
                                                    $count++;
                                                }
                                            }

                                            $semesterAverage = $count > 0 ? round($total / $count, 1) : '';
                                        @endphp
                                        {{ $semesterAverage }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import điểm từ file Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('grades.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                    <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">
                    <input type="hidden" name="academic_year_id" value="{{ $selectedAcademicYearId }}">

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="file">Chọn file Excel</label>
                            <input type="file" class="form-control-file" id="file" name="file" accept=".xlsx,.xls" required>
                            <small class="form-text text-muted">
                                Chỉ chấp nhận file Excel (.xlsx, .xls) theo mẫu đã tải về
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Import điểm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Xử lý thay đổi năm học
            $('#academic_year_id').change(function() {
                var academicYearId = $(this).val();

                // Reset các select box phụ thuộc
                $('#semester_id').empty().append('<option value="">-- Chọn học kỳ --</option>');
                $('#class_id').empty().append('<option value="">-- Chọn lớp --</option>');

                if (!academicYearId) return;

                // Lấy danh sách học kỳ theo năm học
                $.get('/api/get-semesters-by-year', { academic_year_id: academicYearId }, function(data) {
                    $.each(data, function(key, semester) {
                        $('#semester_id').append('<option value="'+semester.id+'">'+semester.name+'</option>');
                    });
                });

                // Lấy danh sách lớp học theo năm học
                $.get('/api/get-classes-by-year', { academic_year_id: academicYearId }, function(data) {
                    $.each(data, function(key, classObj) {
                        $('#class_id').append('<option value="'+classObj.id+'">'+classObj.name+' - '+classObj.grade_level.name+'</option>');
                    });
                });
            });
        });
    </script>
@endpush
