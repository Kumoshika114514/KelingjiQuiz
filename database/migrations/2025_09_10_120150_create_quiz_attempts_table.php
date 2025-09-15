<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizAttemptsTable extends Migration
{
    public function up()
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');  // Use unsignedBigInteger for the foreign key
            $table->unsignedBigInteger('quiz_id');     // Use unsignedBigInteger for the foreign key
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('score')->default(0);
            $table->integer('total_points')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->integer('attempt_number')->default(1);
            $table->timestamps();

            // Add foreign key constraints explicitly
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('quiz_attempts');
    }
}
