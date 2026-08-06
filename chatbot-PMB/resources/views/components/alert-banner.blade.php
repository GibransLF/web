@props([
    'type' => 'success',
    'message' => null,
])

@php
    $styles = match ($type) {
        'success' => [
            'wrapper' => 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200',
            'icon' => 'check-circle',
            'iconColor' => 'text-emerald-600 dark:text-emerald-400',
        ],
        'warning' => [
            'wrapper' => 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200',
            'icon' => 'exclamation-triangle',
            'iconColor' => 'text-amber-600 dark:text-amber-400',
        ],
        'error', 'danger' => [
            'wrapper' => 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200',
            'icon' => 'x-circle',
            'iconColor' => 'text-rose-600 dark:text-rose-400',
        ],
        default => [
            'wrapper' => 'bg-blue-50 dark:bg-blue-950/40 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200',
            'icon' => 'information-circle',
            'iconColor' => 'text-blue-600 dark:text-blue-400',
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => 'p-4 rounded-xl border text-sm flex items-start gap-3 shadow-xs transition-all ' . $styles['wrapper']]) }}>
    <flux:icon :icon="$styles['icon']" class="w-5 h-5 shrink-0 mt-0.5 {{ $styles['iconColor'] }}" />
    <div class="flex-1 font-medium">
        {{ $message ?? $slot }}
    </div>
</div>
