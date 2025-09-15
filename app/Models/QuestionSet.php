<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionSet extends Model
{
    protected $fillable = [
        'topic',
        'description',
        "question_type",
        "answer_time",
        "start_time",
        "end_time",
        "question_count",
        "is_realtime",
        "is_active",
        "status",
        "user_id",
        "class_id"
    ];

    public function quizClass()
    {
        return $this->belongsTo(QuizClass::class, "class_id");
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'question_set_id');
    }
}
