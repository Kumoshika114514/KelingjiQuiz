<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domain\QuizState\{
    QuizState,
    DraftState,
    ScheduledState,
    ActiveState,
    ClosedState,
    ArchivedState
};

class QuestionSet extends Model
{
    protected $fillable = [
        'topic',
        'description',
        'question_type',
        'answer_time',
        'start_time',
        'end_time',
        'question_count',
        'is_realtime',
        'is_active',   
        'status',      
        'state',       
        'user_id',
        'class_id',
    ];

    /** Helpful casting */
    protected $casts = [
        'start_time'     => 'datetime',
        'end_time'       => 'datetime',
        'is_realtime'    => 'boolean',
        'is_active'      => 'boolean',
        'question_count' => 'integer',
    ];

    // Optional: canonical state names
    public const DRAFT     = 'DRAFT';
    public const SCHEDULED = 'SCHEDULED';
    public const ACTIVE    = 'ACTIVE';
    public const CLOSED    = 'CLOSED';
    public const ARCHIVED  = 'ARCHIVED';

    /* ---------------- Relations ---------------- */

    public function quizClass()
    {
        return $this->belongsTo(QuizClass::class, 'class_id');
    }



    /* ---------------- State (Context) ---------------- */

    /** Resolve the current state object */
    public function stateObj(): QuizState
    {
        return match (strtoupper($this->state ?? self::DRAFT)) {
            self::SCHEDULED => new ScheduledState,
            self::ACTIVE    => new ActiveState,
            self::CLOSED    => new ClosedState,
            self::ARCHIVED  => new ArchivedState,
            default         => new DraftState,
        };
    }

    /** Convenience boolean helpers for Blade/Controllers */
    public function canEditMeta(): bool   { return $this->stateObj()->canEditMeta(); }
    public function canAddQuestion(): bool{ return $this->stateObj()->canAddQuestion(); }
    public function canAttempt(): bool    { return $this->stateObj()->canAttempt(); }
    public function canSeeResults(): bool { return $this->stateObj()->canSeeResults(); }

    /** Transitions (delegate to state classes) */
    public function schedule(?string $start, ?string $end): void { $this->stateObj()->schedule($this, $start, $end); }
    public function activate(): void                             { $this->stateObj()->activate($this); }
    public function disable(): void                              { $this->stateObj()->disable($this); }
    public function close(): void                                { $this->stateObj()->close($this); }
    public function archive(): void                              { $this->stateObj()->archive($this); }

    /** Simple convenience for UI badges, without overriding the DB column */
    public function isLive(): bool
    {
        // Prefer state; fall back to is_active if state not present yet
        return strtoupper((string) $this->state) === self::ACTIVE || (bool) $this->getAttribute('is_active');
    }
}
