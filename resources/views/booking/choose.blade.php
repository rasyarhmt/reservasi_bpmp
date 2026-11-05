@extends('layouts.app')

@section('title', 'Buat Reservasi')

@section('content')
    <div class="space-y-6">
        <div class="max-w-3xl">
            <h1 class="text-2xl font-semibold tracking-tight">Buat Reservasi</h1>
            <p class="text-sm text-muted-foreground">
                Pilih jenis reservasi yang ingin Anda buat.
            </p>
        </div>

        <div class="grid max-w-4xl gap-6 sm:grid-cols-2">
            <a href="{{ route('booking.room') }}" class="group block">
                <x-card class="h-full transition-colors group-hover:border-foreground/50">
                    <x-card-header>
                        <x-card-title class="text-base">Kamar</x-card-title>
                        <x-card-description>
                            Pilih kamar dan tanggal inap. Harga dihitung per malam.
                        </x-card-description>
                    </x-card-header>
                    <x-card-content class="pb-6 text-sm text-muted-foreground">
                        <ul class="list-inside list-disc space-y-1">
                            <li>Tanggal check-in & check-out</li>
                            <li>Jumlah tamu (ekstra dikenakan tarif)</li>
                            <li>Skema DP atau lunas</li>
                        </ul>
                        <span class="mt-4 inline-block font-medium text-foreground">
                            Pesan kamar →
                        </span>
                    </x-card-content>
                </x-card>
            </a>

            <a href="{{ route('booking.hall') }}" class="group block">
                <x-card class="h-full transition-colors group-hover:border-foreground/50">
                    <x-card-header>
                        <x-card-title class="text-base">Aula</x-card-title>
                        <x-card-description>
                            Sewa aula untuk acara Anda. Tarif dihitung per jam.
                        </x-card-description>
                    </x-card-header>
                    <x-card-content class="pb-6 text-sm text-muted-foreground">
                        <ul class="list-inside list-disc space-y-1">
                            <li>Jadwal mulai & selesai sewa</li>
                            <li>Durasi otomatis × tarif per jam</li>
                            <li>Skema DP atau lunas</li>
                        </ul>
                        <span class="mt-4 inline-block font-medium text-foreground">
                            Sewa aula →
                        </span>
                    </x-card-content>
                </x-card>
            </a>
        </div>
    </div>
@endsection
