<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizClassController;
use App\Http\Controllers\QuestionSetController;
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

Route::get('/teacher/quizclass/{quizClass}', function ($quizClassId) {
    return view('teacher.quizclass', ['quizClassId' => $quizClassId]);
})->middleware(['auth', 'verified'])->name('teacher.quizclass');
Route::get('/quizclasses/{quizClass}/edit', [QuizClassController::class, 'edit'])->middleware(['auth', 'verified'])->name('quizclasses.edit');
Route::put('/quizclasses/{quizClass}', [QuizClassController::class, 'update'])->middleware(['auth', 'verified'])->name('quizclasses.update');

Route::get('/quizclasses/{quizClass}/questionsets/create', [QuestionSetController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('quizclasses.questionsets.create');

Route::post('/quizclasses/{quizClass}/questionsets', [QuestionSetController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('quizclasses.questionsets.store');

Route::get('/teacher/quizclass/{quizClass}/questionsets/{questionSet}', [QuestionSetController::class, 'show'])->middleware(['auth', 'verified'])->name('teacher.quizclass.questionset');

Route::delete('/studentclasses/{classId}/{studentId}', [StudentClassController::class, 'destroy'])
    ->name('studentclasses.destroy');


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

// API routes
Route::middleware(['auth', 'verified'])->prefix('api/teacher')->group(function () {
    Route::get('dashboard', [TeacherDashboardController::class, 'loadClasses']);

    Route::get('/quizclass/{quizclass}', [QuizClassController::class, 'loadQuizClassDetail']);

    Route::get('/quizclass/{quizclass}/questionsets', [QuizClassController::class, 'loadClassQuestionSets']);

    Route::get('/quizclass/{quizclass}/students', [QuizClassController::class, 'loadClassStudents']);

});



require __DIR__ . '/auth.php';
