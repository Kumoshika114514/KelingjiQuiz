<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $t) {
            $t->id();

            // Foreign keys
            $t->foreignId('question_set_id')
              ->constrained('question_sets')
              ->cascadeOnDelete();

            $t->foreignId('student_id')
              ->constrained('users')
              ->cascadeOnDelete();

            // Lifecycle & timing
            $t->string('status', 32)->default('in_progress'); // in_progress | submitted | expired
            $t->timestamp('started_at')->nullable();          // set by server when attempt starts
            $t->timestamp('submitted_at')->nullable();        // set once on submit

            // Optional global time limit (seconds) and version snapshot
            $t->unsignedInteger('time_limit_sec')->nullable();
            $t->unsignedInteger('quiz_version')->default(1);

            // Final tally for this attempt (sum of StudentAnswer.awarded_points)
            $t->unsignedInteger('score')->default(0);

            $t->timestamps();

            // Enforce one attempt per (student, question set)
            $t->unique(['question_set_id', 'student_id']);

            // Helpful indexes
            $t->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
