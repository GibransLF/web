<?php

use Livewire\Component;
use App\Models\ChatbotSetting;

new class extends Component {
    public int $max_input_character = 500;
    public int $max_chat_memory = 1;
    public int $top_k = 5;
    public int $fetch_k = 15;
    public float $temperature = 0.3;
    public string $system_prompt = '';

    protected array $rules = [
        'max_input_character' => 'required|integer|min:50|max:2000',
        'max_chat_memory' => 'required|integer|min:1|max:10',
        'top_k' => 'required|integer|min:1|max:20',
        'fetch_k' => 'required|integer|min:1|max:50',
        'temperature' => 'required|numeric|min:0|max:1',
        'system_prompt' => 'required|string|min:10',
    ];

    public function mount(): void
    {
        $setting = ChatbotSetting::current();
        $this->max_input_character = $setting->max_input_character;
        $this->max_chat_memory = $setting->max_chat_memory;
        $this->top_k = $setting->top_k;
        $this->fetch_k = $setting->fetch_k;
        $this->temperature = $setting->temperature;
        $this->system_prompt = $setting->system_prompt ?? '';
    }

    public function saveSettings(): void
    {
        $this->validate();

        $setting = ChatbotSetting::current();
        $setting->update([
            'max_input_character' => $this->max_input_character,
            'max_chat_memory' => $this->max_chat_memory,
            'top_k' => $this->top_k,
            'fetch_k' => $this->fetch_k,
            'temperature' => $this->temperature,
            'system_prompt' => $this->system_prompt,
        ]);

        session()->flash('success', 'Konfigurasi Chatbot AI berhasil diperbarui di database PostgreSQL!');
    }
};
?>

<x-layouts::app :title="__('Chatbot Settings')">
    <div class="max-w-4xl space-y-6">
        <!-- Flash Alert -->
        @if (session()->has('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Chatbot Settings</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Atur parameter RAG, batas memori, dan instruksi System Prompt tanpa perlu mengubah kode program.</p>
        </div>

        <form wire:submit.prevent="saveSettings" class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs p-6 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Max Input Character -->
                <flux:field>
                    <flux:label>Maksimal Karakter Input Pengguna</flux:label>
                    <flux:description>Batas panjang teks pertanyaan calon mahasiswa.</flux:description>
                    <flux:input type="number" wire:model="max_input_character" />
                    <flux:error name="max_input_character" />
                </flux:field>

                <!-- Max Chat Memory -->
                <flux:field>
                    <flux:label>Maksimal Memory Chat (Percakapan)</flux:label>
                    <flux:description>Jumlah riwayat percakapan sebelumnya yang dikirim ke LLM.</flux:description>
                    <flux:input type="number" wire:model="max_chat_memory" />
                    <flux:error name="max_chat_memory" />
                </flux:field>

                <!-- Top-K -->
                <flux:field>
                    <flux:label>Nilai Top-K (Retrieval)</flux:label>
                    <flux:description>Jumlah chunk dokumen paling relevan yang dijadikan konteks.</flux:description>
                    <flux:input type="number" wire:model="top_k" />
                    <flux:error name="top_k" />
                </flux:field>

                <!-- Fetch-K -->
                <flux:field>
                    <flux:label>Nilai Fetch-K (MMR Search)</flux:label>
                    <flux:description>Jumlah kandidat chunk awal sebelum pengurutan keberagaman MMR.</flux:description>
                    <flux:input type="number" wire:model="fetch_k" />
                    <flux:error name="fetch_k" />
                </flux:field>

                <!-- Temperature -->
                <flux:field class="md:col-span-2">
                    <flux:label>Temperature LLM (0.0 - 1.0)</flux:label>
                    <flux:description>Nilai lebih rendah (0.1 - 0.3) membuat jawaban lebih faktual dan konsisten.</flux:description>
                    <div class="flex items-center gap-4">
                        <input type="range" min="0" max="1" step="0.05" wire:model.live="temperature" class="w-full accent-[#1B287D]" />
                        <span class="font-mono font-bold text-[#1B287D] bg-blue-50 px-3 py-1 rounded-md text-sm border border-blue-200">{{ number_format($temperature, 2) }}</span>
                    </div>
                    <flux:error name="temperature" />
                </flux:field>

                <!-- System Prompt -->
                <flux:field class="md:col-span-2">
                    <flux:label>System Prompt AI</flux:label>
                    <flux:description>Instruksi kepribadian dan aturan dasar bagi LLM dalam menjawab pertanyaan PMB.</flux:description>
                    <textarea wire:model="system_prompt" rows="5" class="w-full text-sm p-3 border border-zinc-300 rounded-lg bg-zinc-50 font-mono focus:ring-[#1B287D] focus:border-[#1B287D]"></textarea>
                    <flux:error name="system_prompt" />
                </flux:field>
            </div>

            <div class="flex justify-end border-t border-zinc-100 pt-4">
                <flux:button variant="primary" type="submit">
                    Simpan Konfigurasi Database
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
