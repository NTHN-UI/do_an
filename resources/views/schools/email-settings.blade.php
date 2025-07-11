@extends('layouts.app')

@section('content')
    <style>
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
    </style>
    <div class="container">
        <div class="d-flex align-items-center mb-4">
            <h3 class="mb-0 text-primary-color">Cấu hình Email SMTP - {{ $school->name }}</h3>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('schools.update-email-settings', $school) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mt-2">
                            <label for="host">SMTP Host <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('host') is-invalid @enderror"
                                   id="host" name="host" value="{{ old('host', $emailSettings->host ?? '') }}">
                            @error('host')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt-2">
                            <label for="port">Port <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('port') is-invalid @enderror"
                                   id="port" name="port" value="{{ old('port', $emailSettings->port ?? 587) }}">
                            @error('port')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mt-2">
                            <label for="username">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                   id="username" name="username"
                                   value="{{ old('username', $emailSettings->username ?? '') }}">
                            @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt-2">
                            <label for="password">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" value="{{ old('password') }}">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Để trống nếu không muốn thay đổi password</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mt-2">
                            <label for="encryption">Encryption</label>
                            <select class="form-select @error('encryption') is-invalid @enderror" id="encryption"
                                    name="encryption">
                                <option
                                    value="tls" {{ (old('encryption', $emailSettings->encryption ?? 'tls') == 'tls' ? 'selected' : '' )}}>
                                    TLS
                                </option>
                                <option
                                    value="ssl" {{ (old('encryption', $emailSettings->encryption ?? 'tls') == 'ssl' ? 'selected' : '' )}}>
                                    SSL
                                </option>
                            </select>
                            @error('encryption')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mt-2">
                            <label for="from_address">From Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('from_address') is-invalid @enderror"
                                   id="from_address" name="from_address"
                                   value="{{ old('from_address', $emailSettings->from_address ?? '') }}">
                            @error('from_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt-2">
                            <label for="from_name">From Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('from_name') is-invalid @enderror"
                                   id="from_name" name="from_name"
                                   value="{{ old('from_name', $emailSettings->from_name ?? $school->name) }}">
                            @error('from_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('schools.show', $school) }}" class="btn btn-outline-primary-color me-2">Đóng</a>
                    <button type="submit" class="btn btn-primary-color">
                        <i class="fas fa-save me-1"></i> Lưu cấu hình
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

