@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Danh sách giáo viên</h2>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Giáo viên chủ nhiệm
            </div>
            <div class="card-body">
                @if($formTeacher)
                    <div class="d-flex align-items-center">
                        <img src="{{ $formTeacher->avatar_url }}" class="rounded-circle mr-3" width="80" height="80">
                        <div>
                            <h5>{{ $formTeacher->full_name }}</h5>
                            <p class="mb-1"><strong>Môn dạy:</strong> {{ $formTeacher->subject->name }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $formTeacher->email }}</p>
                            <p class="mb-1"><strong>SĐT:</strong> {{ $formTeacher->phone }}</p>
                        </div>
                    </div>
                @else
                    <p>Chưa có giáo viên chủ nhiệm</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-info text-white">
                Giáo viên bộ môn
            </div>
            <div class="card-body">
                @if($subjectTeachers->count() > 0)
                    <div class="row">
                        @foreach($subjectTeachers as $item)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <img src="{{ $item['teacher']->avatar_url }}" class="rounded-circle mr-3" width="60" height="60">
                                    <div>
                                        <h6>{{ $item['teacher']->full_name }}</h6>
                                        <p class="mb-1"><strong>Môn:</strong> {{ $item['subject']->name ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong>Email:</strong> {{ $item['teacher']->email }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p>Chưa có giáo viên bộ môn</p>
                @endif
            </div>
        </div>
    </div>
@endsection
