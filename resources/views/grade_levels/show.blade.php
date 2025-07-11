@extends('layouts.app')

@section('content')
    <style>
        .info-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 18px 16px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(1, 48, 102, 0.07);
            display: flex;
            align-items: center;
        }

        .info-box-icon {
            font-size: 2rem;
            margin-right: 16px;
            color: #fff;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-info {
            background: #0dcaf0 !important;
        }

        .bg-success {
            background: #198754 !important;
        }

        .class-card {
            transition: all 0.3s;
            background: #fff;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(1, 48, 102, 0.04);
        }

        .class-card:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 6px 16px rgba(1, 48, 102, 0.12);
        }
    </style>
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">

            <h3 class="mb-0 text-primary-color">Chi tiết Khối {{ $gradeLevel->grade_number }}</h3>
        </div>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="accordion" id="classesAccordion">
                    @foreach($groupedClasses as $yearId => $classes)
                        @php $academicYear = $classes->first()->academicYear; @endphp
                        <div class="card mb-2">
                            <div class="card-header" id="heading{{ $yearId }}">
                                <h2 class="mb-0">
                                    <button class="btn btn-link text-primary-color fw-bold"
                                            style="text-decoration: none;"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $yearId }}"
                                            aria-expanded="true" aria-controls="collapse{{ $yearId }}">
                                        {{ $academicYear->year }} ({{ $classes->count() }} lớp)
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse{{ $yearId }}" class="collapse show"
                                 aria-labelledby="heading{{ $yearId }}" data-bs-parent="#classesAccordion">
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($classes as $class)
                                            <div class="col-md-3 mb-3">
                                                <div class="class-card p-3 text-center">
                                                    <h5 class="fw-bold">{{ $class->name }}</h5>
                                                    <div class="text-muted small mb-2">
                                                        Sĩ số:{{ $class->students_count ?: '0' }}

                                                    </div>
                                                    <a href="{{ route('class_assignments.show', $class->id) }}"
                                                       class="btn btn-sm btn-primary-color mt-2">
                                                        Xem chi tiết
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
