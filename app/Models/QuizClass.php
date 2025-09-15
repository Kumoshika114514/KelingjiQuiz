<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizClass extends Model
{
    protected $fillable = [
        'name', 'description', 'class_code', 'user_id'
    ];

    /* ---------------- Relationships ---------------- */

    // A QuizClass belongs to a teacher (User)
    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // A QuizClass has many StudentClasses (students enrolled in the class)
    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class, 'class_id');
    }

    // A QuizClass has many QuestionSets (sets of questions related to the class)
    public function questionSets()
    {
        return $this->hasMany(QuestionSet::class, 'class_id');
    }

    // A QuizClass has many students through StudentClasses (many-to-many relation)
    public function students()
    {
        return $this->hasManyThrough(
            User::class,         // The final related model
            StudentClass::class, // The intermediate model
            'class_id',          // Foreign key on StudentClass (points to QuizClass)
            'id',                // Foreign key on User (points to User table)
            'id',                // Local key on QuizClass
            'student_id'         // Local key on StudentClass (points to User)
        )->where('role', 'student'); // Only students
    }
}
