<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\ReadingAssignment;
use App\Models\ReadingAssignmentStudent;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReadingAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('role', 'teacher')->first() ?? User::where('role', 'admin')->first();
        $book = Book::with('chapters')->where('is_published', true)->first();

        if (! $book) {
            return;
        }

        $chapter = $book->chapters->first();
        $studentsGrade4 = User::where('role', 'student')->where('grade_id', 4)->get();

        // 1. Tugas Membaca Bab Spesifik
        $assignment1 = ReadingAssignment::updateOrCreate(
            [
                'title' => 'Membaca Terarah: ' . ($chapter ? $chapter->title : $book->title),
            ],
            [
                'description' => "Harap membaca bab ini dengan seksama. Pahami konsep dan bagan yang ada di dalamnya sebagai persiapan diskusi kelompok pada pertemuan mendatang.",
                'teacher_id' => $teacher->id,
                'book_id' => $book->id,
                'target_chapter_id' => $chapter?->id,
                'grade_id' => 4,
                'due_date' => now()->addDays(5)->setTime(23, 59),
                'is_active' => true,
            ]
        );

        foreach ($studentsGrade4 as $index => $student) {
            $status = 'assigned';
            $percent = 0;
            $completedAt = null;

            if ($index === 0) {
                // Ananda Putri sudah selesai
                $status = 'completed';
                $percent = 100;
                $completedAt = now()->subHours(2);
            } elseif ($index === 1) {
                // Rafi Pratama sedang membaca
                $status = 'in_progress';
                $percent = 60;
            }

            ReadingAssignmentStudent::updateOrCreate(
                [
                    'reading_assignment_id' => $assignment1->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => $status,
                    'progress_percent' => $percent,
                    'completed_at' => $completedAt,
                    'notes' => $status === 'completed' ? 'Selesai membaca dan memahami materi.' : null,
                ]
            );
        }

        // 2. Tugas Membaca Seluruh Buku
        $secondBook = Book::where('is_published', true)->where('id', '!=', $book->id)->first() ?? $book;
        $assignment2 = ReadingAssignment::updateOrCreate(
            [
                'title' => 'Literasi Mingguan: ' . $secondBook->title,
            ],
            [
                'description' => "Tugas literasi mandiri untuk memperluas wawasan. Bacalah seluruh bab di dalam buku ini secara bertahap.",
                'teacher_id' => $teacher->id,
                'book_id' => $secondBook->id,
                'target_chapter_id' => null, // Seluruh buku
                'grade_id' => 4,
                'due_date' => now()->addDays(10)->setTime(23, 59),
                'is_active' => true,
            ]
        );

        foreach ($studentsGrade4 as $index => $student) {
            ReadingAssignmentStudent::updateOrCreate(
                [
                    'reading_assignment_id' => $assignment2->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => $index === 0 ? 'in_progress' : 'assigned',
                    'progress_percent' => $index === 0 ? 35 : 0,
                    'completed_at' => null,
                ]
            );
        }
    }
}
