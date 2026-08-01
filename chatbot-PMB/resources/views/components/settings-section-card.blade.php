@props([
    'title',
    'description' => null,
    'icon' => null,
    'badge' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700/80 shadow-xs overflow-hidden']) }}>
    <div class="p-4 sm:p-5 border-b border-zinc-100 dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-start sm:items-center gap-3">
            @if ($icon)
                <div class="p-2 sm:p-2.5 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-[#1B287D] dark:text-blue-400 border border-blue-100 dark:border-blue-900/40 shrink-0">
                    <flux:icon :icon="$icon" class="w-4 h-4 sm:w-5 sm:h-5" />
                </div>
            @endif
            <div>
                <h3 class="text-sm sm:text-base font-semibold text-zinc-900 dark:text-white flex flex-wrap items-center gap-2">
                    {{ $title }}
                    @if ($badge)
                        <span class="text-[10px] sm:text-xs px-2 py-0.5 rounded-full font-medium bg-blue-100 text-[#1B287D] dark:bg-blue-900/60 dark:text-blue-300">
                            {{ $badge }}
                        </span>
                    @endif
                </h3>
                @if ($description)
                    <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-0.5 leading-normal">{{ $description }}</p>
                @endif
            </div>
        </div>

        @if (isset($headerAction))
            <div class="shrink-0">
                {{ $headerAction }}
            </div>
        @endif
    </div>

    <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
        {{ $slot }}
    </div>
</div>
