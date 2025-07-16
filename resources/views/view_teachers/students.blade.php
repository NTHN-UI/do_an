@extends('layouts.app')

@section('title', 'Danh sách giáo viên')

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

        .year-select-form {
            min-width: 200px;
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
                <form method="GET" action="{{ route('student.teachers') }}" class="mb-0 year-select-form">
                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ ($selectedYearId ?? '') == $year->id ? 'selected' : '' }}>
                                {{ $year->year }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>


        <!-- Bảng danh sách giáo viên -->
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden">
                        <thead class="table-secondary text-center">
                        <tr>
                            <th class = "ps-4">STT</th>
                            <th>Vai trò</th>
                            <th>Môn học</th>
                            <th>Giáo viên</th>
                            <th class="text-center">Liên hệ</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!-- Giáo viên chủ nhiệm -->
                        @if($homeroomTeacher)
                            <tr class="text-center">
                                <td>1</td>
                                <td>
                                    <span class="badge bg-primary-color">
                                        Chủ nhiệm
                                    </span>
                                </td>
                                <td>{{ $homeroomTeacher->subject->name }}</td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="text-start">
                                            <div class="fw-bold">{{ $homeroomTeacher->teacher->full_name }}</div>
                                            <small class="text-muted">{{ $homeroomTeacher->teacher->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            @if($homeroomTeacher->teacher->phone)
                                                <a href="tel:{{ $homeroomTeacher->teacher->phone }}"
                                                   class="text-primary-color"
                                                   title="Gọi điện">
                                                    <i class="fas fa-phone"></i>
                                                </a>
                                            @endif
                                            <a href="mailto:{{ $homeroomTeacher->teacher->email }}"
                                               class="text-primary-color"
                                               title="Gửi email">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        <!-- Giáo viên bộ môn -->
                        @forelse($subjectTeachers as $index => $teacher)
                            <tr class="text-center">
                                <td>{{ $index + ($homeroomTeacher ? 2 : 1) }}</td>
                                <td>
                                    <span class="badge bg-primary-color">
                                        Bộ môn
                                    </span>
                                </td>
                                <td>{{ $teacher->subject->name }}</td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">

                                        <div class="text-start">
                                            <div class="fw-bold">{{ $teacher->teacher->full_name }}</div>
                                            <small class="text-muted">{{ $teacher->teacher->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
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
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Lớp chưa có giáo viên bộ môn
                                </td>
                            </tr>
                        @endforelse

                        @if(!$homeroomTeacher && $subjectTeachers->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Lớp chưa có giáo viên nào
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
