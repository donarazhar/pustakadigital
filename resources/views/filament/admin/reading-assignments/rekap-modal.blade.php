@php
    $students = $assignment->assignmentStudents()->with('student.grade')->get();
    $totalCount = $students->count();
    $completedCount = $students->where('status', 'completed')->count();
    $inProgressCount = $students->where('status', 'in_progress')->count();
    $assignedCount = $students->where('status', 'assigned')->count();
    $rate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 1) : 0;
@endphp

<div class="space-y-6">
    <!-- Ringkasan Statistik -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/60 flex flex-col justify-between">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Siswa</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalCount }}</span>
                <span class="text-xs text-gray-400">siswa</span>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-emerald-200 dark:border-emerald-900/40 bg-emerald-50/60 dark:bg-emerald-950/20 flex flex-col justify-between">
            <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Selesai Membaca</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $completedCount }}</span>
                <span class="text-xs font-medium text-emerald-700 dark:text-emerald-300">({{ $rate }}%)</span>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-amber-200 dark:border-amber-900/40 bg-amber-50/60 dark:bg-amber-950/20 flex flex-col justify-between">
            <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Sedang Membaca</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $inProgressCount }}</span>
                <span class="text-xs text-amber-600 dark:text-amber-400">siswa</span>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/40 flex flex-col justify-between">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Belum Membaca</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-slate-700 dark:text-slate-300">{{ $assignedCount }}</span>
                <span class="text-xs text-slate-400">siswa</span>
            </div>
        </div>
    </div>

    <!-- Overall Progress Bar -->
    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-3 border border-gray-200 dark:border-gray-700/60">
        <div class="flex justify-between items-center text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            <span>Tingkat Ketuntasan Kelas</span>
            <span class="font-bold {{ $rate >= 100 ? 'text-emerald-600' : ($rate > 50 ? 'text-blue-600' : 'text-amber-600') }}">{{ $rate }}% Tuntas</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
            <div class="h-2.5 rounded-full transition-all duration-500 {{ $rate >= 100 ? 'bg-emerald-500' : ($rate > 50 ? 'bg-blue-500' : 'bg-amber-500') }}" style="<?php echo 'width: ' . $rate . '%;'; ?>"></div>
        </div>
    </div>

    <!-- Tabel Daftar Siswa -->
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 shadow-xs">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-800/80 text-xs font-semibold uppercase text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="py-3 px-4">Nama Siswa</th>
                    <th class="py-3 px-4">Kelas</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Progres Baca</th>
                    <th class="py-3 px-4">Waktu Selesai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($students as $item)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                        <td class="py-3.5 px-4 font-medium text-gray-900 dark:text-white">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300 flex items-center justify-center font-bold text-xs uppercase">
                                    {{ substr($item->student->name ?? 'S', 0, 1) }}
                                </div>
                                <div>
                                    <div>{{ $item->student->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $item->student->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-xs font-medium">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                {{ $item->student->grade->name ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($item->status === 'completed')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    Selesai
                                </span>
                            @elseif($item->status === 'in_progress')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    Sedang Membaca
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                    Belum Dibaca
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 min-w-[140px]">
                            <div class="flex items-center gap-2.5">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                    <div class="h-2.5 rounded-full {{ $item->status === 'completed' ? 'bg-emerald-500' : ($item->progress_percent > 0 ? 'bg-amber-500' : 'bg-gray-300') }}" style="<?php echo 'width: ' . $item->progress_percent . '%;'; ?>"></div>
                                </div>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 w-9 text-right">{{ $item->progress_percent }}%</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            @if($item->completed_at)
                                <span class="text-emerald-700 dark:text-emerald-400 font-medium">
                                    {{ $item->completed_at->format('d M Y, H:i') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-500">
                            Belum ada siswa yang ditugaskan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
