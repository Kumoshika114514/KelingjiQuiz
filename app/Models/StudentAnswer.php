<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_id',
        'question_set_id',
        'answer',
        'attempt_id',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class, 'attempt_id'); // Link to the Attempt model
    }

    // Define the relationship to the Question model (if needed)
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
    
}
