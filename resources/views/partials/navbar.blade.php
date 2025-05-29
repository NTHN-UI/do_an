<style>
    .navbar-custom {
        background-color: #013066 !important;
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
        color: white;
    }

    .navbar-custom .nav-link:hover,
    .navbar-custom .nav-link:focus {
        color: #dddd;
    }

    .navbar-custom .nav-link.active {
        color: #dddd;
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
        background-color: #013066;
        color: #ffffff;
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
    /* Logout Modal Styles */
    #logoutModal .modal-content {
        border-radius: 10px;
        border: none;
    }

    #logoutModal .modal-header {
        background-color: #013066;
        color: white;
        border-radius: 10px 10px 0 0;
        padding: 1rem;
    }

    #logoutModal .modal-header .close {
        color: white;
        opacity: 1;
    }

    #logoutModal .modal-body {
        padding: 2rem;
        text-align: center;
    }

    #logoutModal .modal-footer {
        border-top: none;
        justify-content: center;
        padding-bottom: 2rem;
    }
</style>
<nav class="navbar navbar-expand-lg navbar-custom">
    <a class="navbar-brand text-white d-flex align-items-center gap-2" href="#">
        <img src="{{ asset('build/assets/img/education_4207253.png') }}" alt="Logo" style="height: 40px">
        <span class="d-flex flex-column">
        <span class="fw-semibold fs-6">HỆ THỐNG QUẢN LÝ TRƯỜNG HỌC</span>
        <small class="fw-normal" style="font-size: 0.8rem">School Management System</small>
    </span>
    </a>
    <button class="navbar-toggler navbar-toggler-custom" type="button" data-bs-toggle="collapse"
            data-bs-target="#mainMenu">
        <i class="fas fa-bars"></i>
    </button>

    <div class="collapse navbar-collapse d-flex align-items-center justify-content-between" id="mainMenu">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('home.index') }}">
                    <i class="fas fa-home me-1"></i> Trang chủ
                </a>
            </li>
            @if(Auth::check())
                @if(Auth::user()->isSchoolAdmin())
                    <!-- Quản lý học tập -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="learningDropdown" role="button"
                           data-bs-toggle="dropdown">
                            <i class="fas fa-book-open me-1"></i> Quản lý học tập
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('classes.index') }}"><i
                                        class="fas fa-users-class me-2"></i> Danh sách lớp học</a></li>
                            <li><a class="dropdown-item" href="{{ route('grade_levels.index') }}"><i
                                        class="fas fa-layer-group me-2"></i> Khối lớp</a></li>
                        </ul>
                    </li>

                    <!-- Quản lý phân công -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usersDropdown" role="button"
                           data-bs-toggle="dropdown">
                            <i class="fas fa-tasks me-1"></i> Quản lý phân công
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('teacher_assignments.index') }}">
                                    <i class="fas fa-chalkboard-teacher me-2"></i> Phân công giảng dạy
                                </a></li>
                            <li><a class="dropdown-item" href="{{ route('class_assignments.index') }}">
                                    <i class="fas fa-user-friends me-2"></i> Phân công lớp học
                                </a></li>
                        </ul>
                    </li>

                    <!-- Quản lý người dùng -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usersDropdown" role="button"
                           data-bs-toggle="dropdown">
                            <i class="fas fa-users me-1"></i> Quản lý người dùng
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('teachers.index') }}"><i
                                        class="fas fa-chalkboard-teacher me-2"></i> Giáo viên</a></li>
                            <li><a class="dropdown-item" href="{{ route('students.index') }}"><i
                                        class="fas fa-user-graduate me-2"></i> Học sinh</a></li>
                        </ul>
                    </li>

                    <!-- Quản lý năm học -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="academicDropdown" role="button"
                           data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-alt me-1"></i> Quản lý năm học
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('academic_years.index') }}"><i
                                        class="fas fa-calendar me-2"></i> Năm học</a></li>
                            <li><a class="dropdown-item" href="{{ route('semesters.index') }}"><i
                                        class="fas fa-calendar-week me-2"></i> Học kỳ</a></li>
                        </ul>
                    </li>
                @endif
                @if(Auth::user()->isTeacher() || Auth::user()->isStudent())
                    <!-- Tài liệu học tập -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="resourcesDropdown" role="button"
                           data-bs-toggle="dropdown">
                            <i class="fas fa-book me-1"></i> Tài liệu học tập
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('documents.index') }}"><i
                                        class="fas fa-file-download me-2"></i> Tài liệu học tập</a></li>
                        </ul>
                    </li>
                @endif
                    @if(Auth::user()->isTeacher())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-star me-1"></i> Quản lý điểm
                            </a>
                            <ul class="dropdown-menu">
                                @if(Auth::user()->isHomeroomTeacher(session('academic_year_id')))
                                    <li><a class="dropdown-item" href="{{ route('grades.homeroom') }}">
                                            <i class="fas fa-users me-2"></i> Điểm lớp chủ nhiệm
                                        </a></li>
                                @endif
                                <li><a class="dropdown-item" href="{{ route('grades.index') }}">
                                        <i class="fas fa-book me-2"></i> Điểm bộ môn
                                    </a></li>
                            </ul>
                        </li>
                    @endif
                    @if(Auth::user()->isHomeroomTeacher(session('academic_year_id')))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="usersDropdown" role="button"
                               data-bs-toggle="dropdown">
                                <i class="fas fa-bell me-1"></i> Thông báo
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('notifications.create') }}">
                                        <i class="fas fa-paper-plane me-2"></i> Gửi thông báo phụ huynh
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('notifications.history') }}">
                                        <i class="fas fa-paper-plane me-2"></i> Lịch sử thông báo
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    @if(Auth::user()->isTeacher())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-star me-1"></i> Quản lý đề thi
                            </a>
                            <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('exams.index') }}">
                                            <i class="fas fa-users me-2"></i> Đề thi
                                        </a></li>
                            </ul>
                        </li>
                    @endif
            @endif
        </ul>
        @if(Auth::check())
            @if(Auth::user()->isSuperAdmin())
                <!-- Quản trị hệ thống -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button"
                           data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i> Quản trị hệ thống
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('schools.index') }}"><i
                                        class="fas fa-school me-2"></i> Quản lý trường học</a></li>
                            <li><a class="dropdown-item" href="{{ route('school_admins.index') }}"><i
                                        class="fas fa-user-shield me-2"></i> Quản lý Admin</a></li>
                        </ul>
                    </li>
                </ul>
            @endif
        @endif
        <ul class="navbar-nav ms-auto">
            @auth
                <!-- Tài khoản người dùng -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button"
                       data-bs-toggle="dropdown">
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar"
                                 class="rounded-circle me-1"
                                 style="width: 30px; height: 30px; object-fit: cover;">
                        @else
                            <i class="fas fa-user-circle me-1"></i>
                        @endif
                        {{ Auth::user()->full_name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.index') }}">
                                <i class="fas fa-user me-2"></i> Hồ sơ cá nhân
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                            </a>
                        </li>
                    </ul>
                </li>
            @endauth

            @guest
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">
                        <i class="fas fa-sign-in-alt me-1"></i> Đăng nhập
                    </a>
                </li>
            @endguest
        </ul>
    </div>
</nav>
