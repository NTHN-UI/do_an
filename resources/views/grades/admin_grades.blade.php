@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-white rounded shadow-sm">
            <div class="d-flex align-items-center">
                <div>
                    <h3 class="mb-0 text-primary-color fw-bold">Quản lý điểm toàn trường</h3>
                    <small class="text-muted">Theo dõi và quản lý kết quả học tập toàn trường</small>
                </div>
            </div>
            <button class="btn btn-primary-color" onclick="exportToExcel()">
                <i class="fas fa-file-excel me-2"></i>Xuất Excel
            </button>
        </div>

        <!-- Filter Section -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('grades.admin_grades') }}" id="filter-form">
                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group mt-2">
                                <label for="academic_year_id" class="form-label fw-semibold">Năm học</label>
                                <select class="form-select border-2" id="academic_year_id" name="academic_year_id" required>
                                    <option value="">-- Chọn năm học --</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ $selectedAcademicYearId == $year->id ? 'selected' : '' }}>
                                            {{ $year->year }} ({{ $year->start_date->format('d/m/Y') }} - {{ $year->end_date->format('d/m/Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="form-group mt-2">
                                <label for="class_id" class="form-label fw-semibold">Lớp học</label>
                                <select class="form-select border-2" id="class_id" name="class_id" required>
                                    <option value="">-- Chọn lớp --</option>
                                    @if($selectedAcademicYearId)
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>{{ $class->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-12">
                            <div class="form-group mt-2">
                                <label for="semester_id" class="form-label fw-semibold">Học kỳ</label>
                                <select class="form-select border-2" id="semester_id" name="semester_id" required>
                                    <option value="">-- Chọn học kỳ --</option>
                                    @if($selectedAcademicYearId)
                                        @foreach($semesters as $semester)
                                            @if($semester->id !== 0) <!-- Bỏ qua option "Cả năm" nếu đã có trong controller -->
                                            <option value="{{ $semester->id }}" {{ $selectedSemesterId == $semester->id ? 'selected' : '' }}>
                                                {{ $semester->name }}
                                            </option>
                                            @endif
                                        @endforeach
                                        <option value="0" {{ $selectedSemesterId == 0 ? 'selected' : '' }}>Cả năm</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary-color px-3">Xem kết quả
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        <!-- Grades Table Section -->
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
                                            <th class="text-center">HK1</th>
                                            <th class="text-center">HK2</th>
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
                                                $isSpecialSubject = $subjectData['is_special'] ?? false;
                                            @endphp

                                            @if($selectedSemesterId == 0)
                                                <td class="text-center">
                                                    @if($isSpecialSubject)
                                                        {{ $subjectData['semester1_text'] ?? '-' }}
                                                    @else
                                                        {{ $subjectData['semester1_avg'] ?? '-' }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($isSpecialSubject)
                                                        {{ $subjectData['semester2_text'] ?? '-' }}
                                                    @else
                                                        {{ $subjectData['semester2_avg'] ?? '-' }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($isSpecialSubject)
                                                        {{ $subjectData['yearly_result'] ?? '-' }}
                                                    @else
                                                        {{ $subjectData['average'] ?? '-' }}
                                                    @endif
                                                </td>
                                            @else
                                                <td class="text-center">
                                                    {{ $subjectData['display_average'] ?? '-' }}
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
        function exportToExcel() {
            const academicYearId = document.getElementById('academic_year_id').value;
            const classId = document.getElementById('class_id').value;
            const semesterId = document.getElementById('semester_id').value;

            if (!academicYearId || !classId || !semesterId) {
                alert('Vui lòng chọn đầy đủ năm học, lớp và học kỳ trước khi xuất file');
                return;
            }

            const url = "{{ route('grades.admin_export') }}?" + new URLSearchParams({
                academic_year_id: academicYearId,
                class_id: classId,
                semester_id: semesterId
            });

            const link = document.createElement('a');
            link.href = url;
            link.download = 'Diem_toan_truong.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        // Trong file script của bạn, sửa lại phần AJAX như sau:
        $('#academic_year_id').on('change', function() {
            const yearId = $(this).val();
            const $classSelect = $('#class_id');
            const $semesterSelect = $('#semester_id');

            // Reset các dropdown
            $classSelect.empty().append('<option value="">-- Chọn lớp --</option>');
            $semesterSelect.empty().append('<option value="">-- Chọn học kỳ --</option>');

            if (yearId) {


                $.ajax({
                    url: '/grades/admin/get-classes-by-year-admin',
                    method: 'GET',
                    data: {
                        academic_year_id: yearId,
                        _token: '{{ csrf_token() }}' // Thêm CSRF token nếu cần
                    },
                    success: function(response) {
                        // Xóa spinner
                        $('#class-loading').remove();

                        // Cập nhật dropdown lớp học
                        if (response.classes && response.classes.length > 0) {
                            response.classes.forEach(function(classItem) {
                                $classSelect.append(
                                    `<option value="${classItem.id}">
                                ${classItem.name}
                            </option>`
                                );
                            });
                        }

                        // Cập nhật dropdown học kỳ
                        if (response.semesters && response.semesters.length > 0) {
                            response.semesters.forEach(function(semester) {
                                $semesterSelect.append(
                                    `<option value="${semester.id}">${semester.name}</option>`
                                );
                            });
                            // Thêm option "Cả năm"
                            $semesterSelect.append('<option value="0">Cả năm</option>');
                        }

                        // Kích hoạt lại các dropdown
                        $classSelect.prop('disabled', false);
                        $semesterSelect.prop('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        $('#class-loading').remove();
                        console.error("AJAX Error:", error);
                        alert('Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại.');
                        $classSelect.prop('disabled', false);
                        $semesterSelect.prop('disabled', false);
                    }
                });
            }
        });
    </script>
@endpush
