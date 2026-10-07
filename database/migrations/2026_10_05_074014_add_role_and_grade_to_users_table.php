<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->after('email'); // 'admin', 'teacher', 'student'
            $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete()->after('role');
            $table->string('avatar')->nullable()->after('grade_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
            $table->dropColumn(['role', 'grade_id', 'avatar']);
        });
    }
};
