<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_answers', function (Blueprint $t) {
            // One answer per (student, question)
            $t->unique(['user_id', 'question_id'], 'sa_student_question_unique');
        });
    }

    public function down(): void
    {
        Schema::table('student_answers', function (Blueprint $t) {
            $t->dropUnique('sa_student_question_unique');
        });
    }
};
