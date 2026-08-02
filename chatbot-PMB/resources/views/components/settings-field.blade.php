@props([
    'name',
    'label',
    'description' => null,
    'badge' => null,
])

<flux:field {{ $attributes->merge(['class' => 'flex flex-col h-full']) }}>
    <div class="flex flex-wrap items-center justify-between gap-1.5 mb-1">
        <flux:label class="font-medium text-zinc-900 dark:text-white text-sm sm:text-base">{{ $label }}</flux:label>
        @if ($badge)
            <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 shrink-0">
                {{ $badge }}
            </span>
        @endif
    </div>

    @if ($description)
        <flux:description class="text-xs text-zinc-500 dark:text-zinc-400 mb-2 leading-relaxed">{{ $description }}</flux:description>
    @endif

    <div class="mt-auto">
        {{ $slot }}
    </div>

    <flux:error :name="$name" />
</flux:field>
