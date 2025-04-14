@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Chi tiết Khối {{ $gradeLevel->grade_number }}</h4>
                        <a href="{{ route('grade_levels.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-layer-group"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tổng số lớp</span>
                                        <span class="info-box-number">{{ $gradeLevel->classes->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-calendar-alt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Năm học hiện tại</span>
                                        <span class="info-box-number">{{ $currentYear->year ?? '--' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion" id="classesAccordion">
                            @foreach($groupedClasses as $yearId => $classes)
                                @php $academicYear = $classes->first()->academicYear; @endphp
                                <div class="card">
                                    <div class="card-header" id="heading{{ $yearId }}">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link" type="button" data-toggle="collapse"
                                                    data-target="#collapse{{ $yearId }}"
                                                    aria-expanded="true" aria-controls="collapse{{ $yearId }}">
                                                {{ $academicYear->year }} ({{ $classes->count() }} lớp)
                                            </button>
                                        </h2>
                                    </div>

                                    <div id="collapse{{ $yearId }}" class="collapse show"
                                         aria-labelledby="heading{{ $yearId }}" data-parent="#classesAccordion">
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($classes as $class)
                                                    <div class="col-md-3 mb-3">
                                                        <div class="class-card p-3 border rounded text-center">
                                                            <h5>{{ $class->name }}</h5>
                                                            <div class="text-muted small">
                                                                Sĩ số: {{ $class->students_count ?? 0 }}
                                                            </div>
                                                            <a href="{{ route('classes.show', $class->id) }}"
                                                               class="btn btn-sm btn-outline-primary mt-2">
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

                        <div class="mt-4">
                            <a href="{{ route('classes.create', ['grade_level_id' => $gradeLevel->id]) }}"
                               class="btn btn-primary">
                                <i class="fas fa-plus"></i> Thêm lớp mới
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .info-box {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
        }
        .class-card {
            transition: all 0.3s;
        }
        .class-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
@endsection
