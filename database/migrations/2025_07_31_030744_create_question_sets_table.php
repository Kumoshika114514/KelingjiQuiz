<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_sets', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->string('description');
            $table->string('question_type');
            $table->string('answer_time');

            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();

            $table->integer('question_count');
            $table->boolean('is_realtime'); // 1 is true, 0 is false
            $table->boolean('is_active')->nullable(); // 1 is true, 0 is false            
            $table->integer('status')->default(1); // 1 is available, 0 is disabled

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('quiz_classes')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_sets');
    }
};
