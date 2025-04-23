<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ClassAssignmentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradeLevelController;
use App\Http\Controllers\MaterialController;
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

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});


Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get("/login", [LoginController::class, 'showLoginForm']);
Route::post("/login", [LoginController::class, 'login'])->name('login');


Route::middleware(SuperAdminMiddleware::class)->group(function () {
    Route::resource('schools', SchoolController::class);
    Route::resource('school_admins', SchoolAdminController::class);
});

Route::middleware(AdminMiddleware::class)->group(function () {
    Route::resource('academic_years', AcademicYearController::class);
    Route::resource('classes', ClassController::class);
    Route::resource('grade_levels', GradeLevelController::class);
    Route::resource('semesters', SemesterController::class);

    Route::prefix('students')->name('students.')->controller(StudentController::class)->group(function () {
        Route::post('/import', 'importDirect')->name('import');
        Route::get('/export-template', 'exportTemplate')->name('export.template');
        Route::get('/export', 'export')->name('export');
    });

    Route::resource('students', StudentController::class);

    Route::resource('teachers', TeacherController::class);
    Route::resource('teacher_assignments', TeacherAssignmentController::class);

    Route::get('/teachers/{teacher}/assignments/create', [TeacherAssignmentController::class, 'create'])
        ->name('teacher_assignments.create');
    Route::post('teachers/{teacher}/assignments', [TeacherAssignmentController::class, 'store'])
        ->name('teacher_assignments.store');
// Phân lớp học sinh
    Route::prefix('class_assignments')->name('class_assignments.')->group(function () {
        Route::get('/', [ClassAssignmentController::class, 'index'])->name('index');
        Route::get('/auto_assign', [ClassAssignmentController::class, 'showAutoAssignmentForm'])->name('auto_assign');
        Route::post('/auto_assign', [ClassAssignmentController::class, 'autoAssign'])->name('auto_assign.process');
        Route::get('/{class}', [ClassAssignmentController::class, 'showClassStudents'])->name('show');
        Route::post('/move_student', [ClassAssignmentController::class, 'moveStudent'])->name('move_student');
    });
});


Route::middleware(['auth'])->group(function () {
    // Nhóm route cho tài liệu (materials)
    Route::prefix('materials')->group(function () {
        Route::get('/', [MaterialController::class, 'index'])->name('materials.index');
        Route::get('/{material}/download', [MaterialController::class, 'download'])->name('materials.download');

        Route::middleware([TeacherMiddleware::class])->group(function () {
            Route::get('/create', [MaterialController::class, 'create'])->name('materials.create');
            Route::post('/', [MaterialController::class, 'store'])->name('materials.store');
            Route::get('/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit');
            Route::put('/{material}', [MaterialController::class, 'update'])->name('materials.update');
            Route::delete('/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
        });
        Route::get('/{material}', [MaterialController::class, 'show'])->name('materials.show');

    });

// Nhóm route riêng cho điểm số (grades)
    Route::prefix('grades')->middleware([TeacherMiddleware::class])->group(function () {
        Route::get('/', [GradeController::class, 'index'])->name('grades.index');
        Route::get('/export', [GradeController::class, 'exportTemplate'])->name('grades.export');
        Route::post('/import', [GradeController::class, 'import'])->name('grades.import');
        Route::get('/student/{student}', [GradeController::class, 'viewAllGrades'])->name('grades.student_grades');

        // API hỗ trợ
        Route::get('/api/get-semesters-by-year', [GradeController::class, 'getSemestersByYear'])->name('get_semesters_by_year');
        Route::get('/api/get-classes-by-year', [GradeController::class, 'getClassesByYear'])->name('get_classes_by_year');
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
