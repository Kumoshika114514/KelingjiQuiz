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
        // Fetch the class with all related question sets and questions
        $class = QuizClass::with('questionSets.questions')->findOrFail($classId);

        // Get all quizzes (question sets) for this class
        $allQuizzes = $class->questionSets; // All question sets, no filters

        // Get all question IDs for the quizzes
        $allQuestionIds = $allQuizzes->flatMap->questions->pluck('id');

        // Get the student's answered question IDs
        $answeredQuestionIds = \App\Models\StudentAnswer::where('user_id', Auth::id())
            ->whereIn('question_id', $allQuestionIds)
            ->pluck('question_id')
            ->unique();

        // Separate quizzes into available and completed using collection methods
        $availableQuizzes = $allQuizzes->filter(function ($questionSet) use ($answeredQuestionIds) {
            $questionIds = $questionSet->questions->pluck('id');
            // Available if the student hasn't answered any of the questions in the quiz
            return $questionIds->diff($answeredQuestionIds)->isNotEmpty();
        });

        $completedQuizzes = $allQuizzes->filter(function ($questionSet) use ($answeredQuestionIds) {
            $questionIds = $questionSet->questions->pluck('id');
            // Completed if the student has answered all questions in the quiz
            return $questionIds->diff($answeredQuestionIds)->isEmpty();
        });

        // Return the class view with available and completed quizzes
        return view('student.viewClass', [
            'class' => $class,
            'availableQuizzes' => $availableQuizzes, // Display available quizzes
            'completedQuizzes' => $completedQuizzes // Display completed quizzes
        ]);
    }
}