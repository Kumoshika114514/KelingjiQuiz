<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizClass extends Model
{
    protected $fillable = ['name', 'description', 'class_code', 'user_id',];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class);
    }

    public function questionSets()
    {
        return $this->hasMany(QuestionSet::class, 'class_id');
    }

    public function students()
    {
        return $this->hasManyThrough(
            User::class,
            StudentClass::class,
            'class_id',
            'id',
            'id',
            'student_id'
        )->where('role', 'student');
    }

}
