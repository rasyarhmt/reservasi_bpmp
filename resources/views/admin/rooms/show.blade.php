@extends('layouts.app')

@section('title', 'Detail Kamar '.$room->room_number)

@section('content')
    @php
        $komponen = $room->costComponents();
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">{{ $room->room_name }}</h1>
                <p class="text-sm text-muted-foreground">Kamar No. {{ $room->room_number }}</p>
            </div>
            <div class="flex gap-2">
                <x-button href="{{ route('rooms.edit', $room) }}" size="sm">Ubah Kamar</x-button>
                <x-button href="{{ route('katalog') }}" variant="outline" size="sm">← Kembali ke Kamar & Aula</x-button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Harga Dasar</x-card-title>
                </x-card-header>
                <x-card-content class="pb-6">
                    <p class="text-xl font-semibold">
                        Rp{{ number_format($room->base_price, 2, ',', '.') }}
                        <span class="text-sm font-normal text-muted-foreground">/ malam</span>
                    </p>
                </x-card-content>
            </x-card>

            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Tarif Orang Ekstra</x-card-title>
                </x-card-header>
                <x-card-content class="pb-6">
                    <p class="text-xl font-semibold">
                        Rp{{ number_format($room->extra_person_rate, 0, ',', '.') }}
                        <span class="text-sm font-normal text-muted-foreground">/ org / malam</span>
                    </p>
                </x-card-content>
            </x-card>

            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Kapasitas</x-card-title>
                </x-card-header>
                <x-card-content class="pb-6">
                    <p class="text-xl font-semibold">{{ $room->base_capacity }} orang</p>
                </x-card-content>
            </x-card>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-cost-breakdown :items="$komponen" :total="$room->componentTotal()"
                note="Rincian biaya operasional kamar — hanya untuk admin." />

            <x-card>
                <x-card-header>
                    <x-card-title class="text-base">Fasilitas Kamar</x-card-title>
                    <x-card-description>Fasilitas yang tampil di halaman reservasi.</x-card-description>
                </x-card-header>
                <x-card-content class="flex flex-wrap gap-1.5 pb-6">
                    @forelse ($room->amenities as $amenity)
                        <x-badge variant="outline">{{ $amenity->amenity_name }}</x-badge>
                    @empty
                        <p class="text-sm text-muted-foreground">Belum ada fasilitas.</p>
                    @endforelse
                </x-card-content>
            </x-card>
        </div>
    </div>
@endsection
