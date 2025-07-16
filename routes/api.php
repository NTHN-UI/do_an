<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExamController;



Route::get('/exams', [ExamController::class, 'index']);
Route::post('/exams', [ExamController::class, 'store']);

