@props([
    'quote',
    'name',
    'meta' => null,
    'initials' => null,
    'rating' => 5,
])

@php
    $initials = $initials ?? collect(explode(' ', $name))
        ->filter()
        ->take(2)
        ->map(fn (string $part) => mb_substr($part, 0, 1))
        ->implode('');
@endphp

<figure {{ $attributes->merge(['class' => 'group relative flex h-full flex-col justify-between gap-6 overflow-hidden rounded-2xl border border-border/70 bg-card p-6 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-brass/40 hover:shadow-lg sm:p-7']) }}>
    <div class="relative">
        <svg class="absolute -top-1 right-0 size-10 text-brass/25 transition-colors group-hover:text-brass/45"
            viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M9.4 5.2C6.3 6.6 4.2 9.6 4.2 13v5.8h6V12H7.4c.1-1.9 1-3.3 2.9-4.3l-.9-2.5Zm10 0c-3.1 1.4-5.2 4.4-5.2 7.8v5.8h6V12h-2.8c.1-1.9 1-3.3 2.9-4.3l-.9-2.5Z" />
        </svg>

        <blockquote class="pr-10 font-display text-lg leading-relaxed text-pretty text-foreground/90">
            “{{ $quote }}”
        </blockquote>
    </div>

    <figcaption class="flex items-center gap-3">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-full border border-brass/30 bg-brass-soft/70 font-display text-sm font-semibold text-accent-foreground">
            {{ $initials }}
        </span>

        <span class="min-w-0">
            <span class="block truncate text-sm font-medium">{{ $name }}</span>
            @if ($meta)
                <span class="block truncate text-xs text-muted-foreground">{{ $meta }}</span>
            @endif
        </span>

        <span class="ml-auto flex shrink-0 items-center gap-0.5" aria-label="Penilaian {{ $rating }} dari 5">
            @for ($star = 1; $star <= 5; $star++)
                <svg class="size-3.5 {{ $star <= $rating ? 'text-brass' : 'text-border' }}" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
                    <path
                        d="M10 1.8l2.47 5.01 5.53.8-4 3.9.94 5.5L10 14.4l-4.94 2.6.94-5.5-4-3.9 5.53-.8L10 1.8z" />
                </svg>
            @endfor
        </span>
    </figcaption>
</figure>
