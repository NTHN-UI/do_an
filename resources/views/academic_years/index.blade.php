@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Quản lý Năm học</h2>
        <a href="{{ route('academic_years.create') }}" class="btn btn-primary-color mb-3">Thêm mới
        </a>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Năm học</th>
                    <th>Ngày bắt đầu</th>
                    <th>Ngày kết thúc</th>
                    <th>Hành động</th>
                </tr>
                </thead>
                <tbody>
                @foreach($academicYears as $year)
                    <tr>
                        <td>{{ $year->school_auto_id }}</td>
                        <td>{{ $year->year }}</td>
                        <td>{{ $year->start_date->format('d/m/Y') }}</td>
                        <td>{{ $year->end_date->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('academic_years.show', $year->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('academic_years.edit', $year->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('academic_years.destroy', $year->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $academicYears->links() }}
    </div>
@endsection
