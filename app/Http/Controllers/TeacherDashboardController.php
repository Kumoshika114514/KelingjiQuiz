<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuizClass;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $quizClasses = QuizClass::where('teacher_id', auth()->id())->get();
        return view('teacher.dashboard', compact('quizClasses'));
    }

    public function loadClasses()
    {
        $quizClasses = QuizClass::where('teacher_id', auth()->id())
            ->get(['id', 'name', 'created_at']);

        return response()->json([
            'classes' => $quizClasses,
        ]);
    }



}
