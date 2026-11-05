@props([])

<h3 {{ $attributes->merge(['class' => 'text-lg leading-none font-semibold tracking-tight']) }}>
    {{ $slot }}
</h3>
