<div class="flex flex-col h-[calc(100vh-4rem)] bg-white dark:bg-zinc-900 w-full overflow-hidden"
    x-data="{ 
        loading: false, 
        scrollToBottom() { 
            const container = this.$refs.chatContainer;
            if (container) {
                container.scrollTop = container.scrollHeight; 
            }
        } 
    }"
    x-init="
        $watch('$wire.messages', () => { 
            $nextTick(() => {
                scrollToBottom();
            });
        });
        $nextTick(() => scrollToBottom());
        Livewire.hook('request', ({ respond, fail }) => {
            respond(() => {
                $nextTick(() => {
                    loading = false;
                    scrollToBottom();
                });
            });
            fail(() => {
                loading = false;
            });
        });
    "
    @get-ai-response.window="
        loading = true;
        $nextTick(() => scrollToBottom());
        $wire.getAiResponse($event.detail.newMessage, $event.detail.history);
    ">
    <!-- Header -->
    <div class="flex items-center gap-3 p-4 border-b border-zinc-200 dark:border-zinc-800">
        <div class="w-10 h-10 rounded-full bg-[#1B287D] flex items-center justify-center text-white">
            <flux:icon name="sparkles" class="w-5 h-5" />
        </div>
        <div>
            <h3 class="font-bold text-[#1B287D] dark:text-[#F9CE04] text-sm">AI Asisten Akademik PMB</h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Aktif sekarang</p>
        </div>
    </div>

    <!-- Chat Messages -->
    <div wire:navigate:scroll x-ref="chatContainer"
        @scroll-bottom.window="scrollToBottom()"
        class="flex-1 overflow-y-auto p-4 space-y-4 bg-zinc-50 dark:bg-zinc-800/50">
        @foreach($messages as $msg)
        @if($msg['role'] === 'ai')
        <div class="flex flex-col items-start max-w-[85%]">
            <div
                class="bg-[#F5F5F5] dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 px-4 py-3 rounded-2xl rounded-tl-sm text-sm border border-zinc-200 dark:border-zinc-700 shadow-sm">
                {{ $msg['content'] }}
            </div>
        </div>
        @else
        <div class="flex flex-col items-end max-w-[85%] ml-auto">
            <div class="bg-[#1B287D] text-white px-4 py-3 rounded-2xl rounded-tr-sm text-sm shadow-sm">
                {{ $msg['content'] }}
            </div>
        </div>
        @endif
        @endforeach

        <!-- Typing Indicator -->
        <div x-show="loading" class="flex flex-col items-start max-w-[85%]">
            <div class="bg-[#F5F5F5] dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 px-4 py-3 rounded-2xl rounded-tl-sm text-sm border border-zinc-200 dark:border-zinc-700 shadow-sm flex items-center min-h-[40px]">
                <span class="loading loading-dots loading-sm text-zinc-500 dark:text-zinc-400"></span>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
        @if($this->isGuestLimitReached())
            <div class="mb-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700/50 text-xs text-amber-800 dark:text-amber-200 flex flex-col sm:flex-row items-center justify-between gap-2">
                <span>Batas 4 kali pesan tamu telah tercapai. Silakan login untuk melanjutkan.</span>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('login') }}" wire:navigate class="font-bold text-[#1B287D] dark:text-[#F9CE04] hover:underline">Masuk</a>
                    <span>|</span>
                    <a href="{{ route('register') }}" wire:navigate class="font-bold text-[#1B287D] dark:text-[#F9CE04] hover:underline">Daftar</a>
                </div>
            </div>
        @endif

        <form wire:submit="sendMessage" @submit="loading = true; $nextTick(() => scrollToBottom())" class="flex items-center gap-2">
            <div class="relative flex-1">
                <flux:input wire:model="message" maxlength="50" wire:keydown.enter.prevent="sendMessage" wire:loading.attr="disabled" :disabled="$this->isGuestLimitReached()" placeholder="{{ $this->isGuestLimitReached() ? 'Batas pesan tamu tercapai. Silakan login.' : 'Tulis pesan Anda di sini (maks 50 karakter)...' }}"
                    class="pr-10 bg-[#F5F5F5] border-zinc-200 focus:border-[#1B287D] dark:bg-zinc-800 dark:border-zinc-700 w-full rounded-lg disabled:opacity-60 disabled:cursor-not-allowed" />
                <button type="submit" wire:loading.attr="disabled" :disabled="$this->isGuestLimitReached()"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-[#1B287D] dark:hover:text-[#F9CE04] transition-colors p-1 disabled:opacity-40 disabled:cursor-not-allowed"
                    aria-label="Kirim pesan">
                    <flux:icon name="paper-airplane" class="w-5 h-5" />
                </button>
            </div>
        </form>
        <div class="flex justify-between items-center text-[10px] text-zinc-400 mt-2 px-1">
            <span>Maksimal 50 karakter per pesan</span>
            <span>AI dapat membuat kekeliruan</span>
        </div>

        <div class="mt-3">
            <flux:button href="https://wa.me/{{ env('WHATSAPP_NUMBER', '') }}" target="_blank" variant="primary"
                class="w-full !bg-[#25D366] !text-white hover:!bg-[#1EBE5A] !border-[#25D366]"
                icon="chat-bubble-left-right">
                WhatsApp Admin
            </flux:button>
        </div>
    </div>
</div>