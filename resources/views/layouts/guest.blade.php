<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Simple.Calculia')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-muted/40 p-4">
    <div class="w-full max-w-lg">
        <a href="{{ route('home') }}" class="mb-6 flex items-center justify-center gap-2 text-lg font-semibold">
            <img src="{{ asset('images/simple.calculia.webp') }}" alt="Simple.Calculia"
                class="size-9 shrink-0 rounded-lg object-contain">
            Simple.Calculia
        </a>
        @yield('content')
    </div>
</body>
</html>
