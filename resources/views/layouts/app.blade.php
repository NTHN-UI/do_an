<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý trường học</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/main.css') }}">

</head>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-color">
                <h5 class="modal-title" id="logoutModalLabel"><i class="fas fa-sign-out-alt me-2"></i> Xác nhận đăng xuất</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
@include('partials.navbar')

<div class="content">
    <main>
        <div id="app-alert-container" class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1060;"></div>

    </main>@yield('content')

    @if(session('success') || session('error') || session('info'))
        <div class="toast text-white border-0 position-fixed"
             style="background-color: {{ session('success') ? '#013066' : (session('error') ? '#dc3545' : '#0d6efd') }}; top: 5rem; right: 1rem; max-width: 235px; z-index: 9999;"
             role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
            <div class="d-flex">
                <div class="toast-body small">
                    {{ session('success') ?? session('error') ?? session('info') }} {{-- Hiển thị thông báo đầu tiên tìm thấy --}}
                </div>
                <div class="toast-header bg-transparent border-0"> {{-- Thêm toast-header --}}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" async></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <script>
        $(document).ready(function () {
            window.csrfToken = "{{ csrf_token() }}"
            $('.nav-link').click(function () {
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
            });
        });

        @if(session('success') || session('error') || session('info'))
        document.addEventListener('DOMContentLoaded', function () {
            var toastEl = document.querySelector('.toast');
            if (toastEl) { // Kiểm tra nếu phần tử toast tồn tại
                var toast = new bootstrap.Toast(toastEl, {
                    animation: true,
                    autohide: true,
                    delay: 3000
                });
                toast.show();
            }
        });
        @endif
            MathJax = {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$', '$$'], ['\\[', '\\]']],
                processEscapes: true,
                packages: {'[+]': ['ams', 'color', 'boldsymbol']}
            },
            options: {
                ignoreHtmlClass: 'tex2jax_ignore',
                processHtmlClass: 'tex2jax_process'
            },
            loader: {
                load: ['[tex]/ams', '[tex]/color', '[tex]/boldsymbol']
            },
            startup: {
                ready: () => {
                    MathJax.startup.defaultReady();
                    // Tự động render khi có nội dung mới được thêm vào
                    MathJax.startup.promise.then(() => {
                        document.addEventListener('DOMNodeInserted', () => {
                            if (typeof MathJax !== 'undefined') {
                                MathJax.typesetPromise();
                            }
                        });
                    });
                }
            }
        };
    </script>

    @stack('scripts')
</div>
</body>
</html>
