@extends('layouts.guest')

@section('title', 'Daftar')

@section('content')
    <x-card>
        <x-card-header>
            <x-card-title>Buat Akun</x-card-title>
            <x-card-description>Daftar sebagai pelanggan untuk memesan kamar dan aula.</x-card-description>
        </x-card-header>
        <x-card-content>
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div class="space-y-2">
                    <x-label for="name">Nama Lengkap</x-label>
                    <x-input id="name" name="name" placeholder="Nama Anda" required autofocus />
                    <x-input-error name="name" />
                </div>

                <div class="space-y-2">
                    <x-label for="email">Email</x-label>
                    <x-input id="email" name="email" type="email" placeholder="nama@email.com" required />
                    <x-input-error name="email" />
                </div>

                <div class="space-y-2">
                    <x-label for="phone">No. Telepon <span class="text-muted-foreground font-normal">(opsional)</span></x-label>
                    <x-input id="phone" name="phone" type="tel" placeholder="08xxxxxxxxxx" />
                    <x-input-error name="phone" />
                </div>

                <div class="space-y-2">
                    <x-label for="password">Password</x-label>
                    <x-input id="password" name="password" type="password" placeholder="Minimal 8 karakter" required />
                    <x-input-error name="password" />
                </div>

                <div class="space-y-2">
                    <x-label for="password_confirmation">Konfirmasi Password</x-label>
                    <x-input id="password_confirmation" name="password_confirmation" type="password" required />
                </div>

                <x-button type="submit" class="w-full">Daftar</x-button>
            </form>
        </x-card-content>
        <x-card-footer class="justify-center pb-6 text-sm text-muted-foreground">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-medium text-foreground underline-offset-4 hover:underline">Masuk</a>
        </x-card-footer>
    </x-card>
@endsection
