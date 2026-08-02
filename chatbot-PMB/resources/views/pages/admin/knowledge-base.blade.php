<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use App\Models\KnowledgeBase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

new #[Title('Kelola Knowledge Base')] class extends Component {
    use WithFileUploads;

    public $file;
    public string $file_name = '';
    public string $deskripsi = '';
    public string $search = '';

    protected array $rules = [
        'file_name' => 'required|string|max:255',
        'file' => 'required|file|mimes:docx|max:6144', // Max 6MB docx
        'deskripsi' => 'nullable|string|max:255',
    ];

    public function uploadDocument(): void
    {
        $this->validate();

        $metadataName = trim($this->file_name);
        if (! str_ends_with(strtolower($metadataName), '.docx')) {
            $metadataName .= '.docx';
        }

        // Pengecekan nama metadata duplikat
        if (KnowledgeBase::where('metadata_name', $metadataName)->exists()) {
            $this->addError('file_name', 'Dokumen dengan nama metadata tersebut sudah ada di Knowledge Base.');
            return;
        }

        // Simpan file ke private storage dengan nama berkas sesuai metadata (storeAs)
        $storedPath = $this->file->storeAs('knowledge_bases', $metadataName, 'local');

        $doc = KnowledgeBase::create([
            'user_id' => auth()->id(),
            'filename' => $storedPath,
            'metadata_name' => $metadataName,
            'status' => 'processing',
            'deskripsi' => $this->deskripsi,
        ]);

        // Kirim request ke ai-service untuk ekstraksi & vektorisasi di PostgreSQL
        $aiServiceUrl = config('services.ai_service.url', 'http://127.0.0.1:8080');
        $fileContents = Storage::disk('local')->get($storedPath);

        try {
            $response = Http::timeout(120)
                ->attach('file', $fileContents, $metadataName)
                ->post("{$aiServiceUrl}/service/createnewknowledge", [
                    'filename' => $metadataName,
                ]);

            if ($response->successful() && $response->json('success')) {
                $chunksCount = $response->json('chunks', 0);
                $doc->update(['status' => 'success']);
                session()->flash('success', "Dokumen DOCX berhasil diunggah dan divektorisasi ({$chunksCount} chunk disimpan ke PostgreSQL)!");
            } else {
                $doc->update(['status' => 'failed']);
                $errorMsg = $response->json('detail') ?? $response->json('message') ?? 'Gagal membuat vektor di AI service.';
                session()->flash('error', "Dokumen berhasil disimpan secara lokal, namun vektorisasi AI gagal: {$errorMsg}");
            }
        } catch (\Exception $e) {
            $doc->update(['status' => 'failed']);
            session()->flash('error', 'Dokumen berhasil disimpan secara lokal, namun gagal menghubungi AI service: ' . $e->getMessage());
        }

        $this->reset(['file', 'file_name', 'deskripsi']);
        Flux::modal('create-kb')->close();
    }

    public function reindexDocument(int $id): void
    {
        $doc = KnowledgeBase::find($id);
        if (! $doc || ! Storage::disk('local')->exists($doc->filename)) {
            session()->flash('error', 'File dokumen tidak ditemukan di penyimpanan lokal.');
            return;
        }

        $aiServiceUrl = config('services.ai_service.url', 'http://127.0.0.1:8080');
        $fileContents = Storage::disk('local')->get($doc->filename);

        $doc->update(['status' => 'processing']);

        try {
            $response = Http::timeout(120)
                ->attach('file', $fileContents, $doc->metadata_name)
                ->post("{$aiServiceUrl}/service/createnewknowledge", [
                    'filename' => $doc->metadata_name,
                ]);

            if ($response->successful() && $response->json('success')) {
                $chunksCount = $response->json('chunks', 0);
                $doc->update(['status' => 'success']);
                session()->flash('success', "Proses re-index vektorisasi berhasil ({$chunksCount} chunk disimpan ke PostgreSQL)!");
            } else {
                $doc->update(['status' => 'failed']);
                $errorMsg = $response->json('detail') ?? $response->json('message') ?? 'Gagal memproses vektor AI.';
                session()->flash('error', "Proses re-index gagal: {$errorMsg}");
            }
        } catch (\Exception $e) {
            $doc->update(['status' => 'failed']);
            session()->flash('error', 'Gagal menghubungi AI service: ' . $e->getMessage());
        }
    }

    public function downloadDocument(int $id)
    {
        $doc = KnowledgeBase::find($id);
        if ($doc && Storage::disk('local')->exists($doc->filename)) {
            return Storage::disk('local')->download($doc->filename, $doc->metadata_name);
        }

        session()->flash('error', 'Dokumen tidak ditemukan di penyimpanan private.');
    }

    public function deleteDocument(int $id): void
    {
        $doc = KnowledgeBase::find($id);
        if ($doc) {
            if (Storage::disk('local')->exists($doc->filename)) {
                Storage::disk('local')->delete($doc->filename);
            }

            $doc->delete(); // Chunks di PostgreSQL otomatis terhapus via Foreign Key ON DELETE CASCADE
            session()->flash('success', 'Dokumen beserta seluruh chunk berhasil dihapus!');
        }
    }
};
?>

