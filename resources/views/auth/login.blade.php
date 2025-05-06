<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống quản lý trường học</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            background: #013066;
            border-radius: 15px 15px 0 0 !important;
        }
        .input-group-text {
            background-color: transparent;
            border-right: none;
        }
        .form-control {
            border-left: none;
            padding-left: 0;
        }
        .toggle-password {
            cursor: pointer;
        }
        .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body class="d-flex align-items-center">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card login-card border-0">
                <div class="card-header login-header text-white text-center py-4 border-0">
                    <div class="d-inline-block bg-white rounded-circle p-3 mb-3">
                        <i class="fas fa-graduation-cap fa-3x" style = "color: #013066"></i>
                    </div>
                    <h3 class="fw-bold mb-1">HỆ THỐNG QUẢN LÝ TRƯỜNG HỌC</h3>
                    <p class="mb-0 opacity-75">Xin mời đăng nhập hệ thống</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="{{ route('login') }}" class="needs-validation">
                        @csrf
                        <div class="mb-4">
                            <div class="input-group">
                                    <span class="input-group-text bg-transparent">
                                        <i class="fas fa-user-circle"></i>
                                    </span>
                                <input id="login" type="text"
                                       class="form-control @error('login') is-invalid @enderror"
                                       name="login" value="{{ old('login') }}"
                                       placeholder="Email hoặc số điện thoại">
                            </div>
                            @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="mb-4">
                            <div class="input-group">
                                    <span class="input-group-text bg-transparent">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                <input id="password" type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       name="password"
                                       placeholder="Mật khẩu">
                                <span class="input-group-text bg-transparent toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </span>
                            </div>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">
                                    Ghi nhớ đăng nhập
                                </label>
                            </div>
                            <a href="#" class="text-decoration-none" style = "color: #013066">Quên mật khẩu?</a>
                        </div>

                        <button type="submit" class="btn btn-lg w-100 mb-3" style = "background:#013066; color: #ffffff">
                            <i class="fas fa-sign-in-alt me-2"></i> ĐĂNG NHẬP
                        </button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4 small" style = "color: #013066" >
                © <span id="currentYear"></span> Hệ thống Quản lý Trường học
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Hiển thị năm hiện tại
    document.getElementById('currentYear').textContent = new Date().getFullYear();

    // Toggle hiển thị mật khẩu
    document.querySelectorAll('.toggle-password').forEach(function(element) {
        element.addEventListener('click', function() {
            const passwordInput = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    document.querySelector('.needs-validation').addEventListener('submit', function(event) {
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        this.classList.add('was-validated');
    }, false);
</script>
</body>
</html>
