@extends('layouts.app')

@section('content')
    <div class="container-fluid rounded-3 shadow p-4">
            <div class="d-flex align-items-center">
                <div>
                    <h3 class="mb-0 text-primary-color fw-bold">Quản lý điểm toàn trường</h3>
                </div>
            </div>


            <div class="card shadow-sm border-0 mb-3 mt-3">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('grades.admin_grades') }}" id="filter-form">
                        <div class="row g-4">
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group mt-2">
                                    <label for="academic_year_id" class="form-label fw-semibold">Năm học</label>
                                    <select class="form-select border-2" id="academic_year_id" name="academic_year_id">
                                        <option value="">-- Chọn năm học --</option>
                                        @foreach($academicYears as $year)
                                            <option
                                                value="{{ $year->id }}" {{ $selectedAcademicYearId == $year->id ? 'selected' : '' }}>
                                                {{ $year->year }} ({{ $year->start_date->format('d/m/Y') }}
                                                - {{ $year->end_date->format('d/m/Y') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="form-group mt-2">
                                    <label for="class_id" class="form-label fw-semibold">Lớp học</label>
                                    <select class="form-select border-2" id="class_id" name="class_id">
                                        <option value="">-- Chọn lớp --</option>
                                        @if($selectedAcademicYearId)
                                            @foreach($classes as $class)
                                                <option
                                                    value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>{{ $class->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-12">
                                <div class="form-group mt-2">
                                    <label for="semester_id" class="form-label fw-semibold">Học kỳ</label>
                                    <select class="form-select border-2" id="semester_id" name="semester_id">
                                        <option value="">-- Chọn học kỳ --</option>
                                        @if($selectedAcademicYearId)
                                            @foreach($semesters as $semester)
                                                @if($semester->id !== 0)
                                                    <option
                                                        value="{{ $semester->id }}" {{ $selectedSemesterId == $semester->id ? 'selected' : '' }}>
                                                        {{ $semester->name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                            <option value="0" {{ $selectedSemesterId == 0 ? 'selected' : '' }}>Cả năm
                                            </option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" id="view-result-btn" class="btn btn-primary-color px-3" disabled>
                                Xem kết quả
                            </button>
                        </div>
                    </form>
                </div>
        </div>

    <div id="grades-container">
        @if($selectedClassId && $selectedSemesterId !== null)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light py-3">
                    <div>
                        <h5 class="mb-0 text-primary-color fw-bold">
                            Bảng điểm {{ $classDetails->gradeLevel->name }} - {{ $classDetails->name }}
                            @if($selectedSemesterId == 0)
                                (Cả năm)
                            @else
                                 ({{ $selectedSemester->name }})
                            @endif
                        </h5>
                        <small class="text-muted">Dữ liệu được cập nhật theo thời gian thực</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px;">
                        <table class="table table-hover table-striped mb-0" id="dataTable">
                            <thead class="bg-primary-color text-white sticky-top">
                            <tr>
                                <th rowspan="2" class="text-center align-middle">STT</th>
                                <th rowspan="2" class="text-center align-middle">Họ và tên</th>
                                @foreach($subjects as $subject)
                                    @if($selectedSemesterId == 0)
                                        <th colspan="3" class="text-center">{{ $subject->name }}</th>
                                    @else
                                        <th class="text-center">{{ $subject->name }}</th>
                                    @endif
                                @endforeach
                                <th rowspan="2" class="text-center align-middle">
                                    @if($selectedSemesterId == 0)
                                        Điểm TB cả năm
                                    @else
                                        Điểm TB HK
                                    @endif
                                </th>
                                <th rowspan="2" class="text-center align-middle">Xếp loại</th>
                            </tr>
                            @if($selectedSemesterId == 0)
                                <tr>
                                    @foreach($subjects as $subject)
                                        <th class="text-center">HKI</th>
                                        <th class="text-center">HKII</th>
                                        <th class="text-center">CN</th>
                                    @endforeach
                                </tr>
                            @endif
                            </thead>
                            <tbody>
                            @foreach($students as $index => $student)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="fw-semibold text-primary-color">{{ $student->full_name }}</td>

                                    @foreach($subjects as $subject)
                                        @php
                                            $subjectData = $grades[$student->id][$subject->id] ?? [];
                                            $isSpecialSubject = in_array($subject->name, [
                                                'Giáo dục quốc phòng và an ninh',
                                                'Giáo dục thể chất',
                                                'Nghệ thuật'
                                            ]);
                                        @endphp

                                        @if($selectedSemesterId == 0)
                                            <td class="text-center">
                                                @if($isSpecialSubject)
                                                    {{ $subjectData['semester1_text'] ?? 'Chưa đạt' }}
                                                @else
                                                    {{ $subjectData['semester1_avg'] ?? '-' }}
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($isSpecialSubject)
                                                    {{ $subjectData['semester2_text'] ?? 'Chưa đạt' }}
                                                @else
                                                    {{ $subjectData['semester2_avg'] ?? '-' }}
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($isSpecialSubject)
                                                    {{ $subjectData['yearly_result'] ?? 'Chưa đạt' }}
                                                @else
                                                    {{ $subjectData['average'] ?? '-' }}
                                                @endif
                                            </td>
                                        @else
                                            <td class="text-center">
                                                @if($isSpecialSubject)
                                                    {{ $subjectData['display_average'] ?? 'Chưa đạt' }}
                                                @else
                                                    {{ $subjectData['average'] ?? '-' }}
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach

                                    <td class="text-center fw-bold">
                                        @if($selectedSemesterId == 0)
                                            {{ $grades[$student->id]['yearly_average'] ?? '-' }}
                                        @else
                                            {{ $grades[$student->id]['semester_average'] ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $grades[$student->id]['classification'] ?? '-' }}
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
    </div>

@endsection
@push('scripts')
    <script>
        $(document).ready(function () {
            function checkFormValid() {
                const isValid = $('#academic_year_id').val() &&
                    $('#class_id').val() &&
                    $('#semester_id').val();

                $('#view-result-btn').prop('disabled', !isValid);
            }

            $('#academic_year_id, #class_id, #semester_id').change(function () {
                checkFormValid();
            });

            checkFormValid();

            $('#academic_year_id').change(function () {
                const yearId = $(this).val();
                const $classSelect = $('#class_id');
                const $semesterSelect = $('#semester_id');

                $classSelect.html('<option value="">-- Chọn lớp --</option>').prop('disabled', !yearId);
                $semesterSelect.html('<option value="">-- Chọn học kỳ --</option>').prop('disabled', !yearId);

                if (!yearId) return;

                $classSelect.after('<span id="class-loading" class="ms-2"><i class="fas fa-spinner fa-spin"></i></span>');

                $.ajax({
                    url: '/grades/admin/get-classes-by-year-admin',
                    method: 'GET',
                    data: {academic_year_id: yearId, _token: '{{ csrf_token() }}'},
                    success: function (response) {
                        $('#class-loading').remove();

                        if (response.classes?.length) {
                            response.classes.forEach(c => {
                                $classSelect.append(`<option value="${c.id}">${c.name}</option>`);
                            });
                        }

                        if (response.semesters?.length) {
                            response.semesters.forEach(s => {
                                $semesterSelect.append(`<option value="${s.id}">${s.name}</option>`);
                            });
                            $semesterSelect.append('<option value="0">Cả năm</option>');
                        }
                    },
                    error: function () {
                        $('#class-loading').remove();
                        alert('Có lỗi khi tải dữ liệu. Vui lòng thử lại.');
                    }
                });
            });
        });
    </script>
@endpush
