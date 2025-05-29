@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Danh sách học sinh {{ $class->name }} - Năm học {{ $academicYear->year }}</h3>
        </div>

        <div class="card">
            <div class="card-header">
                <button id="advanceClassButton" class="btn btn-success" disabled data-bs-toggle="modal"
                        data-bs-target="#advanceClassModal">
                    <i class="fas fa-level-up-alt me-1"></i> Chuyển lớp (lên khối)
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>
                                <input class="form-check-input" type="checkbox" value="" id="checkedAll">
                            </th>
                            <th>Họ và tên</th>
                            <th>Ngày sinh</th>
                            <th>Giới tính</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>
                                    <input class="form-check-input student-checkbox" type="checkbox"
                                           value="{{ $student->id }}">
                                </td>
                                <td>{{ $student->full_name }}</td>
                                <td>{{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $student->gender ?? 'N/A' }}</td>
                                <td>
                                <span class="badge {{ $student->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $student->is_active ? 'Hoạt động' : 'Ngừng' }}
                                </span>
                                </td>
                                <td>
                                    <a href="{{ route('students.show', $student) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Xem
                                    </a>

                                    <button class="btn btn-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#changeClassStudentModal"
                                            data-student-id="{{ $student->id }}"
                                            data-student-name="{{ $student->full_name }}">
                                        <i class="fas fa-exchange-alt me-1"></i>Chuyển lớp
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if($students->lastPage() > 1)
                    <div class="card-footer border-0 bg-transparent" id="pagination-container">
                        <nav aria-label="page navigation">
                            {{ $students->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="changeClassStudentModal" tabindex="-1" aria-labelledby="changeClassStudentModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                    <input type="hidden" name="current_class_id" value="{{ $class->id }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="changeClassStudentModalLabel">Chuyển học sinh sang lớp khác</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="student_id" class="form-label">Học sinh</label>
                            <input type="hidden" name="user_id" id="student_id">
                            <div class="form-control" id="student_name_display">Vui lòng chọn học sinh</div>
                        </div>

                        <div class="mb-3">
                            <label for="new_class_id" class="form-label">Lớp đích</label>
                            <select name="new_class_id" id="new_class_id" class="form-select" required>
                                <option value="">-- Chọn lớp --</option>
                                @foreach($targetClasses as $targetClass)
                                    <option value="{{ $targetClass->id }}">{{ $targetClass->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Xác nhận chuyển</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="advanceClassModal" tabindex="-1" aria-labelledby="advanceClassModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="advanceClassForm" method="POST" action="{{ route('class_assignments.advance_class') }}">
                    @csrf
                    <input type="hidden" name="current_class_id" value="{{ $class->id }}">
                    <input type="hidden" name="selected_student_ids" id="selected_student_ids">

                    <div class="modal-header">
                        <h5 class="modal-title" id="advanceClassModalLabel">Chuyển lớp các học sinh đã chọn</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="target_academic_year" class="form-label">Năm học mới</label>
                            <input class="form-control" id="target_academic_year" name="target_academic_year"
                                   value="{{ $nextYear->year ?? "Chưa có" }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="target_class_id" class="form-label">Lớp đích</label>
                            <select name="target_class_id" id="target_class_id" class="form-select" required>
                                <option value="">-- Chọn lớp --</option>
                                @foreach($nextGradeClasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="quantity_preview" class="form-label">Tổng học sinh đã chọn</label>
                            <input class="form-control" id="quantity_preview" readonly>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Xác nhận chuyển lớp</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function () {
            const changeClassStudentModal = $('#changeClassStudentModal');

            const advanceClassButton = $("#advanceClassButton");

            const checkedAllElement = $("#checkedAll");
            const checkboxes = $('.student-checkbox');

            function updateBulkMoveButton() {
                const checkedCount = checkboxes.filter(':checked').length;
                advanceClassButton.prop('disabled', checkedCount === 0);
            }

            checkedAllElement.on('click', function () {
                const isChecked = $(this).is(':checked');

                checkboxes.prop('checked', isChecked);
                updateBulkMoveButton();
            });

            checkboxes.on('change', updateBulkMoveButton);

            advanceClassButton.on('click', function () {
                let selectedIds = [];
                checkboxes.each(function () {
                    if ($(this).prop('checked'))
                        selectedIds.push($(this).val());
                });

                $("#quantity_preview").val(selectedIds.length)

                if (selectedIds.length === 0) {
                    alert('Vui lòng chọn ít nhất một học sinh để chuyển lớp.');
                    $('#advanceClassModal').modal('hide');
                    return;
                }

                $('#selected_student_ids').val(selectedIds.join(','));
            });

            changeClassStudentModal.on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget); // Nút đã click
                const form = $(this).find("form");

                const studentId = button.data('student-id');
                const studentName = button.data('student-name');
                form.attr('action', `/class_assignments/change_class/${studentId}`);
                $('#student_id').val(studentId);
                $('#student_name_display').text(studentName);
            });

            $("#advanceClassForm").on("submit", function(event){
            })
        })
    </script>
@endpush
