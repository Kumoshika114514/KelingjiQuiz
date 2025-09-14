<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Facades\Statistic;
use App\Models\QuizClass;
use App\Models\QuestionSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class QuestionSetController extends Controller
{
    public function create($quizclassId)
    {
        return view('teacher.createquestionset', compact('quizclassId'));
    }


    public function store(Request $request, $quizClassId)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'description' => 'nullable|string',
            'question_type' => 'required|string|in:mcq,true_false,short_answer',
            'answer_time' => 'required|integer|min:5',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_realtime' => 'nullable|boolean',
        ]);

        // make sure the quiz class exists
        $quizClass = QuizClass::findOrFail($quizClassId);

        $quizClass->questionSets()->create([
            'topic' => $validated['topic'],
            'description' => $validated['description'] ?? null,
            'question_type' => $validated['question_type'],
            'answer_time' => $validated['answer_time'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_realtime' => $request->has('is_realtime'),
            'is_active' => true,
            'question_count' => 0,
            'user_id' => Auth::id(),
        ]);

        return Redirect::route('teacher.quizclass', $quizClassId)
            ->with('success', 'Question set created successfully.');
    }

    public function show($quizClassId, $questionSetId)
    {
        $questionSet = QuestionSet::findOrFail($questionSetId);

        return view('teacher.questionset', compact('questionSet'));
    }

    public function toggleStatus($quizclass, $questionset)
    {
        try {
            $set = QuestionSet::where('id', $questionset)
                ->where('quiz_class_id', $quizclass)
                ->firstOrFail();

            // make sure the user perform this action is the owner of the questionset
            if ($set->quizClass->user_id !== Auth::id()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            // toggle status (1 = active, 0 = disabled)
            $set->status = $set->status === 1 ? 0 : 1;
            $set->save();

            return Response::json([
                'success' => true,
                'status' => $set->status,
                'message' => $set->status === 1
                    ? 'Question set activated.'
                    : 'Question set closed.'
            ], 200);

        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to toggle status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // not used
    public function getHighestScore($quizclass, $questionset)
    {
        $highestScore = Statistic::getHighestScoreInQuiz($questionset);

        return Response::json([
            'highestScore' => $highestScore,
        ], 200);
    }

}
