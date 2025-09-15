<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'class_id',
        'name',
        'description'
    ];

    /* ---------------- Relationships ---------------- */

    // A Quiz belongs to a QuizClass (the class it belongs to)
    public function quizClass()
    {
        return $this->belongsTo(QuizClass::class, 'class_id'); // Foreign key 'class_id' in quizzes table
    }

    // A Quiz has many QuestionSets (each quiz can have many question sets)
    public function questionSets()
    {
        return $this->hasMany(QuestionSet::class, 'quiz_id');  // 'quiz_id' is the foreign key in question_sets table
    }

    // A Quiz has many Questions (questions belong to the quiz)
    public function questions()
    {
        return $this->hasMany(Question::class, 'quiz_id'); // 'quiz_id' is the foreign key in questions table
    }
}