<div class="space-y-6">
    <!-- Re-indexing Active Loading Banner -->
    <div wire:loading wire:target="reindexDocument" class="p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg text-sm flex items-center gap-2 dark:bg-blue-900/30 dark:border-blue-800 dark:text-blue-400">
        <flux:icon icon="arrow-path" class="w-5 h-5 text-blue-600 dark:text-blue-400 animate-spin shrink-0" />
        <span>Sedang memproses ulang vektorisasi AI ke PostgreSQL. Mohon tunggu beberapa saat...</span>
    </div>

    <!-- Flash Alerts -->
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center justify-between dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400">
            <div class="flex items-center gap-2">
                <flux:icon icon="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm flex items-center justify-between dark:bg-red-900/30 dark:border-red-800 dark:text-red-400">
            <div class="flex items-center gap-2">
                <flux:icon icon="exclamation-triangle" class="w-5 h-5 text-red-600 dark:text-red-400" />
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Knowledge Base') }}</flux:heading>
            <flux:subheading>{{ __('Kelola dokumen referensi Word (DOCX) untuk AI Chatbot PMB.') }}</flux:subheading>
        </div>
        <flux:modal.trigger name="create-kb">
            <flux:button variant="primary" icon="plus">
                {{ __('Dokumen Baru') }}
            </flux:button>
        </flux:modal.trigger>
    </div>

    <!-- Filter & Search -->
    <div class="flex items-center justify-between gap-4 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs">
        <div class="relative w-full max-w-sm">
            <flux:input icon="magnifying-glass" wire:model.live="search" placeholder="Cari nama dokumen / deskripsi..." />
        </div>
        <div class="text-xs text-zinc-500">
            Total Dokumen: <span class="font-bold text-zinc-900 dark:text-white">{{ KnowledgeBase::count() }}</span>
        </div>
    </div>

    <!-- Document Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs overflow-hidden">
        <flux:table>
            <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                    <th class="p-3">Nama Dokumen</th>
                    <th class="p-3">Status Vektor</th>
                    <th class="p-3">Jumlah Chunk</th>
                    <th class="p-3">Deskripsi</th>
                    <th class="p-3">Tanggal Upload</th>
                    <th class="p-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-sm">
                @forelse(KnowledgeBase::with('user')->withCount('chunks')->where('metadata_name', 'like', '%'.$search.'%')->orWhere('deskripsi', 'like', '%'.$search.'%')->latest()->get() as $doc)
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="p-3 font-medium">
                            <div class="flex items-start gap-2">
                                <flux:icon icon="document-text" class="w-5 h-5 text-[#1B287D] dark:text-blue-400 shrink-0 mt-0.5" />
                                <div>
                                    <span class="font-semibold text-zinc-900 dark:text-white block">{{ $doc->metadata_name }}</span>
                                    <div class="flex items-center gap-1 text-[11px] text-zinc-500 font-normal mt-0.5">
                                        <flux:icon icon="user-circle" class="w-3.5 h-3.5 text-zinc-400 shrink-0" />
                                        <span>Diunggah oleh: {{ $doc->user->name ?? 'Sistem' }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3">
                            <div wire:loading wire:target="reindexDocument({{ $doc->id }})">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800">
                                    <flux:icon icon="arrow-path" class="w-3.5 h-3.5 animate-spin" />
                                    Memproses Vektorisasi...
                                </span>
                            </div>
                            <div wire:loading.remove wire:target="reindexDocument({{ $doc->id }})">
                                @if($doc->status === 'success')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800">
                                        <flux:icon icon="check-circle" class="w-3.5 h-3.5" />
                                        Vektor Siap
                                    </span>
                                @elseif($doc->status === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800">
                                        <flux:icon icon="exclamation-circle" class="w-3.5 h-3.5" />
                                        Gagal Vektor
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800">
                                        <flux:icon icon="arrow-path" class="w-3.5 h-3.5 animate-spin" />
                                        Memproses
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="p-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-xs font-mono font-semibold text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                {{ $doc->chunks_count }} Chunk
                            </span>
                        </td>
                        <td class="p-3 text-xs text-zinc-600 dark:text-zinc-300">
                            {{ $doc->deskripsi ?: '-' }}
                        </td>
                        <td class="p-3 text-xs text-zinc-500">
                            {{ $doc->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="p-3 text-right">
                            <div class="flex items-center justify-end">
                                <flux:dropdown align="end">
                                    <flux:button variant="ghost" icon="ellipsis-horizontal" size="sm" title="Aksi" />
                                    <flux:menu>
                                        @if($doc->status === 'failed')
                                            <flux:menu.item icon="arrow-path" wire:click="reindexDocument({{ $doc->id }})">
                                                {{ __('Coba Lagi Vektorisasi') }}
                                            </flux:menu.item>
                                            <flux:menu.separator />
                                        @endif
                                        <flux:menu.item icon="arrow-down-tray" wire:click="downloadDocument({{ $doc->id }})">
                                            {{ __('Unduh Dokumen') }}
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="deleteDocument({{ $doc->id }})" wire:confirm="Apakah Anda yakin ingin menghapus dokumen ini beserta seluruh chunk-nya?">
                                            {{ __('Hapus Dokumen') }}
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-zinc-400 text-sm">
                            Belum ada dokumen Knowledge Base diunggah.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </flux:table>
    </div>

    <!-- Modal Upload DOCX -->
    <flux:modal name="create-kb" class="max-w-md">
        <form wire:submit.prevent="uploadDocument" class="space-y-6">
            <div>
                <flux:heading size="lg">Tambah Dokumen Knowledge Base</flux:heading>
                <flux:subheading>Unggah file Word (DOCX) baru ke private storage untuk AI Chatbot PMB.</flux:subheading>
            </div>

            <flux:field>
                <flux:input wire:model="file_name" label="Nama File (Metadata Unik)" placeholder="Contoh: panduan_pmb_2026" />
                <flux:error name="file_name" />
            </flux:field>

            <flux:field>
                <flux:label>Dokumen Word (*.docx)</flux:label>
                <flux:description>Format wajib: DOCX. Ukuran maksimal: 6MB.</flux:description>
                <input type="file" wire:model="file" accept=".docx" class="block w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300 dark:hover:file:bg-zinc-700 mt-2" />
                <div wire:loading wire:target="file" class="text-xs text-blue-600 dark:text-blue-400 mt-1.5 flex items-center gap-1.5 font-medium">
                    <flux:icon icon="arrow-path" class="w-3.5 h-3.5 animate-spin" />
                    <span>Mengunggah berkas sementara...</span>
                </div>
                <flux:error name="file" />
            </flux:field>

            <flux:field>
                <flux:label>Deskripsi Singkat (Opsional)</flux:label>
                <flux:input wire:model="deskripsi" placeholder="Contoh: Rincian biaya SPP dan registrasi ulang PMB 2026" />
                <flux:error name="deskripsi" />
            </flux:field>

            <div class="flex items-center gap-2 pt-2">
                <flux:spacer />
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="uploadDocument">
                    <span wire:loading.remove wire:target="uploadDocument">Unggah & Proses</span>
                    <span wire:loading wire:target="uploadDocument">Memproses Vektorisasi...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
