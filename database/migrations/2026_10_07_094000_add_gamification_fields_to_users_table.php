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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('reading_streak_days')->default(0)->after('grade_id');
            $table->unsignedInteger('longest_streak_days')->default(0)->after('reading_streak_days');
            $table->date('last_read_date')->nullable()->after('longest_streak_days');
            $table->unsignedInteger('literacy_points')->default(0)->after('last_read_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'reading_streak_days',
                'longest_streak_days',
                'last_read_date',
                'literacy_points',
            ]);
        });
    }
};
