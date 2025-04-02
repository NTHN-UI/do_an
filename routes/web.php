<?php

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
use App\Models\Semester;
use App\Models\StudentClass;
use App\Models\TeacherClass;
use Illuminate\Support\Facades\Route;



Route::resource('academic_years', AcademicYearController::class);
Route::resource('classes', ClassController::class);
Route::resource('grade_levels', GradeLevelController::class);
Route::resource('semesters', SemesterController::class);
Route::resource('students', StudentController::class);
Route::resource('teachers', TeacherController::class);
Route::resource('schools', SchoolController::class);
Route::resource('school_admins', SchoolAdminController::class);
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

Route::resource('materials', MaterialController::class);
Route::get('materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
Route::prefix('grades')->group(function () {
    Route::get('/', [GradeController::class, 'index'])->name('grades.index');
    Route::get('/{class}/{subject}/{semester}/create', [GradeController::class, 'create'])
        ->name('grades.create');
    Route::post('/{class}/{subject}/{semester}', [GradeController::class, 'store'])
        ->name('grades.store');
    Route::get('/{class}/{subject}/{semester}', [GradeController::class, 'show'])
        ->name('grades.show');
});
