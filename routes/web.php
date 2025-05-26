<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ClassAssignmentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradeLevelController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SchoolAdminController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\TeacherController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\TeacherMiddleware;
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\TeacherClass;
use Illuminate\Support\Facades\Route;

// routes/web.php

// Profile routes

Route::get('/', function () {
    return view('home.index');
})->name('home.index');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get("/login", [LoginController::class, 'showLoginForm']);
Route::post("/login", [LoginController::class, 'login'])->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware(SuperAdminMiddleware::class)->group(function () {
        // routes/web.php
        Route::get('/districts/{province}', [SchoolController::class, 'getDistricts']);
        Route::resource('schools', SchoolController::class);
        Route::prefix('schools/{school}')->group(function () {
            Route::get('/email-settings', [SchoolController::class, 'emailSettings'])->name('schools.email-settings');
            Route::put('/email-settings', [SchoolController::class, 'updateEmailSettings'])->name('schools.update-email-settings');
            Route::post('/test-email-settings', [SchoolController::class, 'testEmailSettings'])->name('schools.test-email-settings');
        });


        Route::resource('school_admins', SchoolAdminController::class);
    });

    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::resource('academic_years', AcademicYearController::class);
        // index, create, store, edit, update, destroy
        Route::resource('classes', ClassController::class);
        Route::resource('grade_levels', GradeLevelController::class);
        Route::resource('semesters', SemesterController::class);

        Route::prefix('students')->name('students.')->controller(StudentController::class)->group(function () {
            Route::post('/import', 'importDirect')->name('import');
            Route::get('/export-template', 'exportTemplate')->name('export.template');
            Route::get('/export', 'export')->name('export');
            Route::post('/get-by-grades', 'getStudentsByGrade')->name('getStudentsByGrade');
        });

        Route::resource('students', StudentController::class);

        Route::resource('teachers', TeacherController::class);
        Route::resource('teacher_assignments', TeacherAssignmentController::class);

        Route::get('/teachers/{teacher}/assignments/create', [TeacherAssignmentController::class, 'create'])
            ->name('teacher_assignments.create');
        Route::post('teachers/{teacher}/assignments', [TeacherAssignmentController::class, 'store'])
            ->name('teacher_assignments.store');
        Route::get('teacher-assignments/get-classes-by-year', [TeacherAssignmentController::class, 'getClassesByAcademicYear'])
            ->name('teacher_assignments.getClassesByAcademicYear');

        // Phân lớp học sinh
        Route::prefix('class_assignments')->name('class_assignments.')->group(function () {
            Route::get('/', [ClassAssignmentController::class, 'index'])->name('index');
            Route::get('/auto_assign', [ClassAssignmentController::class, 'showAutoAssignmentForm'])->name('auto_assign');
            Route::post('/auto_assign', [ClassAssignmentController::class, 'autoAssign'])->name('auto_assign.process');
            Route::get('/{class}', [ClassAssignmentController::class, 'showClassStudents'])->name('show');
            Route::post('/move_student/{id}', [ClassAssignmentController::class, 'moveStudent'])->name('move_student');
        });
    });


    Route::middleware(['auth'])->group(function () {
        // Nhóm route cho tài liệu (documents)
        Route::prefix('documents')->group(function () {
            Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
            Route::get('/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

            Route::middleware([TeacherMiddleware::class])->group(function () {
                Route::get('/create', [DocumentController::class, 'create'])->name('documents.create');
                Route::post('/', [DocumentController::class, 'store'])->name('documents.store');
                Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
                Route::put('/{document}', [DocumentController::class, 'update'])->name('documents.update');
                Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
            });
            Route::get('/{document}', [DocumentController::class, 'show'])->name('documents.show');

        });

// Nhóm route riêng cho điểm số (grades)
        Route::prefix('grades')->middleware([TeacherMiddleware::class])->group(function () {
            Route::get('/', [GradeController::class, 'index'])->name('grades.index');
            Route::get('/grades/export-template', [GradeController::class, 'exportTemplate'])->name('grades.exportTemplate');
            Route::post('/import', [GradeController::class, 'import'])->name('grades.import');
            Route::get('/student/{student}', [GradeController::class, 'viewAllGrades'])->name('grades.student_grades');
            Route::get('/homeroom-grades', [GradeController::class, 'homeroomGrades'])->name('grades.homeroom');
            // API hỗ trợ
            Route::get('/get-semesters-by-year', [GradeController::class, 'getSemestersByYear'])->name('get_semesters_by_year');
            Route::get('/get-classes-by-year', [GradeController::class, 'getClassesByYear'])->name('get_classes_by_year');
        });
    });
    Route::middleware('auth')->middleware([TeacherMiddleware::class])->group(function () {
        // Thông báo giáo viên chủ nhiệm
        Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/preview/{notification}', 'preview')->name('preview');
            Route::post('/send/{notification}', 'send')->name('send');
            Route::get('/history', 'history')->name('history');
            Route::get('/templates/{template}', 'getTemplateContent')
                ->name('template.content');
        });
    });


//        // Route cho trang nhập điểm (sửa lại bằng cách bỏ /grades thừa)
//        Route::get('/class/{class}/subject/{subject}/semester/{semester}/create', [
//            GradeController::class, 'create'
//        ])->name('grades.create');
//
//        // Route để lưu điểm
//        Route::post('/class/{class}/subject/{subject}/semester/{semester}/store', [GradeController::class, 'store'])
//            ->name('grades.store');
//
//        Route::get('/class/{class}/subject/{subject}/semester/{semester}',
//            [GradeController::class, 'show'])
//            ->name('grades.show');
});
