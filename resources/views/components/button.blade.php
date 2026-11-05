@props([
    'variant' => 'default',
    'size' => 'default',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'default' => 'bg-primary text-primary-foreground shadow-xs hover:bg-primary/90',
        'secondary' => 'bg-secondary text-secondary-foreground shadow-xs hover:bg-secondary/80',
        'outline' => 'border border-input bg-background shadow-xs hover:bg-accent hover:text-accent-foreground',
        'ghost' => 'hover:bg-accent hover:text-accent-foreground',
        'destructive' => 'bg-destructive text-destructive-foreground shadow-xs hover:bg-destructive/90',
        'inverse' => 'bg-primary-foreground text-primary shadow-xs hover:bg-primary-foreground/90',
        'outline-inverse' => 'border border-primary-foreground/30 bg-transparent text-primary-foreground shadow-xs hover:bg-primary-foreground/10 hover:text-primary-foreground',
    ];

    $sizes = [
        'default' => 'h-9 rounded-md px-4 py-2 text-sm',
        'sm' => 'h-8 rounded-md px-3 text-xs',
        'lg' => 'h-10 rounded-md px-8 text-sm',
        'xl' => 'h-12 rounded-full px-7 text-[0.95rem]',
        'icon' => 'size-9 rounded-md',
        'icon-sm' => 'size-8 rounded-md',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 whitespace-nowrap font-medium transition-colors disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 '.
        $variants[$variant].' '.$sizes[$size];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => $type, 'class' => $classes]) }}>{{ $slot }}</button>
@endif
