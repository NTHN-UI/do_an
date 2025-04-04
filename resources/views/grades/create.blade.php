@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Nhập điểm {{ $subject->name }} - {{ $class->name }} ({{ $semester->name }})
                </h4>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('grades.store', [
                'class' => $class,
                'subject' => $subject,
                'semester' => $semester
            ]) }}">
                    @csrf

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                            <tr>
                                <th rowspan="2" width="5%">STT</th>
                                <th rowspan="2" width="25%">Họ tên</th>
                                @foreach($testTypes as $type => $label)
                                    <th class="text-center">{{ $label }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($testTypes as $type => $label)
                                    <th width="10%" class="text-center">Điểm</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($students as $index => $student)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $student->full_name }}</td>
                                    @foreach($testTypes as $type => $label)
                                        <td>
                                            <input type="number" step="0.01" min="0" max="10"
                                                   class="form-control text-center"
                                                   name="grades[{{ $student->id }}][{{ $type }}][score]"
                                                   value="{{ $grades[$student->id][$type][0]->score ?? '' }}">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Lưu điểm
                        </button>
                        <a href="{{ route('grades.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-2"></i>Hủy bỏ
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
