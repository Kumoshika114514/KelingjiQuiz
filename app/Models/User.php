<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // for differentiate students and teachers role
     public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }



    public function classrooms()
    {
        return $this->hasMany(QuizClass::class, 'teacher_id');
    }
    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class, 'student_id');
    }
    public function quizClasses()
    {
        return $this->belongsToMany(
            QuizClass::class,
            'student_classes',
            'student_id',
            'class_id'
        );
    }

    public function comments() 
    {
        return $this->hasMany(Comment::class);
    }
    public function commentLikes() 
    {
        return $this->hasMany(CommentLike::class);
    }
}
