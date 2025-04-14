@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Quản lý Admin Trường</h2>

        <!-- Form tìm kiếm -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('school_admins.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-10">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="Tìm theo tên, email hoặc tên trường..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                    </div>
                    @if(request()->has('search'))
                        <div class="mt-2">
                            <a href="{{ route('school_admins.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i> Xóa tìm kiếm
                            </a>
                            <small class="text-muted">Đang tìm kiếm: "{{ request('search') }}"</small>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Nút thêm mới -->
        <a href="{{ route('school_admins.create') }}" class="btn btn-success mb-3">
            <i class="fas fa-plus"></i> Thêm Admin
        </a>

        <!-- Bảng danh sách -->
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên Admin</th>
                        <th>Email</th>
                        <th>Trường</th>
                        <th>Hành động</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($schoolAdmins as $index => $admin)
                        <tr>
                            <td>{{ $index + $schoolAdmins->firstItem() }}</td>
                            <td>{{ $admin->full_name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>{{ $admin->school->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('school_admins.show', $admin->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('school_admins.edit', $admin->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('school_admins.destroy', $admin->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $schoolAdmins->links() }}
            </div>
        </div>
    </div>
@endsection
