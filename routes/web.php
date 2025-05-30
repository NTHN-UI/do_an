<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ExamAssignmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\StudentExamController;
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
    Route::resource('profile', ProfileController::class)->only(['index', 'edit', 'update']);

    Route::middleware(SuperAdminMiddleware::class)->group(function () {
        // routes/web.php
        Route::resource('schools', SchoolController::class);
        Route::resource('school_admins', SchoolAdminController::class);

        Route::get('/districts/{province}', [SchoolController::class, 'getDistricts']);
        Route::prefix('schools/{school}')->group(function () {
            Route::get('/email-settings', [SchoolController::class, 'emailSettings'])->name('schools.email-settings');
            Route::put('/email-settings', [SchoolController::class, 'updateEmailSettings'])->name('schools.update-email-settings');
            Route::post('/test-email-settings', [SchoolController::class, 'testEmailSettings'])->name('schools.test-email-settings');
        });
    });

    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::resource('academic_years', AcademicYearController::class);
        Route::resource('classes', ClassController::class);
        Route::resource('grade_levels', GradeLevelController::class);
        Route::resource('semesters', SemesterController::class);
        Route::resource('students', StudentController::class);
        Route::resource('teachers', TeacherController::class);
        Route::resource('teacher_assignments', TeacherAssignmentController::class);

        Route::prefix('students')->name('students.')->controller(StudentController::class)->group(function () {
            Route::post('/import', 'importDirect')->name('import');
            Route::get('/export-template', 'exportTemplate')->name('export.template');
            Route::get('/export', 'export')->name('export');
            Route::post('/get-by-grades', 'getStudentsByGrade')->name('getStudentsByGrade');

        });

        Route::get('/teachers/{teacher}/assignments/create', [TeacherAssignmentController::class, 'create'])
            ->name('teacher_assignments.create');
        Route::post('teachers/{teacher}/assignments', [TeacherAssignmentController::class, 'store'])
            ->name('teacher_assignments.store');
        Route::get('teacher-assignments/get-classes-by-year', [TeacherAssignmentController::class, 'getClassesByAcademicYear'])
            ->name('teacher_assignments.getClassesByAcademicYear');

        // Phân lớp học sinh
        Route::prefix('class_assignments')->name('class_assignments.')->controller(ClassAssignmentController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/auto_assign', 'showAutoAssignmentForm')->name('auto_assign');
            Route::post('/auto_assign', 'autoAssign')->name('auto_assign.process');
            Route::get('/{class}', 'showClassStudents')->name('show');
            Route::post('/change_class/{id}', 'changeClassStudent')->name('change_class');
            Route::post('/advance_class', 'advanceClassStudents')->name('advance_class');
        });
    });

    Route::middleware(['auth'])->group(function () {
        // Nhóm route cho tài liệu (documents)
        Route::middleware([TeacherMiddleware::class])->group(function () {
            Route::resource('documents', DocumentController::class)->except('index', 'show');
        });

        Route::controller(DocumentController::class)->prefix('documents')->name('documents.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{document}/download', 'download')->name('download');
            Route::get('/{document}', 'show')->name('show');
        });

        // Nhóm route riêng cho điểm số (grades)
        Route::controller(GradeController::class)
            ->prefix('grades')->name('grades.')
            ->middleware([TeacherMiddleware::class])->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/grades/export-template', 'exportTemplate')->name('exportTemplate');
                Route::post('/import', 'import')->name('import');
                Route::get('/student/{student}', 'viewAllGrades')->name('student_grades');
                Route::get('/homeroom-grades', 'homeroomGrades')->name('homeroom');
                // API hỗ trợ
                Route::get('/get-semesters-by-year', 'getSemestersByYear')->name('get_semesters_by_year');
                Route::get('/get-classes-by-year', 'getClassesByYear')->name('get_classes_by_year');
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
    Route::middleware('auth')->middleware([TeacherMiddleware::class])->group(function () {
        Route::prefix('exams')->group(function () {
            Route::get('/', [ExamController::class, 'index'])->name('exams.index');
            Route::get('/create', [ExamController::class, 'create'])->name('exams.create');
            Route::post('/', [ExamController::class, 'store'])->name('exams.store');
            Route::get('/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
            Route::put('/{exam}', [ExamController::class, 'update'])->name('exams.update');
            Route::get('/{exam}', [ExamController::class, 'show'])->name('exams.show');
            Route::delete('/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
            Route::post('exams/preview', [ExamController::class, 'preview'])->name('exams.preview');
            Route::put('exams/preview', [ExamController::class, 'preview'])->name('exams.preview.put');

            // Xuất bản đề thi
            Route::post('/{exam}/publish', [ExamController::class, 'publish'])
                ->name('exams.publish');



            // Xuất đề thi ra Word
            Route::get('/{exam}/export-word', [ExamController::class, 'exportWord'])
                ->name('exams.export-word');
            Route::post('/question-bank/get-questions', [QuestionBankController::class, 'getQuestions'])
                ->name('question-bank.get-questions');
        });
        Route::get('/get-semesters-by-year', [ExamController::class, 'getSemestersByYear']);

        Route::prefix('exam_assignments')->name('exam_assignments.')->group(function () {
            // Tạo form giao đề
            Route::get('/', [ExamAssignmentController::class, 'index'])
                ->name('index');

            Route::get('/create/{exam}', [ExamAssignmentController::class, 'create'])
                ->name('create');

            // Lưu thông tin giao đề
            Route::post('/store/{exam}', [ExamAssignmentController::class, 'store'])
                ->name('store');

        });
    });
    Route::middleware(['auth'])->prefix('student_exams')->name('student_exams.')->group(function() {
        Route::get('/assigned-exams', [StudentExamController::class, 'assignedExams'])->name('assigned_exams');
        Route::get('/', [StudentExamController::class, 'index'])->name('index');
        Route::get('/{assignment}', [StudentExamController::class, 'show'])->name('show');
        Route::post('/{assignment}/submit', [StudentExamController::class, 'submit'])->name('submit');
        Route::get('/{assignment}/result', [StudentExamController::class, 'result'])->name('result');
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
