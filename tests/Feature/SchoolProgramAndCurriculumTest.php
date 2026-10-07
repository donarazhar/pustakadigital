<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Grade;
use App\Models\ReadingAssignment;
use App\Models\SchoolProgram;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class SchoolProgramAndCurriculumTest extends TestCase
{
    public function test_admin_can_access_school_programs_list(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/school-programs');
        $response->assertStatus(200);
        $response->assertSee('Program Sekolah');
    }

    public function test_admin_can_access_create_school_program_page(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/school-programs/create');
        $response->assertStatus(200);
        $response->assertSee('Program Sekolah');
    }

    public function test_school_program_model_relationships(): void
    {
        $program = SchoolProgram::where('code', 'BIL')->first();
        $this->assertNotNull($program);

        // Siswa terdaftar pada program Bilingual
        $this->assertTrue($program->students()->count() >= 1);
        $student = $program->students()->first();
        $this->assertEquals($program->id, $student->program_id);

        // Mata pelajaran khusus Bilingual
        $this->assertTrue($program->subjects()->count() >= 1);
        $subject = $program->subjects()->first();
        $this->assertEquals($program->id, $subject->program_id);
    }

    public function test_student_has_grade_and_program_badge(): void
    {
        $student = User::where('email', 'siswa@sekolah.id')->first();
        $this->assertNotNull($student);
        $this->assertNotNull($student->grade);
        $this->assertNotNull($student->program);

        $badge = $student->getAcademicBadge();
        $this->assertStringContainsString('Kelas 4 SD', $badge);
        $this->assertStringContainsString($student->program->name, $badge);
    }

    public function test_subject_can_be_general_or_program_specific(): void
    {
        // Mapel umum: IPA
        $generalSubject = Subject::where('code', 'IPA')->first();
        $this->assertNotNull($generalSubject);
        $this->assertTrue($generalSubject->isGeneral());
        $this->assertNull($generalSubject->program_id);

        // Mapel khusus: Cambridge Primary Science
        $programSubject = Subject::where('code', 'CAM-SCI')->first();
        $this->assertNotNull($programSubject);
        $this->assertFalse($programSubject->isGeneral());
        $this->assertNotNull($programSubject->program_id);
        $this->assertEquals('BIL', $programSubject->program->code);
    }

    public function test_public_catalog_can_filter_books_by_program(): void
    {
        $bilProgram = SchoolProgram::where('code', 'BIL')->first();
        $this->assertNotNull($bilProgram);

        // Request catalog dengan filter program
        $response = $this->get('/?program=' . $bilProgram->id);
        $response->assertStatus(200);
        $response->assertSee('Program');
        $response->assertSee($bilProgram->name);
    }

    public function test_reading_assignment_can_target_specific_program(): void
    {
        $teacher = User::where('role', 'teacher')->first() ?? User::where('role', 'admin')->first();
        $grade = Grade::where('name', 'Kelas 4 SD')->first();
        $program = SchoolProgram::where('code', 'BIL')->first();
        $book = Book::first();

        $assignment = ReadingAssignment::create([
            'title' => 'Tugas Sains Khusus Bilingual',
            'description' => 'Membaca materi Cambridge Science',
            'book_id' => $book->id,
            'teacher_id' => $teacher->id,
            'grade_id' => $grade->id,
            'program_id' => $program->id,
            'due_date' => now()->addDays(7),
            'status' => 'published',
        ]);

        $this->assertNotNull($assignment);
        $this->assertEquals($program->id, $assignment->program_id);
        $this->assertEquals($program->name, $assignment->program->name);
    }
}
