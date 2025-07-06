<style>
    /* Sidebar Styles */
    .sidebar {
        width: 250px;
        min-height: 100vh;
        background-color: #013066;
        color: white;
        position: fixed;
        transition: all 0.3s;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        z-index: 1000;
    }

    .sidebar-brand {
        height: 70px;
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        color: white;
        text-decoration: none;
        font-weight: 700;
        font-size: 1.2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-brand img {
        height: 40px;
        margin-right: 10px;
    }

    .sidebar-nav {
        padding: 0;
        list-style: none;
    }

    .sidebar-item {
        position: relative;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s;
    }

    .sidebar-link:hover,
    .sidebar-link:focus {
        color: white;
        background-color: rgba(255, 255, 255, 0.1);
    }

    .sidebar-link.active {
        color: white;
        background-color: rgba(255, 255, 255, 0.2);
    }

    .sidebar-link.active::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background-color: #1a3b8b;
    }

    .sidebar-icon {
        margin-right: 0.75rem;
        font-size: 1rem;
        width: 20px;
        text-align: center;
    }

    .sidebar-dropdown {
        list-style: none;
        padding: 0;
        background-color: rgba(0, 0, 0, 0.1);
    }

    .sidebar-dropdown .sidebar-link {
        padding-left: 3rem;
    }

    .sidebar-divider {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin: 1rem 0;
    }

    .sidebar-footer {
        position: absolute;
        bottom: 0;
        width: 100%;

        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .user-menu {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
        color: white;
        text-decoration: none;
    }

    .user-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 0.75rem;
        object-fit: cover;
    }

    /* Toggle button for mobile */
    .sidebar-toggler {
        position: fixed;
        top: 10px;
        left: 10px;
        z-index: 1100;
        background: #013066;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 5px 10px;
        display: none;
    }

    @media (max-width: 992px) {
        .sidebar {
            margin-left: -250px;
        }
        .sidebar.show {
            margin-left: 0;
        }
        .sidebar-toggler {
            display: block;
        }
    }

    /* Main content area adjustment */
    .main-content {
        margin-left: 250px;
        transition: all 0.3s;
    }

    @media (max-width: 992px) {
        .main-content {
            margin-left: 0;
        }
    }
</style>

