<x-layouts::app :title="__('Analytics Dashboard')">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Analytics & Overview PMB</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Statistik penggunaan AI Assistant dan Knowledge Base PMB STMIK Bandung.</p>
            </div>
            <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-semibold bg-[#1B287D] text-white px-3.5 py-2 rounded-lg hover:bg-[#000E65] transition shadow-xs">
                <flux:icon icon="arrow-top-right-on-square" class="w-4 h-4 text-[#F9CE04]" />
                Lihat Landing Page
            </a>
        </div>

        @php
            $totalChat = \App\Models\ChatHistory::count();
            $totalDoc = \App\Models\KnowledgeBase::count();
            $setting = \App\Models\ChatbotSetting::current();
            $recentChats = \App\Models\ChatHistory::latest()->take(5)->get();
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

        <!-- FAQ Analytics & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Top Frequently Asked Questions -->
            <div class="lg:col-span-7 bg-white dark:bg-zinc-800 rounded-xl p-6 border border-zinc-200 dark:border-zinc-700 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-700 pb-3">
                    <h3 class="font-bold text-base text-zinc-900 dark:text-white flex items-center gap-2">
                        <flux:icon icon="fire" class="w-5 h-5 text-amber-500" />
                        Topik Pertanyaan Paling Sering Diajukan (FAQ)
                    </h3>
                </div>

                <div class="space-y-3">
                    <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-900 flex items-center justify-between">
                        <div class="space-y-0.5">
                            <p class="text-xs font-bold text-[#1B287D] dark:text-blue-300">Biaya Kuliah & Skema Angsuran</p>
                            <p class="text-[11px] text-zinc-500">Berapa biaya SPP S1 Teknik Informatika dan Sistem Informasi?</p>
                        </div>
                        <span class="px-2.5 py-1 bg-blue-100 text-[#1B287D] font-bold text-xs rounded-full">42%</span>
                    </div>

                    <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-900 flex items-center justify-between">
                        <div class="space-y-0.5">
                            <p class="text-xs font-bold text-[#1B287D] dark:text-blue-300">Syarat Berkas Pendaftaran</p>
                            <p class="text-[11px] text-zinc-500">Apa saja berkas yang harus diunggah untuk mendaftar?</p>
                        </div>
                        <span class="px-2.5 py-1 bg-blue-100 text-[#1B287D] font-bold text-xs rounded-full">28%</span>
                    </div>

                    <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-900 flex items-center justify-between">
                        <div class="space-y-0.5">
                            <p class="text-xs font-bold text-[#1B287D] dark:text-blue-300">Program Beasiswa KIP & Prestasi</p>
                            <p class="text-[11px] text-zinc-500">Apakah tersedia beasiswa penuh hingga lulus?</p>
                        </div>
                        <span class="px-2.5 py-1 bg-blue-100 text-[#1B287D] font-bold text-xs rounded-full">18%</span>
                    </div>

                    <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-900 flex items-center justify-between">
                        <div class="space-y-0.5">
                            <p class="text-xs font-bold text-[#1B287D] dark:text-blue-300">Jadwal Kelas Reguler vs Karyawan</p>
                            <p class="text-[11px] text-zinc-500">Apakah ada kuliah malam atau akhir pekan?</p>
                        </div>
                        <span class="px-2.5 py-1 bg-blue-100 text-[#1B287D] font-bold text-xs rounded-full">12%</span>
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
