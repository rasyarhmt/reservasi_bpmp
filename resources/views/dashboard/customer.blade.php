@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Halo, {{ auth()->user()->name }} 👋</h1>
            <p class="text-sm text-muted-foreground">
                Anda masuk sebagai <span class="font-medium text-foreground">{{ auth()->user()->roleLabel() }}</span>.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Reservasi Kamar & Aula</x-card-title>
                    <x-card-description>
                        Melihat daftar kamar, aula, fasilitas, dan harga sewa.
                    </x-card-description>
                </x-card-header>
                {{-- <x-card-content class="pb-6">
                    <x-button href="{{ route('katalog') }}" variant="outline" size="sm">Buka Reservasi</x-button>
                </x-card-content> --}}
            </x-card>

            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Reservasi Saya</x-card-title>
                    <x-card-description>
                        Pesan kamar, sewa aula, dan pantau status reservasi Anda.
                    </x-card-description>
                </x-card-header>
                <x-card-content class="flex gap-2 pb-6">
                    <x-button href="{{ route('booking.create') }}" size="sm">Buat Reservasi</x-button>
                    <x-button href="{{ route('reservations.index') }}" variant="outline" size="sm">Lihat Riwayat</x-button>
                </x-card-content>
            </x-card>
        </div>
    </div>
@endsection
