<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Models\QuizClass;
use App\Models\StudentClass;
class StudentClassController extends Controller
{
    public function create()
    {
        return view("join");
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_code' => 'required|string'
        ]);

        $quizClass = QuizClass::where('class_code', $request->class_code)->first();

        if (!$quizClass) {
            return back()->with('error', 'Class code not found.');
        }

        StudentClass::firstOrCreate([
            'student_id' => Auth::id(),
            'class_id' => $quizClass->id,
        ]);

        return Redirect::route('dashboard')->with('success', 'Successfully joined class.');
    }

    public function destroy($classId, $studentId)
    {
        $studentClass = StudentClass::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->first();

        if ($studentClass) {
            $studentClass->delete();
            return Redirect::back()->with('success', 'Student removed from the class.');
        }

        return Redirect::back()->with('error', 'Student not found in this class.');
    }

    public function show($classId)
    {
        // Fetch the class with related question sets and questions
        $class = QuizClass::with('questionSets.questions')->findOrFail($classId);

        // Get all question sets for this class (active and within time)
        $allQuizzes = $class->questionSets()
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->where('status', 1)
            ->where('is_active', 1)
            ->get();

        // Get all question IDs for all quizzes in this class
        $allQuestionIds = $allQuizzes->flatMap->questions->pluck('id');

        // Get all question IDs the student has answered
        $answeredQuestionIds = \App\Models\StudentAnswer::where('user_id', Auth::id())
            ->whereIn('question_id', $allQuestionIds)
            ->pluck('question_id')
            ->unique();

        $availableQuizzesCount = $allQuizzes->filter(function ($questionSet) use ($answeredQuestionIds) {
            $questionIds = $questionSet->questions->pluck('id');
            return $questionIds->count() > 0 && !$questionIds->diff($answeredQuestionIds)->isEmpty();
        })->count();

        $class->available_quizzes_count = $availableQuizzesCount;

        // Separate quizzes into completed and not completed
        $completedQuizzes = $allQuizzes->filter(function ($questionSet) use ($answeredQuestionIds) {
            $questionIds = $questionSet->questions->pluck('id');
            // Completed if student answered ALL questions in this set
            return $questionIds->count() > 0 && $questionIds->diff($answeredQuestionIds)->isEmpty();
        });

        $notCompletedQuizzes = $allQuizzes->filter(function ($questionSet) use ($answeredQuestionIds) {
            $questionIds = $questionSet->questions->pluck('id');
            // Not completed if there are unanswered questions
            return $questionIds->count() > 0 && !$questionIds->diff($answeredQuestionIds)->isEmpty();
        });

        return view('student.viewClass', [
            'class' => $class,
            'availableQuizzes' => $notCompletedQuizzes,
            'completedQuizzes' => $completedQuizzes
        ]);
    }
}