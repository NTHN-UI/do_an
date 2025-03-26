<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\GradeLevelController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
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


