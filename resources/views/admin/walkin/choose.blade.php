@extends('layouts.app')

@section('title', 'Reservasi Walk-In')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Reservasi Walk-In</h1>
            <p class="text-sm text-muted-foreground">
                Buatkan reservasi untuk pelanggan yang datang langsung ke lokasi. DP minimal
                Rp{{ number_format(App\Models\Reservation::MIN_DOWN_PAYMENT, 0, ',', '.') }} dibayar di tempat, sisa tagihan atau denda
                dibayarkan cash saat check-out.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Kamar</x-card-title>
                    <x-card-description>Reservasi inap kamar untuk pelanggan di lokasi.</x-card-description>
                </x-card-header>
                <x-card-content class="pb-6">
                    <x-button href="{{ route('walkin.room') }}">Reservasi Kamar →</x-button>
                </x-card-content>
            </x-card>

            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Aula</x-card-title>
                    <x-card-description>Reservasi sewa aula per jam untuk pelanggan di lokasi.</x-card-description>
                </x-card-header>
                <x-card-content class="pb-6">
                    <x-button href="{{ route('walkin.hall') }}">Reservasi Aula →</x-button>
                </x-card-content>
            </x-card>
        </div>
    </div>
@endsection