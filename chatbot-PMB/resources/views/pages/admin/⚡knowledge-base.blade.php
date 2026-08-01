<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use App\Models\KnowledgeBase;
use Illuminate\Support\Facades\Storage;

new #[Title('Knowledge Base Management')] class extends Component {
    use WithFileUploads;

    public $file;
    public string $kategori = 'Umum';
    public string $deskripsi = '';
    public bool $showModal = false;
    public string $search = '';

    protected array $rules = [
        'file' => 'required|file|mimes:docx|max:10240', // Max 10MB docx
        'kategori' => 'required|in:Biaya,Jadwal,Program Studi,Persyaratan,Umum',
        'deskripsi' => 'nullable|string|max:255',
    ];

    public function uploadDocument(): void
    {
        $this->validate();

        $originalName = $this->file->getClientOriginalName();

        // Check duplicate name
        if (KnowledgeBase::where('original_name', $originalName)->exists()) {
            $this->addError('file', 'Dokumen dengan nama file tersebut sudah ada di Knowledge Base.');
            return;
        }

        $storedPath = $this->file->store('knowledge_bases', 'public');

        KnowledgeBase::create([
            'filename' => $storedPath,
            'original_name' => $originalName,
            'kategori' => $this->kategori,
            'deskripsi' => $this->deskripsi,
        ]);

        $this->reset(['file', 'deskripsi', 'kategori']);
        $this->showModal = false;
        session()->flash('success', 'Dokumen DOCX berhasil diunggah ke Knowledge Base!');
    }

    public function deleteDocument(int $id): void
    {
        $doc = KnowledgeBase::find($id);
        if ($doc) {
            if (Storage::disk('public')->exists($doc->filename)) {
                Storage::disk('public')->delete($doc->filename);
            }
            $doc->delete(); // Chunks deleted via foreign key cascade
            session()->flash('success', 'Dokumen beserta seluruh chunk berhasil dihapus!');
        }
    }
};
?>

<div class="space-y-6">
    <!-- Flash Alert -->
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Knowledge Base PMB</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Kelola berkas dokumen pengetahuan resmi (DOCX) untuk rujukan AI Assistant.</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="$set('showModal', true)">
            Upload Dokumen DOCX
        </flux:button>
    </div>

    <!-- Filter & Search -->
    <div class="flex items-center justify-between gap-4 bg-white dark:bg-zinc-800 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs">
        <div class="relative w-full max-w-sm">
            <flux:input icon="magnifying-glass" wire:model.live="search" placeholder="Cari nama dokumen / deskripsi..." />
        </div>
        <div class="text-xs text-zinc-500">
            Total Dokumen: <span class="font-bold text-zinc-900 dark:text-white">{{ KnowledgeBase::count() }}</span>
        </div>
    </div>

    <!-- Document Table -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs overflow-hidden">
        <flux:table>
            <flux:columns>
                <flux:column>Nama Dokumen</flux:column>
                <flux:column>Kategori Metadata</flux:column>
                <flux:column>Deskripsi</flux:column>
                <flux:column>Tanggal Upload</flux:column>
                <flux:column align="end">Aksi</flux:column>
            </flux:columns>

            <flux:rows>
                @forelse(KnowledgeBase::where('original_name', 'like', '%'.$search.'%')->orWhere('deskripsi', 'like', '%'.$search.'%')->latest()->get() as $doc)
                    <flux:row>
                        <flux:cell class="font-medium flex items-center gap-2">
                            <flux:icon icon="document-text" class="w-5 h-5 text-[#1B287D]" />
                            <div>
                                <span class="font-semibold text-zinc-900 dark:text-white block">{{ $doc->original_name }}</span>
                                <span class="text-[11px] text-zinc-400 font-mono">{{ $doc->filename }}</span>
                            </div>
                        </flux:cell>
                        <flux:cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-[#1B287D] border border-blue-200">
                                {{ $doc->kategori }}
                            </span>
                        </flux:cell>
                        <flux:cell class="text-xs text-zinc-600 dark:text-zinc-300">
                            {{ $doc->deskripsi ?: '-' }}
                        </flux:cell>
                        <flux:cell class="text-xs text-zinc-500">
                            {{ $doc->created_at->format('d M Y, H:i') }}
                        </flux:cell>
                        <flux:cell align="end">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button variant="ghost" icon="trash" class="text-red-600 hover:bg-red-50" wire:click="deleteDocument({{ $doc->id }})" wire:confirm="Apakah Anda yakin ingin menghapus dokumen ini beserta seluruh chunk-nya?" />
                            </div>
                        </flux:cell>
                    </flux:row>
                @empty
                    <flux:row>
                        <flux:cell colspan="5" class="text-center py-10 text-zinc-400">
                            Belum ada dokumen Knowledge Base diunggah.
                        </flux:cell>
                    </flux:row>
                @endforelse
            </flux:rows>
        </flux:table>
    </div>

    <!-- Modal Upload DOCX -->
    <flux:modal wire:model="showModal" class="max-w-md">
        <form wire:submit.prevent="uploadDocument" class="space-y-4">
            <div>
                <flux:heading size="lg">Upload Dokumen DOCX</flux:heading>
                <flux:subheading>Unggah berkas resmi PMB untuk diproses menjadi pengetahuan AI.</flux:subheading>
            </div>

            <flux:field>
                <flux:label>File DOCX (*.docx)</flux:label>
                <input type="file" wire:model="file" accept=".docx" class="w-full text-xs border border-zinc-300 rounded-lg p-2 bg-zinc-50" />
                <flux:error name="file" />
            </flux:field>

            <flux:field>
                <flux:label>Kategori (Wajib)</flux:label>
                <select wire:model="kategori" class="w-full text-sm border border-zinc-300 rounded-lg p-2.5 bg-white">
                    <option value="Biaya">Biaya</option>
                    <option value="Jadwal">Jadwal</option>
                    <option value="Program Studi">Program Studi</option>
                    <option value="Persyaratan">Persyaratan</option>
                    <option value="Umum">Umum</option>
                </select>
                <flux:error name="kategori" />
            </flux:field>

            <flux:field>
                <flux:label>Deskripsi Singkat (Opsional)</flux:label>
                <flux:input wire:model="deskripsi" placeholder="Contoh: Rincian biaya SPP dan registrasi ulang PMB 2026" />
                <flux:error name="deskripsi" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Batal</flux:button>
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove>Simpan & Upload</span>
                    <span wire:loading>Mengunggah...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
