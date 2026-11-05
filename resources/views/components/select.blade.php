@props(['name'])

<select
    {{ $attributes->merge([
        'name' => $name,
        'class' => 'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:border-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
    ]) }}
>
    {{ $slot }}
</select>
