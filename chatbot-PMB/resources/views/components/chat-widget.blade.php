<?php

use Livewire\Component;
use App\Models\ChatbotSetting;
use App\Models\ChatHistory;
use App\Jobs\ProcessAiChatResponse;
use Illuminate\Support\Str;

new class extends Component {
    public string $guestId = '';
    public string $message = '';
    public bool $loading = false;
    public array $messages = [];
    public int $maxCharacter = 500;
    public int $guestMessageCount = 0;
    public int $guestMaxLimit = 4;
    public int $maxChatMemory = 1;
    public string $adminWhatsappUrl = '';

    public bool $isProcessing = false;
    public ?int $pendingHistoryId = null;
    public int $pollAttempts = 0;

    public function mount(): void
    {
        if (!session()->has('pmb_guest_id')) {
            session()->put('pmb_guest_id', 'guest_' . Str::random(10));
        }
        $this->guestId = session('pmb_guest_id');

        $setting = ChatbotSetting::current();
        $this->maxCharacter = $setting->max_input_character;
        $this->guestMaxLimit = $setting->max_guest_chat;
        $this->maxChatMemory = $setting->max_chat_memory;
        $this->adminWhatsappUrl = config('services.whatsapp_admin', 'https://wa.me/628112342113');

        // Count messages sent by this guest session
        if (!auth()->check()) {
            $this->guestMessageCount = ChatHistory::where('guest_id', $this->guestId)->count();
        }

        $this->messages = [];

        // Load past chat history from database if available
        $historyQuery = auth()->check()
            ? ChatHistory::where('user_id', auth()->id())->whereDate('created_at', today())
            : ChatHistory::where('guest_id', $this->guestId);

        $pastChats = $historyQuery->orderBy('created_at', 'asc')->get();

        foreach ($pastChats as $chat) {
            $this->messages[] = [
                'role' => 'user',
                'content' => $chat->question,
                'time' => $chat->created_at ? $chat->created_at->format('H:i') : now()->format('H:i'),
            ];

            if ($chat->status === 'pending') {
                $this->isProcessing = true;
                $this->pendingHistoryId = $chat->id;
                $this->pollAttempts = 0;
            } elseif ($chat->answer !== null) {
                $this->messages[] = [
                    'role' => 'ai',
                    'content' => $chat->answer,
                    'time' => $chat->created_at ? $chat->created_at->format('H:i') : now()->format('H:i'),
                ];
            }
        }
    }

    public function isGuestLimitReached(): bool
    {
        return !auth()->check() && ($this->guestMaxLimit === 0 || $this->guestMessageCount >= $this->guestMaxLimit);
    }

    public function sendMessage(): void
    {
        if ($this->isGuestLimitReached() || $this->isProcessing) {
            return;
        }

        $cleanInput = preg_replace('/\s+/', ' ', trim($this->message));
        if (empty($cleanInput)) return;

        if (mb_strlen($cleanInput) > $this->maxCharacter) {
            $cleanInput = mb_substr($cleanInput, 0, $this->maxCharacter);
        }

        $currentTime = now()->format('H:i');

        // Step 1: Add User Message immediately to $this->messages so DOM renders it instantly
        $this->messages[] = [
            'role' => 'user',
            'content' => $cleanInput,
            'time' => $currentTime,
        ];

        // Step 2: Prepare history payload according to maxChatMemory
        $historyPayload = [];
        $tempQuestion = null;
        $msgCount = count($this->messages) - 1; // Exclude the user question just added
        for ($i = 0; $i < $msgCount; $i++) {
            $msg = $this->messages[$i];
            if ($msg['role'] === 'user') {
                $tempQuestion = $msg['content'];
            } elseif ($msg['role'] === 'ai' && $tempQuestion !== null) {
                $historyPayload[] = [
                    'question' => $tempQuestion,
                    'answer' => $msg['content'],
                ];
                $tempQuestion = null;
            }
        }

        if ($this->maxChatMemory <= 0) {
            $historyPayload = [];
        } elseif (count($historyPayload) > $this->maxChatMemory) {
            $historyPayload = array_slice($historyPayload, -$this->maxChatMemory);
        }

        // Step 3: Create pending ChatHistory record in database
        $pendingId = (string) Str::uuid();
        $chatHistory = ChatHistory::create([
            'user_id' => auth()->id(),
            'guest_id' => $this->guestId,
            'question' => $cleanInput,
            'answer' => null,
            'status' => 'pending',
            'pending_id' => $pendingId,
        ]);

        // Step 4: Dispatch queue job
        ProcessAiChatResponse::dispatch($chatHistory->id, $cleanInput, $historyPayload);

        $this->message = '';
        $this->isProcessing = true;
        $this->pendingHistoryId = $chatHistory->id;
        $this->pollAttempts = 0;

        if (!auth()->check()) {
            $this->guestMessageCount++;
        }

        $this->dispatch('scroll-bottom');
    }

    public function checkPendingResponse(): void
    {
        if (! $this->isProcessing || ! $this->pendingHistoryId) {
            $this->isProcessing = false;
            return;
        }

        $this->pollAttempts++;
        $timeout = (int) config('services.ai_service.timeout', 180);
        $maxPollAttempts = (int) ceil($timeout / 2) + 5;

        $chatHistory = ChatHistory::find($this->pendingHistoryId);

        if (! $chatHistory) {
            $this->isProcessing = false;
            $this->pendingHistoryId = null;
            return;
        }

        if ($chatHistory->status === 'completed' || $chatHistory->status === 'failed') {
            $this->messages[] = [
                'role' => 'ai',
                'content' => $chatHistory->answer ?? 'Maaf, terjadi kesalahan.',
                'time' => $chatHistory->created_at ? $chatHistory->created_at->format('H:i') : now()->format('H:i'),
            ];
            $this->isProcessing = false;
            $this->pendingHistoryId = null;
            $this->dispatch('scroll-bottom');
            return;
        }

        if ($this->pollAttempts >= $maxPollAttempts) {
            $fallbackMessage = 'Maaf, pemrosesan pesan mengalami waktu tunggu habis (timeout). Silakan coba beberapa saat lagi atau hubungi panitia PMB melalui tombol WhatsApp di bawah.';
            $chatHistory->update([
                'status' => 'failed',
                'answer' => $fallbackMessage,
            ]);
            $this->messages[] = [
                'role' => 'ai',
                'content' => $fallbackMessage,
                'time' => now()->format('H:i'),
            ];
            $this->isProcessing = false;
            $this->pendingHistoryId = null;
            $this->dispatch('scroll-bottom');
        }
    }
};
?>

