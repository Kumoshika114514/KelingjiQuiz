<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizClassController;
use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\TeacherDashboardController;
use Illuminate\Support\Facades\Route;

// student's routes
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/join', [StudentClassController::class, 'create'])->middleware(['auth', 'verified'])->name('studentclasses.join');
Route::post('/', [StudentClassController::class, 'store'])->middleware(['auth', 'verified'])->name('studentclasses.store');

//teacher's routes
Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('teacher.dashboard');

Route::get('/teacher/create', [QuizClassController::class, 'create'])->name('quizclasses.create');
Route::post('/teacher', [QuizClassController::class, 'store'])->name('quizclasses.store');

Route::get('/teacher/quizclass/{id}', [QuizClassController::class, 'show'])->middleware(['auth', 'verified'])->name('teacher.quizclass');
Route::get('/quizclasses/{id}/edit', [QuizClassController::class, 'edit'])->middleware(['auth', 'verified'])->name('quizclasses.edit');
Route::put('/quizclasses/{id}', [QuizClassController::class, 'update'])->middleware(['auth', 'verified'])->name('quizclasses.update');


// global routes
Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', function () {
    return view('about');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
