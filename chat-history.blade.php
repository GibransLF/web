<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div>
        <flux:heading size="xl">{{ __('History Chatbot') }}</flux:heading>
        <flux:subheading>{{ __('Daftar riwayat chat asisten AI.') }}</flux:subheading>
    </div>

    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 space-y-8">
        @forelse($chatMessages as $msg)
            <div class="border-b border-zinc-200 dark:border-zinc-800 pb-6 last:border-b-0 last:pb-0">
                <!-- Metadata: User Name or "guest" and created_at -->
                <div class="flex items-center justify-between mb-4 text-xs text-zinc-500 dark:text-zinc-400">
                    <span class="inline-flex items-center gap-1.5 font-semibold text-zinc-700 dark:text-zinc-300">
                        <flux:icon name="user" class="w-3.5 h-3.5" />
                        @if($msg->user_id && $msg->user)
                            {{ $msg->user->first_name }} {{ $msg->user->last_name }}
                        @else
                            guest ({{ $msg->anonymous_id ?? 'unknown' }})
                        @endif
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <flux:icon name="clock" class="w-3.5 h-3.5" />
                        {{ $msg->created_at ? $msg->created_at->format('d M Y H:i:s') : '-' }}
                    </span>
                </div>

                <!-- Chat bubbles in the style of ai-chat.blade.php -->
                <div class="space-y-3">
                    <!-- User Message -->
                    <div class="flex flex-col items-end max-w-[85%] ml-auto">
                        <div class="bg-[#1B287D] text-white px-4 py-3 rounded-2xl rounded-tr-sm text-sm shadow-sm">
                            {{ $msg->message }}
                        </div>
                    </div>

                    <!-- AI Answer -->
                    <div class="flex flex-col items-start max-w-[85%]">
                        <div class="bg-[#F5F5F5] dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 px-4 py-3 rounded-2xl rounded-tl-sm text-sm border border-zinc-200 dark:border-zinc-700 shadow-sm">
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

        <!-- Pagination Links -->
        @if($chatMessages->hasPages())
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800">
                {{ $chatMessages->links() }}
            </div>
        @endif
    </div>
</div>
