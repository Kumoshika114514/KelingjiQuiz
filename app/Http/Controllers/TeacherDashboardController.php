<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

use App\Models\QuizClass;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $quizClasses = QuizClass::where('user_id', Auth::id())->get();
        return view('teacher.dashboard', compact('quizClasses'));
    }

    public function loadClasses()
    {
        $quizClasses = QuizClass::where('user_id', Auth::id())
            ->get(['id', 'name', 'created_at']);

        return Response::json([
            'classes' => $quizClasses,
        ]);
    }



}