<!-- Sidebar Toggler Button (Mobile) -->
<button class="sidebar-toggler" type="button">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar" id="sidebar">
    <a class="sidebar-brand" href="#">
        <img src="{{ asset('build/assets/img/education_4207253.png') }}" alt="Logo">
        <div class="d-flex flex-column">
            <span class="fw-semibold text-center fs-6 ">HỆ THỐNG QUẢN LÝ TRƯỜNG HỌC</span>
            <small class="fw-normal" style="font-size: 0.7rem">School Management System</small>
        </div>
    </a>

    <ul class="sidebar-nav">
        <li class="sidebar-item">
            <a class="sidebar-link active" href="{{ route('home.index') }}">
                <i class="fas fa-home sidebar-icon"></i> Trang chủ
            </a>
        </li>

        @if(Auth::check())
            @if(Auth::user()->isSchoolAdmin())
                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#academicMenu">
                        <i class="fas fa-calendar-alt sidebar-icon"></i> Quản lý năm học
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div id="academicMenu" class="collapse">
                        <ul class="sidebar-dropdown">
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('academic_years.index') }}">
                                    <i class="fas fa-calendar-check sidebar-icon"></i> Năm học
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('semesters.index') }}">
                                    <i class="fas fa-calendar-week sidebar-icon"></i> Học kỳ
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#learningMenu">
                        <i class="fas fa-book-open sidebar-icon"></i> Quản lý học tập
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div id="learningMenu" class="collapse">
                        <ul class="sidebar-dropdown">
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('classes.index') }}">
                                    <i class="fas fa-chalkboard sidebar-icon"></i> Lớp học
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('grade_levels.index') }}">
                                    <i class="fas fa-layer-group sidebar-icon"></i> Khối lớp
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#usersMenu">
                        <i class="fas fa-users-cog sidebar-icon"></i> Quản lý người dùng
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div id="usersMenu" class="collapse">
                        <ul class="sidebar-dropdown">
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('teachers.index') }}">
                                    <i class="fas fa-chalkboard-teacher sidebar-icon"></i> Giáo viên
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('students.index') }}">
                                    <i class="fas fa-user-graduate sidebar-icon"></i> Học sinh
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#assignmentMenu">
                        <i class="fas fa-tasks sidebar-icon"></i> Quản lý phân công
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div id="assignmentMenu" class="collapse">
                        <ul class="sidebar-dropdown">
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('teacher_assignments.index') }}">
                                    <i class="fas fa-user-tie sidebar-icon"></i> Phân công giảng dạy
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('class_assignments.index') }}">
                                    <i class="fas fa-users sidebar-icon"></i> Phân công lớp học
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#resultsMenu">
                        <i class="fas fa-chart-line sidebar-icon"></i> Kết quả học tập
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div id="resultsMenu" class="collapse">
                        <ul class="sidebar-dropdown">
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('grades.admin_grades') }}">
                                    <i class="fas fa-table sidebar-icon"></i> Xem điểm toàn trường
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            @if(Auth::user()->isTeacher())
                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#gradesMenu">
                        <i class="fas fa-clipboard-check sidebar-icon"></i> Quản lý điểm
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div id="gradesMenu" class="collapse">
                        <ul class="sidebar-dropdown">
                            @if(Auth::user()->isHomeroomTeacher(session('academic_year_id')))
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="{{ route('grades.homeroom') }}">
                                        <i class="fas fa-clipboard-list sidebar-icon"></i> Điểm lớp chủ nhiệm
                                    </a>
                                </li>
                            @endif
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('grades.index') }}">
                                    <i class="fas fa-bookmark sidebar-icon"></i> Điểm bộ môn
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                    @if(Auth::user()->isHomeroomTeacher(session('academic_year_id')))
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('homeroom_teacher.index') }}">
                                <i class="fas fa-users sidebar-icon"></i> Quản lý học sinh
                            </a>
                        </li>
                    @endif
                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#examsMenu">
                        <i class="fas fa-file-signature sidebar-icon"></i> Quản lý đề thi
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div id="examsMenu" class="collapse">
                        <ul class="sidebar-dropdown">
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('exams.index') }}">
                                    <i class="fas fa-file-alt sidebar-icon"></i> Đề thi
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('exam_assignments.index') }}">
                                    <i class="fas fa-tasks sidebar-icon"></i> Đề thi đã giao
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            @if(Auth::user()->isStudent())
                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#studentGradesMenu">
                        <i class="fas fa-star-half-alt sidebar-icon"></i> Điểm số
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div id="studentGradesMenu" class="collapse">
                        <ul class="sidebar-dropdown">
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('student_grades') }}">
                                    <i class="fas fa-list-ol sidebar-icon"></i> Tất cả năm
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('student_exams.assigned_exams') }}">
                        <i class="fas fa-file-download sidebar-icon"></i> Nhận đề
                    </a>
                </li>
            @endif

            @if(Auth::user()->isHomeroomTeacher(session('academic_year_id')))
                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#notificationsMenu">
                        <i class="fas fa-bell sidebar-icon"></i> Thông báo
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div id="notificationsMenu" class="collapse">
                        <ul class="sidebar-dropdown">
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('notifications.create') }}">
                                    <i class="fas fa-paper-plane sidebar-icon"></i> Gửi thông báo phụ huynh
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('notifications.history') }}">
                                    <i class="fas fa-history sidebar-icon"></i> Lịch sử thông báo
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            @if(Auth::user()->isTeacher() || Auth::user()->isStudent())
                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#resourcesMenu">
                        <i class="fas fa-book-reader sidebar-icon"></i> Tài liệu học tập
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div id="resourcesMenu" class="collapse">
                        <ul class="sidebar-dropdown">
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('documents.index') }}">
                                    <i class="fas fa-file-alt sidebar-icon"></i> Tài liệu học tập
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            @if(Auth::user()->isSuperAdmin())
                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#adminMenu">
                        <i class="fas fa-cogs sidebar-icon"></i> Quản trị hệ thống
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div id="adminMenu" class="collapse">
                        <ul class="sidebar-dropdown">
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('schools.index') }}">
                                    <i class="fas fa-school sidebar-icon"></i> Quản lý trường học
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route('school_admins.index') }}">
                                    <i class="fas fa-user-shield sidebar-icon"></i> Quản lý Admin
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif
        @endif
    </ul>

    <div class="sidebar-footer">
        <div class="dropdown">
            <a href="#" class="user-menu dropdown-toggle" data-bs-toggle="dropdown">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="user-avatar">
                @else
                    <i class="fas fa-user-circle sidebar-icon"></i>
                @endif
                <span>{{ Auth::user()->full_name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-center">
                <li>
                    <a class="dropdown-item" href="{{ route('profile.index') }}">
                        <i class="fas fa-user-edit me-2"></i> Hồ sơ cá nhân
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- JavaScript for Sidebar Toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggler = document.querySelector('.sidebar-toggler');

        toggler.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });

        // Auto collapse other menus when one is opened
        document.querySelectorAll('.sidebar-link[data-bs-toggle="collapse"]').forEach(link => {
            link.addEventListener('click', function() {
                const target = this.getAttribute('href');
                document.querySelectorAll('.collapse.show').forEach(openMenu => {
                    if (openMenu.id !== target.substring(1)) {
                        openMenu.classList.remove('show');
                    }
                });
            });
        });
    });
</script>
