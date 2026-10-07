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
        if (! Schema::hasTable('reading_assignments')) {
            Schema::create('reading_assignments', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
                $table->foreignId('target_chapter_id')->nullable()->constrained('chapters')->nullOnDelete();
                $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete();
                $table->dateTime('due_date');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reading_assignment_students')) {
            Schema::create('reading_assignment_students', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reading_assignment_id')->constrained('reading_assignments')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->enum('status', ['assigned', 'in_progress', 'completed'])->default('assigned');
                $table->unsignedInteger('progress_percent')->default(0);
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['reading_assignment_id', 'student_id'], 'ras_assignment_student_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_assignment_students');
        Schema::dropIfExists('reading_assignments');
    }
};
