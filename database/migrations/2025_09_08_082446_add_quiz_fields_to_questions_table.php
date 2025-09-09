<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Core fields used by views/controllers
            $table->text('text')->after('question_set_id');          // question text
            $table->integer('points')->default(1)->after('text');    // score
            $table->integer('order')->default(0)->after('points');   // display order
            $table->integer('time_limit_sec')->nullable()->after('order');

            // Auto-marking support for different types
            $table->enum('correct_choice', ['A','B','C','D'])->nullable()->after('answer_d'); // MCQ
            $table->string('correct_text')->nullable()->after('correct_choice');              // Short answer
            $table->boolean('correct_bool')->nullable()->after('correct_text');              // True/False

            // Make distractors optional so TF/ShortAnswer can save cleanly
            $table->string('answer_b')->nullable()->change();
            $table->string('answer_c')->nullable()->change();
            $table->string('answer_d')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'text','points','order','time_limit_sec',
                'correct_choice','correct_text','correct_bool'
            ]);
            // NOTE: reverting nullable->notNullable on answer_b/c/d is optional & omitted
        });
    }
};
