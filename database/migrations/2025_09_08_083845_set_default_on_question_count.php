<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // backfill any NULLs just in case
        DB::table('question_sets')->whereNull('question_count')->update(['question_count' => 0]);

        Schema::table('question_sets', function (Blueprint $table) {
            $table->unsignedInteger('question_count')->default(0)->change();
        });
    }

    public function down(): void {
        Schema::table('question_sets', function (Blueprint $table) {
            // If you really want to remove the default on rollback:
            $table->unsignedInteger('question_count')->nullable(false)->default(null)->change();
        });
    }
};