<div @if($isProcessing) wire:poll.2s="checkPendingResponse" @endif
    class="flex flex-col h-[calc(100vh-4rem)] bg-white dark:bg-zinc-900 w-full border-l border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden"
    x-data="{ 
        scrollToBottom() { 
            const container = this.$refs.chatContainer;
            if (container) {
                container.scrollTop = container.scrollHeight; 
            }
        } 
    }"
    x-init="
        const container = $refs.chatContainer;
        if (container) {
            const observer = new MutationObserver(() => {
                $nextTick(() => scrollToBottom());
            });
            observer.observe(container, { childList: true, subtree: true, attributes: true });
        }
        $nextTick(() => scrollToBottom());
    "
    @scroll-bottom.window="scrollToBottom()">
    
    <!-- Header -->
    <div class="flex items-center justify-between gap-3 p-4 border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-full bg-[#1B287D] flex items-center justify-center text-white shrink-0 shadow-xs">
                <flux:icon name="sparkles" class="w-5 h-5 text-[#F9CE04]" />
            </div>
            <div class="min-w-0">
                <h3 class="font-bold text-[#1B287D] dark:text-[#F9CE04] text-sm leading-tight truncate">AI Asisten Akademik PMB</h3>
                <div class="flex items-center gap-1.5 mt-0.5">
                    @if(!auth()->check() && $guestMaxLimit === 0)
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <p class="text-xs font-medium text-amber-600 dark:text-amber-400 truncate">Sedang Perbaikan</p>
                    @else
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">Aktif sekarang</p>
                    @endif
                </div>
            </div>
        </div>

        @if(request()->is('chat'))
            <a href="/" wire:navigate
                class="inline-flex items-center justify-center gap-1.5 shrink-0 w-9 h-9 sm:w-auto sm:h-auto sm:px-3.5 sm:py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-[#1B287D] hover:text-white dark:hover:bg-[#F9CE04] dark:hover:text-[#1B287D] text-xs font-semibold text-zinc-700 dark:text-zinc-300 transition-all border border-zinc-200 dark:border-zinc-700 shadow-xs"
                title="Kembali ke Home" aria-label="Kembali ke Home">
                <flux:icon name="home" class="w-4 h-4 shrink-0" />
                <span class="hidden sm:inline">Kembali ke Home</span>
            </a>
        @endif
    </div>

    <!-- Chat Messages -->
    <div wire:navigate:scroll x-ref="chatContainer"
        @scroll-bottom.window="scrollToBottom()"
        class="flex-1 overflow-y-auto p-4 space-y-4 bg-zinc-50 dark:bg-zinc-800/50">
        <!-- Initial Welcome Message (Always rendered at the top of UI) -->
        <div class="flex flex-col items-start max-w-[85%] space-y-1">
            <div class="bg-[#F5F5F5] dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 px-3.5 py-2.5 rounded-2xl rounded-tl-sm text-sm border border-zinc-200 dark:border-zinc-700 shadow-sm leading-relaxed whitespace-pre-line">Halo! 👋 Selamat datang di PMB STMIK Bandung.
Saya AI Asisten Akademik PMB yang siap membantu Anda menjawab pertanyaan seputar PMB. 

