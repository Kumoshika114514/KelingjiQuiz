<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attempt extends Model
{
    use HasFactory;

    // Server-owned fields are guarded so the client cannot set them directly
    protected $guarded = [
        'question_set_id',
        'student_id',
        'status',
        'started_at',
        'submitted_at',
        'score',
        'quiz_version',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'submitted_at' => 'datetime',
    ];

    // --- Relationships ---
    public function questionSet(): BelongsTo
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // --- Helpers (optional) ---
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }
}
