<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttemptController extends Controller
{
    public function start(QuestionSet $questionSet)
    {
        // (Optional) authorize enrollment/visibility here if you have policies.
        if (isset($questionSet->open_at, $questionSet->close_at)) {
            abort_unless(now()->between($questionSet->open_at, $questionSet->close_at), 403);
        }

        $attempt = Attempt::create([
            'question_set_id' => $questionSet->id,
            'student_id'      => Auth::id(),
            'status'          => 'in_progress',
            'started_at'      => now(),                          // server time
            'time_limit_sec'  => $questionSet->time_limit_sec ?? null,
            'quiz_version'    => $questionSet->version ?? 1,
        ]);

        return response()->json(['attempt_id' => $attempt->id], 201);
    }

    public function answer(Request $r, Attempt $attempt)
    {
        // Ownership check: prevent using someone else’s attempt id
        abort_unless($attempt->student_id === Auth::id(), 403);

        // Minimal input validation (doesn't count as your strategy)
        $data = $r->validate([
            'question_id'     => ['required','integer','exists:questions,id'],
            'selected_choice' => ['nullable','string','in:A,B,C,D'],
        ]);

        $studentId      = Auth::id();
        $questionId     = (int) $data['question_id'];
        $selectedChoice = $data['selected_choice'] ?? null; // 'A'..'D' or null

        // Authoritative fetch & scope check: question must be in same QuestionSet
        $question = Question::where('id', $questionId)
            ->where('question_set_id', $attempt->question_set_id)
            ->firstOrFail();

        // Server-authoritative grading
        $isCorrect = ($selectedChoice === $question->correct_choice);
        $awarded   = $isCorrect ? (int) $question->points : 0;

        // NOTE: key by (student_id, question_id) since you don't have attempt_id in student_answers
        StudentAnswer::updateOrCreate(
            ['user_id' => $userId, 'question_id' => $question->id],
            [
                'selected_choice' => $selectedChoice,
                'is_correct'      => $isCorrect,
                'awarded_points'  => $awarded,
                'answered_at'     => now(),
            ]
        );

        return response()->json(['saved' => true]);
    }

    public function submit(Request $r, Attempt $attempt)
    {
        // Ownership check
        abort_unless($attempt->student_id === Auth::id(), 403);

        // Server-side time enforcement (ignore client time)
        if ($attempt->time_limit_sec) {
            $deadline = $attempt->started_at->clone()->addSeconds($attempt->time_limit_sec);
            if (now()->greaterThan($deadline)) {
                // Optional: $attempt->update(['status' => 'expired', 'submitted_at' => now()]);
                // Optional return: return response()->json(['submitted' => false, 'reason' => 'expired'], 409);
            }
        }

        $studentId = Auth::id();

        // Compute score for THIS student within THIS question set
        $questionIds = Question::where('question_set_id', $attempt->question_set_id)->pluck('id');

        $score = StudentAnswer::where('student_id', $studentId)
            ->whereIn('question_id', $questionIds)
            ->sum('awarded_points');

        // Finalize once; make idempotent without extra table:
        // - If already submitted, just return the current score (same success body).
        DB::transaction(function () use ($attempt, $score) {
            $updated = Attempt::whereKey($attempt->id)
                ->whereNull('submitted_at')
                ->where('status', 'in_progress')
                ->update(['submitted_at' => now(), 'status' => 'submitted']);

            // Write (or overwrite) the computed score
            Attempt::whereKey($attempt->id)->update(['score' => $score]);
        });

        return response()->json(['submitted' => true, 'score' => $score]);
    }
}