Ada yang bisa saya bantu?</div>
            <div class="px-1">
                <span class="text-[10px] text-zinc-400 font-medium">{{ now()->format('H:i') }}</span>
            </div>
        </div>

        @foreach($messages as $msg)
            @if($msg['role'] === 'ai')
                <div class="flex flex-col items-start max-w-[85%] space-y-1">
                    <div class="bg-[#F5F5F5] dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 px-3.5 py-2.5 rounded-2xl rounded-tl-sm text-sm border border-zinc-200 dark:border-zinc-700 shadow-sm leading-relaxed whitespace-pre-line">{{ trim($msg['content']) }}</div>
                    <div class="px-1">
                        <span class="text-[10px] text-zinc-400 font-medium">{{ $msg['time'] ?? now()->format('H:i') }}</span>
                    </div>
                </div>
            @else
                <div class="flex flex-col items-end max-w-[85%] ml-auto space-y-1">
                    <div class="bg-[#1B287D] text-white px-3.5 py-2.5 rounded-2xl rounded-tr-sm text-sm shadow-sm leading-relaxed whitespace-pre-line">{{ trim($msg['content']) }}</div>
                    <div class="px-1 text-right">
                        <span class="text-[10px] text-zinc-400 font-medium">{{ $msg['time'] ?? now()->format('H:i') }}</span>
                    </div>
                </div>
            @endif
        @endforeach

        <!-- Typing Indicator (Active while processing queue response) -->
        @if($isProcessing)
            <div class="flex flex-col items-start max-w-[85%]">
                <div class="bg-[#F5F5F5] dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 px-3.5 py-2.5 rounded-2xl rounded-tl-sm text-sm border border-zinc-200 dark:border-zinc-700 shadow-sm flex items-center gap-1.5 min-h-[38px]">
                    <span class="w-2 h-2 bg-[#1B287D] rounded-full animate-bounce"></span>
                    <span class="w-2 h-2 bg-[#1B287D] rounded-full animate-bounce [animation-delay:0.2s]"></span>
                    <span class="w-2 h-2 bg-[#1B287D] rounded-full animate-bounce [animation-delay:0.4s]"></span>
                </div>
            </div>
        @endif
    </div>

    <!-- Input Area -->
    <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
        @if($this->isGuestLimitReached())
            <div class="mb-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700/50 text-xs text-amber-800 dark:text-amber-200 flex items-center gap-2">
                <flux:icon name="clock" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" />
                @if(!auth()->check() && $guestMaxLimit === 0)
                    <span>Layanan chat tamu saat ini sedang dalam perbaikan (maintenance). Silakan hubungi admin via WhatsApp.</span>
                @else
                    <span>Batas {{ $guestMaxLimit }} kali pesan tamu telah tercapai. Silakan tunggu beberapa saat (-/+ 2 jam).</span>
                @endif
            </div>
        @endif

        <form wire:submit.prevent="sendMessage" @submit="$nextTick(() => scrollToBottom())" class="flex items-center gap-2">
            <div class="relative flex-1">
                <flux:input wire:model="message" autocomplete="off" maxlength="{{ $maxCharacter }}" wire:keydown.enter.prevent="sendMessage" wire:loading.attr="disabled" wire:target="sendMessage" :disabled="$this->isGuestLimitReached() || $isProcessing" placeholder="{{ $this->isGuestLimitReached() ? (!auth()->check() && $guestMaxLimit === 0 ? 'Fitur chat sedang dalam perbaikan...' : 'Batas pesan tamu tercapai.') : ($isProcessing ? 'AI sedang memproses pesan...' : 'Tulis pesan... (maks ' . $maxCharacter . ' karakter)') }}"
                    class="pr-10 bg-[#F5F5F5] border-zinc-200 focus:border-[#1B287D] dark:bg-zinc-800 dark:border-zinc-700 w-full rounded-lg disabled:opacity-60 disabled:cursor-not-allowed text-sm" />
                <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage" @disabled($this->isGuestLimitReached() || $isProcessing)
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-[#1B287D] dark:hover:text-[#F9CE04] transition-colors p-1 disabled:opacity-40 disabled:cursor-not-allowed"
                    aria-label="Kirim pesan">
                    <flux:icon name="paper-airplane" class="w-5 h-5" />
                </button>
            </div>
        </form>
        <div class="flex justify-between text-center text-[10px] text-zinc-400 mt-2 px-1">
            <span>Maksimal {{ $maxCharacter }} karakter per pesan</span>
            <span>AI dapat membuat kekeliruan</span>
        </div>

        <div class="mt-3">
            <flux:button href="{{ $adminWhatsappUrl }}" target="_blank" variant="primary"
                class="w-full !bg-[#25D366] !text-white hover:!bg-[#1EBE5A] !border-[#25D366] font-bold"
                icon="chat-bubble-left-right">
                WhatsApp Admin
            </flux:button>
        </div>
    </div>
</div>
