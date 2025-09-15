<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'quiz_id', 'started_at', 'completed_at', 'score',
        'total_points', 'is_completed', 'attempt_number'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // For compatibility with your controller/blade
    public function questionSet()
    {
        return $this->belongsTo(QuestionSet::class, 'quiz_id');
    }

    public function getPercentageAttribute()
    {
        if ($this->total_points == 0) return 0;
        return round(($this->score / $this->total_points) * 100, 2);
    }
}