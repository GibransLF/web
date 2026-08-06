<?php

use App\Enums\UserRole;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Kelola Akun')] class extends Component {
    use WithPagination;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?int $editingSupervisorId = null;

    public string $editingSupervisorName = '';

    public string $edit_name = '';

    public string $edit_email = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function createSupervisor(): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama supervisor wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email tersebut sudah terdaftar di sistem.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        User::create([
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'password' => Hash::make($this->password),
            'role' => UserRole::SUPERVISOR,
            'email_verified_at' => now(),
        ]);

        $this->reset(['name', 'email', 'password', 'password_confirmation']);
        session()->flash('success', 'Akun supervisor baru berhasil dibuat!');
        Flux::modal('create-supervisor')->close();
    }

    public function openEditUserModal(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $user = User::find($id);
        if ($user) {
            $this->editingSupervisorId = $user->id;
            $this->edit_name = $user->name;
            $this->edit_email = $user->email;
            $this->resetErrorBag();
            Flux::modal('edit-user-modal')->show();
        }
    }

    public function updateUser(): void
    {
        if (! auth()->user()->isAdmin() || ! $this->editingSupervisorId) {
            return;
        }

        $this->validate([
            'edit_name' => 'required|string|max:255',
            'edit_email' => 'required|string|email|max:255|unique:users,email,'.$this->editingSupervisorId,
        ], [
            'edit_name.required' => 'Nama wajib diisi.',
            'edit_email.required' => 'Email wajib diisi.',
            'edit_email.email' => 'Format email tidak valid.',
            'edit_email.unique' => 'Email tersebut sudah digunakan oleh pengguna lain.',
        ]);

        $user = User::find($this->editingSupervisorId);
        if ($user) {
            $user->update([
                'name' => trim($this->edit_name),
                'email' => strtolower(trim($this->edit_email)),
            ]);

            session()->flash('success', "Data akun {$user->name} berhasil diperbarui!");
        }

        $this->reset(['editingSupervisorId', 'edit_name', 'edit_email']);
        Flux::modal('edit-user-modal')->close();
    }

    public function openChangePasswordModal(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $user = User::find($id);
        if ($user) {
            $this->editingSupervisorId = $user->id;
            $this->editingSupervisorName = $user->name;
            $this->reset(['new_password', 'new_password_confirmation']);
            $this->resetErrorBag();
            Flux::modal('change-password-modal')->show();
        }
    }

    public function updateSupervisorPassword(): void
    {
        if (! auth()->user()->isAdmin() || ! $this->editingSupervisorId) {
            return;
        }

        $this->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = User::find($this->editingSupervisorId);
        if ($user) {
            $user->update([
                'password' => Hash::make($this->new_password),
            ]);

            session()->flash('success', "Password untuk supervisor {$user->name} berhasil diperbarui!");
        }

        $this->reset(['editingSupervisorId', 'editingSupervisorName', 'new_password', 'new_password_confirmation']);
        Flux::modal('change-password-modal')->close();
    }

    public function deleteSupervisor(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        if ($id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');

            return;
        }

        $user = User::find($id);
        if ($user) {
            $name = $user->name;
            $user->delete();
            session()->flash('success', "Akun supervisor \"{$name}\" berhasil dihapus!");
        }
    }

    public function with(): array
    {
        $query = User::query();

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        return [
            'supervisors' => $query->orderByRaw("CASE WHEN role = ? THEN 0 ELSE 1 END", [UserRole::ADMIN->value])->latest()->paginate(10),
            'totalSupervisors' => User::where('role', UserRole::SUPERVISOR)->count(),
        ];
    }
};
?>

