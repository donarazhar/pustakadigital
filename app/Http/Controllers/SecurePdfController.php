<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SecurePdfController extends Controller
{
    /**
     * Stream or download PDF file with full authentication and role authorization.
     */
    public function stream(Request $request, Book $book)
    {
        // 1. Pastikan buku berformat PDF dan memiliki berkas
        if (! $book->isPdf() || empty($book->pdf_file)) {
            abort(Response::HTTP_NOT_FOUND, 'Berkas PDF untuk buku ini tidak ditemukan.');
        }

        // 2. Otorisasi status publikasi
        // Siswa hanya boleh mengakses buku yang sudah dipublikasikan (is_published = true)
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $book->is_published && (! $user || $user->isStudent())) {
            abort(Response::HTTP_FORBIDDEN, 'Buku ini belum dipublikasikan oleh pihak sekolah.');
        }

        // 3. Verifikasi keberadaan file di storage
        $disk = Storage::disk('public');
        if (! $disk->exists($book->pdf_file)) {
            abort(Response::HTTP_NOT_FOUND, 'File fisik PDF tidak ditemukan pada server.');
        }

        $fullPath = $disk->path($book->pdf_file);
        $safeFileName = Str::slug($book->title) . '.pdf';

        // 4. Tentukan mode inline (tampil di browser/flipbook) atau attachment (unduh)
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        // 5. Kembalikan secure binary stream dengan proteksi header keamanan
        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "{$disposition}; filename=\"{$safeFileName}\"",
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
