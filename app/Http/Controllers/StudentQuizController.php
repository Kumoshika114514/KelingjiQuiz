<?php

namespace App\Http\Controllers;

use App\Models\QuestionSet;
use App\Models\QuizAttempt;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;

class StudentQuizController extends Controller
{
    public function takeQuiz($questionSetId)
    {
        $questionSet = QuestionSet::with('questions')->findOrFail($questionSetId);

        // Get all question IDs for this quiz
        $questionIds = $questionSet->questions->pluck('id');

        return view('student.quizzes.takeQuiz', compact('questionSet'));
    }

    public function submit(Request $request, $questionSetId)
    {
        $questionSet = QuestionSet::with('questions')->findOrFail($questionSetId);

        $rules = [];
        foreach ($questionSet->questions as $question) {
            $rules["answers.{$question->id}"] = 'required';
        }

        $messages = [
            'required' => 'Answer cannot be empty.',
        ];

        $validated = $request->validate($rules, $messages);

        // Create a new QuizAttempt (optional, for tracking)
        $quizAttempt = QuizAttempt::create([
            'student_id' => auth()->id(),
            'quiz_id' => $questionSet->id,
            'started_at' => now(),
            'completed_at' => now(),
            'score' => 0, // will update later
            'total_points' => $questionSet->questions->count(),
            'is_completed' => true,
            'attempt_number' => 1, // or increment if you support multiple attempts
        ]);

        // Save student answers
        foreach ($questionSet->questions as $question) {
            $answer = $request->input('answers.' . $question->id);

            StudentAnswer::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'question_id' => $question->id,
                ],
                [
                    'answer' => $answer,
                ]
            );
        }

        // Calculate score
        $score = 0;
        $answers = StudentAnswer::where('user_id', auth()->id())
            ->whereIn('question_id', $questionSet->questions->pluck('id'))
            ->get();

        foreach ($answers as $studentAnswer) {
            $question = $questionSet->questions->where('id', $studentAnswer->question_id)->first();
            if ($question) {
                // Multiple Choice
                if ($question->correct_choice && strtolower($studentAnswer->answer) == strtolower($question->correct_choice)) {
                    $score++;
                }
                // Short Answer
                elseif ($question->correct_text && trim(strtolower($studentAnswer->answer)) == trim(strtolower($question->correct_text))) {
                    $score++;
                }
                // True/False
                elseif (!is_null($question->correct_bool) && (
                    strtolower($studentAnswer->answer) == ($question->correct_bool ? 'true' : 'false')
                )) {
                    $score++;
                }
            }
        }

        // Update score in QuizAttempt
        $quizAttempt->score = $score;
        $quizAttempt->save();

        return redirect()->route('student.quizzes.summary', $questionSet->id)
            ->with('success', 'Quiz submitted successfully!');
    }

    public function summary($questionSetId)
    {
        $questionSet = QuestionSet::with('questions')->findOrFail($questionSetId);

        // Get all answers for this user and this quiz
        $answers = StudentAnswer::where('user_id', auth()->id())
            ->whereIn('question_id', $questionSet->questions->pluck('id'))
            ->get()
            ->keyBy('question_id');

        return view('student.quizzes.summary', compact('questionSet', 'answers'));
    }

    public function liveUpdate(Request $request, $questionSetId)
    {
        // You can save partial answers or update a "last seen" timestamp for monitoring
        // Example: store in a QuizAttempt or a temp table for teacher monitoring
        // For now, just return OK
        return response()->json(['status' => 'ok']);
    }
}