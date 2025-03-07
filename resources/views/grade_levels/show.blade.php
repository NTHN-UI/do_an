@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Chi tiết khối</h2>
        <div class="card">
            <div class="card-body">
                <h4>ID: {{ $grade->id }}</h4>
                <h4>Số khối: {{ $grade->grade_number }}</h4>
                <a href="{{ route('grade_levels.index') }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </div>
    </div>
@endsection

