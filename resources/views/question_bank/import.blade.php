@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Import Câu Hỏi Vào Ngân Hàng</div>

                    <div class="card-body">
                        <form action="{{ route('question_bank.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label>Chọn file Excel</label>
                                <input type="file" class="form-control" name="import_file" required>
                                <small class="text-muted">
                                    <a href="{{ route('question_bank.template') }}" class="text-primary-color">
                                        <i class="fas fa-download"></i> Tải file mẫu
                                    </a>
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary-color">
                                <i class="fas fa-upload"></i> Import Câu Hỏi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