<div class="space-y-6">
    <!-- Flash Alerts -->
    @if (session()->has('success'))
        <x-alert-banner type="success" :message="session('success')" />
    @endif

    @if (session()->has('error'))
        <x-alert-banner type="error" :message="session('error')" />
    @endif

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Kelola Akun') }}</flux:heading>
            <flux:subheading>{{ __('Kelola akun administrator & supervisor untuk pemantauan sistem PMB.') }}</flux:subheading>
        </div>
        <flux:modal.trigger name="create-supervisor">
            <flux:button variant="primary" icon="plus">
                {{ __('Supervisor Baru') }}
            </flux:button>
        </flux:modal.trigger>
    </div>

    <!-- Filter & Search Bar -->
    <div class="flex items-center justify-between gap-4 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs">
        <div class="relative w-full max-w-sm">
            <flux:input icon="magnifying-glass" wire:model.live="search" placeholder="Cari nama supervisor / email..." />
        </div>
        <div class="text-xs text-zinc-500">
            Total Supervisor: <span class="font-bold text-zinc-900 dark:text-white">{{ $totalSupervisors }}</span>
        </div>
    </div>

    <!-- User Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-xs overflow-hidden">
        <flux:table>
            <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                    <th class="p-3">Pengguna</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Role</th>
                    <th class="p-3">Tanggal Dibuat</th>
                    <th class="p-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-sm">
                @forelse($supervisors as $user)
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="p-3 font-medium">
                            <div class="flex items-center gap-3">
                                <flux:avatar :name="$user->name" :initials="$user->initials()" size="sm" />
                                <div>
                                    <span class="font-semibold text-zinc-900 dark:text-white block">{{ $user->name }}</span>
                                    @if($user->id === auth()->id())
                                        <span class="text-[10px] text-blue-600 font-bold">(Akun Anda)</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="p-3 text-xs text-zinc-600 dark:text-zinc-300 font-mono">
                            {{ $user->email }}
                        </td>
                        <td class="p-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $user->role->badgeColor() }}">
                                {{ $user->role->label() }}
                            </span>
                        </td>
                        <td class="p-3 text-xs text-zinc-500">
                            {{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}
                        </td>
                        <td class="p-3 text-right">
                            <div class="flex items-center justify-end">
                                @if($user->id !== auth()->id())
                                    <flux:dropdown align="end">
                                        <flux:button variant="ghost" icon="ellipsis-horizontal" size="sm" title="Aksi" />
                                        <flux:menu>
                                            <flux:menu.item icon="pencil-square" wire:click="openEditUserModal({{ $user->id }})">
                                                {{ __('Edit Data Akun') }}
                                            </flux:menu.item>
                                            <flux:menu.item icon="key" wire:click="openChangePasswordModal({{ $user->id }})">
                                                {{ __('Ubah Password') }}
                                            </flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item icon="trash" variant="danger" wire:click="deleteSupervisor({{ $user->id }})" wire:confirm="Apakah Anda yakin ingin menghapus akun '{{ $user->name }}'?">
                                                {{ __('Hapus Akun') }}
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                @else
                                    <span class="text-xs text-zinc-400 font-italic">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-zinc-400 text-sm">
                            Belum ada supervisor yang terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </flux:table>
    </div>

    @if($supervisors->hasPages())
        <div class="pt-2 overflow-x-auto w-full">
            <flux:pagination :paginator="$supervisors" />
        </div>
    @endif

    <!-- Modal Form Tambah Supervisor Baru -->
    <flux:modal name="create-supervisor" class="max-w-md">
        <form wire:submit.prevent="createSupervisor" class="space-y-5">
            <div>
                <flux:heading size="lg">Tambah Supervisor Baru</flux:heading>
                <flux:subheading>Buat akun supervisor baru untuk pemantauan dashboard & history chat PMB.</flux:subheading>
            </div>

            <flux:field>
                <flux:input
                    wire:model="name"
                    label="Nama Lengkap"
                    placeholder="Masukan nama"
                />
            </flux:field>

            <flux:field>
                <flux:input
                    type="email"
                    wire:model="email"
                    label="Alamat Email"
                    placeholder="supervisor@stmikbandung.ac.id"
                />
            </flux:field>

            <flux:field>
                <flux:input
                    type="password"
                    wire:model="password"
                    label="Password"
                    placeholder="Minimal 8 karakter"
                />
            </flux:field>

            <flux:field>
                <flux:input
                    type="password"
                    wire:model="password_confirmation"
                    label="Konfirmasi Password"
                    placeholder="Masukkan ulang password"
                />
            </flux:field>

            <div class="flex items-center gap-2 pt-2">
                <flux:spacer />
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="createSupervisor">
                    <span wire:loading.remove wire:target="createSupervisor">Buat Supervisor</span>
                    <span wire:loading wire:target="createSupervisor">Menyimpan...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Form Ubah Password Supervisor -->
    <flux:modal name="change-password-modal" class="max-w-md">
        <form wire:submit.prevent="updateSupervisorPassword" class="space-y-5">
            <div>
                <flux:heading size="lg">Ubah Password Supervisor</flux:heading>
                <flux:subheading>Perbarui password untuk akun {{ $editingSupervisorName }}.</flux:subheading>
            </div>

            <flux:field>
                <flux:input
                    type="password"
                    wire:model="new_password"
                    label="Password Baru"
                    placeholder="Minimal 8 karakter"
                />
            </flux:field>

            <flux:field>
                <flux:input
                    type="password"
                    wire:model="new_password_confirmation"
                    label="Konfirmasi Password Baru"
                    placeholder="Masukkan ulang password baru"
                />
            </flux:field>

            <div class="flex items-center gap-2 pt-2">
                <flux:spacer />
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="updateSupervisorPassword">
                    <span wire:loading.remove wire:target="updateSupervisorPassword">Simpan Password</span>
                    <span wire:loading wire:target="updateSupervisorPassword">Menyimpan...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Form Edit Data Akun -->
    <flux:modal name="edit-user-modal" class="max-w-md">
        <form wire:submit.prevent="updateUser" class="space-y-5">
            <div>
                <flux:heading size="lg">Edit Data Akun</flux:heading>
                <flux:subheading>Ubah nama lengkap atau alamat email pengguna.</flux:subheading>
            </div>

            <flux:field>
                <flux:input
                    wire:model="edit_name"
                    label="Nama Lengkap"
                    placeholder="Masukan nama"
                />
            </flux:field>

            <flux:field>
                <flux:input
                    type="email"
                    wire:model="edit_email"
                    label="Alamat Email"
                    placeholder="supervisor@stmikbandung.ac.id"
                />
            </flux:field>

            <div class="flex items-center gap-2 pt-2">
                <flux:spacer />
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="updateUser">
                    <span wire:loading.remove wire:target="updateUser">Simpan Perubahan</span>
                    <span wire:loading wire:target="updateUser">Menyimpan...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
