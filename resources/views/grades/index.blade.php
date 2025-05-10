@extends('layouts.app')

@section('content')
    <style>
        .container-fluid {
            background: white;
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
    </style>
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn btn-back me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0">Quản lý điểm học sinh</h4>
        </div>
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('grades.index') }}" id="filter-form">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="academic_year_id">Năm học *</label>
                                <select class="form-control" id="academic_year_id" name="academic_year_id" required>
                                    <option value="" disabled>-- Chọn năm học --</option>
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

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="class_id">Lớp học *</label>
                                <select class="form-control" id="class_id" name="class_id" required>
                                    <option value="" disabled>-- Chọn lớp --</option>
                                    @foreach($assignedClasses as $classId => $assignments)
                                        @php $class = $assignments->first()->class; @endphp
                                        <option
                                            value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="semester_id">Học kỳ *</label>
                                <select class="form-control" id="semester_id" name="semester_id" required>
                                    <option value="" disabled>-- Chọn học kỳ --</option>
                                    @foreach($semesters as $semester)
                                        <option
                                            value="{{ $semester->id }}" {{ $selectedSemesterId == $semester->id ? 'selected' : '' }}>
                                            {{ $semester->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="d-flex justify-content-end mt-3">
                        @if($selectedAcademicYearId && $selectedClassId && $selectedSemesterId)
                            <a href="{{ route('grades.exportTemplate', [
            'academic_year_id' => $selectedAcademicYearId,
            'class_id' => $selectedClassId,
            'semester_id' => $selectedSemesterId
        ]) }}" class="btn btn-success" id="btn-download">
                                <i class="fas fa-download"></i> Tải file mẫu
                            </a>
                        @else
                            <button class="btn btn-success" disabled
                                    title="Vui lòng chọn đầy đủ năm học, lớp và học kỳ">
                                <i class="fas fa-download"></i> Tải file mẫu
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div id="grades-container">
            @if($selectedClassId && $selectedSemesterId)
                <!-- Phần hiển thị danh sách điểm -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Danh sách điểm học sinh</h6>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-upload"></i> Import điểm
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable">
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
                                                $fifteenMinutes = $subjectGrades['fifteen_minutes'] ?? collect();
                                                $onePeriod = $subjectGrades['one_period'] ?? collect();
                                                $semester = $subjectGrades['semester'] ?? collect();
                                                $subjectAverage = $subjectGrades['average'] ?? null;

                                                // Hiển thị điểm
                                                $avgFifteen = $fifteenMinutes->isNotEmpty()
                                                    ? round($fifteenMinutes->avg('score'), 1)
                                                    : '';
                                                $avgOnePeriod = $onePeriod->isNotEmpty()
                                                    ? round($onePeriod->avg('score'), 1)
                                                    : '';
                                                $semesterScore = $semester->isNotEmpty()
                                                    ? round($semester->first()->score, 1)
                                                    : '';
                                            @endphp

                                            <td>{{ $avgFifteen }}</td>
                                            <td>{{ $avgOnePeriod }}</td>
                                            <td>{{ $semesterScore }}</td>
                                        @endforeach

                                        <td>
                                            {{ $grades[$student->id]['semester_average'] ?? '' }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
        </div>
        @endif
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="importModalLabel">Modal title</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="fileInput" name="file"
                                   accept=".xlsx,.xls" required>
                        </div>
                        <small class="form-text text-muted">
                            Chỉ chấp nhận file Excel (.xlsx, .xls) theo mẫu đã tải về
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary">Lưu</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Kiểm tra và cập nhật trạng thái nút tải file
        function updateDownloadButton() {
            const academicYear = $('#academic_year_id').val();
            const classId = $('#class_id').val();
            const semesterId = $('#semester_id').val();

            if (academicYear && classId && semesterId) {
                $('#btn-download').prop('disabled', false).removeAttr('title');

                // Cập nhật link download
                const downloadUrl = "{{ route('grades.exportTemplate') }}" +
                    `?academic_year_id=${academicYear}&class_id=${classId}&semester_id=${semesterId}`;
                $('#btn-download').attr('href', downloadUrl);
            } else {
                $('#btn-download').prop('disabled', true)
                    .attr('title', 'Vui lòng chọn đầy đủ năm học, lớp và học kỳ');
            }
        }

        // Gọi khi trang load
        updateDownloadButton();

        $(document).ready(function(){
            $("#academic_year_id, #class_id, #semester_id").on("change", () => {
                updateDownloadButton();
                if ($('#academic_year_id').val() && $('#class_id').val() && $('#semester_id').val()) {
                    $('#filter-form').submit();
                }
            })

            // Xử lý khi năm học thay đổi
            $('#academic_year_id').change(function () {
                var academicYearId = $(this).val();

                if (!academicYearId) {
                    $('#class_id').empty().append('<option value="" >-- Chọn lớp --</option>');
                    $('#semester_id').empty().append('<option value="" >-- Chọn học kỳ --</option>');
                    return;
                }

                $.ajax({
                    url: '/grades/get-classes-by-year',
                    method: 'GET',
                    data: {academic_year_id: academicYearId},
                    success: function (data) {
                        var $classSelect = $('#class_id').empty().append('<option value="" >-- Chọn lớp --</option>');

                        if (data && data.length > 0) {
                            $(data).each(function (index, value) {
                                let html = $('<option></option>').attr('value', value.id).text(value.name);
                                $classSelect.append(html);
                            });
                        }

                    }
                });

                // Load danh sách học kỳ
                $.ajax({
                    url: '/grades/get-semesters-by-year',
                    method: 'GET',
                    data: {academic_year_id: academicYearId},
                    success: function (data) {
                        var $semesterSelect = $('#semester_id').empty().append('<option value="" >-- Chọn học kỳ --</option>');

                        if (data && data.length > 0) {
                            $(data).each(function (index, value) {
                                let html = ($('<option></option>').attr('value', value.id).text(value.name));
                                $semesterSelect.append(html);
                            });
                        }
                    }
                });
            });

            // Xử lý hiển thị tên file khi chọn (di chuyển ra ngoài sự kiện change năm học)
            $('#fileInput').on('change', function () {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            //
            // // Reset form khi modal đóng
            // $('#importModal').on('hidden.bs.modal', function () {
            //     $('#importForm')[0].reset();
            //     $('#fileInput').next('.custom-file-label').removeClass("selected").html('Chọn file Excel');
            // });
            // Thêm vào file scripts
            $('#importModal').on('click', '.btn-primary', function() {
                const fileInput = $('#fileInput')[0];
                const file = fileInput.files[0];

                if (!file) {
                    alert('Vui lòng chọn file Excel để import');
                    return;
                }

                // Hiển thị loading
                $('.loading-spinner').show();

                const formData = new FormData();
                formData.append('grades_file', file);
                formData.append('class_id', $('#class_id').val());
                formData.append('semester_id', $('#semester_id').val());
                formData.append('academic_year_id', $('#academic_year_id').val());
                formData.append('subject_name', '{{ $subjectsTaught->first()?->name }}');
                formData.append('class_name', $('#class_id option:selected').text()); // Thêm tên lớp để kiểm tra

                formData.append("_token", window.csrfToken);

                $.ajax({
                    url: '{{ route("grades.import") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        alert("Hello");
                        $('#importModal').modal('hide');
                        // Reload lại bảng điểm sau khi import thành công
                        // $('#filter-form').submit();
                    },
                    error: function(xhr) {
                        alert('Lỗi khi import: ' + xhr.responseJSON.message);
                    },
                    complete: function() {
                        $('.loading-spinner').hide();
                    }
                });
            });
        })
    </script>
@endpush
