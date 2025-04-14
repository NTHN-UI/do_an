@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Chỉnh Sửa Khối Học</h3>
                        <div class="card-tools">
                            <a href="{{ route('grade_levels.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('grade_levels.update', $gradeLevel->id) }}" id="gradeLevelForm">
                            @csrf
                            @method('PUT')

                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">Trường học</label>
                                <input type="hidden" name="school_id" value="{{ Auth::user()->school_id  }}">
                                <div class="col-md-6">
                                    <div class="form-control bg-light">
                                        {{ Auth::user()->school->name }}
                                        ({{Auth::user()->school->education_level == 'secondary' ? 'THCS' : 'THPT' }})
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="grade_number" class="col-md-4 col-form-label text-md-right">Khối học</label>
                                <div class="col-md-6">
                                    <input type="number" id="grade_number" name="grade_number" class="form-control @error('grade_number') is-invalid @enderror"
                                           value="{{ old('grade_number', $gradeLevel->grade_number) }}" min="1" max="12">
                                    @error('grade_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập Nhật
                                    </button>
                                    <a href="{{ route('grade_levels.show', $gradeLevel->id) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Hủy
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

