@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">Profil Saya</h1>
        <p class="text-sm text-muted-foreground">Kelola informasi pribadi dan keamanan akun Anda.</p>
    </div>

    <!-- Form Informasi Dasar -->
    <x-card>
        <x-card-header>
            <x-card-title>Informasi Pribadi</x-card-title>
        </x-card-header>
        <x-card-content>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="space-y-1">
                    <label for="name" class="text-sm font-medium">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" required>
                    @error('name') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label for="email" class="text-sm font-medium">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" required>
                    @error('email') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label for="phone" class="text-sm font-medium">Nomor WhatsApp / HP</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    @error('phone') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2">
                    <x-button type="submit">Simpan Perubahan</x-button>
                </div>
            </form>
        </x-card-content>
    </x-card>

    <!-- Form Ubah Password -->
    <x-card>
        <x-card-header>
            <x-card-title>Ubah Kata Sandi</x-card-title>
        </x-card-header>
        <x-card-content>
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="space-y-1">
                    <label for="current_password" class="text-sm font-medium">Kata Sandi Saat Ini</label>
                    <input type="password" id="current_password" name="current_password" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" required>
                    @error('current_password') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label for="password" class="text-sm font-medium">Kata Sandi Baru</label>
                    <input type="password" id="password" name="password" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" required>
                    @error('password') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label for="password_confirmation" class="text-sm font-medium">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" required>
                </div>

                <div class="pt-2">
                    <x-button type="submit">Perbarui Kata Sandi</x-button>
                </div>
            </form>
        </x-card-content>
    </x-card>
</div>
@endsection