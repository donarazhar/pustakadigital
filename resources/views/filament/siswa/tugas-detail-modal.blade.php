@php
    $studentPivot = $assignment->assignmentStudents->where('student_id', auth()->id())->first();
    $status = $studentPivot->status ?? 'assigned';
    $progress = $studentPivot->progress_percent ?? 0;
    $completedAt = $studentPivot->completed_at ?? null;

    $targetUrl = $assignment->target_chapter_id
        ? route('books.read.chapter', ['book' => $assignment->book->slug, 'chapter' => $assignment->target_chapter_id])
        : route('books.read', $assignment->book->slug);
@endphp

<div class="space-y-6">
    <!-- Header Ringkasan Tugas -->
    <div class="p-5 rounded-2xl bg-linear-to-br from-primary-50 to-indigo-50/40 dark:from-gray-900 dark:to-primary-950/20 border border-primary-100 dark:border-primary-900/30">
        <div class="flex items-start justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 mb-2">
                    Tugas dari {{ $assignment->teacher->name ?? 'Guru Pengampu' }}
                </span>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $assignment->title }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    📖 Buku: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $assignment->book->title }}</span>
                    @if($assignment->targetChapter)
                        <span class="text-primary-600 dark:text-primary-400 font-medium"> • Bab: {{ $assignment->targetChapter->title }}</span>
                    @else
                        <span class="text-gray-500 font-medium"> • Seluruh Isi Buku</span>
                    @endif
                </p>
            </div>

            <!-- Status Badge Siswa -->
            <div>
                @if($status === 'completed')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                        ✅ Selesai
                    </span>
                @elseif($status === 'in_progress')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                        ⏳ Sedang Dibaca ({{ $progress }}%)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                        ⚪ Belum Dimulai
                    </span>
                @endif
            </div>
        </div>

        <!-- Progress Bar Siswa -->
        <div class="mt-4 pt-4 border-t border-primary-200/40 dark:border-gray-800">
            <div class="flex justify-between items-center text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">
                <span>Progres Membaca Kamu</span>
                <span class="{{ $status === 'completed' ? 'text-emerald-600' : 'text-primary-600' }}">{{ $progress }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-3 overflow-hidden">
                <div class="h-3 rounded-full transition-all duration-500 {{ $status === 'completed' ? 'bg-emerald-500' : 'bg-primary-500' }}" style="<?php echo 'width: ' . $progress . '%;'; ?>"></div>
            </div>
            @if($completedAt)
                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2 font-medium">
                    🎉 Kamu telah menyelesaikan penugasan ini pada {{ $completedAt->format('d M Y, H:i') }}.
                </p>
            @endif
        </div>
    </div>

    <!-- Info Detail & Tenggat Waktu -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/40">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tenggat Waktu (Deadline)</span>
            <div class="mt-2 flex items-center gap-2">
                <svg class="w-5 h-5 {{ $assignment->isOverdue() && $status !== 'completed' ? 'text-rose-500' : 'text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                @if($assignment->due_date)
                    <span class="text-sm font-bold {{ $assignment->isOverdue() && $status !== 'completed' ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $assignment->due_date->format('d M Y, H:i') }}
                        @if($assignment->isOverdue() && $status !== 'completed')
                            <span class="text-xs font-normal text-rose-500 block">(Telah melewati batas waktu)</span>
                        @else
                            <span class="text-xs font-normal text-gray-500 block">({{ $assignment->due_date->diffForHumans() }})</span>
                        @endif
                    </span>
                @else
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tidak ada batas waktu (Bebas)</span>
                @endif
            </div>
        </div>

        <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/40">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenjang & Mata Pelajaran</span>
            <div class="mt-2 flex flex-wrap gap-2">
                @if($assignment->book->grade)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300">
                        Kelas {{ $assignment->book->grade->name }}
                    </span>
                @endif
                @if($assignment->book->subject)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300">
                        {{ $assignment->book->subject->name }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Instruksi Guru -->
    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Petunjuk & Instruksi dari Guru</h4>
        @if($assignment->description)
            <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed bg-gray-50 dark:bg-gray-800/60 p-3 rounded-lg border border-gray-100 dark:border-gray-800">
                {{ $assignment->description }}
            </div>
        @else
            <p class="text-sm text-gray-400 italic">Tidak ada instruksi khusus. Silakan baca buku hingga tuntas sesuai target.</p>
        @endif
    </div>

    <!-- Tombol Baca Langsung -->
    <div class="pt-2 flex justify-end">
        <a href="{{ $targetUrl }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm text-white bg-primary-600 hover:bg-primary-700 shadow-md hover:shadow-lg transition">
            📖 {{ $status === 'completed' ? 'Baca Ulang Buku' : ($status === 'in_progress' ? 'Lanjutkan Membaca' : 'Mulai Membaca Sekarang') }}
        </a>
    </div>
</div>
