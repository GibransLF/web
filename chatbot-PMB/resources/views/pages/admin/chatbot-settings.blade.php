<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\ChatbotSetting;

new #[Title('Chatbot Settings')] class extends Component {
    public int $max_input_character = 50;
    public int $max_chat_memory = 0;
    public int $max_guest_chat = 4;
    public int $top_k = 5;
    public int $fetch_k = 15;
    public float $temperature = 0.2;
    public string $system_prompt = '';

    protected array $rules = [
        'max_input_character' => 'required|integer|min:50|max:2000',
        'max_chat_memory' => 'required|integer|min:0|max:10',
        'max_guest_chat' => 'required|integer|min:0|max:50',
        'top_k' => 'required|integer|min:1|max:20',
        'fetch_k' => 'required|integer|min:1|max:50',
        'temperature' => 'required|numeric|min:0|max:1',
        'system_prompt' => 'required|string|min:10',
    ];

    protected array $messages = [
        'max_input_character.required' => 'Batas karakter input wajib diisi.',
        'max_input_character.min' => 'Batas karakter input minimal 50 karakter.',
        'max_input_character.max' => 'Batas karakter input maksimal 2000 karakter.',
        'max_chat_memory.required' => 'Memori percakapan wajib diisi.',
        'max_chat_memory.min' => 'Memori percakapan minimal 0.',
        'max_chat_memory.max' => 'Memori percakapan maksimal 10.',
        'max_guest_chat.required' => 'Maksimal chat guest wajib diisi.',
        'max_guest_chat.min' => 'Maksimal chat guest minimal 0.',
        'max_guest_chat.max' => 'Maksimal chat guest maksimal 50.',
        'top_k.required' => 'Nilai Top-K wajib diisi.',
        'top_k.min' => 'Nilai Top-K minimal 1.',
        'top_k.max' => 'Nilai Top-K maksimal 20.',
        'fetch_k.required' => 'Nilai Fetch-K wajib diisi.',
        'fetch_k.min' => 'Nilai Fetch-K minimal 1.',
        'fetch_k.max' => 'Nilai Fetch-K maksimal 50.',
        'temperature.required' => 'Nilai temperature wajib diisi.',
        'temperature.min' => 'Temperature minimal 0.0.',
        'temperature.max' => 'Temperature maksimal 1.0.',
        'system_prompt.required' => 'System Prompt AI wajib diisi.',
        'system_prompt.min' => 'System Prompt minimal 10 karakter.',
    ];

    public function mount(): void
    {
        $setting = ChatbotSetting::current();
        $this->max_input_character = $setting->max_input_character;
        $this->max_chat_memory = $setting->max_chat_memory;
        $this->max_guest_chat = $setting->max_guest_chat ?? 4;
        $this->top_k = $setting->top_k;
        $this->fetch_k = $setting->fetch_k;
        $this->temperature = (float) $setting->temperature;
        $this->system_prompt = $setting->system_prompt ?? '';
    }

    public function saveSettings(): void
    {
        $this->validate();

        if ($this->fetch_k < $this->top_k) {
            $this->addError('fetch_k', 'Nilai Fetch-K sebaiknya lebih besar atau sama dengan Nilai Top-K.');
            return;
        }

        $setting = ChatbotSetting::current();
        $setting->update([
            'user_id' => auth()->id(),
            'max_input_character' => $this->max_input_character,
            'max_chat_memory' => $this->max_chat_memory,
            'max_guest_chat' => $this->max_guest_chat,
            'top_k' => $this->top_k,
            'fetch_k' => $this->fetch_k,
            'temperature' => $this->temperature,
            'system_prompt' => $this->system_prompt,
        ]);

        session()->flash('success', 'Konfigurasi Chatbot AI berhasil diperbarui!');
    }

    public function resetDefaults(): void
    {
        $defaults = ChatbotSetting::getDefaults();
        $this->max_input_character = $defaults['max_input_character'];
        $this->max_chat_memory = $defaults['max_chat_memory'];
        $this->max_guest_chat = $defaults['max_guest_chat'];
        $this->top_k = $defaults['top_k'];
        $this->fetch_k = $defaults['fetch_k'];
        $this->temperature = (float) $defaults['temperature'];
        $this->system_prompt = $defaults['system_prompt'];

        session()->flash('info', 'Form konfigurasi telah dikembalikan ke nilai default PMB. Klik "Simpan Konfigurasi" untuk menyimpan perubahan.');
    }

    public function loadDefaultPrompt(): void
    {
        $defaults = ChatbotSetting::getDefaults();
        $this->system_prompt = $defaults['system_prompt'];
    }
};
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Chatbot Settings') }}</flux:heading>
            <flux:subheading>{{ __('Atur parameter RAG, batas memori, dan instruksi System Prompt tanpa perlu mengubah kode program.') }}</flux:subheading>
            @php
                $currentSetting = \App\Models\ChatbotSetting::with('user')->first();
            @endphp
            @if($currentSetting && $currentSetting->updated_at)
                <div class="flex items-center gap-2 mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/60 text-[#1B287D] dark:text-blue-300 font-bold border border-blue-100 dark:border-blue-900/60">
                        <flux:icon icon="user" class="w-3.5 h-3.5 text-[#1B287D] dark:text-blue-400" />
                        {{ $currentSetting->user->name ?? 'Sistem' }}
                    </span>
                    <span>•</span>
                    <span class="font-medium text-zinc-600 dark:text-zinc-400 capitalize">
                        {{ $currentSetting->updated_at->isoFormat('dddd, DD MMMM YYYY') }}
                    </span>
                </div>
            @endif
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto shrink-0">
            <flux:modal.trigger name="confirm-reset-settings">
                <flux:button variant="ghost" icon="arrow-path" class="w-full sm:w-auto justify-center text-xs sm:text-sm">
                    Reset Default
                </flux:button>
            </flux:modal.trigger>
            <flux:modal.trigger name="confirm-save-settings">
                <flux:button variant="primary" icon="check" class="w-full sm:w-auto justify-center text-xs sm:text-sm whitespace-nowrap">
                    Simpan Konfigurasi
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <!-- Notification Alerts -->
    @if (session()->has('success'))
        <x-alert-banner type="success" :message="session('success')" />
    @endif

    @if (session()->has('info'))
        <x-alert-banner type="info" :message="session('info')" />
    @endif

    <form wire:submit.prevent="saveSettings" class="space-y-4 sm:space-y-6">
        <!-- Section 1: Batas Input & Memori -->
        <x-settings-section-card
            title="Batas Input & Memori Percakapan"
            description="Pengaturan batas panjang karakter pertanyaan calon mahasiswa, batas memori percakapan, dan kuota chat tamu."
            icon="chat-bubble-left-right"
            badge="General Limits"
        >
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 items-stretch">
                <x-settings-field
                    name="max_input_character"
                    label="Maksimal Karakter Input Pengguna"
                    description="Batas maksimal panjang teks pertanyaan per pesan."
                    badge="50 - 2000 Karakter"
                >
                    <flux:input type="number" min="50" max="2000" wire:model="max_input_character" icon="pencil-square" />
                </x-settings-field>

                <x-settings-field
                    name="max_chat_memory"
                    label="Maksimal Memory Chat"
                    description="Jumlah riwayat percakapan sebelumnya sebagai konteks LLM (0 = Tanpa Memori)."
                    badge="0 - 10 Turn"
                >
                    <flux:input type="number" min="0" max="10" wire:model="max_chat_memory" icon="clock" />
                </x-settings-field>

                <x-settings-field
                    name="max_guest_chat"
                    label="Maksimal Chat Tamu (Guest)"
                    description="Batas kuota pesan gratis untuk pengunjung tanpa login (isi 0 untuk menonaktifkan chat tamu / maintenance)."
                    badge="0 - 50 Pesan"
                >
                    <flux:input type="number" min="0" max="50" wire:model="max_guest_chat" icon="user" />
                </x-settings-field>
            </div>
        </x-settings-section-card>

        <!-- Section 2: Konfigurasi RAG & Vector Retrieval -->
        <x-settings-section-card
            title="Konfigurasi Retrieval Augmented Generation (RAG)"
            description="Parameter pencarian pencocokan similarity vector (pgvector HNSW index)."
            icon="cpu-chip"
            badge="Vector Database"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 items-stretch">
                <x-settings-field
                    name="top_k"
                    label="Nilai Top-K (Final Context Chunks)"
                    description="Jumlah potongan dokumen paling relevan yang dimasukkan ke konteks prompt LLM."
                    badge="Default: 5"
                >
                    <flux:input type="number" min="1" max="20" wire:model="top_k" icon="document-magnifying-glass" />
                </x-settings-field>

                <x-settings-field
                    name="fetch_k"
                    label="Nilai Fetch-K (MMR Candidate Search)"
                    description="Jumlah kandidat chunk awal yang diambil sebelum penyaringan keberagaman (Maximal Marginal Relevance)."
                    badge="Default: 15"
                >
                    <flux:input type="number" min="1" max="50" wire:model="fetch_k" icon="funnel" />
                </x-settings-field>
            </div>
        </x-settings-section-card>

        <!-- Section 3: Model LLM & System Prompt -->
        <x-settings-section-card
            title="Model LLM & System Prompt AI"
            description="Instruksi perilaku AI Assistant PMB STMIK Bandung dan parameter kreativitas respons."
            icon="sparkles"
        >
            <div class="space-y-4 sm:space-y-6">
                <!-- Temperature Range Slider with Alpine.js entangle for zero-lag drag -->
                <x-settings-field
                    name="temperature"
                    label="Temperature LLM (Creativity / Factuality)"
                    description="Nilai lebih rendah (0.1 - 0.3) membuat jawaban lebih faktual dan konsisten sesuai knowledge base."
                >
                    <div x-data="{ temp: @entangle('temperature') }" class="p-3.5 sm:p-4 bg-zinc-50 dark:bg-zinc-900/60 rounded-xl border border-zinc-200 dark:border-zinc-700/60 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 flex items-center gap-1.5">
                                <flux:icon icon="adjustments-vertical" class="w-4 h-4 text-[#1B287D] shrink-0" />
                                Level Temperature:
                            </span>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-md"
                                      :class="parseFloat(temp) <= 0.3 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300'"
                                      x-text="parseFloat(temp) <= 0.3 ? 'Faktual & Konsisten (Rekomendasi)' : 'Lebih Bervariasi / Kreatif'">
                                </span>
                                <span class="font-mono font-bold text-[#1B287D] bg-blue-50 dark:bg-blue-950 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-md text-xs sm:text-sm border border-blue-200 dark:border-blue-800 shrink-0"
                                      x-text="parseFloat(temp).toFixed(2)">
                                </span>
                            </div>
                        </div>
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.05"
                            x-model="temp"
                            class="w-full h-2 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-[#1B287D] my-1"
                        />
                        <div class="flex justify-between text-[10px] sm:text-[11px] text-zinc-400 font-mono">
                            <span>0.0 (Presisi)</span>
                            <span>0.2 (Standard)</span>
                            <span>1.0 (Kreatif)</span>
                        </div>
                    </div>
                </x-settings-field>

                <!-- System Prompt Textarea with Alpine.js -->
                <x-settings-field
                    name="system_prompt"
                    label="System Prompt AI Assistant"
                    description="Aturan dasar kepribadian, gaya bahasa, dan instruksi pembatasan jawaban chatbot."
                >
                    <div x-data="{ prompt: @entangle('system_prompt') }" class="space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <span class="text-xs text-zinc-500 font-mono">
                                Jumlah Karakter: <strong class="text-zinc-900 dark:text-white" x-text="prompt ? prompt.length : 0"></strong>
                            </span>
                            <flux:button variant="ghost" size="sm" icon="document-duplicate" class="self-start sm:self-auto" wire:click="loadDefaultPrompt">
                                Muat Template Standard PMB
                            </flux:button>
                        </div>
                        <textarea
                            x-model="prompt"
                            rows="6"
                            placeholder="Masukkan instruksi System Prompt AI..."
                            class="w-full text-xs sm:text-sm p-3 sm:p-3.5 border border-zinc-300 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-mono focus:ring-2 focus:ring-[#1B287D] focus:border-transparent transition-all"
                        ></textarea>
                    </div>
                </x-settings-field>
            </div>
        </x-settings-section-card>

        <!-- Bottom Action Footer -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-t border-zinc-200 dark:border-zinc-700 pt-5 sm:pt-6 gap-4">
            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                * Perubahan konfigurasi akan langsung berlaku untuk semua percakapan chatbot baru.
            </p>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full sm:w-auto shrink-0">
                <flux:modal.trigger name="confirm-save-settings">
                    <flux:button variant="primary" icon="check" class="w-full sm:w-auto justify-center">
                        Simpan Konfigurasi
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>
    </form>

    <!-- Flux UI Danger Confirmation Modal for Reset -->
    <x-modal-danger
        name="confirm-reset-settings"
        title="Reset Konfigurasi Chatbot AI?"
        description="Tindakan ini akan mengembalikan seluruh parameter RAG, batas memori, dan System Prompt ke nilai default PMB STMIK Bandung. Apakah Anda yakin ingin melanjutkan?"
        confirmText="Ya, Reset Default"
        confirmAction="resetDefaults"
    />

    <!-- Flux UI Danger Confirmation Modal for Save -->
    <x-modal-danger
        name="confirm-save-settings"
        title="Simpan Konfigurasi Chatbot AI?"
        description="Apakah Anda yakin ingin menyimpan seluruh perubahan parameter RAG, batas memori, dan System Prompt ini?"
        confirmText="Ya, Simpan Perubahan"
        confirmAction="saveSettings"
    />
</div>
