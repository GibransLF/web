<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\ChatHistory;

new #[Title('Chat History')] class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearAllHistory(): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        ChatHistory::truncate();
        session()->flash('success', 'Seluruh riwayat chat percakapan berhasil dibersihkan.');
    }

    public function with(): array
    {
        $query = ChatHistory::with('user');

        if (! empty($this->search)) {
            $likeOp = \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($likeOp) {
                $q->where('guest_id', $likeOp, '%'.$this->search.'%')
                    ->orWhere('question', $likeOp, '%'.$this->search.'%')
                    ->orWhere('answer', $likeOp, '%'.$this->search.'%')
                    ->orWhereHas('user', function ($uq) use ($likeOp) {
                        $uq->where('name', $likeOp, '%'.$this->search.'%');
                    });
            });
        }

        return [
            'chatMessages' => $query->latest()->paginate(10),
        ];
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl space-y-6">
    <!-- Flash Alert -->
    @if (session()->has('success'))
        <x-alert-banner type="success" :message="session('success')" />
    @endif

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('History Chatbot') }}</flux:heading>
            <flux:subheading>{{ __('Daftar riwayat chat asisten AI.') }}</flux:subheading>
        </div>
        @if(auth()->user()->isAdmin() && ChatHistory::exists())
            <flux:modal.trigger name="confirm-clear-history">
                <flux:button variant="ghost" icon="trash" class="text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30">
                    Bersihkan Riwayat
                </flux:button>
            </flux:modal.trigger>
        @endif
    </div>

    <!-- Alert for Unanswered / Unvalidated Questions -->
    <livewire:admin.unanswered-questions-alert />

    <!-- Search Filter & Counter Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs">
        <div class="relative w-full max-w-md">
            <flux:input icon="magnifying-glass" wire:model.live="search" placeholder="Cari ID Guest, Nama User, atau kata kunci..." />
        </div>
        <div class="text-xs text-zinc-500">
            Total Percakapan: <span class="font-bold text-zinc-900 dark:text-white">{{ ChatHistory::count() }}</span>
        </div>
    </div>

    <!-- Chat History List in the style of chat-history.blade.php -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 space-y-8">
        @forelse($chatMessages as $msg)
            <div class="border-b border-zinc-200 dark:border-zinc-800 py-6 first:pt-0 last:border-b-0 last:pb-0">
                <!-- Metadata: User Name or guest and created_at -->
                <div class="flex items-center justify-between mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                    <span class="inline-flex items-center gap-1.5 font-semibold text-zinc-700 dark:text-zinc-300">
                        <flux:icon name="user" class="w-3.5 h-3.5" />
                        @if($msg->user_id && $msg->user)
                            {{ $msg->user->name }}
                        @else
                            guest ({{ $msg->guest_id ?? 'unknown' }})
                        @endif
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <flux:icon name="clock" class="w-3.5 h-3.5" />
                        {{ $msg->created_at ? $msg->created_at->format('d M Y H:i:s') : '-' }}
                    </span>
                </div>

                <!-- Chat bubbles in the style of ai-chat.blade.php / chat-history.blade.php -->
                <div class="space-y-4">
                    <!-- User Message -->
                    <div class="flex flex-col items-end max-w-[85%] ml-auto">
                        <div class="bg-[#1B287D] text-white px-4 py-3 rounded-2xl rounded-tr-sm text-sm shadow-sm leading-relaxed">
                            {{ $msg->question }}
                        </div>
                    </div>

                    <!-- AI Answer -->
                    <div class="flex flex-col items-start max-w-[85%]">
                        <div class="bg-[#F5F5F5] dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 px-4 py-3 rounded-2xl rounded-tl-sm text-sm border border-zinc-200 dark:border-zinc-700 shadow-sm leading-relaxed">
                            {{ $msg->answer }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-zinc-500 py-8">
                {{ __('Belum ada riwayat chat.') }}
            </div>
        @endforelse

        @if($chatMessages->hasPages())
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 overflow-x-auto w-full">
                <flux:pagination :paginator="$chatMessages" />
            </div>
        @endif
    </div>

    <!-- Danger Confirmation Modal for Clear History -->
    @if(auth()->user()->isAdmin())
        <x-modal-danger
            name="confirm-clear-history"
            title="Kosongkan Riwayat Percakapan?"
            description="Tindakan ini akan menghapus seluruh data riwayat percakapan chatbot secara permanen. Apakah Anda yakin ingin melanjutkan?"
            confirmText="Ya, Hapus Semua"
            confirmAction="clearAllHistory"
        />
    @endif
</div>
