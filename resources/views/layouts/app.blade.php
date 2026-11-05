<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Simple.Calculia')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-muted/40 {{ request()->routeIs('home') ? 'ambient-canvas' : '' }}">
    @guest
        <div class="flex min-h-screen flex-col" x-data="{ mobileMenuOpen: false }">
            <header data-layout="navbar" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 12"
                x-init="scrolled = window.scrollY > 12"
                class="sticky top-0 z-30 border-b transition-colors duration-300"
                :class="scrolled ? 'border-border bg-background/85 shadow-sm backdrop-blur-xl' : 'border-transparent bg-transparent'">
                <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-x-4 px-4 py-3 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 font-semibold">
                        <img src="{{ asset('images/simple.calculia.webp') }}" alt="Simple.Calculia"
                            class="size-8 shrink-0 rounded-lg object-contain">
                        <span>Simple.Calculia</span>
                    </a>

                    <div class="hidden items-center gap-2 md:flex">
                        <x-button href="{{ route('login') }}" variant="outline" size="sm">Masuk</x-button>
                        <x-button href="{{ route('register') }}" size="sm">Daftar</x-button>
                    </div>

                    <button type="button" @click="mobileMenuOpen = true"
                        class="inline-flex size-9 items-center justify-center rounded-md border border-input md:hidden">
                        <span class="sr-only">Buka menu</span>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
            </header>

            <!-- TANDA KOMENTAR BLADE DIHAPUS PADA BAGIAN INI -->
            <div x-cloak x-show="mobileMenuOpen" @click="mobileMenuOpen = false" class="fixed inset-0 z-40 bg-black/40 md:hidden"></div>

            <aside x-cloak x-show="mobileMenuOpen"
                class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r bg-background md:hidden">
                <div class="flex h-14 items-center justify-between border-b px-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold">
                        <img src="{{ asset('images/simple.calculia.webp') }}" alt="Simple.Calculia"
                            class="size-8 shrink-0 rounded-lg object-contain">
                        Simple.Calculia
                    </a>
                    <button type="button" @click="mobileMenuOpen = false"
                        class="inline-flex size-9 items-center justify-center rounded-md border border-input">
                        <span class="sr-only">Tutup menu</span>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 space-y-1 p-3 text-sm">
                    <a href="{{ route('home') }}" @click="mobileMenuOpen = false"
                        class="flex items-center rounded-md px-3 py-2 transition-colors {{ request()->routeIs('home') ? 'bg-accent font-medium text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                        Beranda
                    </a>
                    <a href="{{ route('katalog') }}" @click="mobileMenuOpen = false"
                        class="flex items-center rounded-md px-3 py-2 transition-colors {{ request()->routeIs('katalog') ? 'bg-accent font-medium text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                        Kamar &amp; Aula
                    </a>
                </nav>

                <div class="space-y-2 border-t p-3">
                    <x-button href="{{ route('login') }}" variant="outline" size="sm" class="w-full justify-center">Masuk</x-button>
                    <x-button href="{{ route('register') }}" size="sm" class="w-full justify-center">Daftar</x-button>
                </div>
            </aside>
            <!-- AKHIR BAGIAN YANG DI-UNCOMMENT -->

            <main class="flex-1 @if (request()->routeIs('home')) w-full @else mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8 @endif">
                @if (session('success'))
                    <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
                @endif
                @if (session('error'))
                    <x-alert type="destructive" class="mb-6">{{ session('error') }}</x-alert>
                @endif
                @yield('content')
            </main>
        </div>
    @elseif (auth()->user()->isAdmin())
        @php
            $user = auth()->user();
            $navItems = [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
                ['label' => 'Kamar & Aula', 'href' => route('katalog'), 'active' => request()->routeIs('katalog')],
                ['label' => 'Reservasi', 'href' => route('reservations.index'), 'active' => request()->routeIs('reservations.index')],
                ['label' => 'Reservasi Walk-In', 'href' => route('walkin.chooser'), 'active' => request()->routeIs('walkin.*')],
                ['label' => 'Pelanggan', 'href' => route('users.index'), 'active' => request()->routeIs('admin.users.*')],
            ];
        @endphp

        <div class="flex min-h-screen" x-data="{ open: false }">
            <aside data-layout="sidebar" x-cloak
                class="fixed inset-y-0 left-0 z-50 flex w-60 -translate-x-full flex-col border-r bg-background transition-transform md:sticky md:top-0 md:h-screen md:translate-x-0 md:shrink-0"
                :class="{ 'translate-x-0': open }">
                <a href="{{ route('dashboard') }}" class="flex h-14 items-center gap-2 border-b px-4 font-semibold">
                    <img src="{{ asset('images/simple.calculia.webp') }}" alt="Simple.Calculia"
                        class="size-8 shrink-0 rounded-lg object-contain">
                    Simple.Calculia
                </a>

                <nav class="flex-1 space-y-1 overflow-y-auto p-3 text-sm">
                    @foreach ($navItems as $item)
                        <a href="{{ $item['href'] }}"
                            class="flex items-center rounded-md px-3 py-2 transition-colors {{ $item['active'] ? 'bg-accent font-medium text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="space-y-3 border-t p-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">{{ $user->name }}</p>
                        <p class="text-xs text-muted-foreground">{{ $user->roleLabel() ?? 'Admin' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-button variant="outline" size="sm" type="submit" class="w-full">Keluar</x-button>
                    </form>
                </div>
            </aside>

            <div x-cloak x-show="open" @click="open = false" class="fixed inset-0 z-40 bg-black/40 md:hidden"></div>

            <div class="flex min-h-screen flex-1 flex-col">
                <header class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b bg-background/95 px-4 backdrop-blur md:hidden">
                    <button type="button" @click="open = true"
                        class="inline-flex size-9 items-center justify-center rounded-md border border-input">
                        <span class="sr-only">Buka menu</span>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/simple.calculia.webp') }}" alt="Simple.Calculia"
                            class="size-7 shrink-0 rounded-md object-contain">
                        <span class="font-semibold">Simple.Calculia</span>
                    </a>
                </header>

                <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    @if (session('success'))
                        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
                    @endif
                    @if (session('error'))
                        <x-alert type="destructive" class="mb-6">{{ session('error') }}</x-alert>
                    @endif
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        {{-- TAMPILAN CUSTOMER LOGIN --}}
        <div class="flex min-h-screen flex-col" x-data="{ mobileMenuOpen: false }">
            <header data-layout="navbar" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 12"
                x-init="scrolled = window.scrollY > 12"
                class="sticky top-0 z-30 border-b transition-colors duration-300"
                :class="scrolled ? 'border-border bg-background/85 shadow-sm backdrop-blur-xl' : 'border-transparent bg-transparent'">
                <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-x-4 px-4 py-3 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 font-semibold">
                        <img src="{{ asset('images/simple.calculia.webp') }}" alt="Simple.Calculia"
                            class="size-8 shrink-0 rounded-lg object-contain">
                        <span>Simple.Calculia</span>
                    </a>

                    <nav class="hidden items-center gap-1 text-sm md:flex">
                        <a href="{{ route('home') }}"
                            class="rounded-md px-3 py-2 transition-colors {{ request()->routeIs('home') ? 'bg-accent font-medium text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                            Beranda
                        </a>
                        <a href="{{ route('reservations.index') }}"
                            class="rounded-md px-3 py-2 transition-colors {{ request()->routeIs('reservations.*') ? 'bg-accent font-medium text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                            Reservasi
                        </a>
                    </nav>

                    <div class="hidden items-center gap-4 md:flex">
                        <a href="{{ route('profile.edit') }}" class="text-sm font-medium transition-colors hover:text-primary {{ request()->routeIs('profile.*') ? 'text-primary' : 'text-muted-foreground' }}">
                            Profil ({{ auth()->user()->name }})
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-button variant="outline" size="sm" type="submit">Keluar</x-button>
                        </form>
                    </div>

                    <button type="button" @click="mobileMenuOpen = true"
                        class="inline-flex size-9 items-center justify-center rounded-md border border-input md:hidden">
                        <span class="sr-only">Buka menu</span>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
            </header>

            <div x-cloak x-show="mobileMenuOpen" @click="mobileMenuOpen = false" class="fixed inset-0 z-40 bg-black/40 md:hidden"></div>

            <aside x-cloak x-show="mobileMenuOpen"
                class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r bg-background md:hidden">
                <div class="flex h-14 items-center justify-between border-b px-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold">
                        <img src="{{ asset('images/simple.calculia.webp') }}" alt="Simple.Calculia"
                            class="size-8 shrink-0 rounded-lg object-contain">
                        Simple.Calculia
                    </a>
                    <button type="button" @click="mobileMenuOpen = false"
                        class="inline-flex size-9 items-center justify-center rounded-md border border-input">
                        <span class="sr-only">Tutup menu</span>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 space-y-1 p-3 text-sm">
                    <a href="{{ route('home') }}" @click="mobileMenuOpen = false"
                        class="flex items-center rounded-md px-3 py-2 transition-colors {{ request()->routeIs('home') ? 'bg-accent font-medium text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                        Beranda
                    </a>
                    <a href="{{ route('reservations.index') }}" @click="mobileMenuOpen = false"
                        class="flex items-center rounded-md px-3 py-2 transition-colors {{ request()->routeIs('reservations.*') ? 'bg-accent font-medium text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                        Reservasi
                    </a>
                    <a href="{{ route('profile.edit') }}" @click="mobileMenuOpen = false"
                        class="flex items-center rounded-md px-3 py-2 transition-colors {{ request()->routeIs('profile.*') ? 'bg-accent font-medium text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                        Profil Saya
                    </a>
                </nav>

                <div class="space-y-3 border-t p-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-button variant="outline" size="sm" type="submit" class="w-full">Keluar</x-button>
                    </form>
                </div>
            </aside>

            <main class="flex-1 @if (request()->routeIs('home')) w-full @else mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8 @endif">
                @if (session('success'))
                    <x-alert type="success" class="mb-6">{{ session('success' ) }}</x-alert>
                @endif
                @if (session('error'))
                    <x-alert type="destructive" class="mb-6">{{ session('error') }}</x-alert>
                @endif
                @yield('content')
            </main>
        </div>
    @endguest
</body>
</html>