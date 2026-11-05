@props(['type' => 'text', 'name' => null, 'value' => null])

@php
    $merged = [
        'type' => $type,
        'class' => 'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
    ];

    if ($name !== null && $name !== '') {
        $merged['name'] = $name;
        $merged['value'] = old($name, $value);
    } elseif ($value !== null) {
        $merged['value'] = $value;
    }
@endphp

<input
    {{ $attributes->merge($merged) }}
/>
