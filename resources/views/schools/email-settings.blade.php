@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-envelope me-2"></i>Cấu hình Email SMTP - {{ $school->name }}
                </h4>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('schools.update-email-settings', $school) }}" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="host" name="host"
                                       value="{{ old('host', $emailSettings->host ?? '') }}"
                                       placeholder="smtp.example.com" required>
                                <label for="host">SMTP Host</label>
                                <div class="invalid-feedback">Vui lòng nhập SMTP host</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="port" name="port"
                                       value="{{ old('port', $emailSettings->port ?? 587) }}"
                                       placeholder="587" required>
                                <label for="port">Port</label>
                                <div class="invalid-feedback">Vui lòng nhập port</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username"
                                       value="{{ old('username', $emailSettings->username ?? '') }}"
                                       placeholder="username" required>
                                <label for="username">Username</label>
                                <div class="invalid-feedback">Vui lòng nhập username</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password"
                                       value="{{ old('password', $emailSettings->password ?? '') }}"
                                       placeholder="password" required>
                                <label for="password">Password</label>
                                <div class="invalid-feedback">Vui lòng nhập password</div>
                                <small class="text-muted">Để trống nếu không muốn thay đổi password</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="encryption" name="encryption">
                                    <option value="tls" {{ (old('encryption', $emailSettings->encryption ?? 'tls') == 'tls' ? 'selected' : '' )}}>TLS</option>
                                    <option value="ssl" {{ (old('encryption', $emailSettings->encryption ?? 'tls') == 'ssl' ? 'selected' : '' )}}>SSL</option>
                                </select>
                                <label for="encryption">Encryption</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="from_address" name="from_address"
                                       value="{{ old('from_address', $emailSettings->from_address ?? '') }}"
                                       placeholder="no-reply@school.edu.vn" required>
                                <label for="from_address">From Address</label>
                                <div class="invalid-feedback">Vui lòng nhập địa chỉ email hợp lệ</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="from_name" name="from_name"
                                       value="{{ old('from_name', $emailSettings->from_name ?? $school->name) }}"
                                       placeholder="Tên trường" required>
                                <label for="from_name">From Name</label>
                                <div class="invalid-feedback">Vui lòng nhập tên hiển thị</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('schools.show', $school) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>

                        <div>
                            <button type="button" class="btn btn-info me-2" onclick="testEmailSettings()">
                                <i class="fas fa-paper-plane me-1"></i> Test Email
                            </button>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Lưu cấu hình
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Test email settings
        function testEmailSettings() {
            const btn = document.querySelector('#test-email-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...';

            fetch("{{ route('schools.test-email-settings', $school) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json', // Thêm header này
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({}) // Thêm body rỗng
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Lỗi không xác định');
                    }
                    return data;
                })
                .then(data => {
                    alert(data.message);
                })
                .catch(error => {
                    alert('Lỗi: ' + error.message);
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Test Email';
                });
        }
    </script>

    <style>
        .card {
            border-radius: 10px;
            border: none;
        }

        .form-floating label {
            color: #6c757d;
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        .was-validated .form-control:invalid ~ .invalid-feedback,
        .was-validated .form-control:invalid ~ .invalid-tooltip {
            display: block;
        }
    </style>
@endsection
