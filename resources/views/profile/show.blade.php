@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Hồ sơ cá nhân</h5>
                    </div>

                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                                        <i class="fas fa-user fa-4x text-white"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <h4>{{ $user->full_name }}</h4>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-envelope me-2"></i> {{ $user->email }}
                                </p>
                                @if($user->phone)
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-phone me-2"></i> {{ $user->phone }}
                                    </p>
                                @endif
                                <p class="text-muted mb-1">
                                    <i class="fas fa-user-tag me-2"></i>
                                    @if($user->isSuperAdmin())
                                        Quản trị viên hệ thống
                                    @elseif($user->isSchoolAdmin())
                                        Quản trị viên trường
                                    @elseif($user->isTeacher())
                                        Giáo viên
                                    @elseif($user->isStudent())
                                        Học sinh
                                    @endif
                                </p>
                                @if($user->school)
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-school me-2"></i> {{ $user->school->name }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Chỉnh sửa hồ sơ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
