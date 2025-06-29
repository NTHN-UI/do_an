@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        <div class="d-flex align-items-center mb-4">

            <h3 class="mb-0 text-primary-color">Xem thông tin năm học</h3>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-12">
                    <dl class="row">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">{{ $academicYear->id }}</dd>

                        <dt class="col-sm-4">Năm học:</dt>
                        <dd class="col-sm-8">{{ $academicYear->year }}</dd>

                        <dt class="col-sm-4">Ngày bắt đầu:</dt>
                        <dd class="col-sm-8">{{ $academicYear->start_date->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4">Ngày kết thúc:</dt>
                        <dd class="col-sm-8">{{ $academicYear->end_date->format('d/m/Y') }}</dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                <a href="{{ route('academic_years.index') }}" class="btn btn-primary-color me-2">
                    Đóng
                </a>
            </div>
        </div>
    </div>
@endsection
