@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Chi Tiết Giáo Viên</h2>
        <table class="table">
            <tr>
                <th>Tên:</th>
                <td>{{ $teacher->full_name }}</td>
            </tr>
            <tr>
                <th>Email:</th>
                <td>{{ $teacher->email }}</td>
            </tr>
            <tr>
                <th>Số điện thoại:</th>
                <td>{{ $teacher->phone }}</td>
            </tr>
            <tr>
                <th>Giới tính:</th>
                <td>{{ $teacher->gender }}</td>
            </tr>
            <tr>
                <th>Ngày sinh:</th>
                <td>{{ \Carbon\Carbon::parse($teacher->date_of_birth)->format('d/m/Y') }}</td>
            </tr>

        </table>

        <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Quay lại</a>
        <a href="{{ route('teachers.edit', $teacher->id) }}" class="btn btn-warning">Sửa</a>

        <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xoá?')">Xoá</button>
        </form>
    </div>
@endsection
