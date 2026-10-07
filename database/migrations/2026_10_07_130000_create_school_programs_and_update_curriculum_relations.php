<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat Tabel Program Sekolah (Bilingual, Tahfizh, Reguler, dll.)
        Schema::create('school_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: "Program Bilingual (Cambridge)", "Program Tahfizh Al-Qur'an"
            $table->string('code')->unique(); // Contoh: "BIL", "TFZ", "REG", "STEM"
            $table->string('level')->default('ALL'); // 'ALL', 'SD', 'SMP', 'SMA'
            $table->string('icon')->nullable(); // e.g. "🌐", "📖", "🌱", "🔬"
            $table->string('color')->nullable(); // e.g. "#3b82f6", "#10b981"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tambah program_id ke users (Murid berada di program tertentu)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('program_id')
                ->nullable()
                ->after('grade_id')
                ->constrained('school_programs')
                ->nullOnDelete();
        });

        // 3. Tambah program_id dan level ke subjects (Pelajaran per program dan jenjang)
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('program_id')
                ->nullable()
                ->after('name')
                ->constrained('school_programs')
                ->nullOnDelete();
            $table->string('level')
                ->default('ALL')
                ->after('program_id'); // 'ALL', 'SD', 'SMP', 'SMA'
        });

        // 4. Tambah program_id ke books (Buku khusus program tertentu)
        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('program_id')
                ->nullable()
                ->after('grade_id')
                ->constrained('school_programs')
                ->nullOnDelete();
        });

        // 5. Tambah program_id ke reading_assignments (Tugas membaca per program)
        Schema::table('reading_assignments', function (Blueprint $table) {
            $table->foreignId('program_id')
                ->nullable()
                ->after('grade_id')
                ->constrained('school_programs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reading_assignments', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn(['program_id', 'level']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });

        Schema::dropIfExists('school_programs');
    }
};
