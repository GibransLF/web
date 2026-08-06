<div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-zinc-200 dark:border-zinc-700 shadow-xs space-y-5">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-100 dark:border-zinc-700 pb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-[#1B287D] dark:text-indigo-400 flex items-center justify-center shrink-0">
                <flux:icon icon="hashtag" class="w-5 h-5" />
            </div>
            <div>
                <h3 class="font-bold text-base text-zinc-900 dark:text-white flex items-center gap-2">
                    Top 10 Kata Sering Ditanyakan
                    <span class="px-2 py-0.5 text-[10px] font-extrabold tracking-wide uppercase rounded-md bg-indigo-100 dark:bg-indigo-900/60 text-[#1B287D] dark:text-indigo-300">
                        Sastrawi
                    </span>
                </h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Analisis unigram kata pertanyaan calon mahasiswa 7 hari terakhir</p>
            </div>
        </div>

        @if(auth()->user()->isAdmin())
            <flux:modal.trigger name="frequent-words-stopwords-modal">
                <flux:button variant="subtle" icon="cog-6-tooth" class="text-xs font-semibold shrink-0">
                    Setting Stopwords ({{ $totalCustomCount }})
                </flux:button>
            </flux:modal.trigger>
        @endif
    </div>

    <!-- Chart Content Body -->
    @if(empty($topWords))
        <div class="py-12 text-center space-y-2">
            <flux:icon icon="document-magnifying-glass" class="w-10 h-10 text-zinc-300 dark:text-zinc-600 mx-auto" />
            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Belum ada cukup kata pertanyaan signifikan dalam 7 hari terakhir.</p>
        </div>
    @else
        <div class="space-y-3 pt-1">
            @foreach($topWords as $index => $item)
                <div class="flex items-center gap-3 text-xs group">
                    <!-- Rank Number -->
                    <span class="w-6 text-center font-mono font-bold text-xs {{ $index < 3 ? 'text-[#1B287D] dark:text-blue-400' : 'text-zinc-400' }}">
                        #{{ $index + 1 }}
                    </span>

                    <!-- Word Label -->
                    <div class="w-28 sm:w-36 font-semibold text-zinc-800 dark:text-zinc-200 truncate capitalize flex items-center gap-1.5" title="{{ $item['word'] }}">
                        <span class="px-2 py-1 bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700/70 rounded-md font-mono text-xs group-hover:border-[#1B287D] transition-colors truncate">
                            {{ $item['word'] }}
                        </span>
                    </div>

                    <!-- Progress / Frequency Bar -->
                    <div class="flex-1 bg-zinc-100 dark:bg-zinc-900 h-6 rounded-md overflow-hidden p-0.5 border border-zinc-200/60 dark:border-zinc-700/60 flex items-center">
                        <div class="h-full rounded-sm transition-all duration-500 relative flex items-center px-2 {{ $index === 0 ? 'bg-gradient-to-r from-[#1B287D] to-indigo-600 dark:from-blue-600 dark:to-indigo-500' : ($index < 3 ? 'bg-gradient-to-r from-blue-700 to-indigo-500 dark:from-blue-700 dark:to-blue-500' : 'bg-gradient-to-r from-zinc-500 to-zinc-600 dark:from-zinc-700 dark:to-zinc-600') }}"
                             style="width: {{ max($item['percentage'], 6) }}%;">
                        </div>
                    </div>

                    <!-- Count Tag -->
                    <div class="w-16 text-right shrink-0">
                        <span class="font-mono font-bold text-xs text-zinc-900 dark:text-white">
                            {{ $item['count'] }}x
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Flux UI Modal for Managing Custom Stopwords -->
    @if(auth()->user()->isAdmin())
        <flux:modal name="frequent-words-stopwords-modal" class="max-w-xl">
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg" class="flex items-center gap-2">
                        <flux:icon icon="funnel" class="w-5 h-5 text-[#1B287D]" />
                        Pengaturan Custom Stopwords Admin
                    </flux:heading>
                    <flux:subheading>
                        Tambah atau hapus kata buang (*stopwords*) yang akan diabaikan dari analisis grafik Top 10. Data tersimpan dengan ID admin terautentikasi.
                    </flux:subheading>
                </div>

                <!-- Flash Alert inside Modal -->
                @if (session()->has('success_stopword'))
                    <x-alert-banner type="success" :message="session('success_stopword')" />
                @endif

                @if (session()->has('info_stopword'))
                    <x-alert-banner type="info" :message="session('info_stopword')" />
                @endif

                <!-- Add New Stopword Form -->
                <form wire:submit.prevent="addStopwords" class="space-y-3 bg-zinc-50 dark:bg-zinc-900/60 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700/70">
                    <flux:field>
                        <flux:label>Tambah Kata Stopword Baru</flux:label>
                        <flux:description class="text-xs">Dapat memasukkan kata tunggal atau beberapa kata sekaligus (pisahkan dengan koma/spasi).</flux:description>
                        <div class="flex items-center gap-2 mt-2">
                            <flux:input
                                wire:model="new_stopwords"
                                placeholder="Contoh: kak, min, admin, info, tolong, pmb"
                                class="flex-1 text-xs"
                            />
                            <flux:button variant="primary" type="submit" size="sm" icon="plus" wire:loading.attr="disabled">
                                Tambah
                            </flux:button>
                        </div>
                        <flux:error name="new_stopwords" />
                    </flux:field>
                </form>

                <!-- Custom Stopwords List with Search -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-4">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-zinc-500">
                            Daftar Stopwords Admin ({{ $totalCustomCount }})
                        </h4>
                        <div class="w-48">
                            <flux:input icon="magnifying-glass" size="sm" wire:model.live="search_custom" placeholder="Cari stopword..." />
                        </div>
                    </div>

                    <!-- Stopwords Badges Container -->
                    <div class="max-h-56 overflow-y-auto p-3 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-2">
                        @forelse($customStopwords as $sw)
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 m-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 text-xs font-mono group hover:border-red-300">
                                <span class="font-bold">{{ $sw->word }}</span>
                                @if($sw->user)
                                    <span class="text-[10px] text-zinc-400 font-sans">({{ $sw->user->name }})</span>
                                @endif
                                <button
                                    type="button"
                                    wire:click="deleteStopword({{ $sw->id }})"
                                    class="text-zinc-400 hover:text-red-600 transition-colors ml-1 p-0.5 rounded hover:bg-red-50 dark:hover:bg-red-950/50"
                                    title="Hapus stopword '{{ $sw->word }}'"
                                >
                                    <flux:icon icon="x-mark" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        @empty
                            <p class="text-xs text-zinc-400 text-center py-4">Belum ada custom stopword ditambahkan oleh admin.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="flex justify-end pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:modal.close>
                        <flux:button variant="ghost" size="sm">Tutup Modal</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
