<?php

use App\Http\Controllers\SecurePdfController;
use App\Livewire\BookReader;
use App\Models\Book;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $query = Book::with(['grade', 'program', 'subject', 'category', 'chapters'])
        ->where('is_published', true);

    if ($request->filled('grade')) {
        $query->where('grade_id', $request->grade);
    }

    if ($request->filled('program')) {
        $query->where('program_id', $request->program);
    }

    if ($request->filled('subject')) {
        $query->where('subject_id', $request->subject);
    }

    if ($request->filled('type')) {
        if ($request->type === 'pdf') {
            $query->where('book_type', 'pdf');
        } elseif ($request->type === 'interactive') {
            $query->where(function ($q) {
                $q->where('book_type', 'interactive')
                  ->orWhereNull('book_type');
            });
        }
    }

    if ($request->filled('q')) {
        $search = $request->q;
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('author', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    $books = $query->latest()->get();
    $grades = Grade::where('is_active', true)->orderBy('order')->get();
    $programs = \App\Models\SchoolProgram::where('is_active', true)->get();
    $subjects = Subject::all();

    $stats = [
        'total_books' => Book::where('is_published', true)->count(),
        'interactive_books' => Book::where('is_published', true)->where(function($q){
            $q->where('book_type', 'interactive')->orWhereNull('book_type');
        })->count(),
        'pdf_books' => Book::where('is_published', true)->where('book_type', 'pdf')->count(),
    ];

    return view('welcome', compact('books', 'grades', 'programs', 'subjects', 'stats'));
})->name('home');

Route::view('/offline', 'offline')->name('offline');

Route::middleware(['auth'])->group(function () {
    Route::get('/siswa/baca/{book:slug}', BookReader::class)->name('siswa.books.read');
    Route::get('/siswa/baca/{book:slug}/bab/{chapter}', BookReader::class)->name('siswa.books.read.chapter');
    Route::get('/baca/{book:slug}', BookReader::class)->name('books.read');
    Route::get('/baca/{book:slug}/bab/{chapter}', BookReader::class)->name('books.read.chapter');
    Route::get('/secure-pdf/{book:slug}', [SecurePdfController::class, 'stream'])->name('books.secure-pdf');
});
