@props([
    'name',
    'title',
    'description' => null,
    'confirmText' => 'Ya, Lanjutkan',
    'confirmAction' => null,
])

<flux:modal :name="$name" class="min-w-[22rem]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>

            @if ($description)
                <flux:subheading class="mt-1 leading-relaxed">
                    {{ $description }}
                </flux:subheading>
            @endif
        </div>

        {{ $slot }}

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="filled" class="cursor-pointer">Batal</flux:button>
            </flux:modal.close>

            <flux:modal.close>
                <flux:button variant="danger" class="cursor-pointer" wire:click="{{ $confirmAction }}">
                    {{ $confirmText }}
                </flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>
