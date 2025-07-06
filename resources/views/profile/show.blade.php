@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header text-white py-3 rounded-top">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-primary-color">
                        <i class="fas fa-user-circle me-2"></i> Hồ sơ cá nhân
                    </h4>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-4 text-center mb-4 mb-md-0">
                        <div class="position-relative">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}"
                                     class="img-thumbnail rounded-circle mb-3">
                            @else
                                <div class="bg-primary-color rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                     style="width: 180px; height: 180px;">
                                    <i class="fas fa-user text-white fa-4x"></i>
                                </div>
                            @endif

                            <div class="mt-3">
                                <span class="badge bg-primary-color px-3 py-2">
                                    @if($user->isSuperAdmin())
                                        <i class="fas fa-shield-alt me-1"></i> Quản trị hệ thống
                                    @elseif($user->isSchoolAdmin())
                                        <i class="fas fa-user-shield me-1"></i> Quản trị trường
                                    @elseif($user->isTeacher())
                                        <i class="fas fa-chalkboard-teacher me-1"></i> Giáo viên
                                    @elseif($user->isStudent())
                                        <i class="fas fa-user-graduate me-1"></i> Học sinh
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h3 class="text-primary-color mb-4">{{ $user->full_name }}</h3>
                        <div class="mb-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-envelope text-primary-color me-3 fa-lg"></i>
                                        <div>
                                            <small class="text-muted d-block">Email</small>
                                            <p class="mb-0">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if($user->phone)
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-phone text-primary-color me-3 fa-lg"></i>
                                            <div>
                                                <small class="text-muted d-block">Điện thoại</small>
                                                <p class="mb-0">{{ $user->phone }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($user->school)
                                    <div class="mb-3">
                                        <div class="d-flex align-items-start">
                                            <i class="fas fa-school text-primary-color me-3 fa-lg mt-1"></i>
                                            <div>
                                                <small class="text-muted d-block">Trường học</small>
                                                <p class="mb-0">{{ $user->school->name }}</p>
                                                @if($user->school->address)
                                                    <small class="text-muted d-block mt-1">Địa chỉ</small>
                                                    <p class="mb-0">{{ $user->school->address }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($user->isStudent())
                            <div class="border-top pt-3">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-users me-2"></i> Thông tin phụ huynh
                                </h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-tie text-primary me-3 fa-lg"></i>
                                            <div>
                                                <small class="text-muted d-block">Tên phụ huynh</small>
                                                <p class="mb-0">{{ $user->guardian_name ?? 'Chưa cập nhật' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-at text-primary me-3 fa-lg"></i>
                                            <div>
                                                <small class="text-muted d-block">Email phụ huynh</small>
                                                <p class="mb-0">{{ $user->guardian_email ?? 'Chưa cập nhật' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-mobile-alt text-primary me-3 fa-lg"></i>
                                            <div>
                                                <small class="text-muted d-block">Điện thoại phụ huynh</small>
                                                <p class="mb-0">{{ $user->guardian_phone ?? 'Chưa cập nhật' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-0 py-3">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('home.index') }}" class="btn btn-primary-color px-4">
                        Đóng
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
