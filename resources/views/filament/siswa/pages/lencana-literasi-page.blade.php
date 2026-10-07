<x-filament-panels::page>
    <div class="space-y-6">
        @php
            /** @var \App\Models\User $user */
            $user = auth()->user();
            $badges = $this->badges;
            $earnedCount = $badges->where('is_earned', true)->count();
            $totalCount = $badges->count();
            $earnedPoints = $badges->where('is_earned', true)->sum('points_reward');
        @endphp

        <!-- Header Ringkasan Lencana -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-5 text-white shadow-md flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-amber-100">Lencana Koleksimu</span>
                    <div class="text-3xl font-black mt-1">
                        {{ $earnedCount }} <span class="text-lg font-medium text-amber-200">/ {{ $totalCount }}</span>
                    </div>
                    <span class="text-xs text-amber-100 mt-1 block">
                        {{ round(($earnedCount / max(1, $totalCount)) * 100) }}% Lencana telah terbuka
                    </span>
                </div>
                <div class="text-4xl p-3 bg-white/20 rounded-2xl backdrop-blur-xs">
                    🏅
                </div>
            </div>

            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-5 text-white shadow-md flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-emerald-100">Total Poin Literasi</span>
                    <div class="text-3xl font-black mt-1">
                        ⭐ {{ number_format($user->literacy_points ?? 0) }}
                    </div>
                    <span class="text-xs text-emerald-100 mt-1 block">
                        +{{ number_format($earnedPoints) }} Poin dari hadiah lencana
                    </span>
                </div>
                <div class="text-4xl p-3 bg-white/20 rounded-2xl backdrop-blur-xs">
                    🌟
                </div>
            </div>

            <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-5 text-white shadow-md flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-rose-100">Reading Streak Aktif</span>
                    <div class="text-3xl font-black mt-1">
                        🔥 {{ $user->reading_streak_days ?? 0 }} <span class="text-lg font-medium text-rose-200">Hari</span>
                    </div>
                    <span class="text-xs text-rose-100 mt-1 block">
                        Rekor terpanjang: {{ $user->longest_streak_days ?? 0 }} hari berturut-turut
                    </span>
                </div>
                <div class="text-4xl p-3 bg-white/20 rounded-2xl backdrop-blur-xs">
                    ⚡
                </div>
            </div>
        </div>

        <!-- Etalase Kartu Lencana -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>Etalase Seluruh Lencana</span>
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                        (Dapatkan lencana dengan membaca, menyelesaikan kuis, dan menulis catatan)
                    </span>
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($badges as $item)
                    <div class="relative rounded-2xl border transition-all duration-200 flex flex-col justify-between overflow-hidden {{ $item['is_earned'] ? 'bg-white dark:bg-gray-900 border-amber-300 dark:border-amber-700/60 shadow-md hover:-translate-y-1' : 'bg-gray-50/70 dark:bg-gray-800/40 border-gray-200 dark:border-gray-800 opacity-80' }}">
                        
                        <!-- Ribbon Status -->
                        @if($item['is_earned'])
                            <div class="bg-gradient-to-r from-amber-500 to-yellow-400 text-amber-950 text-[10px] font-black uppercase tracking-wider py-1 px-3 text-center">
                                ✨ Telah Diraih ✨
                            </div>
                        @else
                            <div class="bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-[10px] font-bold uppercase tracking-wider py-1 px-3 text-center flex items-center justify-center gap-1">
                                <span>🔒 Terkunci</span>
                            </div>
                        @endif

                        <div class="p-5 flex-1 flex flex-col items-center text-center">
                            <!-- Icon Bulat Lencana -->
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mb-3 shadow-inner {{ $item['is_earned'] ? 'bg-amber-100 dark:bg-amber-900/50 border-2 border-amber-400' : 'bg-gray-200 dark:bg-gray-700/60 grayscale border border-gray-300 dark:border-gray-600' }}">
                                {{ $item['icon'] }}
                            </div>

                            <h4 class="font-extrabold text-base text-gray-900 dark:text-white mb-1">
                                {{ $item['name'] }}
                            </h4>

                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold mb-3 {{ $item['is_earned'] ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                                ⭐ +{{ $item['points_reward'] }} Poin
                            </span>

                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                                {{ $item['description'] }}
                            </p>
                        </div>

                        <!-- Footer Kartu: Progres atau Tanggal Diraih -->
                        <div class="p-4 pt-0">
                            @if($item['is_earned'])
                                <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 rounded-xl p-2.5 text-center">
                                    <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-300 flex items-center justify-center gap-1">
                                        <span>✅</span>
                                        <span>Diraih {{ $item['awarded_at'] ? \Carbon\Carbon::parse($item['awarded_at'])->diffForHumans() : 'oleh siswa' }}</span>
                                    </span>
                                </div>
                            @else
                                <div class="bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 rounded-xl p-2.5">
                                    <div class="flex items-center justify-between text-[11px] font-semibold text-gray-600 dark:text-gray-300 mb-1.5">
                                        <span>Progres:</span>
                                        <span>{{ $item['progress_current'] }} / {{ $item['progress_target'] }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                        <div class="bg-amber-500 h-full rounded-full transition-all duration-300" style="width: {{ $item['progress_percent'] }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
