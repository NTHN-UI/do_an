@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Thêm Giáo Viên</h2>
        <form action="{{ route('teachers.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="form-group">
                <label>Giới tính</label>
                <select name="gender" class="form-control" required>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                    <option value="Khác">Khác</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ngày sinh</label>
                <input type="date" name="date_of_birth" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Thêm</button>
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
@endsection
