<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAnswer extends Model
{
    // Only the student's selection comes from the client.
    protected $fillable = ['selected_choice'];

    // Server-owned fields are guarded.
    protected $guarded = [
        'user_id',
        'question_id',
        'is_correct',
        'awarded_points',
        'answered_at',
    ];

    protected $casts = [
        'is_correct'  => 'boolean',
        'answered_at' => 'datetime',
    ];

    // Relationships
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
