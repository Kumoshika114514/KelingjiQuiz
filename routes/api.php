<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\AttemptController;
use App\Http\Controllers\QuestionSetController;
use App\Http\Controllers\QuizClassController;

//public API routes, for register and login 
Route::post('/register', [RegisterController::class, 'register'])->name('api.register');
Route::post('/login',    [LoginController::class, 'login'])->name('api.login');

// protected API endpoints (require token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class,'logout']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post('/attempts', [AttemptController::class, 'store']);

Route::middleware('auth:sanctum')->patch(
    '/teacher/quizclass/{quizclass}/questionsets/{questionset}/toggle',
    [QuestionSetController::class, 'toggleStatus']
)->name('api.questionsets.toggle');

Route::middleware('auth:sanctum')->get('/classes/{id}/students', [QuizClassController::class, 'loadClassStudents']);
