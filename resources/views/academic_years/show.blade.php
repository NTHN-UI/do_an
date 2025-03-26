
@extends('layouts.app')

@section('content')
    <h2>Chi tiết Năm học</h2>
    <p><strong>ID:</strong> {{ $academicYear->id }}</p>
    <p><strong>Năm học:</strong> {{ $academicYear->year }}</p>
    <a href="{{ route('academic_years.index') }}">Quay lại</a>
@endsection
