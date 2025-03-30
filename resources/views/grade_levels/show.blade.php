@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Thông Tin Chi Tiết Khối Học</h3>
                        <div class="card-tools">
                            <a href="{{ route('grade_levels.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                <tr>
                                    <th width="30%">ID</th>
                                    <td>{{ $gradeLevel->id }}</td>
                                </tr>
                                <tr>
                                    <th>Khối học</th>
                                    <td>Khối {{ $gradeLevel->grade_number }}</td>
                                </tr>
                                <tr>
                                    <th>Trường học</th>
                                    <td>
                                        {{ $gradeLevel->school->name }}
                                        <span class="badge bg-info float-right">
                                            {{ $gradeLevel->school->education_level == 'primary' ? 'Tiểu học' :
                                              ($gradeLevel->school->education_level == 'secondary' ? 'THCS' : 'THPT') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Địa chỉ</th>
                                    <td>{{ $gradeLevel->school->district }}, {{ $gradeLevel->school->province }}</td>
                                </tr>
                                <tr>
                                    <th>Số lớp học</th>
                                    <td>{{ $gradeLevel->classes->count() }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày tạo</th>
                                    <td>{{ $gradeLevel->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Cập nhật cuối</th>
                                    <td>{{ $gradeLevel->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('grade_levels.edit', $gradeLevel->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Chỉnh Sửa
                            </a>

                            <form action="{{ route('grade_levels.destroy', $gradeLevel->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa khối học này?')">
                                    <i class="fas fa-trash"></i> Xóa Khối Học
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
