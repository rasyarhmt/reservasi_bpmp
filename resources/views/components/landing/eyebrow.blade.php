@props([
    'dot' => true,
    'variant' => 'default',
])

@php
    $variants = [
        'default' => 'border-brass/35 bg-brass-soft/70 text-accent-foreground',
        'light' => 'border-primary-foreground/25 bg-primary-foreground/10 text-primary-foreground',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[0.7rem] font-medium uppercase tracking-[0.16em] '.$variants[$variant]]) }}>
    @if ($dot)
        <span class="size-1.5 shrink-0 rounded-full bg-brass"></span>
    @endif

    {{ $slot }}
</span>
