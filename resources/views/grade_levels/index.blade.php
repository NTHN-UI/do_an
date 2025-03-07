@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Danh sách khối</h2>
        <a href="{{ route('grade_levels.create') }}" class="btn btn-primary">Thêm khối mới</a>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Khối</th>
                <th>Hành động</th>
            </tr>
            </thead>
            <tbody>
            @foreach($grades as $grade)
                <tr>
                    <td>{{ $grade->id }}</td>
                    <td>{{ $grade->grade_number }}</td>
                    <td>
                        <a href="{{ route('grade_levels.show', $grade->id) }}" class="btn btn-info">Xem</a>
                        <a href="{{ route('grade_levels.edit', $grade->id) }}" class="btn btn-warning">Sửa</a>
                        <form action="{{ route('grade_levels.destroy', $grade->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc muốn xóa không?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

