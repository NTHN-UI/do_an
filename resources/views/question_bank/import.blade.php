@extends('layouts.app')

@section('content')
    <div class="container rounded-3 shadow p-4">
        {{-- Header Section --}}
        <div class="d-flex align-items-center mb-4">
            <a href="{{ url()->previous() }}" class="btn text-primary-color btn-sm me-3" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="mb-0 text-primary-color">Import câu hỏi vào ngân hàng</h3>
        </div>

        <div class="card border-0 shadow-sm rounded-2">
            <div class="card-body">
                <form action="{{ route('question_bank.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label for="import_file" class="form-label">Chọn file Excel <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="file" class="form-control @error('import_file') is-invalid @enderror"
                                   id="import_file" name="import_file" required accept=".xlsx,.xls">
                            @error('import_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <a href="{{ route('question_bank.template') }}" class="text-primary-color text-decoration-none">
                                    <i class="fas fa-download me-1"></i> Tải file mẫu
                                </a>
                            </small>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Lưu ý:</strong> File import phải đúng định dạng theo mẫu, dung lượng tối đa 10MB.
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">Đóng</a>
                        <button type="submit" class="btn btn-primary-color">
                            <i class="fas fa-upload me-1"></i> Import Câu Hỏi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
