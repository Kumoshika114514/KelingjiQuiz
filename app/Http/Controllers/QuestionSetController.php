<?php

namespace App\Http\Controllers;

use App\Models\QuizClass;
use App\Models\QuestionSet;
use Illuminate\Http\Request;

class QuestionSetController extends Controller
{
    public function create($quizclassId)
    {
        return view('teacher.createquestionset', compact('quizclassId'));
    }


    public function store(Request $request, $quizClassId)
    {
        // Validate inputs
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'description' => 'nullable|string',
            'question_type' => 'required|string|in:mcq,true_false,short_answer',
            'answer_time' => 'required|integer|min:5',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_realtime' => 'nullable|boolean',
        ]);

        // Find the QuizClass (make sure it exists)
        $quizClass = QuizClass::findOrFail($quizClassId);

        // Create QuestionSet under this QuizClass
        $quizClass->questionSets()->create([
            'topic' => $validated['topic'],
            'description' => $validated['description'] ?? null,
            'question_type' => $validated['question_type'],
            'answer_time' => $validated['answer_time'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_realtime' => $request->has('is_realtime'),
            'is_active' => true,
            'user_id' => auth()->id(),
        ]);

        // Redirect back to the quizclass page
        return redirect()
            ->route('teacher.quizclass', $quizClassId)
            ->with('success', 'Question set created successfully.');
    }

    public function show($quizClassId, $questionSetId)
    {
        $questionSet = QuestionSet::findOrFail($questionSetId);

        return view('teacher.questionset', compact('questionSet'));
    }


}
