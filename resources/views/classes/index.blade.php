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
            padding: 15px;
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
            padding: 12px;
            text-align: center;
        }

        tbody tr {
            border-bottom: 2px solid #ddd;
        }

        tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
            transition: background 0.2s ease-in-out;
        }

        .button-container {
            display: flex;
            justify-content: flex-end;
        }
        .btn-add {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 10px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 25px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: filter 0.3s, box-shadow 0.3s;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            float: right;
            margin-bottom: 15px;
        }

        .btn-add:hover {
            filter: brightness(0.85);
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.15);
            transform: scale(1.02);
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
        <h2>Danh sách Lớp học</h2>
        <div class="button-container">
            <a href="{{ route('classes.create') }}" class="btn-add">
                <i class="fas fa-plus-circle"></i> Thêm mới
            </a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên Lớp</th>
                    <th>Khối</th>
                    <th>Năm Học</th>
                    <th>Hành động</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($classes as $index => $class)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $class->name }}</td>
                        <td>{{ $class->gradeLevel->grade_number }}</td>
                        <td>{{ $class->academicYear->year }}</td>
                        <td class="action-icons">
                            <a href="{{ route('classes.show', $class->id) }}" title="Xem">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('classes.edit', $class->id) }}" title="Sửa">
                                <i class="far fa-edit"></i>
                            </a>
                            <form action="{{ route('classes.destroy', $class->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa?');">
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
