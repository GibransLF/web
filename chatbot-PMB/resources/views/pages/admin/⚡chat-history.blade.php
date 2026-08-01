<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ChatHistory;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearAllHistory(): void
    {
        ChatHistory::truncate();
        session()->flash('success', 'Seluruh riwayat chat percakapan berhasil dibersihkan.');
    }
};
?>

<x-layouts::app :title="__('Chat History')">
    <div class="space-y-6">
        <!-- Flash Alert -->
        @if (session()->has('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Riwayat Percakapan Chatbot</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Log pertanyaan calon mahasiswa beserta jawaban AI dan rujukan dokumen sumber.</p>
            </div>
            @if(ChatHistory::exists())
                <flux:button variant="ghost" icon="trash" class="text-red-600 hover:bg-red-50" wire:click="clearAllHistory" wire:confirm="Apakah Anda yakin ingin mengosongkan seluruh riwayat percakapan?">
                    Bersihkan Riwayat
                </flux:button>
            @endif
        </div>

        <!-- Search Filter -->
        <div class="flex items-center justify-between gap-4 bg-white dark:bg-zinc-800 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs">
            <div class="relative w-full max-w-md">
                <flux:input icon="magnifying-glass" wire:model.live="search" placeholder="Cari Guest ID atau kata kunci pertanyaan..." />
            </div>
            <div class="text-xs text-zinc-500">
                Total Percakapan: <span class="font-bold text-zinc-900 dark:text-white">{{ ChatHistory::count() }}</span>
            </div>
        </div>

        <!-- Chat History Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs overflow-hidden">
            <flux:table>
                <flux:columns>
                    <flux:column>Guest ID</flux:column>
                    <flux:column>Pertanyaan Pengguna</flux:column>
                    <flux:column>Jawaban AI</flux:column>
                    <flux:column>Dokumen Sumber</flux:column>
                    <flux:column>Waktu</flux:column>
                </flux:columns>

                <flux:rows>
                    @forelse(ChatHistory::where('guest_id', 'like', '%'.$search.'%')->orWhere('question', 'like', '%'.$search.'%')->orWhere('answer', 'like', '%'.$search.'%')->latest()->paginate(10) as $history)
                        <flux:row>
                            <flux:cell>
                                <span class="font-mono text-xs font-semibold px-2 py-1 bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200 rounded-md">
                                    {{ $history->guest_id }}
                                </span>
                            </flux:cell>
                            <flux:cell class="max-w-xs">
                                <p class="text-xs font-medium text-zinc-900 dark:text-white leading-relaxed line-clamp-2">
                                    {{ $history->question }}
                                </p>
                            </flux:cell>
                            <flux:cell class="max-w-md">
                                <p class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed line-clamp-3">
                                    {{ $history->answer }}
                                </p>
                            </flux:cell>
                            <flux:cell>
                                @if(!empty($history->source_documents))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($history->source_documents as $doc)
                                            <span class="inline-flex items-center gap-1 text-[10px] bg-blue-50 text-[#1B287D] px-2 py-0.5 rounded-md font-medium border border-blue-200">
                                                <flux:icon icon="document-text" class="w-3 h-3" />
                                                {{ $doc }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400">-</span>
                                @endif
                            </flux:cell>
                            <flux:cell class="text-xs text-zinc-500 whitespace-nowrap">
                                {{ $history->created_at->format('d M Y, H:i') }}
                            </flux:cell>
                        </flux:row>
                    @empty
                        <flux:row>
                            <flux:cell colspan="5" class="text-center py-10 text-zinc-400">
                                Belum ada riwayat percakapan ditemukan.
                            </flux:cell>
                        </flux:row>
                    @endforelse
                </flux:rows>
            </flux:table>
        </div>
    </div>
</x-layouts::app>
