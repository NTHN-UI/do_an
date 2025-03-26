@extends('layouts.app')

@section('content')
    <style>
        body {
            background-color: #f4f7f6;
            font-family: Arial, sans-serif;
        }
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        thead {
            background-color: #2c3e50;
            color: white;
        }
        th, td {
            padding: 15px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
        tbody tr {
            border-bottom: 1px solid #ddd;
        }
        tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
            transition: background 0.2s ease-in-out;
        }
        .button-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }
        .btn-add {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 25px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s ease-in-out;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-add:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .btn-add i {
            font-size: 18px;
        }
        .action-icons {
            display: flex;
            justify-content: center;
            gap: 12px;
        }
        .action-icons a, .action-icons button {
            color: #333;
            font-size: 16px;
            padding: 5px;
            border-radius: 5px;
            transition: background 0.2s ease-in-out;
            text-decoration: none;
            background: none;
            border: none;
        }
        .action-icons a:hover, .action-icons button:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
        .action-icons button:hover {
            cursor: pointer;
        }
    </style>

    <div class="container">
        <h2>Danh sách Giáo viên</h2>
        <div class="button-container">
            <a href="{{ route('teachers.create') }}" class="btn-add">
                <i class="fas fa-plus-circle"></i> Thêm mới
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif

        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th>STT</th>
                    <th>Họ và Tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Giới tính</th>
                    <th>Ngày sinh</th>
                    <th>Hành động</th>
                </tr>
                </thead>
                <tbody>
                @foreach($teachers as $index => $teacher)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $teacher->full_name }}</td>
                        <td>{{ $teacher->email }}</td>
                        <td>{{ $teacher->phone }}</td>
                        <td>{{ $teacher->gender ?? 'Chưa cập nhật' }}</td>
                        <td>{{ $teacher->date_of_birth ? \Carbon\Carbon::parse($teacher->date_of_birth)->format('d/m/Y') : 'Chưa cập nhật' }}</td>
                        <td class="action-icons">
                            <a href="{{ route('teachers.show', $teacher->id) }}" title="Xem">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('teachers.edit', $teacher->id) }}" title="Sửa">
                                <i class="far fa-edit"></i>
                            </a>
                            <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa?');">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
