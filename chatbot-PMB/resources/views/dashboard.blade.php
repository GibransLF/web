<x-layouts::app :title="__('Analytics Dashboard')">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl">Analytics & Overview PMB</flux:heading>
                <flux:subheading>Statistik penggunaan AI Assistant dan Knowledge Base PMB STMIK Bandung.</flux:subheading>
            </div>
            <flux:button variant="primary" icon="arrow-top-right-on-square" href="{{ route('home') }}" target="_blank">
                Lihat Landing Page
            </flux:button>
        </div>

        @php
            $totalChat = \App\Models\ChatHistory::count();
            $totalDoc = \App\Models\KnowledgeBase::count();
            $setting = \App\Models\ChatbotSetting::current();
            $recentChats = \App\Models\ChatHistory::latest()->take(5)->get();

            // 7-Day Chatbot Usage Statistics
            $usageDays = collect(range(6, 0))->map(function ($daysAgo) {
                $date = \Carbon\Carbon::now()->subDays($daysAgo);
                $count = \App\Models\ChatHistory::whereDate('created_at', $date->toDateString())->count();
                return [
                    'date' => $date->format('d/m'),
                    'dayName' => $date->isoFormat('ddd'),
                    'count' => $count,
                    'isToday' => $daysAgo === 0,
                ];
            });

            $weeklyTotal = $usageDays->sum('count');
            $weeklyAvg = round($usageDays->avg('count'), 1);
            $maxCount = max($usageDays->max('count'), 1);
        @endphp

        <!-- Stat Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 uppercase">Total Percakapan</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#1B287D] flex items-center justify-center">
                        <flux:icon icon="chat-bubble-bottom-center-text" class="w-5 h-5" />
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-zinc-900 dark:text-white">{{ number_format($totalChat) }}</p>
                <p class="text-[11px] text-emerald-600 font-medium">↑ Tersimpan di PostgreSQL</p>
            </div>

            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 uppercase">Dokumen Knowledge</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                        <flux:icon icon="document-text" class="w-5 h-5" />
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-zinc-900 dark:text-white">{{ number_format($totalDoc) }}</p>
                <p class="text-[11px] text-zinc-500">DOCX Knowledge Base</p>
            </div>

            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 uppercase">Max Input Limit</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="w-5 h-5" />
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-zinc-900 dark:text-white">{{ $setting->max_input_character }} <span class="text-sm font-normal text-zinc-400">karakter</span></p>
                <p class="text-[11px] text-purple-600 font-medium">Batas Input Pengguna</p>
            </div>

            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 uppercase">LLM Temperature</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <flux:icon icon="sparkles" class="w-5 h-5" />
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-zinc-900 dark:text-white">{{ $setting->temperature }}</p>
                <p class="text-[11px] text-emerald-600 font-medium">Respon Konsisten & Presisi</p>
            </div>
        </div>

        <!-- 7-Day Usage Chart & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- 7-Day Chatbot Usage Chart -->
            <div class="lg:col-span-7 bg-white dark:bg-zinc-800 rounded-xl p-6 border border-zinc-200 dark:border-zinc-700 shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-700 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-[#1B287D] dark:text-blue-400 flex items-center justify-center">
                            <flux:icon icon="chart-bar" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-zinc-900 dark:text-white">Grafik 7 Hari Pemakaian Chatbot</h3>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Jumlah pertanyaan dari pengunjung PMB per hari</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full bg-blue-50 text-[#1B287D] dark:bg-blue-900/40 dark:text-blue-300 border border-blue-100 dark:border-blue-800">
                        7 Hari Terakhir
                    </span>
                </div>

                <!-- Summary Metrics Bar -->
                <div class="grid grid-cols-3 gap-3 p-3 bg-zinc-50 dark:bg-zinc-900/60 rounded-lg border border-zinc-100 dark:border-zinc-800">
                    <div class="text-center border-r border-zinc-200 dark:border-zinc-800 pr-2">
                        <span class="text-[10px] uppercase font-bold text-zinc-400">Total 7 Hari</span>
                        <p class="text-base font-extrabold text-zinc-900 dark:text-white">{{ number_format($weeklyTotal) }} <span class="text-[11px] font-normal text-zinc-500">chat</span></p>
                    </div>
                    <div class="text-center border-r border-zinc-200 dark:border-zinc-800 px-2">
                        <span class="text-[10px] uppercase font-bold text-zinc-400">Rata-Rata</span>
                        <p class="text-base font-extrabold text-[#1B287D] dark:text-blue-400">{{ $weeklyAvg }} <span class="text-[11px] font-normal text-zinc-500">/hari</span></p>
                    </div>
                    <div class="text-center pl-2">
                        <span class="text-[10px] uppercase font-bold text-zinc-400">Puncak</span>
                        <p class="text-base font-extrabold text-emerald-600 dark:text-emerald-400">{{ $usageDays->max('count') }} <span class="text-[11px] font-normal text-zinc-500">chat</span></p>
                    </div>
                </div>

                <!-- Custom Bar Chart Container (No External JS / CDN) -->
                <div class="pt-4">
                    <div class="h-48 flex items-end justify-between gap-2 sm:gap-4 px-2 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                        @foreach($usageDays as $day)
                            @php
                                $heightPercent = max(round(($day['count'] / $maxCount) * 100), $day['count'] > 0 ? 8 : 4);
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-1 group h-full justify-end">
                                <!-- Count Tag above Bar -->
                                <span class="text-[11px] font-bold text-zinc-600 dark:text-zinc-400 opacity-80 group-hover:opacity-100 group-hover:scale-110 transition-transform">
                                    {{ $day['count'] }}
                                </span>

                                <!-- Bar Column -->
                                <div class="w-full max-w-[38px] bg-zinc-100 dark:bg-zinc-800 rounded-t-md h-full flex items-end overflow-hidden p-0.5 border border-zinc-200/50 dark:border-zinc-700/50">
                                    <div class="w-full rounded-t-sm transition-all duration-500 group-hover:brightness-110 shadow-xs relative {{ $day['isToday'] ? 'bg-gradient-to-t from-[#1B287D] to-indigo-500 dark:from-blue-600 dark:to-indigo-400' : 'bg-gradient-to-t from-blue-900/80 to-[#1B287D] dark:from-blue-700 dark:to-blue-500' }}"
                                         style="height: {{ $heightPercent }}%;">
                                        @if($day['isToday'])
                                            <div class="absolute inset-x-0 top-0 h-1 bg-[#F9CE04] rounded-t-sm"></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- X-Axis Date Labels -->
                    <div class="flex justify-between gap-2 sm:gap-4 px-2 pt-2 text-center">
                        @foreach($usageDays as $day)
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-bold truncate {{ $day['isToday'] ? 'text-[#1B287D] dark:text-blue-400' : 'text-zinc-600 dark:text-zinc-400' }}">
                                    {{ $day['dayName'] }}
                                </p>
                                <p class="text-[10px] text-zinc-400 dark:text-zinc-500 truncate">
                                    {{ $day['date'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Chat Log -->
            <div class="lg:col-span-5 bg-white dark:bg-zinc-800 rounded-xl p-6 border border-zinc-200 dark:border-zinc-700 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-700 pb-3">
                    <h3 class="font-bold text-base text-zinc-900 dark:text-white flex items-center gap-2">
                        <flux:icon icon="clock" class="w-5 h-5 text-blue-600" />
                        Percakapan Terakhir
                    </h3>
                    <a href="{{ route('admin.chat-history') }}" class="text-xs font-semibold text-[#1B287D] hover:underline">Lihat Semua →</a>
                </div>

                <div class="space-y-3">
                    @forelse($recentChats as $chat)
                        <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-900 space-y-1">
                            <div class="flex items-center justify-between text-[11px] text-zinc-400">
                                <span class="font-mono font-bold text-zinc-600 dark:text-zinc-300">{{ $chat->guest_id }}</span>
                                <span>{{ $chat->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs font-semibold text-zinc-900 dark:text-white truncate">Q: {{ $chat->question }}</p>
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-zinc-400">
                            Belum ada riwayat percakapan tercatat.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
