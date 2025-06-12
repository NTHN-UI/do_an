    @extends('layouts.app')

    @section('content')
        <div class="container rounded-3 shadow p-4">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('teachers.index') }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h3 class="mb-0 text-primary-color">Thông tin giáo viên</h3>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3 text-center">
                        <div class="avatar-lg mx-auto bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:96px;height:96px;">
                            <i class="fas fa-user-tie fa-3x text-secondary"></i>
                        </div>
                        <h3>{{ $teacher->full_name }}</h3>
                        <p class="text-muted mb-0">{{ $teacher->school->name ?? 'Chưa phân công trường' }}</p>
                    </div>
                    <div class="col-md-9">
                        <dl class="row">
                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8">{{ $teacher->email }}</dd>

                            <dt class="col-sm-4">Điện thoại:</dt>
                            <dd class="col-sm-8">{{ $teacher->phone }}</dd>

                            <dt class="col-sm-4">Môn dạy:</dt>
                            <dd class="col-sm-8">{{ $teacher->subject->name ?? 'Chưa phân công' }}</dd>

                            <dt class="col-sm-4">Giới tính:</dt>
                            <dd class="col-sm-8">{{ $teacher->gender }}</dd>

                            <dt class="col-sm-4">Ngày sinh:</dt>
                            <dd class="col-sm-8">{{ $teacher->date_of_birth->format('d/m/Y') }}</dd>

                            <dt class="col-sm-4">Trạng thái:</dt>
                            <dd class="col-sm-8">
                                @if($teacher->is_active)
                                    <span class="badge bg-primary-color">Hoạt động</span>
                                @else
                                    <span class="badge bg-primary-color">Không hoạt động</span>
                                @endif
                            </dd>

                            <dt class="col-sm-4">Địa chỉ:</dt>
                            <dd class="col-sm-8">{{ $teacher->address }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <a href="{{ route('teachers.index', $teacher) }}" class="btn btn-primary-color">
                         Đóng
                    </a>

                </div>
            </div>
        </div>
    @endsection
