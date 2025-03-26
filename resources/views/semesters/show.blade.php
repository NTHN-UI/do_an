@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Chi tiết Học kỳ</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tên Học kỳ: {{ $semester->name }}</h5>
                <p class="card-text">Năm học: {{ $semester->academicYear->year ?? 'Chưa có năm học' }}</p>
                <a href="{{ route('semesters.index') }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </div>
    </div>
@endsection
