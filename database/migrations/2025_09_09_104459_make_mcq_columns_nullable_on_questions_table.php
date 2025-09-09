<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // MCQ-only fields should be optional
            $table->string('answer_a')->nullable()->change();
            $table->string('answer_b')->nullable()->change();
            $table->string('answer_c')->nullable()->change();
            $table->string('answer_d')->nullable()->change();
            $table->enum('correct_choice', ['A','B','C','D'])->nullable()->change();

            // Short-answer / True-False fields should also be optional
            $table->text('correct_text')->nullable()->change();
            $table->boolean('correct_bool')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('answer_a')->nullable(false)->change();
            $table->string('answer_b')->nullable(false)->change();
            $table->string('answer_c')->nullable(false)->change();
            $table->string('answer_d')->nullable(false)->change();
            $table->enum('correct_choice', ['A','B','C','D'])->nullable(false)->change();

            $table->text('correct_text')->nullable(false)->change();
            $table->boolean('correct_bool')->nullable(false)->change();
        });
    }
};
