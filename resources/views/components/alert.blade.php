@props(['type' => 'default'])

@php
    $types = [
        'default' => 'bg-card text-card-foreground border-border',
        'destructive' => 'border-destructive/50 text-destructive bg-destructive/10 [&>svg]:text-destructive',
        'success' => 'border-success/50 text-success bg-success/10 [&>svg]:text-success',
    ];

    $icons = [
        'default' => 'M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm1 15h-2v-2h2Zm0-4h-2V7h2Z',
        'destructive' => 'M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm1 15h-2v-2h2Zm0-4h-2V7h2Z',
        'success' => 'M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm-1 15-5-5 1.41-1.41L11 14.17l7.59-7.59L20 8Z',
    ];
@endphp

<div {{ $attributes->merge(['role' => 'alert', 'class' => 'relative flex w-full gap-4 rounded-lg border px-4 py-3 text-sm '.$types[$type]]) }}>
    <svg class="size-4 shrink-0 translate-y-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="{{ $icons[$type] }}" />
    </svg>
    <div>{{ $slot }}</div>
</div>
