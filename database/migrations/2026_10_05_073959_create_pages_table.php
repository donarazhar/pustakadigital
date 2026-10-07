<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->cascadeOnDelete();
            $table->integer('page_number')->default(1);
            $table->string('title')->nullable();
            $table->longText('content')->nullable(); // Rich Text / HTML pembelajaran interaktif
            $table->string('audio_narration_url')->nullable(); // Berkas audio MP3 suara narator
            $table->string('featured_image')->nullable(); // Gambar ilustrasi halaman
            $table->string('video_embed_url')->nullable(); // URL YouTube / MP4 video edukasi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
