@extends('layouts.app')

@section('content')
    <style>
        .table-responsive .dropdown-menu {
            position: fixed !important;
            z-index: 1000 !important;
            min-width: 90px;
        }
        .dropdown-item:active,
        .dropdown-item:focus {
            background-color: #013066 !important;
            color: white !important;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: var(--bs-white);
            border-color: var(--primary-color);
        }
    </style>
    <div class="container rounded-3 shadow p-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
            <i class="fas fa-arrow-left"></i>
        </a>

            <h3 class="text-primary-color">Danh sách học sinh {{ $class->name }} - Năm học {{ $academicYear->year }}</h3>
        </div>
        <div class="mb-3 d-flex justify-content-end align-items-center">
            <button id="advanceClassButton" class="btn btn-primary-color" disabled
                    style="background-color: var(--primary-color); color: var(--bs-white); border-color: var(--primary-color);">
                Chuyển lớp (lên khối)
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="advanceClassSpinner"></span>
            </button>
        </div>
        <div class="card">

            <div class="card border-0 shadow-sm rounded-2">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                            <thead class="table-secondary">
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
                                <span class="badge {{ $student->is_active ? 'bg-primary-color' : 'bg-secondary' }}">
                                    {{ $student->is_active ? 'Hoạt động' : 'Ngừng' }}
                                </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                                            <li>
                                                <a href="{{ route('students.show', $student) }}"
                                                   class="btn btn-sm text-primary-color">
                                                    Xem
                                                </a>
                                            </li>
                                            <li>
                                                <button class="btn text-primary-color btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#changeClassStudentModal"
                                                        data-student-id="{{ $student->id }}"
                                                        data-student-name="{{ $student->full_name }}">
                                                   Chuyển lớp
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
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
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function () {
            const changeClassStudentModal = $('#changeClassStudentModal');
            const advanceClassButton = $("#advanceClassButton"); // The button for bulk advance
            const advanceClassSpinner = $("#advanceClassSpinner"); // The spinner within the button
            const checkedAllElement = $("#checkedAll");
            const checkboxes = $('.student-checkbox');


            // Function to update the disabled state of the "Chuyển lớp (lên khối)" button
            function updateAdvanceClassButtonState() {
                const checkedCount = checkboxes.filter(':checked').length;
                advanceClassButton.prop('disabled', checkedCount === 0);
            }

            // Initial call to set button state on page load
            updateAdvanceClassButtonState();

            // Handle "checkedAll" checkbox click
            checkedAllElement.on('click', function () {
                const isChecked = $(this).is(':checked');
                checkboxes.prop('checked', isChecked);
                updateAdvanceClassButtonState();
            });

            // Handle individual student checkbox changes
            checkboxes.on('change', function() {
                // If any checkbox is unchecked, uncheck "checkedAll"
                if (!$(this).is(':checked')) {
                    checkedAllElement.prop('checked', false);
                } else {
                    // If all checkboxes are checked, check "checkedAll"
                    if (checkboxes.filter(':checked').length === checkboxes.length) {
                        checkedAllElement.prop('checked', true);
                    }
                }
                updateAdvanceClassButtonState();
            });

            // Handle click on "Chuyển lớp (lên khối)" button
            advanceClassButton.on('click', function () {
                let selectedIds = [];
                checkboxes.each(function () {
                    if ($(this).prop('checked')) {
                        selectedIds.push($(this).val());
                    }
                });


                // Confirm with the user before proceeding
                if (!confirm(`Bạn có chắc chắn muốn chuyển lớp ${selectedIds.length} học sinh đã chọn?`)) {
                    return;
                }

                // Show spinner and disable button
                advanceClassButton.prop('disabled', true);
                advanceClassSpinner.removeClass('d-none');

                // Send AJAX request
                $.ajax({
                    url: '{{ route('class_assignments.advance_class') }}', // This route needs to be defined
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        student_ids: selectedIds,
                        current_class_id: '{{ $class->id }}',
                        next_academic_year_id: '{{ $nextYear->id ?? '' }}' // Pass the next academic year ID
                    },
                    // Trong phần AJAX success
                    success: function (response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('Có lỗi xảy ra: ' + response.message);
                        }
                    },
                    error: function (xhr) {
                        let errorMessage = 'Đã xảy ra lỗi khi chuyển lớp.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                    },
                    complete: function () {
                        // Hide spinner and re-enable button (only if not reloading the page)
                        // If you use location.reload(), these lines might not be strictly necessary
                        // as the page will re-render anyway.
                        advanceClassSpinner.addClass('d-none');
                        // advanceClassButton.prop('disabled', false); // Re-enable only if not reloading
                        updateAdvanceClassButtonState(); // Ensure correct state after operation
                    }
                });
            });

            // Handle individual student class change modal
            changeClassStudentModal.on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const form = $(this).find("form");

                const studentId = button.data('student-id');
                const studentName = button.data('student-name');

                form.attr('action', `/class_assignments/change_class/${studentId}`);
                $('#student_id').val(studentId);
                $('#student_name_display').text(studentName);
            });
        });
    </script>
@endpush
