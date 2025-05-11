@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <h3 class="mb-0">Danh Sách Khối Học</h3>
            <div class="col-md-12">
                <div class="card">

                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="thead-dark">
                                <tr>
                                    <th >ID</th>
                                    <th >Khối</th>
                                    <th>Trường</th>
                                    <th >Địa chỉ</th>
                                    <th >Số lớp</th>
                                    <th >Thao tác</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($gradeLevels as $gradeLevel)
                                    <tr>
                                        <td>{{ $gradeLevel->school_auto_id }}</td>
                                        <td>
                                            <span class="badge text-dark p-2">
                                                Khối {{ $gradeLevel->grade_number }}
                                            </span>
                                        </td>
                                        <td>{{ $gradeLevel->school->name }}</td>
                                        <td>{{ $gradeLevel->school->district }}, {{ $gradeLevel->school->province }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-pill text-dark">
                                                {{ $gradeLevel->classes_count ?? $gradeLevel->classes->count() }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-around">
                                                <a href="{{ route('grade_levels.show', $gradeLevel->id) }}"
                                                   class="btn btn-sm btn-info" title="Xem chi tiết">
                                                    Xem
                                                </a>

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Không có dữ liệu khối học</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($gradeLevels->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $gradeLevels->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .badge {
            font-size: 0.9rem;
            font-weight: 500;


        }
        .table th {
            white-space: nowrap;
        }
        .btn-sm {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endsection
