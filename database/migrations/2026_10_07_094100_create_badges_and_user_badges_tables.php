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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon')->default('🏅');
            $table->string('badge_color')->default('amber'); // amber, emerald, blue, purple, rose, cyan
            $table->string('category')->default('reading'); // 'streak', 'quiz', 'reading', 'annotation', 'subject'
            $table->string('criteria_type'); // 'streak_days', 'perfect_quiz', 'completed_books', 'total_readings', 'annotations_count', 'subject_books'
            $table->unsignedInteger('criteria_value')->default(1);
            $table->unsignedInteger('points_reward')->default(50);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('awarded_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
