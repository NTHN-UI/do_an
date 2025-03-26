@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Sửa Giáo Viên</h2>
        <form action="{{ route('teachers.update', $teacher->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="full_name" value="{{ $teacher->full_name }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ $teacher->email }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" name="phone" value="{{ $teacher->phone }}" class="form-control">
            </div>
            <div class="form-group">
                <label>Giới tính</label>
                <select name="gender" class="form-control" required>
                    <option value="Nam" {{ $teacher->gender == 'Nam' ? 'selected' : '' }}>Nam</option>
                    <option value="Nữ" {{ $teacher->gender == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                    <option value="Khác" {{ $teacher->gender == 'Khác' ? 'selected' : '' }}>Khác</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ngày sinh</label>
                <input type="date" name="date_of_birth" value="{{ $teacher->date_of_birth }}" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Cập nhật</button>
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
@endsection
