<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ReadingAssignment;
use App\Models\ReadingAssignmentStudent;
use App\Models\User;
use Tests\TestCase;

class ReadingAssignmentTest extends TestCase
{
    public function test_teacher_can_access_reading_assignments_list(): void
    {
        $teacher = User::where('role', 'teacher')->first() ?? User::where('role', 'admin')->first();
        $this->assertNotNull($teacher);

        $response = $this->actingAs($teacher)->get('/admin/reading-assignments');
        $response->assertStatus(200);
        $response->assertSee('Penugasan Membaca');
    }

    public function test_teacher_can_access_create_reading_assignment_page(): void
    {
        $teacher = User::where('role', 'teacher')->first() ?? User::where('role', 'admin')->first();

        $response = $this->actingAs($teacher)->get('/admin/reading-assignments/create');
        $response->assertStatus(200);
        $response->assertSee('Buat Penugasan Membaca');
    }

    public function test_teacher_can_access_edit_reading_assignment_page(): void
    {
        $teacher = User::where('role', 'teacher')->first() ?? User::where('role', 'admin')->first();
        $assignment = ReadingAssignment::first();
        $this->assertNotNull($assignment);

        $response = $this->actingAs($teacher)->get("/admin/reading-assignments/{$assignment->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Ubah Penugasan Membaca');
    }

    public function test_student_can_access_tugas_membaca_list(): void
    {
        $student = User::where('role', 'student')->where('email', 'siswa@sekolah.id')->first();
        $this->assertNotNull($student);

        $response = $this->actingAs($student)->get('/siswa/tugas-membaca');
        $response->assertStatus(200);
        $response->assertSee('Tugas Membaca');
    }

    public function test_student_dashboard_displays_tugas_membaca_widget(): void
    {
        $student = User::where('role', 'student')->where('email', 'siswa@sekolah.id')->first();

        $response = $this->actingAs($student)->get('/siswa');
        $response->assertStatus(200);
        $response->assertSee('Tugas Membaca');
    }

    public function test_reader_page_displays_active_assignment_badge_for_student(): void
    {
        $student = User::where('role', 'student')->where('email', 'siswa@sekolah.id')->first();
        $assignment = ReadingAssignment::first();

        $response = $this->actingAs($student)->get("/siswa/baca/{$assignment->book->slug}");
        $response->assertStatus(200);
        $response->assertSee('Tugas');
    }
}
