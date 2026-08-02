<?php

use Livewire\Component;
use App\Models\ChatbotSetting;
use App\Models\ChatHistory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

new class extends Component {
    public string $guestId = '';
    public string $message = '';
    public bool $loading = false;
    public array $messages = [];
    public int $maxCharacter = 500;
    public int $guestMessageCount = 0;
    public int $guestMaxLimit = 4;
    public string $adminWhatsappUrl = '';

    public function mount(): void
    {
        if (!session()->has('pmb_guest_id')) {
            session()->put('pmb_guest_id', 'guest_' . Str::random(10));
        }
        $this->guestId = session('pmb_guest_id');

        $setting = ChatbotSetting::current();
        $this->maxCharacter = $setting->max_input_character;
        $this->guestMaxLimit = $setting->max_guest_chat;
        $this->adminWhatsappUrl = config('services.whatsapp_admin', 'https://wa.me/628112342113');


        // Count messages sent by this guest session
        if (!auth()->check()) {
            $this->guestMessageCount = ChatHistory::where('guest_id', $this->guestId)->count();
        }

        // Initial welcome message
        $this->messages = [
            [
                'role' => 'ai',
                'content' => "Halo! 👋 Selamat datang di PMB STMIK Bandung.\nSaya AI Asisten Akademik PMB yang siap membantu Anda menjawab pertanyaan seputar PMB. \n\nAda yang bisa saya bantu?",
                'sources' => [],
                'time' => now()->format('H:i'),
            ]
        ];
    }

    public function isGuestLimitReached(): bool
    {
        return !auth()->check() && $this->guestMessageCount >= $this->guestMaxLimit;
    }

    public function sendMessage(): void
    {
        if ($this->isGuestLimitReached()) {
            return;
        }

        $cleanInput = trim($this->message);
        if (empty($cleanInput)) return;

        if (mb_strlen($cleanInput) > $this->maxCharacter) {
            $cleanInput = mb_substr($cleanInput, 0, $this->maxCharacter);
        }

        $currentTime = now()->format('H:i');

        // Step 1: Add User Message immediately to $this->messages so DOM renders it instantly
        $this->messages[] = [
            'role' => 'user',
            'content' => $cleanInput,
            'sources' => [],
            'time' => $currentTime,
        ];

        $this->message = '';

        if (!auth()->check()) {
            $this->guestMessageCount++;
        }

        $this->dispatch('scroll-bottom');

        // Step 2: Trigger async AI response fetch on client
        $this->js('$wire.fetchAiResponse()');
    }

    public function fetchAiResponse(): void
    {
        if (empty($this->messages)) {
            return;
        }

        $lastIndex = count($this->messages) - 1;
        if ($this->messages[$lastIndex]['role'] !== 'user') {
            return;
        }
        $userQuestion = $this->messages[$lastIndex]['content'];

        // Susun history percakapan dari $this->messages (sebelum pertanyaan terakhir)
        $historyPayload = [];
        $tempQuestion = null;
        for ($i = 0; $i < $lastIndex; $i++) {
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

        // Kirim HTTP request ke FastAPI AI Service (/service/chat)
        $aiServiceUrl = config('services.ai_service.url', 'http://127.0.0.1:8080');
        $timeout = config('services.ai_service.timeout', 180);
        $aiAnswerText = '';
        $sources = [];

        try {
            $response = Http::timeout($timeout)->post("{$aiServiceUrl}/service/chat", [
                'newMessage' => $userQuestion,
                'history' => $historyPayload,
            ]);

            if ($response->successful() && $response->json('success')) {
                $aiAnswerText = $response->json('response');
            } else {
                $errorDetail = $response->json('detail') ?? $response->json('message') ?? 'Terjadi kesalahan pada layanan AI.';
                $aiAnswerText = "Maaf, sistem AI sedang mengalami kendala ({$errorDetail}). Silakan coba beberapa saat lagi atau hubungi panitia PMB melalui tombol WhatsApp di bawah.";
            }
        } catch (\Throwable $e) {
            $aiAnswerText = "Maaf, server AI saat ini sedang tidak dapat dijangkau. Silakan pastikan layanan AI aktif atau hubungi panitia PMB melalui tombol WhatsApp di bawah.";
        }

        $this->messages[] = [
            'role' => 'ai',
            'content' => $aiAnswerText,
            'sources' => $sources,
            'time' => now()->format('H:i'),
        ];

        // Save to PostgreSQL chat_histories table
        try {
            ChatHistory::create([
                'user_id' => auth()->id(),
                'guest_id' => $this->guestId,
                'question' => $userQuestion,
                'answer' => $aiAnswerText,
                'source_documents' => $sources,
            ]);
        } catch (\Throwable $e) {
            // Ignore DB log error if unreachable
        }

        $this->dispatch('scroll-bottom');
    }
};
?>

<div class="flex flex-col h-[calc(100vh-4rem)] bg-white dark:bg-zinc-900 w-full border-l border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden"
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
    <div class="flex items-center gap-3 p-4 border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
        <div class="w-10 h-10 rounded-full bg-[#1B287D] flex items-center justify-center text-white shrink-0 shadow-xs">
            <flux:icon name="sparkles" class="w-5 h-5 text-[#F9CE04]" />
        </div>
        <div>
            <h3 class="font-bold text-[#1B287D] dark:text-[#F9CE04] text-sm leading-tight">AI Asisten Akademik PMB</h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Aktif sekarang</p>
        </div>
    </div>

    <!-- Chat Messages -->
    <div wire:navigate:scroll x-ref="chatContainer"
        @scroll-bottom.window="scrollToBottom()"
        class="flex-1 overflow-y-auto p-4 space-y-4 bg-zinc-50 dark:bg-zinc-800/50">
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

        <!-- Typing Indicator (Native Livewire Loading) -->
        <div wire:loading wire:target="fetchAiResponse" class="flex flex-col items-start max-w-[85%]">
            <div class="bg-[#F5F5F5] dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 px-3.5 py-2.5 rounded-2xl rounded-tl-sm text-sm border border-zinc-200 dark:border-zinc-700 shadow-sm flex items-center gap-1.5 min-h-[38px]">
                <span class="w-2 h-2 bg-[#1B287D] rounded-full animate-bounce"></span>
                <span class="w-2 h-2 bg-[#1B287D] rounded-full animate-bounce [animation-delay:0.2s]"></span>
                <span class="w-2 h-2 bg-[#1B287D] rounded-full animate-bounce [animation-delay:0.4s]"></span>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
        @if($this->isGuestLimitReached())
            <div class="mb-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700/50 text-xs text-amber-800 dark:text-amber-200 flex items-center gap-2">
                <flux:icon name="clock" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" />
                <span>Batas {{ $guestMaxLimit }} kali pesan tamu telah tercapai. Silakan tunggu beberapa saat (-/+ 2 jam).</span>
            </div>
        @endif

        <form wire:submit.prevent="sendMessage" @submit="$nextTick(() => scrollToBottom())" class="flex items-center gap-2">
            <div class="relative flex-1">
                <flux:input wire:model="message" maxlength="{{ $maxCharacter }}" wire:keydown.enter.prevent="sendMessage" wire:loading.attr="disabled" wire:target="sendMessage, fetchAiResponse" :disabled="$this->isGuestLimitReached()" placeholder="{{ $this->isGuestLimitReached() ? 'Batas pesan tamu tercapai. Silakan tunggu beberapa saat.' : 'Tulis pesan Anda di sini (maks ' . $maxCharacter . ' karakter)...' }}"
                    class="pr-10 bg-[#F5F5F5] border-zinc-200 focus:border-[#1B287D] dark:bg-zinc-800 dark:border-zinc-700 w-full rounded-lg disabled:opacity-60 disabled:cursor-not-allowed text-sm" />
                <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage, fetchAiResponse" @disabled($this->isGuestLimitReached())
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
