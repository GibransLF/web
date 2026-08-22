<div>
    @if (session()->has('success_validation'))
        <div class="mb-4">
            <x-alert-banner type="success" :message="session('success_validation')" />
        </div>
    @endif

    @if($unansweredCount > 0)
        <!-- Danger Orange Alert Component -->
        <div class="p-4 rounded-xl border bg-orange-50 dark:bg-orange-950/40 border-orange-200 dark:border-orange-800 text-orange-900 dark:text-orange-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900/60 text-orange-600 dark:text-orange-400 flex items-center justify-center shrink-0">
                    <flux:icon icon="exclamation-triangle" class="w-5 h-5" />
                </div>
                <div>
                    <h4 class="font-bold text-sm text-orange-900 dark:text-orange-100 flex items-center gap-2">
                        Pertanyaan Belum Terjawab
                        <span class="px-2 py-0.5 text-[10px] font-extrabold tracking-wide uppercase rounded-md bg-orange-200 dark:bg-orange-900/80 text-orange-800 dark:text-orange-200">
                            {{ $unansweredCount }} Perlu Validasi
                        </span>
                    </h4>
                    <p class="text-xs text-orange-700 dark:text-orange-300 mt-0.5">
                        Terdapat {{ $unansweredCount }} pertanyaan pengunjung yang belum terjawab atau membutuhkan validasi knowledge base.
                    </p>
                </div>
            </div>

            <flux:modal.trigger name="unanswered-questions-modal">
                <flux:button variant="danger" icon="magnifying-glass" class="text-xs font-semibold shrink-0 cursor-pointer bg-orange-600 hover:bg-orange-700 text-white border-orange-600">
                    Lihat & Validasi ({{ $unansweredCount }})
                </flux:button>
            </flux:modal.trigger>
        </div>

        <!-- Flux UI Modal for Unanswered Questions -->
        <flux:modal name="unanswered-questions-modal" class="max-w-2xl">
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg" class="flex items-center gap-2 text-orange-600 dark:text-orange-400">
                        <flux:icon icon="exclamation-triangle" class="w-5 h-5" />
                        Daftar Pertanyaan Belum Terjawab / Belum Divalidasi
                    </flux:heading>
                    <flux:subheading class="mt-1">
                        Pertanyaan yang tidak dapat dijawab.
                    </flux:subheading>
                </div>

                <!-- Flash Alert inside Modal using x-alert-banner -->
                @if (session()->has('success_validation'))
                    <x-alert-banner type="success" :message="session('success_validation')" />
                @endif

                <!-- Search Filter & Bulk Action -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Pertanyaan Perlu Validasi ({{ count($unansweredQuestions) }})
                    </h4>
                    <div class="flex items-center gap-2">
                        <div class="w-48 sm:w-56">
                            <flux:input icon="magnifying-glass" size="sm" wire:model.live="search" placeholder="Cari pertanyaan..." />
                        </div>
                        @if(auth()->user()->isAdmin() && count($unansweredQuestions) > 0)
                            <flux:button
                                size="sm"
                                variant="primary"
                                icon="check-badge"
                                wire:click="markAllAsValidated"
                                class="shrink-0 cursor-pointer text-xs"
                            >
                                Validasi Semua
                            </flux:button>
                        @endif
                    </div>
                </div>

                <!-- Compact Scrollable List -->
                <div class="max-h-64 overflow-y-auto p-2.5 bg-zinc-50 dark:bg-zinc-900/60 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-2">
                    @forelse($unansweredQuestions as $item)
                        <div class="p-2.5 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700/80 shadow-2xs space-y-1">
                            <div class="flex items-center justify-between text-[11px] text-zinc-500">
                                <span class="font-semibold text-zinc-700 dark:text-zinc-300 flex items-center gap-1">
                                    <flux:icon icon="user" class="w-3 h-3" />
                                    {{ $item->user ? $item->user->name : ($item->guest_id ? 'guest ('.$item->guest_id.')' : 'Guest') }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <flux:icon icon="clock" class="w-3 h-3" />
                                    {{ $item->created_at ? $item->created_at->format('d M H:i') : '-' }}
                                </span>
                            </div>

                            <p class="text-xs font-semibold text-zinc-900 dark:text-white leading-tight">
                                Q: {{ $item->question }}
                            </p>
                            <p class="text-[11px] text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-900 p-2 rounded border border-zinc-100 dark:border-zinc-800 italic leading-relaxed whitespace-pre-line">
                                A: {{ $item->answer }}
                            </p>

                            <div class="flex items-center justify-end pt-0.5">
                                @if(auth()->user()->isAdmin())
                                    <flux:button
                                        size="xs"
                                        variant="primary"
                                        icon="check-circle"
                                        wire:click="markAsValidated({{ $item->id }})"
                                        class="cursor-pointer text-[11px] py-0.5"
                                    >
                                        Tandai Divalidasi
                                    </flux:button>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <flux:icon icon="eye" class="w-3 h-3" />
                                        Hanya Lihat (Supervisor)
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center space-y-1">
                            <flux:icon icon="check-circle" class="w-7 h-7 text-emerald-500 mx-auto" />
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                Tidak ada pertanyaan yang belum divalidasi.
                            </p>
                        </div>
                    @endforelse
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
