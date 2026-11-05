@props([
    'title',
    'highlight' => null,
    'eyebrow' => null,
    'description' => null,
    'align' => 'center',
])

@php
    $alignment = $align === 'center'
        ? 'mx-auto max-w-2xl items-center text-center'
        : 'max-w-2xl items-start text-left';
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 '.$alignment]) }}>
    @if ($eyebrow)
        <x-landing.eyebrow>{{ $eyebrow }}</x-landing.eyebrow>
    @endif

    <h2 class="font-display text-3xl leading-[1.15] tracking-tight text-balance sm:text-4xl">
        {{ $title }}

        @if ($highlight)
            <span class="brass-text italic">{{ $highlight }}</span>
        @endif
    </h2>

    @if ($description)
        <p class="text-base leading-relaxed text-pretty text-muted-foreground">
            {{ $description }}
        </p>
    @endif
</div>
