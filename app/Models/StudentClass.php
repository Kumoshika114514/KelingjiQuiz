<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    public function quizClass()
    {
        return $this->belongsTo(QuizClass::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
