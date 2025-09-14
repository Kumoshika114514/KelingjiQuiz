<?php

namespace App\Http\Controllers;

use App\Facades\Statistic;
use App\Models\QuizClass;
use App\Models\QuestionSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;

class QuestionSetController extends Controller
{
    /* ---------------- Helpers ---------------- */

    protected function assertBelongs(QuizClass $class, QuestionSet $set): void
    {
        abort_unless((int)$set->class_id === (int)$class->id, 404);
        // UI/teacher flows: ensure the teacher owns the class
        abort_unless((int)$class->user_id === (int)Auth::id(), 403);
    }

    /* ---------------- Pages ---------------- */

    public function create($quizClassId)
    {
        return view('teacher.createquestionset', compact('quizClassId'));
    }

    public function store(Request $request, $quizClassId)
    {
        $data = $request->validate([
            'topic'         => 'required|string|max:255',
            'description'   => 'required|string|max:2000',
            'question_type' => 'required|string|in:mcq,true_false,short_answer',
            'answer_time'   => 'required|integer|min:5|max:7200',
            'start_time'    => 'required|date',
            'end_time'      => 'required|date|after:start_time',
            'is_realtime'   => 'nullable|boolean',
        ]);

        $class = QuizClass::findOrFail($quizClassId);
        abort_unless((int)$class->user_id === (int)Auth::id(), 403);

        $set = $class->questionSets()->create([
            'topic'          => $data['topic'],
            'description'    => $data['description'] ?? null,
            'question_type'  => $data['question_type'],
            'answer_time'    => $data['answer_time'],
            'start_time'     => $data['start_time'],
            'end_time'       => $data['end_time'],
            'is_realtime'    => (bool)$request->boolean('is_realtime'),
            'question_count' => 0,
            'user_id'        => Auth::id(),
            'is_active'      => false,
            'state'          => 'SCHEDULED',
        ]);

        return Redirect::route('teacher.quizclass', $quizClassId)
            ->with('success', 'Question set created successfully.');
    }

    public function show($quizClassId, $questionSetId)
    {
        $class = QuizClass::findOrFail($quizClassId);
        $set   = QuestionSet::findOrFail($questionSetId);
        $this->assertBelongs($class, $set);

        return view('teacher.questionset', [
            'quizClass'   => $class,
            'questionSet' => $set,
        ]);
    }

    /* ---------------- State transitions ---------------- */

    public function schedule(Request $request, $quizClassId, $questionSetId)
    {
        $class = QuizClass::findOrFail($quizClassId);
        $set   = QuestionSet::findOrFail($questionSetId);
        $this->assertBelongs($class, $set);

        $data = $request->validate([
            'start' => 'nullable|date',
            'end'   => 'nullable|date|after:start',
        ]);

        $set->schedule($data['start'] ?? null, $data['end'] ?? null);

        return back()->with('success', 'Quiz scheduled.');
    }

    public function activate($quizClassId, $questionSetId)
    {
        $class = QuizClass::findOrFail($quizClassId);
        $set   = QuestionSet::findOrFail($questionSetId);
        $this->assertBelongs($class, $set);

        $set->activate();

        return back()->with('success', 'Quiz activated.');
    }

    public function disable($quizClassId, $questionSetId)
    {
        $class = QuizClass::findOrFail($quizClassId);
        $set   = QuestionSet::findOrFail($questionSetId);
        $this->assertBelongs($class, $set);

        $set->disable(); // Scheduled -> Draft, or Active -> Closed (as defined in your states)

        return back()->with('success', 'Quiz disabled.');
    }

    public function close($quizClassId, $questionSetId)
    {
        $class = QuizClass::findOrFail($quizClassId);
        $set   = QuestionSet::findOrFail($questionSetId);
        $this->assertBelongs($class, $set);

        $set->close();

        return back()->with('success', 'Quiz closed.');
    }

    public function archive($quizClassId, $questionSetId)
    {
        $class = QuizClass::findOrFail($quizClassId);
        $set   = QuestionSet::findOrFail($questionSetId);
        $this->assertBelongs($class, $set);

        $set->archive();

        return back()->with('success', 'Quiz archived.');
    }

    /* ---------------- JSON helpers ---------------- */

    /**
     * Toggle endpoint usable by:
     *  - Your Blade UI (session auth)
     *  - Teammates via API (Sanctum bearer token with ability "toggle-questionset")
     *
     * If ACTIVE -> close; otherwise -> activate.
     * Returns normalized JSON: success, state ("ACTIVE"/...), status (1/0), message.
     */
    public function toggleStatus($quizClassId, $questionSetId)
    {
        try {
            $class = QuizClass::findOrFail($quizClassId);
            $set   = QuestionSet::findOrFail($questionSetId);

            // Ownership check for normal UI; allow service tokens with ability to bypass
            $user = Auth::user();
            $hasAbility = method_exists($user, 'currentAccessToken')
                && $user->currentAccessToken()
                && $user->tokenCan('toggle-questionset');

            if (!$hasAbility) {
                // Normal path (UI teacher): must belong & be owner
                abort_unless((int)$set->class_id === (int)$class->id, 404);
                abort_unless((int)$class->user_id === (int)Auth::id(), 403);
            } else {
                // Service token path: still ensure the set belongs to the class
                abort_unless((int)$set->class_id === (int)$class->id, 404);
            }

            $current = strtoupper((string)$set->state);

            if ($current === 'ACTIVE') {
                $set->close();
                $fresh = strtoupper((string)$set->fresh()->state);
                return Response::json([
                    'success' => true,
                    'state'   => $fresh,
                    'status'  => $fresh === 'ACTIVE' ? 1 : 0,
                    'message' => 'Question set closed.',
                ], 200);
            }

            // from DRAFT/SCHEDULED/CLOSED -> try to activate (domain rules decide legality)
            $set->activate();
            $fresh = strtoupper((string)$set->fresh()->state);

            return Response::json([
                'success' => true,
                'state'   => $fresh,
                'status'  => $fresh === 'ACTIVE' ? 1 : 0,
                'message' => 'Question set activated.',
            ], 200);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        } catch (\Throwable $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to change status.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function getHighestScore($quizClassId, $questionSetId)
    {
        $class = QuizClass::findOrFail($quizClassId);
        $set   = QuestionSet::findOrFail($questionSetId);
        $this->assertBelongs($class, $set);

        $highestScore = Statistic::getHighestScoreInQuiz($questionSetId);

        return Response::json(['highestScore' => $highestScore], 200);
    }
}
