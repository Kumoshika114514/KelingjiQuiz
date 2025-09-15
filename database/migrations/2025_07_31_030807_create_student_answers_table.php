<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_answers', function (Blueprint $t) {
            $t->id();

            // Link each answer to an attempt and a question
            $t->foreignId('attempt_id')->constrained('attempts')->cascadeOnDelete();
            $t->foreignId('question_id')->constrained('questions')->cascadeOnDelete();

            // For MCQ: 'A'..'D' (nullable for unanswered or non-MCQ types if you add later)
            $t->char('selected_choice', 1)->nullable();

            // Computed on the server
            $t->boolean('is_correct')->default(false);
            $t->unsignedInteger('awarded_points')->default(0);

            // When the student saved/answered
            $t->timestamp('answered_at')->nullable();

            $t->timestamps();

            // One row per question per attempt
            $t->unique(['attempt_id', 'question_id']);

            // Helpful indexes
            $t->index(['attempt_id']);
            $t->index(['question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
