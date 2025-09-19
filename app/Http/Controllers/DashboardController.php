<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuizClass;
use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    public function index()
    {
        // Get all quiz classes that the student is enrolled in
        $quizClasses = QuizClass::whereHas('studentClasses', function ($q) {
            $q->where('student_id', auth()->id());
        })
            ->with([
                'questionSets' => function ($query) {
                    // Fetch only active question sets within the time window
                    $query->where('status', 1)
                        ->where('is_active', 1)
                        ->where('start_time', '<=', now())
                        ->where('end_time', '>=', now());
                },
                'questionSets.questions'
            ]) // Load questions for each question set
            ->get();

        foreach ($quizClasses as $class) {
            // Get all question IDs for these active quizzes
            $allQuizzes = $class->questionSets;

            // Get all question IDs for these quizzes
            $allQuestionIds = $allQuizzes->flatMap->questions->pluck('id');

            // Get all question IDs the student has answered
            $answeredQuestionIds = \App\Models\StudentAnswer::where('user_id', Auth::id())
                ->whereIn('question_id', $allQuestionIds)
                ->pluck('question_id')
                ->unique();

            // Count available quizzes (those that are not fully completed)
            $availableQuizzesCount = $allQuizzes->filter(function ($questionSet) use ($answeredQuestionIds) {
                // Get the question IDs for the current quiz
                $questionIds = $questionSet->questions->pluck('id');
                // Check if the student hasn't answered all the questions in this quiz
                return $questionIds->diff($answeredQuestionIds)->isNotEmpty();
            })->count();

            // Attach the count of available quizzes to the class object
            $class->available_quizzes_count = $availableQuizzesCount;
        }

        // Pass quiz classes to the view
        return view('dashboard', compact('quizClasses'));
    }

}