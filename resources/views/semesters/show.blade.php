@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0 text-primary-color">Xem thông tin học kỳ</h4>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-12">
                    <dl class="row">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">{{ $semester->id }}</dd>

                        <dt class="col-sm-4">Tên học kỳ:</dt>
                        <dd class="col-sm-8">{{ $semester->name }}</dd>

                        <dt class="col-sm-4">Năm học:</dt>
                        <dd class="col-sm-8">{{ $semester->academicYear->year }}</dd>

                        <dt class="col-sm-4">Ngày bắt đầu:</dt>
                        <dd class="col-sm-8">{{ $semester->start_date->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4">Ngày kết thúc:</dt>
                        <dd class="col-sm-8">{{ $semester->end_date->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4">Học kỳ hiện tại:</dt>
                        <dd class="col-sm-8">{{ $semester->is_current ? '✓' : '✗' }}</dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                <a href="{{ route('semesters.index', $semester->id) }}" class="btn btn-primary-color">
                   Đóng
                </a>
            </div>
        </div>
    </div>
@endsection
