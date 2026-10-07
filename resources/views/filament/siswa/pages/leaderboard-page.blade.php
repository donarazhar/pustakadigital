<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Banner Info Peringkat Pengguna -->
        @php
            /** @var \App\Models\User $currentUser */
            $currentUser = auth()->user();
            $myRank = $this->currentUserRank;
            $students = $this->students;
            $firstPlace = $students->get(0);
            $secondPlace = $students->get(1);
            $thirdPlace = $students->get(2);
        @endphp

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-700 p-6 text-white shadow-lg">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur-xs mb-2">
                        🌟 Musim Literasi Sekolah
                    </span>
                    <h2 class="text-2xl font-black tracking-tight text-white">
                        Papan Peringkat Literasi & Membaca
                    </h2>
                    <p class="text-sm text-emerald-100 max-w-xl mt-1">
                        Terus membaca buku digital sekolah, pertahankan <em>Reading Streak</em> harian, selesaikan kuis, dan kumpulkan poin untuk menduduki puncak klasemen!
                    </p>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 flex items-center gap-4 shrink-0">
                    <div class="w-12 h-12 rounded-full bg-amber-400 text-amber-900 font-black text-xl flex items-center justify-center shadow-md">
                        #{{ $myRank }}
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-emerald-200 uppercase tracking-wider">Peringkat Kamu Saat Ini</div>
                        <div class="text-lg font-bold text-white flex items-center gap-2">
                            <span>{{ $currentUser->name }}</span>
                            <span class="text-xs bg-amber-400/30 text-amber-200 px-2 py-0.5 rounded-full font-semibold">
                                🔥 {{ $currentUser->reading_streak_days ?? 0 }} Hari
                            </span>
                        </div>
                        <div class="text-xs text-emerald-100">
                            ⭐ <strong>{{ number_format($currentUser->literacy_points ?? 0) }}</strong> Poin Literasi
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Switcher -->
            <div class="mt-6 pt-4 border-t border-white/15 flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-2">
                    <button 
                        wire:click="setFilter('all')" 
                        type="button"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $filterScope === 'all' ? 'bg-white text-emerald-800 shadow-sm' : 'bg-white/15 text-white hover:bg-white/25' }}"
                    >
                        🌐 Semua Siswa (Global)
                    </button>
                    @if($currentUser && $currentUser->grade)
                        <button 
                            wire:click="setFilter('grade')" 
                            type="button"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $filterScope === 'grade' ? 'bg-white text-emerald-800 shadow-sm' : 'bg-white/15 text-white hover:bg-white/25' }}"
                        >
                            🏫 {{ $currentUser->grade->name }}
                        </button>
                    @endif
                </div>

                <div class="text-xs text-emerald-100">
                    Menampilkan <strong>{{ $students->count() }}</strong> Siswa Berprestasi
                </div>
            </div>
        </div>

        <!-- Podium Top 3 Juara Visual -->
        @if($students->count() >= 3)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end pt-4 pb-2">
                <!-- Juara 2: Perak -->
                @if($secondPlace)
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border-2 border-slate-300 dark:border-slate-700 p-5 text-center shadow-md flex flex-col items-center relative order-2 md:order-1">
                        <div class="w-8 h-8 rounded-full bg-slate-300 text-slate-800 font-extrabold flex items-center justify-center text-sm absolute -top-3 shadow-sm">
                            🥈 2
                        </div>
                        <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 border-2 border-slate-300 flex items-center justify-center font-bold text-xl text-slate-700 dark:text-slate-200 mt-2 shadow-inner">
                            {{ strtoupper(substr($secondPlace->name, 0, 2)) }}
                        </div>
                        <h4 class="font-bold text-gray-900 dark:text-white mt-3 text-base truncate max-w-full">
                            {{ $secondPlace->name }}
                        </h4>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $secondPlace->grade ? $secondPlace->grade->name : 'Siswa' }}
                        </span>
                        <div class="mt-3 flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200">
                                ⭐ {{ number_format($secondPlace->literacy_points ?? 0) }} XP
                            </span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200">
                                🔥 {{ $secondPlace->reading_streak_days ?? 0 }} hr
                            </span>
                        </div>
                    </div>
                @endif

                <!-- Juara 1: Emas (Center & Elevated) -->
                @if($firstPlace)
                    <div class="bg-gradient-to-b from-amber-50 to-white dark:from-amber-950/40 dark:to-gray-900 rounded-2xl border-2 border-amber-400 p-6 text-center shadow-xl flex flex-col items-center relative order-1 md:order-2 scale-105 z-10">
                        <div class="text-2xl absolute -top-5 animate-bounce">
                            👑
                        </div>
                        <div class="w-10 h-10 rounded-full bg-amber-400 text-amber-900 font-extrabold flex items-center justify-center text-sm absolute -top-3 shadow-md border-2 border-white">
                            🥇 1
                        </div>
                        <div class="w-20 h-20 rounded-full bg-amber-100 dark:bg-amber-900/60 border-4 border-amber-400 flex items-center justify-center font-black text-2xl text-amber-900 dark:text-amber-100 mt-2 shadow-lg">
                            {{ strtoupper(substr($firstPlace->name, 0, 2)) }}
                        </div>
                        <h4 class="font-extrabold text-gray-900 dark:text-white mt-3 text-lg truncate max-w-full">
                            {{ $firstPlace->name }}
                        </h4>
                        <span class="text-xs font-semibold text-amber-700 dark:text-amber-300">
                            {{ $firstPlace->grade ? $firstPlace->grade->name : 'Siswa' }} &bull; Bintang Literasi
                        </span>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-400 text-amber-950 shadow-xs">
                                ⭐ {{ number_format($firstPlace->literacy_points ?? 0) }} Poin
                            </span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                🔥 {{ $firstPlace->reading_streak_days ?? 0 }} Hari
                            </span>
                        </div>
                    </div>
                @endif

                <!-- Juara 3: Perunggu -->
                @if($thirdPlace)
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border-2 border-amber-600/40 dark:border-amber-800 p-5 text-center shadow-md flex flex-col items-center relative order-3">
                        <div class="w-8 h-8 rounded-full bg-amber-700 text-white font-extrabold flex items-center justify-center text-sm absolute -top-3 shadow-sm">
                            🥉 3
                        </div>
                        <div class="w-16 h-16 rounded-full bg-amber-50 dark:bg-amber-950/60 border-2 border-amber-600/50 flex items-center justify-center font-bold text-xl text-amber-800 dark:text-amber-200 mt-2 shadow-inner">
                            {{ strtoupper(substr($thirdPlace->name, 0, 2)) }}
                        </div>
                        <h4 class="font-bold text-gray-900 dark:text-white mt-3 text-base truncate max-w-full">
                            {{ $thirdPlace->name }}
                        </h4>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $thirdPlace->grade ? $thirdPlace->grade->name : 'Siswa' }}
                        </span>
                        <div class="mt-3 flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200">
                                ⭐ {{ number_format($thirdPlace->literacy_points ?? 0) }} XP
                            </span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200">
                                🔥 {{ $thirdPlace->reading_streak_days ?? 0 }} hr
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Tabel Lengkap Peringkat -->
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                    Klasemen Seluruh Siswa
                </h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Peringkat diperbarui secara real-time
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-center w-16">Peringkat</th>
                            <th scope="col" class="px-6 py-3.5">Nama Siswa</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Kelas</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Poin Literasi</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Streak Membaca</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Buku Tuntas</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Lencana</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($students as $idx => $st)
                            @php
                                $rank = $idx + 1;
                                $isMe = ($st->id === $currentUser->id);
                            @endphp
                            <tr class="transition-colors {{ $isMe ? 'bg-emerald-50/70 dark:bg-emerald-950/30 font-semibold' : 'hover:bg-gray-50/50 dark:hover:bg-gray-800/40' }}">
                                <td class="px-6 py-4 text-center font-black">
                                    @if($rank === 1)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-400 text-amber-950 shadow-xs">🥇</span>
                                    @elseif($rank === 2)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-800 shadow-xs">🥈</span>
                                    @elseif($rank === 3)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-700 text-white shadow-xs">🥉</span>
                                    @else
                                        <span class="text-gray-400 text-sm">#{{ $rank }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 font-bold text-xs flex items-center justify-center">
                                            {{ strtoupper(substr($st->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                                <span>{{ $st->name }}</span>
                                                @if($isMe)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white uppercase tracking-wider">
                                                        Kamu
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-xs text-gray-400">{{ $st->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center text-xs">
                                    @if($st->grade)
                                        <span class="inline-block px-2.5 py-1 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium">
                                            {{ $st->grade->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-extrabold text-amber-600 dark:text-amber-400 text-base">
                                        ⭐ {{ number_format($st->literacy_points ?? 0) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 font-bold text-rose-600 dark:text-rose-400 text-sm">
                                        🔥 {{ $st->reading_streak_days ?? 0 }} hari
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold">
                                    {{ $st->completed_books_count }} buku
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                                        🏅 {{ $st->badges_count }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                    Belum ada data siswa pada papan peringkat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
