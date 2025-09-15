<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuizClass;
use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    public function index()
    {
        // Get all classes the student is enrolled in, with quizzes and questions eager loaded
        $quizClasses = QuizClass::with(['questionSets.questions'])->get();

        foreach ($quizClasses as $class) {
            // Filter quizzes that are active, within time window, and is_active
            $allQuizzes = $class->questionSets
                ->where('status', 1)
                ->where('is_active', 1)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now());

            // Get all question IDs for these quizzes
            $allQuestionIds = $allQuizzes->flatMap->questions->pluck('id');

            // Get all question IDs the student has answered
            $answeredQuestionIds = \App\Models\StudentAnswer::where('user_id', Auth::id())
                ->whereIn('question_id', $allQuestionIds)
                ->pluck('question_id')
                ->unique();

            // Count available quizzes (not fully completed)
            $availableQuizzesCount = $allQuizzes->filter(function ($questionSet) use ($answeredQuestionIds) {
                $questionIds = $questionSet->questions->pluck('id');
                return $questionIds->count() > 0 && !$questionIds->diff($answeredQuestionIds)->isEmpty();
            })->count();

            // Attach the count to the class object
            $class->available_quizzes_count = $availableQuizzesCount;
        }

        return view('dashboard', compact('quizClasses'));
    }
}