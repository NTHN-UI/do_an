<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý trường học</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('build/assets/img/education_4207253.png') }}">

    <link rel="stylesheet" href="{{ asset('css/main.css') }}">

</head>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-color">
                <h5 class="modal-title" id="logoutModalLabel"><i class="fas fa-sign-out-alt me-2"></i> Xác nhận đăng
                    xuất</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-question-circle fa-4x mb-3 text-primary-color"></i>
                <h5>Bạn có chắc chắn muốn đăng xuất?</h5>
                <p class="text-muted">Bạn sẽ cần đăng nhập lại để tiếp tục sử dụng hệ thống</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i> Hủy bỏ
                </button>
                <form id="logoutForm" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary-color px-4">
                        <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<body>
@include('partials.sidebar')

<div class="main-content">
    <main class="container-fluid px-4 py-3">
        <div id="app-alert-container" class="position-fixed top-0 start-50 translate-middle-x p-3"
             style="z-index: 1060;"></div>

        <div class="content-wrapper mt-4">
            @yield('content')
        </div>

        @if(session('success') || session('error') || session('info'))
            <div class="toast-container position-fixed end-0 p-3" style="top: 8px;">
                <div class="toast text-white border-0 show"
                     style="background-color: {{ session('success') ? '#013066' : (session('error') ? '#dc3545' : '#0d6efd') }};"
                     role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body small">
                            <i class="fas {{ session('success') ? 'fa-check-circle' : (session('error') ? 'fa-exclamation-circle' : 'fa-info-circle') }} me-2"></i>
                            {{ session('success') ?? session('error') ?? session('info') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" async></script>
<script>
    $(document).ready(function () {
        window.csrfToken = "{{ csrf_token() }}"
        $('.nav-link').on('click', function () {
            $('.nav-link').removeClass('active');
            $(this).addClass('active');
        });

        @if(session('success') || session('error') || session('info'))
        var $toastEl = $('.toast');
        if ($toastEl.length) {
            var toast = new bootstrap.Toast($toastEl[0], {
                animation: true,
                autohide: true,
                delay: 3000
            });
            toast.show();
        }
        @endif
    });

</script>

@stack('scripts')
</body>
</html>
