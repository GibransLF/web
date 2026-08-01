<?php

use Livewire\Component;
use App\Models\ChatbotSetting;
use App\Models\ChatHistory;
use Illuminate\Support\Str;

new class extends Component {
    public string $guestId = '';
    public string $message = '';
    public bool $loading = false;
    public array $messages = [];
    public int $maxCharacter = 500;
    public int $guestMessageCount = 0;
    public int $guestMaxLimit = 4;

    public function mount(): void
    {
        if (!session()->has('pmb_guest_id')) {
            session()->put('pmb_guest_id', 'guest_' . Str::random(10));
        }
        $this->guestId = session('pmb_guest_id');

        $setting = ChatbotSetting::first();
        if ($setting) {
            $this->maxCharacter = $setting->max_input_character;
        }

        // Count messages sent by this guest session
        if (!auth()->check()) {
            $this->guestMessageCount = ChatHistory::where('guest_id', $this->guestId)->count();
        }

        // Initial welcome message
        $this->messages = [
            [
                'role' => 'ai',
                'content' => "Halo! 👋 Selamat datang di PMB STMIK Bandung.\nSaya AI Asisten Akademik PMB yang siap membantu Anda menjawab pertanyaan seputar pendaftaran, biaya kuliah, program studi, dan fasilitas kampus.\n\nAda yang bisa saya bantu?",
                'sources' => [],
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

        // Add User Message
        $this->messages[] = [
            'role' => 'user',
            'content' => $cleanInput,
            'sources' => [],
        ];

        $userQuestion = $cleanInput;
        $this->message = '';
        $this->loading = true;

        if (!auth()->check()) {
            $this->guestMessageCount++;
        }

        // Simulated AI Response for UI demonstration
        $aiAnswer = $this->generateMockResponse($userQuestion);

        $this->messages[] = [
            'role' => 'ai',
            'content' => $aiAnswer['text'],
            'sources' => $aiAnswer['sources'],
        ];

        // Save to PostgreSQL chat_histories table
        try {
            ChatHistory::create([
                'guest_id' => $this->guestId,
                'question' => $userQuestion,
                'answer' => $aiAnswer['text'],
                'source_documents' => $aiAnswer['sources'],
            ]);
        } catch (\Throwable $e) {
            // Ignore DB log error if unreachable
        }

        $this->loading = false;
        $this->dispatch('scroll-bottom');
    }

    private function generateMockResponse(string $question): array
    {
        $q = mb_strtolower($question);

        if (Str::contains($q, ['biaya', 'harga', 'spp', 'bayar', 'cicil'])) {
            return [
                'text' => "Biaya pendidikan di STMIK Bandung bervariasi sesuai dengan Program Studi:\n\n"
                    . "• Teknik Informatika (S1): SPP terjangkau dengan skema pembayaran dicicil per bulan.\n"
                    . "• Sistem Informasi (S1): Biaya kuliah fleksibel + beasiswa prestasi.\n\n"
                    . "Tersedia juga potongan biaya hingga 50% untuk pendaftar Gelombang 1.",
                'sources' => ['Panduan_Biaya_PMB_2026.docx']
            ];
        }

        if (Str::contains($q, ['syarat', 'persyaratan', 'berkas', 'dokumen'])) {
            return [
                'text' => "Persyaratan umum pendaftaran calon mahasiswa baru STMIK Bandung:\n\n"
                    . "1. Pasfoto terbaru ukuran 3x4 (Background Merah/Biru).\n"
                    . "2. Fotokopi Ijazah / SKL SMA/SMK/MA sederajat.\n"
                    . "3. Fotokopi Kartu Keluarga (KK) & KTP.\n"
                    . "4. Mengisi formulir pendaftaran online.",
                'sources' => ['Persyaratan_Umum_PMB.docx']
            ];
        }

        if (Str::contains($q, ['prodi', 'jurusan', 'program studi'])) {
            return [
                'text' => "STMIK Bandung menyelenggarakan program studi unggulan:\n\n"
                    . "• S1 Teknik Informatika: Mempelajari AI, Software Engineering, & Cybersecurity.\n"
                    . "• S1 Sistem Informasi: Mempelajari Business Intelligence, UI/UX Design, & IT Governance.",
                'sources' => ['Brosur_Prodi_STMIK_Bandung.docx']
            ];
        }

        return [
            'text' => "Terima kasih atas pertanyaan Anda mengenai \"" . e($question) . "\". Informasi lebih lanjut dapat Anda dapatkan langsung melalui Sekretariat PMB STMIK Bandung di Jl. Cikutra No. 113 Bandung.",
            'sources' => ['Informasi_Umum_PMB.docx']
        ];
    }
};
?>

<div class="flex flex-col h-[calc(100vh-4rem)] bg-white dark:bg-zinc-900 w-full border-l border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden"
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
                <div class="flex flex-col items-start max-w-[88%] space-y-1">
                    <div class="bg-[#F5F5F5] dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 px-4 py-3 rounded-2xl rounded-tl-sm text-sm border border-zinc-200 dark:border-zinc-700 shadow-xs leading-relaxed whitespace-pre-line">
                        {{ $msg['content'] }}
                    </div>
                    @if(!empty($msg['sources']))
                        <div class="flex flex-wrap gap-1 items-center px-1">
                            <span class="text-[10px] text-zinc-400">Sumber:</span>
                            @foreach($msg['sources'] as $src)
                                <span class="inline-flex items-center gap-1 bg-blue-50 dark:bg-zinc-700 text-[#1B287D] dark:text-[#F9CE04] text-[10px] px-2 py-0.5 rounded-md font-medium border border-blue-100 dark:border-zinc-600">
                                    <flux:icon name="document-text" class="w-3 h-3" />
                                    {{ $src }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="flex flex-col items-end max-w-[85%] ml-auto">
                    <div class="bg-[#1B287D] text-white px-4 py-3 rounded-2xl rounded-tr-sm text-sm shadow-xs leading-relaxed whitespace-pre-line">
                        {{ $msg['content'] }}
                    </div>
                </div>
            @endif
        @endforeach

        <!-- Typing Indicator -->
        <div x-show="loading" class="flex flex-col items-start max-w-[85%]">
            <div class="bg-[#F5F5F5] dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 px-4 py-3 rounded-2xl rounded-tl-sm text-sm border border-zinc-200 dark:border-zinc-700 shadow-xs flex items-center gap-1.5 min-h-[40px]">
                <span class="w-2 h-2 bg-[#1B287D] rounded-full animate-bounce"></span>
                <span class="w-2 h-2 bg-[#1B287D] rounded-full animate-bounce [animation-delay:0.2s]"></span>
                <span class="w-2 h-2 bg-[#1B287D] rounded-full animate-bounce [animation-delay:0.4s]"></span>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
        @if($this->isGuestLimitReached())
            <div class="mb-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700/50 text-xs text-amber-800 dark:text-amber-200 flex flex-col sm:flex-row items-center justify-between gap-2">
                <span>Batas {{ $guestMaxLimit }} kali pesan tamu telah tercapai. Silakan login untuk melanjutkan.</span>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('login') }}" wire:navigate class="font-bold text-[#1B287D] dark:text-[#F9CE04] hover:underline">Masuk</a>
                    <span>|</span>
                    <a href="{{ route('register') }}" wire:navigate class="font-bold text-[#1B287D] dark:text-[#F9CE04] hover:underline">Daftar</a>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="sendMessage" @submit="loading = true; $nextTick(() => scrollToBottom())" class="flex items-center gap-2">
            <div class="relative flex-1">
                <flux:input wire:model="message" maxlength="{{ $maxCharacter }}" wire:keydown.enter.prevent="sendMessage" wire:loading.attr="disabled" :disabled="$this->isGuestLimitReached()" placeholder="{{ $this->isGuestLimitReached() ? 'Batas pesan tamu tercapai. Silakan login.' : 'Tulis pesan Anda di sini...' }}"
                    class="pr-10 bg-[#F5F5F5] border-zinc-200 focus:border-[#1B287D] dark:bg-zinc-800 dark:border-zinc-700 w-full rounded-lg disabled:opacity-60 disabled:cursor-not-allowed" />
                <button type="submit" wire:loading.attr="disabled" :disabled="$this->isGuestLimitReached()"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-[#1B287D] dark:hover:text-[#F9CE04] transition-colors p-1 disabled:opacity-40 disabled:cursor-not-allowed"
                    aria-label="Kirim pesan">
                    <flux:icon name="paper-airplane" class="w-5 h-5" />
                </button>
            </div>
        </form>
        <div class="flex justify-between items-center text-[10px] text-zinc-400 mt-2 px-1">
            <span>Maksimal {{ $maxCharacter }} karakter per pesan</span>
            <span>AI dapat membuat kekeliruan</span>
        </div>

        <div class="mt-3">
            <flux:button href="https://wa.me/6281222242026" target="_blank" variant="primary"
                class="w-full !bg-[#25D366] !text-white hover:!bg-[#1EBE5A] !border-[#25D366] font-bold"
                icon="chat-bubble-left-right">
                WhatsApp Admin
            </flux:button>
        </div>
    </div>
</div>
