@props(['name'])

@if ($errors->has($name))
    <p {{ $attributes->merge(['class' => 'text-sm font-medium text-destructive']) }} role="alert">
        {{ $errors->first($name) }}
    </p>
@endif
