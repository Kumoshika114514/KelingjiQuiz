<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_set_id',
        'text',              // question text
        'points',            // integer points
        'order',             // display order within set (0..N)
        'time_limit_sec',    // optional per-question timer (overrides set's answer_time if you want)

        // MCQ choices (A–D) + correct option
        'answer_a', 'answer_b', 'answer_c', 'answer_d',
        'correct_choice',    // 'A'|'B'|'C'|'D' for MCQ

        // Short answer (exact match by default; you can expand later)
        'correct_text',

        // True/False
        'correct_bool',      // boolean true/false
    ];

    protected $casts = [
        'points'         => 'integer',
        'order'          => 'integer',
        'time_limit_sec' => 'integer',
        'correct_bool'   => 'boolean',
    ];

    /* ---------------- Relations ---------------- */

    public function questionSet()
    {
        return $this->belongsTo(QuestionSet::class, 'question_set_id');
    }

    /* ---------------- Scopes ---------------- */

    public function scopeOrdered($q)
    {
        return $q->orderBy('order');
    }

    /* ---------------- Accessors / Helpers ---------------- */

    // Choices as array: ['A'=>'...', 'B'=>'...', ...] filtered for non-empty
    public function getChoices(): array
    {
        return array_filter([
            'A' => $this->answer_a,
            'B' => $this->answer_b,
            'C' => $this->answer_c,
            'D' => $this->answer_d,
        ], fn ($v) => $v !== null && $v !== '');
    }

    // Resolve the effective question type:
    // prefers a 'type' column if you add one later; else falls back to the parent QuestionSet.question_type
    public function resolvedType(): ?string
    {
        $local = $this->getAttribute('type');
        if (!is_null($local)) return $local;
        $set = $this->relationLoaded('questionSet') ? $this->questionSet : $this->questionSet()->first();
        return $set?->question_type; // 'mcq' | 'true_false' | 'short_answer'
    }

    // Basic auto-marking; returns null if type is unknown
    public function checkAnswer($answer): ?bool
    {
        $type = $this->resolvedType();
        if ($type === 'mcq') {
            if ($answer === null) return false;
            return strtoupper(trim((string)$answer)) === strtoupper((string)$this->correct_choice);
        }
        if ($type === 'true_false') {
            return (bool)$answer === (bool)$this->correct_bool;
        }
        if ($type === 'short_answer') {
            if ($this->correct_text === null) return null;
            // exact match, case-insensitive, trimmed
            return mb_strtolower(trim((string)$answer)) === mb_strtolower(trim((string)$this->correct_text));
        }
        return null;
    }

    // Points awarded (0 or full points for now)
    public function awardedPoints($answer): int
    {
        $ok = $this->checkAnswer($answer);
        return $ok === true ? (int)$this->points : 0;
    }

}
