@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <x-card>
        <x-card-header>
            <x-card-title>Masuk</x-card-title>
            <x-card-description>Login ke akun Anda untuk melanjutkan.</x-card-description>
        </x-card-header>
        <x-card-content>
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div class="space-y-2">
                    <x-label for="email">Email</x-label>
                    <x-input id="email" name="email" type="email" placeholder="nama@email.com" required autofocus />
                    <x-input-error name="email" />
                </div>

                <div class="space-y-2">
                    <x-label for="password">Password</x-label>
                    <x-input id="password" name="password" type="password" required />
                    <x-input-error name="password" />
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded border-input">
                    Ingat saya
                </label>

                <x-button type="submit" class="w-full">Masuk</x-button>
            </form>
        </x-card-content>
        <x-card-footer class="justify-center pb-6 text-sm text-muted-foreground">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-medium text-foreground underline-offset-4 hover:underline">Daftar</a>
        </x-card-footer>
    </x-card>
@endsection
