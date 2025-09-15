<?php

namespace App\Http\Controllers;

use App\Models\QuizClass;
use App\Models\QuestionSet;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class QuestionController extends Controller
{
    // Centralized limits
    private const PTS_MIN  = 1;
    private const PTS_MAX  = 100;
    private const ORD_MIN  = 1;
    private const ORD_MAX  = 999;
    private const TLIM_MIN = 10;
    private const TLIM_MAX = 7200;

    /* -----------------------------------------------------------
     * Helpers
     * --------------------------------------------------------- */

    protected function authorizeTeacher(QuizClass $quizClass, QuestionSet $questionSet): void
    {
        abort_unless((int) $quizClass->user_id === (int) Auth::id(), 403);
        abort_unless((int) $questionSet->class_id === (int) $quizClass->id, 404);
    }

    protected function normalizeType(string $raw, QuestionSet $set): string
    {
        $raw = strtoupper(trim($raw ?: $set->question_type));
        return match ($raw) {
            'MCQ', 'MULTIPLE_CHOICE'                    => 'MCQ',
            'TRUE_FALSE', 'TRUEFALSE', 'TF'             => 'TRUE_FALSE',
            'SUBJECTIVE', 'SHORT_ANSWER','SHORT-ANSWER' => 'SUBJECTIVE',
            default                                      => 'MCQ',
        };
    }

    protected function rulesForType(string $type, bool $isUpdate = false): array
    {
        $base = [
            'text'           => ['required','string','max:2000'],
            'points'         => ['required','integer','min:'.self::PTS_MIN,'max:'.self::PTS_MAX],
            'order'          => ['nullable','integer','min:'.self::ORD_MIN,'max:'.self::ORD_MAX],
            'time_limit_sec' => ['nullable','integer','min:'.self::TLIM_MIN,'max:'.self::TLIM_MAX],
            'image'          => ['nullable','image','max:3072'], // 3MB
        ];

        $typeRules = match ($type) {
            'MCQ' => [
                'answer_a'       => ['required','string','max:255'],
                'answer_b'       => ['required','string','max:255'],
                'answer_c'       => ['required','string','max:255'],
                'answer_d'       => ['required','string','max:255'],
                'correct_choice' => ['required','in:A,B,C,D'],
            ],
            'TRUE_FALSE' => [
                'correct_bool'   => ['required','boolean'],
                'answer_a'       => ['nullable','string','max:255'],
                'answer_b'       => ['nullable','string','max:255'],
                'answer_c'       => ['nullable','string','max:255'],
                'answer_d'       => ['nullable','string','max:255'],
            ],
            'SUBJECTIVE' => [
                'correct_text'   => ['required','string','max:1000'],
                'answer_a'       => ['nullable','string','max:255'],
                'answer_b'       => ['nullable','string','max:255'],
                'answer_c'       => ['nullable','string','max:255'],
                'answer_d'       => ['nullable','string','max:255'],
            ],
        };

        return $base + $typeRules;
    }

    protected function storeImageFromRequest(Request $request, ?string $existingPath = null): ?string
    {
        if (!$request->hasFile('image')) return $existingPath;
        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }
        return $request->file('image')->store('questions', 'public');
    }

    protected function syncQuestionCount(int $questionSetId): void
    {
        $count = Question::where('question_set_id', $questionSetId)->count();
        QuestionSet::where('id', $questionSetId)->update(['question_count' => $count]);
    }

    /**
     * Renumber orders in a set to 1..n (stable by id) and keep within limits.
     */
    protected function normalizeOrders(int $questionSetId): void
    {
        $qs = Question::where('question_set_id', $questionSetId)
            ->orderBy('order')->orderBy('id')->get();

        $i = 1;
        foreach ($qs as $q) {
            $target = min(max($i, self::ORD_MIN), self::ORD_MAX);
            if ((int)$q->order !== $target) {
                $q->forceFill(['order' => $target])->saveQuietly();
            }
            $i++;
        }
    }

    /* -----------------------------------------------------------
     * Pages
     * --------------------------------------------------------- */

    public function index($quizClassId, $questionSetId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $this->authorizeTeacher($quizClass, $questionSet);

        $questions = Question::where('question_set_id', $questionSetId)
            ->orderBy('order')->orderBy('id')->get();

        return view('teacher.questions.index', compact('quizClass', 'questionSet', 'questions'));
    }

    public function create($quizClassId, $questionSetId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $this->authorizeTeacher($quizClass, $questionSet);

        return view('teacher.questions.create', compact('quizClass', 'questionSet'));
    }

    // STORE new question
    public function store(Request $request, $quizClassId, $questionSetId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $this->authorizeTeacher($quizClass, $questionSet);

        $type  = $this->normalizeType((string) $request->input('type', ''), $questionSet);

        // Validation with unique 'order' inside the set (when provided)
        $rules = $this->rulesForType($type);
        if ($request->filled('order')) {
            $rules['order'][] = Rule::unique('questions')
                ->where(fn($q) => $q->where('question_set_id', $questionSetId));
        }
        $validated = $request->validate($rules);

        // Default order -> append to end (after validation)
        if (empty($validated['order'])) {
            $validated['order'] = ((int) Question::where('question_set_id', $questionSetId)->max('order')) + 1;
        }

        // Clean type-specific fields
        switch ($type) {
            case 'MCQ':
                $validated['correct_text'] = null;
                $validated['correct_bool'] = null;
                break;
            case 'SUBJECTIVE':
                $validated['answer_a'] = $validated['answer_b'] =
                $validated['answer_c'] = $validated['answer_d'] = null;
                $validated['correct_choice'] = null;
                $validated['correct_bool']   = null;
                break;
            case 'TRUE_FALSE':
                $validated['answer_a'] = $validated['answer_b'] =
                $validated['answer_c'] = $validated['answer_d'] = null;
                $validated['correct_choice'] = null;
                $validated['correct_text']   = null;
                $validated['correct_bool']   = $request->boolean('correct_bool');
                break;
        }
        $validated['type'] = $type;

        // Optional image
        $imagePath = $this->storeImageFromRequest($request);

        try {
            $question = new Question($validated);
            $question->question_set_id = $questionSetId;
            if ($imagePath) $question->image_path = $imagePath;
            $question->save();
        } catch (QueryException $e) {
            if ((int)$e->getCode() === 23000) {
                return back()->withErrors([
                    'order' => 'This order is already used in this question set. Please choose another number.',
                ])->withInput();
            }
            throw $e;
        }

        $this->syncQuestionCount($questionSetId);
        $this->normalizeOrders($questionSetId);

        return Redirect::route('teacher.questions.index', [$quizClassId, $questionSetId])
            ->with('success', 'Question created.');
    }

    public function edit($quizClassId, $questionSetId, $questionId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $question    = Question::where('question_set_id', $questionSetId)->findOrFail($questionId);
        $this->authorizeTeacher($quizClass, $questionSet);

        return view('teacher.questions.edit', compact('quizClass', 'questionSet', 'question'));
    }

    public function update(Request $request, $quizClassId, $questionSetId, $questionId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $question    = Question::where('question_set_id', $questionSetId)->findOrFail($questionId);
        $this->authorizeTeacher($quizClass, $questionSet);

        $type = $this->normalizeType($question->type ?? $questionSet->question_type, $questionSet);

        // ✅ No unique rule here — we handle swap ourselves
        $validated = $request->validate($this->rulesForType($type, true));

        // Optional image replace
        $imagePath = $this->storeImageFromRequest($request, $question->image_path);
        if ($imagePath !== $question->image_path) {
            $question->image_path = $imagePath;
        }

        // Normalize irrelevant fields by type
        switch ($type) {
            case 'MCQ':
                $validated['correct_text'] = null;
                $validated['correct_bool'] = null;
                break;
            case 'SUBJECTIVE':
                $validated['answer_a'] = $validated['answer_b'] =
                $validated['answer_c'] = $validated['answer_d'] = null;
                $validated['correct_choice'] = null;
                $validated['correct_bool']   = null;
                break;
            case 'TRUE_FALSE':
                $validated['answer_a'] = $validated['answer_b'] =
                $validated['answer_c'] = $validated['answer_d'] = null;
                $validated['correct_choice'] = null;
                $validated['correct_text']   = null;
                $validated['correct_bool']   = $request->boolean('correct_bool');
                break;
        }

        // If order provided, do an atomic swap/move and keep list tidy
        if ($request->filled('order')) {
            $newOrder = (int) $validated['order'];
            unset($validated['order']); // avoid mass-assign race with DB unique
            $this->moveOrSwapOrder($question, $newOrder);
            $question->refresh();
        }

        // Update remaining fields
        $question->update($validated);

        $this->syncQuestionCount($questionSetId);

        return Redirect::route('teacher.questions.index', [$quizClassId, $questionSetId])
            ->with('success', 'Question updated.');
    }

    public function destroy($quizClassId, $questionSetId, $questionId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $question    = Question::where('question_set_id', $questionSetId)->findOrFail($questionId);
        $this->authorizeTeacher($quizClass, $questionSet);

        if ($question->image_path && Storage::disk('public')->exists($question->image_path)) {
            Storage::disk('public')->delete($question->image_path);
        }

        $question->delete();
        $this->syncQuestionCount($questionSetId);
        $this->normalizeOrders($questionSetId);

        return Redirect::route('teacher.questions.index', [$quizClassId, $questionSetId])
            ->with('success', 'Question deleted.');
    }

    public function show($quizClassId, $questionSetId, $questionId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $question    = Question::where('question_set_id', $questionSetId)->findOrFail($questionId);
        $this->authorizeTeacher($quizClass, $questionSet);

        return view('teacher.questions.show', compact('quizClass', 'questionSet', 'question'));
    }

    public function preview($quizClassId, $questionSetId, $questionId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $question    = Question::where('question_set_id', $questionSetId)->findOrFail($questionId);
        $this->authorizeTeacher($quizClass, $questionSet);

        return view('teacher.questions.preview', compact('quizClass', 'questionSet', 'question'));
    }

    // BULK REORDER: expects { items: [ {id, order}, ... ] }
    public function reorder(Request $request, $quizClassId, $questionSetId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $this->authorizeTeacher($quizClass, $questionSet);

        $data = $request->validate([
            'items'           => ['required','array','min:1'],
            'items.*.id'      => ['required','integer','distinct'],
            'items.*.order'   => ['required','integer','min:'.self::ORD_MIN,'max:'.self::ORD_MAX],
        ]);

        // Two-phase update to avoid unique collisions when swapping orders
        DB::transaction(function () use ($data, $questionSetId) {
            $offset = 10000; // > ORD_MAX to avoid clashes
            // Phase 1: move to temporary (order + offset)
            foreach ($data['items'] as $row) {
                Question::where('question_set_id', $questionSetId)
                    ->where('id', $row['id'])
                    ->update(['order' => $row['order'] + $offset]);
            }
            // Phase 2: move to final order
            foreach ($data['items'] as $row) {
                Question::where('question_set_id', $questionSetId)
                    ->where('id', $row['id'])
                    ->update(['order' => $row['order']]);
            }
        });

        $this->normalizeOrders($questionSetId);

        return back()->with('success', 'Order updated.');
    }

    /**
     * Move a question to $newOrder inside its set.
     * If another question already has $newOrder, swap their orders atomically.
     */
    protected function moveOrSwapOrder(Question $question, int $newOrder): void
    {
        $setId    = (int) $question->question_set_id;
        $oldOrder = (int) $question->order;

        // Clamp to allowed range
        $target = min(max($newOrder, self::ORD_MIN), self::ORD_MAX);
        if ($target === $oldOrder) return;

        DB::transaction(function () use ($question, $setId, $oldOrder, $target) {
            // lock current
            $current = Question::where('question_set_id', $setId)
                ->where('id', $question->id)
                ->lockForUpdate()
                ->first();

            // lock any conflicting target row
            $conflict = Question::where('question_set_id', $setId)
                ->where('order', $target)
                ->where('id', '!=', $question->id)
                ->lockForUpdate()
                ->first();

            $offset = 10000; // if you add a CHECK constraint 1..999, use a NULL-park strategy instead

            if ($conflict) {
                // park conflicting row
                Question::whereKey($conflict->id)->update(['order' => $target + $offset]);
                // move current to target
                Question::whereKey($current->id)->update(['order' => $target]);
                // move conflict to old slot
                Question::whereKey($conflict->id)->update(['order' => $oldOrder]);
            } else {
                Question::whereKey($current->id)->update(['order' => $target]);
            }
        });

        // keep sequence tidy
        $this->normalizeOrders($setId);
    }
}
