@php use Illuminate\Support\Facades\Auth; @endphp
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="card-tools">
                            <a href="{{ route('grade_levels.index') }}" class="btn btn-sm ">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <h3 class="card-title">Thêm Khối Học Mới</h3>

                        </div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('grade_levels.store') }}" id="gradeLevelForm">
                            @csrf
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">Trường học</label>
                                <input type="hidden" name="school_id" value="{{ Auth::user()->school_id  }}">
                                <div class="col-md-6">
                                    <div class="form-control bg-light">
                                        {{ Auth::user()->school->name }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="grade_number" class="col-md-4 col-form-label text-md-right">Khối học</label>
                                <div class="col-md-6">
                                    <input type="number" id="grade_number" name="grade_number" class="form-control @error('grade_number') is-invalid @enderror"
                                           value="{{ old('grade_number') }}" min="1" max="12">
                                    @error('grade_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Lưu Khối Học
                                    </button>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection



