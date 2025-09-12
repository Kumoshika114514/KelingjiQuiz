<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizClassController;
use App\Http\Controllers\QuestionSetController;
use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;

// student's routes
Route::middleware(['auth', RoleMiddleware::class . ':student'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
    Route::get('/join', [StudentClassController::class, 'create'])->middleware(['auth', 'verified'])->name('studentclasses.join');
    Route::post('/', [StudentClassController::class, 'store'])->middleware(['auth', 'verified'])->name('studentclasses.store');
});

//teacher's routes
Route::middleware(['auth', RoleMiddleware::class . ':teacher'])->group(function () {
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

    Route::get('/teacher/questionset/{quizClass}/{questionSet}', [QuestionSetController::class, 'show'])
        ->middleware(['auth', 'verified'])
        ->name('teacher.questionset');

    Route::get('/teacher/quizclass/{quizClass}/questionsets/{questionSet}', [QuestionSetController::class, 'show'])->middleware(['auth', 'verified'])->name('teacher.quizclass.questionset');

    Route::delete('/studentclasses/{classId}/{studentId}', [StudentClassController::class, 'destroy'])
        ->name('studentclasses.destroy');

    Route::middleware(['auth', 'verified'])
        ->prefix('teacher/quizclass/{quizClass}/questionsets/{questionSet}')
        ->name('teacher.questions.')
        ->group(function () {
            Route::get('/questions', [QuestionController::class, 'index'])->name('index');
            Route::get('/questions/create', [QuestionController::class, 'create'])->name('create');
            Route::post('/questions', [QuestionController::class, 'store'])->name('store');
            Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('show');
            Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('edit');
            Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('update');
            Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('destroy');
            Route::get('/questions/{question}/preview', [QuestionController::class, 'preview'])->name('preview');
            Route::post('/questions/reorder', [QuestionController::class, 'reorder'])->name('reorder');
        });

});

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

//User preferences 
Route::get('/user-preferences', function () {
    return response()->json([
        'theme' => session('theme', null),
        'font_size' => session('font_size', null),
    ]);
})->middleware('web');

//for authenticated users only
Route::post('/user-preferences', function (Request $request) {
    $request->validate(['theme' => 'required|in:light,dark']);
    session(['theme' => $request->input('theme')]);
    return response()->json(['ok' => true]);
})->middleware(['web', 'auth']);

// API routes
Route::middleware(['auth', 'verified'])->prefix('api/teacher')->group(function () {
    Route::get('dashboard', [TeacherDashboardController::class, 'loadClasses']);

    Route::get('/quizclass/{quizclass}', [QuizClassController::class, 'loadQuizClassDetail']);

    Route::get('/quizclass/{quizclass}/questionsets', [QuizClassController::class, 'loadClassQuestionSets']);

    Route::get('/quizclass/{quizclass}/questionsets/{questionset}/highestscore', [QuestionSetController::class, 'getHighestScore']);

    Route::patch('/quizclass/{quizclass}/questionsets/{questionset}/toggle', [QuestionSetController::class, 'toggleStatus']);

    Route::get('/quizclass/{quizclass}/students', [QuizClassController::class, 'loadClassStudents']);
});

// API routes for students 
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
});



require __DIR__ . '/auth.php';
