@extends('layouts.app')

@section('title', 'Detail Aula '.$hall->hall_name)

@section('content')
    @php
        $komponen = $hall->costComponents();

        $fasilitas = [
            'Kursi Besi/Metal H.S' => 19,
            'Kursi Besi/Metal' => 80,
            'Meja Kerja Kayu' => 80,
            'Buffet' => 80,
            'Meja Rapat' => 80,
            'Kursi Fiber Glas/Plastik' => 80,
            'Jam Mekanis' => 67,
            'A.C. Sentral' => 68,
            'Amplifier' => 228,
            'Microphone Table Stand' => 80,
            'Dispenser' => 62,
            'Mimbar/Podium' => 133,
            'Panggung' => 67,
            'Audio Mixing Console' => 81,
            'Power Amplifier' => 139,
            'Video Monitor S/ UH' => 146,
        ];
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">{{ $hall->hall_name }}</h1>
                <p class="text-sm text-muted-foreground">Kapasitas {{ $hall->capacity }} orang</p>
            </div>
            <div class="flex gap-2">
                <x-button href="{{ route('halls.edit') }}" size="sm">Ubah Aula</x-button>
                <x-button href="{{ route('katalog') }}" variant="outline" size="sm">← Kembali ke Katalog</x-button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Harga Sewa Per Hari</x-card-title>
                </x-card-header>
                <x-card-content class="pb-6">
                    <p class="text-xl font-semibold">
                        Rp{{ number_format($hall->base_price, 2, ',', '.') }}
                    </p>
                </x-card-content>
            </x-card>

            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Jam Operasional</x-card-title>
                </x-card-header>
                <x-card-content class="pb-6">
                    <p class="text-xl font-semibold">
                        {{ $hall->operational_start }}–{{ $hall->operational_end }}
                        <span class="text-sm font-normal text-muted-foreground">WITA</span>
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ $hall->operational_hours }} jam per hari. Denda keterlambatan
                        Rp{{ number_format($hall->lateChargeRate(), 2, ',', '.') }}/jam.
                    </p>
                </x-card-content>
            </x-card>

            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Kapasitas</x-card-title>
                </x-card-header>
                <x-card-content class="pb-6">
                    <p class="text-xl font-semibold">{{ $hall->capacity }} orang</p>
                </x-card-content>
            </x-card>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-cost-breakdown :items="$komponen" :total="$hall->componentTotal()"
                total-label="Total tarif per hari"
                note="Rincian biaya operasional tarif per hari — hanya untuk admin." />

            <x-facility-list :items="$fasilitas"
                note="Inventaris penyusun aula — hanya untuk admin." />
        </div>
    </div>
@endsection
