<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GradeLevelController;
Route::get('/', function () {
    return view('welcome');
});
Route::resource('grade_levels', GradeLevelController::class);

