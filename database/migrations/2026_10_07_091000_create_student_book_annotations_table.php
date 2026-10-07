<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_book_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('page_id')->nullable()->constrained()->cascadeOnDelete();
            
            // 'highlight' (hanya stabilo), 'sticky_note' (catatan halaman), 'highlight_note' (stabilo ber-catatan)
            $table->string('type', 25)->default('highlight');
            
            $table->text('highlighted_text')->nullable();
            $table->string('color', 20)->default('yellow'); // yellow, green, blue, orange, purple, pink
            $table->text('note')->nullable();
            $table->json('position_data')->nullable(); // optional metadata (offset, page_number)
            
            $table->timestamps();

            $table->index(['user_id', 'book_id']);
            $table->index(['user_id', 'page_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_book_annotations');
    }
};
