<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý trường học</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .navbar-custom {
            background-color: white !important;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 0.5rem 6rem;
        }

        .navbar-custom .navbar-brand {
            color: #4e73df;
            font-weight: 700;
            font-size: 1.2rem;
            padding: 0.5rem 0;
        }

        .navbar-custom .nav-link {
            font-weight: 600;
            padding: 0.75rem 1rem;
            position: relative;
            transition: all 0.3s;
        }

        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link:focus {
            color: #2e59d9;
        }

        .navbar-custom .nav-link.active {
            color: #1a3b8b;
        }

        .navbar-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 1rem;
            right: 1rem;
            height: 0.25rem;
            background-color: #1a3b8b;
            border-radius: 0.25rem 0.25rem 0 0;
        }

        .navbar-custom .dropdown-menu {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .navbar-custom .dropdown-item {
            color: #5a5c69;
        }

        .navbar-custom .dropdown-item:hover {
            background-color: #f8f9fc;
            color: #4e73df;
        }

        .navbar-toggler-custom {
            border: none;
            padding: 0.5rem;
        }

        .navbar-toggler-custom:focus {
            box-shadow: none;
        }

        @media (max-width: 991.98px) {
            .navbar-custom .nav-link.active::after {
                left: 0.5rem;
                right: 0.5rem;
            }
        }
        .content {
            padding-top: 1.5rem;
        }

    </style>
</head>
<body>
@include('partials.navbar')

<div class="content">
    <main>
    </main>@yield('content')
</div>
@if(session('success'))
    <div class="toast text-white bg-success border-0 position-fixed"
         style="top: 5rem; right: 1rem; max-width: 235px; z-index: 9999;"
         role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
        <div class="d-flex">
            <div class="toast-body small">
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Thêm hiệu ứng active khi click vào menu
        $('.nav-link').click(function() {
            $('.nav-link').removeClass('active');
            $(this).addClass('active');
        });
    });
    @if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        var toastEl = document.querySelector('.toast');
        var toast = new bootstrap.Toast(toastEl, {
        animation: true,
        autohide: true,
        delay: 5000
    });
        toast.show();
    });
@endif

</script>
</body>
</html>
