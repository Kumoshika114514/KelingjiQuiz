<?php

namespace App\Http\Controllers;

use App\Models\QuizClass;
use App\Models\QuestionSet;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    /* -----------------------------------------------------------
     * Helpers
     * --------------------------------------------------------- */

    /**
     * Ensure the authenticated teacher owns this class and the set belongs to the class.
     */
    protected function authorizeTeacher(QuizClass $quizClass, QuestionSet $questionSet): void
    {
        abort_unless((int) $quizClass->user_id === (int) Auth::id(), 403);
        abort_unless((int) $questionSet->class_id === (int) $quizClass->id, 404);
    }

    /**
     * Build validation rules based on question type.
     */
    protected function rulesFor(QuestionSet $questionSet, bool $isUpdate = false): array
    {
        $base = [
            'text'           => 'required|string|max:2000',
            'points'         => 'required|integer|min:0',
            'order'          => 'nullable|integer|min:0',
            'time_limit_sec' => 'nullable|integer|min:5',
            'image'          => ($isUpdate ? 'nullable' : 'nullable') . '|image|max:3072', // up to 3MB
        ];

        switch ($questionSet->question_type) {
            case 'mcq':
                $type = [
                    'answer_a'       => 'required|string|max:255',
                    'answer_b'       => 'required|string|max:255',
                    'answer_c'       => 'required|string|max:255',
                    'answer_d'       => 'required|string|max:255',
                    'correct_choice' => 'required|in:A,B,C,D',
                ];
                break;

            case 'true_false':
                $type = [
                    'correct_bool' => 'required|boolean', // expects "1" or "0"
                    // make distractors optional if your schema allows NULLs
                    'answer_a' => 'nullable|string|max:255',
                    'answer_b' => 'nullable|string|max:255',
                    'answer_c' => 'nullable|string|max:255',
                    'answer_d' => 'nullable|string|max:255',
                ];
                break;

            case 'short_answer':
                $type = [
                    'correct_text' => 'required|string|max:1000',
                    'answer_a'     => 'nullable|string|max:255',
                    'answer_b'     => 'nullable|string|max:255',
                    'answer_c'     => 'nullable|string|max:255',
                    'answer_d'     => 'nullable|string|max:255',
                ];
                break;

            default:
                $type = [];
        }

        return $base + $type;
    }

    /**
     * Handle optional image upload and return the storage path (or null).
     */
    protected function storeImageFromRequest(Request $request, ?string $existingPath = null): ?string
    {
        if (!$request->hasFile('image')) {
            return $existingPath; // unchanged
        }

        // delete old file if present
        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        $path = $request->file('image')->store('questions', 'public'); // storage/app/public/questions
        return $path; // relative path to use in DB
    }

    /**
     * Recompute and persist the question_count on a set.
     */
    protected function syncQuestionCount(int $questionSetId): void
    {
        $count = Question::where('question_set_id', $questionSetId)->count();
        QuestionSet::where('id', $questionSetId)->update(['question_count' => $count]);
    }

    /* -----------------------------------------------------------
     * Pages
     * --------------------------------------------------------- */

    // LIST all questions in a set
    public function index($quizClassId, $questionSetId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $this->authorizeTeacher($quizClass, $questionSet);

        $questions = Question::where('question_set_id', $questionSetId)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return view('teacher.questions.index', compact('quizClass', 'questionSet', 'questions'));
    }

    // CREATE form
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

    // 1) Derive + normalize type (prefer form, fallback to set)
    $typeInput    = strtoupper((string) $request->input('type', ''));
    $typeFromSet  = strtoupper((string) $questionSet->question_type); // e.g. SHORT_ANSWER, MCQ, TRUE_FALSE
    $typeRaw      = $typeInput ?: $typeFromSet;

    // Map aliases -> internal enum
    $type = match ($typeRaw) {
        'MCQ', 'MULTIPLE_CHOICE' => 'MCQ',
        'SUBJECTIVE', 'SHORT_ANSWER', 'SHORT-ANSWER' => 'SUBJECTIVE',
        'TRUE_FALSE', 'TRUEFALSE', 'TF' => 'TRUE_FALSE',
        default => 'MCQ', // safe default
    };

    // 2) Base rules + per-type rules
    $baseRules = [
        'text'           => 'required|string',
        'points'         => 'nullable|numeric|min:0',
        'order'          => 'nullable|integer|min:1',
        'time_limit_sec' => 'nullable|integer|min:1',
    ];

    $typeRules = match ($type) {
        'MCQ' => [
            'answer_a'       => 'required|string',
            'answer_b'       => 'required|string',
            'answer_c'       => 'required|string',
            'answer_d'       => 'required|string',
            'correct_choice' => 'required|in:A,B,C,D',
        ],
        'SUBJECTIVE' => [
            'correct_text' => 'required|string',
        ],
        'TRUE_FALSE' => [
            'correct_bool' => 'required|boolean',
        ],
    };

    $validated = $request->validate($baseRules + $typeRules);
    $validated['type'] = $type;

    // 3) Default order = append to end
    if (!isset($validated['order'])) {
        $maxOrder = (int) Question::where('question_set_id', $questionSetId)->max('order');
        $validated['order'] = $maxOrder + 1;
    }

    // 4) Null-out fields that don’t apply
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
            // ensure boolean (checkbox "on" -> true)
            $validated['correct_bool']   = $request->boolean('correct_bool');
            break;
    }

    // 5) Optional image
    $imagePath = $this->storeImageFromRequest($request);

    $question = new Question($validated);
    $question->question_set_id = $questionSetId;
    if ($imagePath) {
        $question->image_path = $imagePath;
    }
    $question->save();

    $this->syncQuestionCount($questionSetId);

    return Redirect::route('teacher.questions.index', [$quizClassId, $questionSetId])
        ->with('success', 'Question created.');
}

    // EDIT form
    public function edit($quizClassId, $questionSetId, $questionId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $question    = Question::where('question_set_id', $questionSetId)->findOrFail($questionId);
        $this->authorizeTeacher($quizClass, $questionSet);

        return view('teacher.questions.edit', compact('quizClass', 'questionSet', 'question'));
    }

    // UPDATE question
    public function update(Request $request, $quizClassId, $questionSetId, $questionId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $question    = Question::where('question_set_id', $questionSetId)->findOrFail($questionId);
        $this->authorizeTeacher($quizClass, $questionSet);

        $validated = $request->validate($this->rulesFor($questionSet, true));

        // optional image replace
        $imagePath = $this->storeImageFromRequest($request, $question->image_path);
        if ($imagePath !== $question->image_path) {
            $question->image_path = $imagePath;
        }

        $question->update($validated);

        // keep count in sync (in case you add soft-deletes etc.)
        $this->syncQuestionCount($questionSetId);

        return Redirect::route('teacher.questions.index', [$quizClassId, $questionSetId])
            ->with('success', 'Question updated.');
    }

    // DELETE question
    public function destroy($quizClassId, $questionSetId, $questionId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $question    = Question::where('question_set_id', $questionSetId)->findOrFail($questionId);
        $this->authorizeTeacher($quizClass, $questionSet);

        // delete stored image if present
        if ($question->image_path && Storage::disk('public')->exists($question->image_path)) {
            Storage::disk('public')->delete($question->image_path);
        }

        $question->delete();

        $this->syncQuestionCount($questionSetId);

        return Redirect::route('teacher.questions.index', [$quizClassId, $questionSetId])
            ->with('success', 'Question deleted.');
    }

    // SHOW one question (teacher view with correct answers)
    public function show($quizClassId, $questionSetId, $questionId)
    {
        $quizClass   = QuizClass::findOrFail($quizClassId);
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $question    = Question::where('question_set_id', $questionSetId)->findOrFail($questionId);
        $this->authorizeTeacher($quizClass, $questionSet);

        return view('teacher.questions.show', compact('quizClass', 'questionSet', 'question'));
    }

    // PREVIEW a single question (student-style; no correctness shown)
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
            'items'           => 'required|array|min:1',
            'items.*.id'      => 'required|integer|distinct',
            'items.*.order'   => 'required|integer|min:0',
        ]);

        foreach ($data['items'] as $row) {
            Question::where('question_set_id', $questionSetId)
                ->where('id', $row['id'])
                ->update(['order' => $row['order']]);
        }

        return back()->with('success', 'Order updated.');
    }
}
