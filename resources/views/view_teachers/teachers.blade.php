@extends('layouts.app')

@section('title', 'Giáo viên bộ môn')

@section('content')
    <style>
        .dropdown-menu {
            min-width: 80px;
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


        /* Thêm style mới cho form */
        .year-select-form {
            min-width: 200px;
        }

        .year-select {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 0.375rem 2.25rem 0.375rem 0.75rem;
            background-color: white;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .year-select:focus {
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>

    <div class="container rounded-3 shadow p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 text-primary-color fw-bold">
                @isset($class)
                    Giáo viên {{ $class->name }}
                @else
                    Giáo viên
                @endisset
            </h3>

            @if($academicYears->count() > 1)
                <form method="GET" action="{{ route('teacher.subject_teachers') }}" class="mb-0 year-select-form">
                    <select name="year" class="form-select form-select-sm year-select" onchange="this.form.submit()">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ ($selectedYearId ?? '') == $year->id ? 'selected' : '' }}>{{ $year->year }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        <!-- Phần bảng -->
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                @if($subjectTeachers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                            <thead class="table-light">
                            <tr>
                                <th class="ps-4 text-center">STT</th>
                                <th>Môn học</th>
                                <th>Giáo viên</th>
                                <th>Thông tin liên hệ</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($subjectTeachers as $index => $teacher)
                                <tr>
                                    <td class="fw-bold text-center">{{ $index + 1 }}</td>
                                    <td>
                                    <span class="badge bg-primary-color">
                                        {{ $teacher->subject->name }}
                                    </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-bold">{{ $teacher->teacher->full_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mb-1">
                                            <i class="fas fa-envelope me-2 text-primary-color"></i>
                                            {{ $teacher->teacher->email }}
                                        </div>
                                        <div>
                                            <i class="fas fa-phone me-2 text-primary-color"></i>
                                            {{ $teacher->teacher->phone ?? 'Chưa cập nhật' }}
                                        </div>
                                    </td>
                                    <td class= "text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            @if($teacher->teacher->phone)
                                                <a href="tel:{{ $teacher->teacher->phone }}"
                                                   class="text-primary-color"
                                                   title="Gọi điện">
                                                    <i class="fas fa-phone"></i>
                                                </a>
                                            @endif
                                            <a href="mailto:{{ $teacher->teacher->email }}"
                                               class="text-primary-color"
                                               title="Gửi email">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Hiện chưa có giáo viên bộ môn nào được phân công giảng dạy lớp này
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
