<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizClassController;
use App\Http\Controllers\QuestionSetController;
use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\Integrations\ClassSvcController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\ClickjackingHeaders;
use App\Http\Controllers\AttemptController;

// Student routes
Route::middleware([
    'auth',
    'verified',
    RoleMiddleware::class . ':student',
    ClickjackingHeaders::class,
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/join', [StudentClassController::class, 'create'])
        ->name('studentclasses.join');

    Route::post('/', [StudentClassController::class, 'store'])
        ->name('studentclasses.store');

    // Attempt routes (minimal payload; throttled)
    Route::post('/questionsets/{questionSet}/attempts', [AttemptController::class, 'start'])
        ->middleware('throttle:5,1') // 5 requests/min
        ->name('attempts.start');

    Route::post('/attempts/{attempt}/answer', [AttemptController::class, 'answer'])
        ->middleware('throttle:10,1') // 10 requests/min
        ->name('attempts.answer');

    Route::post('/attempts/{attempt}/submit', [AttemptController::class, 'submit'])
        ->middleware('throttle:5,1') // 5 requests/min
        ->name('attempts.submit');
});


// Teacher routes
Route::middleware(['auth', RoleMiddleware::class . ':teacher', ClickjackingHeaders::class])->group(function () {
    Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index'])
        ->middleware(['auth', 'verified'])->name('teacher.dashboard');

    Route::get('/teacher/create', [QuizClassController::class, 'create'])->name('quizclasses.create');
    Route::post('/teacher', [QuizClassController::class, 'store'])->name('quizclasses.store');

    // Class overview page
    Route::get('/teacher/quizclass/{quizClass}', function ($quizClass) {
        return view('teacher.quizclass', ['quizClassId' => $quizClass]);
    })->middleware(['auth', 'verified'])->name('teacher.quizclass');

    Route::get('/quizclasses/{quizClass}/edit', [QuizClassController::class, 'edit'])
        ->middleware(['auth', 'verified'])->name('quizclasses.edit');

    Route::put('/quizclasses/{quizClass}', [QuizClassController::class, 'update'])
        ->middleware(['auth', 'verified'])->name('quizclasses.update');

    Route::get('/quizclasses/{quizClass}/questionsets/create', [QuestionSetController::class, 'create'])
        ->middleware(['auth', 'verified'])->name('quizclasses.questionsets.create');

    Route::post('/quizclasses/{quizClass}/questionsets', [QuestionSetController::class, 'store'])
        ->middleware(['auth', 'verified'])->name('quizclasses.questionsets.store');

    Route::get('/teacher/questionset/{quizClass}/{questionSet}', [QuestionSetController::class, 'show'])
        ->middleware(['auth', 'verified'])->name('teacher.questionset');

    Route::get('/teacher/quizclass/{quizClass}/questionsets/{questionSet}', [QuestionSetController::class, 'show'])
        ->middleware(['auth', 'verified'])->name('teacher.quizclass.questionset');

    Route::delete('/studentclasses/{classId}/{studentId}', [StudentClassController::class, 'destroy'])
        ->name('studentclasses.destroy');

    // Question routes (within a QuestionSet)
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

    // Additional QuestionSet actions (teacher)
    Route::prefix('teacher/quizclass/{quizClassId}')->group(function () {
        Route::get('/questionsets/create', [QuestionSetController::class, 'create'])
            ->name('teacher.questionset.create');
        Route::post('/questionsets', [QuestionSetController::class, 'store'])
            ->name('teacher.questionset.store');

        Route::get('/questionsets/{questionSetId}', [QuestionSetController::class, 'show'])
            ->name('teacher.questionset');

        Route::post('/questionsets/{questionSetId}/schedule', [QuestionSetController::class, 'schedule'])
            ->name('teacher.questionset.schedule');
        Route::post('/questionsets/{questionSetId}/activate', [QuestionSetController::class, 'activate'])
            ->name('teacher.questionset.activate');
        Route::post('/questionsets/{questionSetId}/disable', [QuestionSetController::class, 'disable'])
            ->name('teacher.questionset.disable');
        Route::post('/questionsets/{questionSetId}/close', [QuestionSetController::class, 'close'])
            ->name('teacher.questionset.close');
        Route::post('/questionsets/{questionSetId}/archive', [QuestionSetController::class, 'archive'])
            ->name('teacher.questionset.archive');

        Route::post('/questionsets/{questionSetId}/toggle', [QuestionSetController::class, 'toggleStatus'])
            ->name('teacher.questionset.toggle');

        Route::get('/questionsets/{questionSetId}/highest-score', [QuestionSetController::class, 'getHighestScore'])
            ->name('teacher.questionset.highest');
    });

    Route::patch(
        '/integrations/classsvc/quizclass/{quizclass}/questionsets/{questionset}/toggle',
        [ClassSvcController::class, 'toggle']
    )->name('integrations.classsvc.questionsets.toggle');
});

// Global routes
Route::get('/', function () {
    return view('welcome'); })
    ->middleware(ClickjackingHeaders::class);
Route::get('/about', function () {
    return view('about'); })
    ->middleware(ClickjackingHeaders::class);

Route::middleware(['auth', ClickjackingHeaders::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// User preferences (uses Request; keep 'web' middleware for session)
Route::get('/user-preferences', function () {
    return response()->json([
        'theme' => session('theme', null),
        'font_size' => session('font_size', null),
    ]);
})->middleware(['web', ClickjackingHeaders::class]);

Route::post('/user-preferences', function (Request $request) {
    $request->validate(['theme' => 'required|in:light,dark']);
    session(['theme' => $request->input('theme')]);
    return response()->json(['ok' => true]);
})->middleware(['web', 'auth', ClickjackingHeaders::class]);

// API routes
Route::middleware(['auth', 'verified', ClickjackingHeaders::class])->prefix('api/teacher')->group(function () {
    Route::get('dashboard', [TeacherDashboardController::class, 'loadClasses']);

    Route::get('/quizclass/{quizclass}', [QuizClassController::class, 'loadQuizClassDetail']);
    Route::get('/quizclass/{quizclass}/questionsets', [QuizClassController::class, 'loadClassQuestionSets']);
    Route::get('/quizclass/{quizclass}/questionsets/{questionset}/highestscore', [QuestionSetController::class, 'getHighestScore']);

    Route::patch('/quizclass/{quizclass}/questionsets/{questionset}/toggle', [QuestionSetController::class, 'toggleStatus']);
    Route::get('/quizclass/{quizclass}/students', [QuizClassController::class, 'loadClassStudents']);

    });

require __DIR__ . '/auth.php';
