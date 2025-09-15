<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'student_id', 'avatar'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function classes()
    {
        return $this->belongsToMany(ClassRoom::class, 'class_students', 'student_id', 'class_id')
                    ->withTimestamps();
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function answers()
    {
        return $this->hasMany(StudentAnswer::class);
    }
}