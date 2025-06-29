@extends('layouts.app')

@section('content')
    <style>
        .table-responsive .dropdown-menu {
            position: fixed !important;
            z-index: 1000 !important;
            min-width: 90px;
        }
        .dropdown-item:active,
        .dropdown-item:focus {
            background-color: #013066 !important;
            color: white !important;
        }
        .badge-count {
            background-color: #f8f9fa;
            color: #6c757d;
            border-radius: 10px;
            padding: 3px 8px;
            font-size: 0.85rem;
        }
        .text-primary-color {
            color: #013066;
        }
    </style>

    <div class="container rounded-3 shadow p-4">
        <h3 class="mb-3 text-primary-color">Thống kê trường học</h3>
        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 rounded-3 overflow-hidden" id="schoolsTable">
                        <thead class="table-secondary ">
                        <tr>
                            <th>ID</th>
                            <th>Tên trường</th>
                            <th>Địa chỉ</th>
                            <th class="text-center">Số giáo viên</th>
                            <th class="text-center">Số học sinh</th>
                            <th class="text-center">Số lớp</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($schools as $school)
                            <tr >
                                <td>{{ $school->id }}</td>
                                <td>{{ $school->name }}</td>
                                <td>{{ $school->address }}</td>
                                <td class="text-center">
                            <span class="badge badge-pill text-dark">
                                {{ $school->teachers()->count() }}
                            </span>
                                </td>
                                <td class="text-center">
                            <span class="badge badge-pill text-dark">
                                {{ $school->students()->count() }}
                            </span>
                                </td>
                                <td class="text-center">

                            <span class="badge badge-pill text-dark">
                                {{ $school->classes()->count() }}
                            </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('home.index', ['school_id' => $school->id]) }}"
                                       class="btn btn-primary-color d-inline-flex align-items-center py-1 px-2 rounded">
                                        <i class="fas fa-chart-line me-2"></i> Xem thống kê
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Không có dữ liệu trường học</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
{{--            @if ($schools->hasPages())--}}
{{--                <div class="card-footer border-0 bg-transparent" id="pagination-container">--}}
{{--                    <nav aria-label="page navigation">--}}
{{--                        {{ $schools->links('pagination::bootstrap-5') }}--}}
{{--                    </nav>--}}
{{--                </div>--}}
{{--            @endif--}}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#schoolsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Vietnamese.json"
                },
                "dom": '<"top"f>rt<"bottom"lip><"clear">',
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": [6] }
                ],
                "initComplete": function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                    $('.dataTables_filter label').contents().filter(function() {
                        return this.nodeType === 3;
                    }).remove();
                }
            });
        });
    </script>
@endpush
