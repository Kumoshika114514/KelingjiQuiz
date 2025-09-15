<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // unique index on (question_set_id, order)
            $table->unique(['question_set_id', 'order'], 'questions_set_order_unique');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropUnique('questions_set_order_unique');
        });
    }
};
