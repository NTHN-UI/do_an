@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Thông tin giáo viên</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="avatar-lg mx-auto bg-light rounded-circle d-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-user-tie fa-3x text-secondary"></i>
                        </div>
                        <h4>{{ $teacher->full_name }}</h4>
                        <p class="text-muted">{{ $teacher->school->name ?? 'Chưa phân công trường' }}</p>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Email</label>
                                <p class="form-control-plaintext">{{ $teacher->email }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Điện thoại</label>
                                <p class="form-control-plaintext">{{ $teacher->phone }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Giới tính</label>
                                <p class="form-control-plaintext">{{ $teacher->gender }}</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Ngày sinh</label>
                                <p class="form-control-plaintext">{{ $teacher->date_of_birth->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Trạng thái</label>
                                <p class="form-control-plaintext">
                                    @if($teacher->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-danger">Không hoạt động</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Địa chỉ</label>
                            <p class="form-control-plaintext">{{ $teacher->address }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('teachers.edit', $teacher) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    <form action="{{ route('teachers.destroy', $teacher) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Bạn chắc chắn muốn xóa giáo viên này?')">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
