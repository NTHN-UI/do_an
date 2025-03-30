@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Danh sách Học kỳ</h2>

        <a href="{{ route('semesters.create') }}" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> Thêm học kỳ
        </a>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên học kỳ</th>
                    <th>Năm học</th>
                    <th>Ngày bắt đầu</th>
                    <th>Ngày kết thúc</th>
                    <th>Hiện tại</th>
                    <th>Hành động</th>
                </tr>
                </thead>
                <tbody>
                @foreach($semesters as $semester)
                    <tr>
                        <td>{{ $semester->id }}</td>
                        <td>{{ $semester->name }}</td>
                        <td>{{ $semester->academicYear->year }}</td>
                        <td>{{ $semester->start_date->format('d/m/Y') }}</td>
                        <td>{{ $semester->end_date->format('d/m/Y') }}</td>
                        <td>{{ $semester->is_current ? '✓' : '' }}</td>
                        <td>
                            <a href="{{ route('semesters.show', $semester->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('semesters.edit', $semester->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('semesters.destroy', $semester->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $semesters->links() }}
    </div>
@endsection
