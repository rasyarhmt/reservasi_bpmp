@extends('layouts.app')

@section('title', 'Reservasi')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Reservasi</h1>
                <p class="text-sm text-muted-foreground">
                    @if (auth()->user()->isAdmin())
                        Reservasi aktif serta riwayat untuk semua pelanggan, online maupun walk-in.
                    @else
                        Reservasi aktif serta riwayat pemesanan kamar dan aula Anda.
                    @endif
                </p>
            </div>
            @if (auth()->user()->isAdmin())
                <x-button href="{{ route('walkin.chooser') }}">+ Reservasi Walk-In</x-button>
            @else
                <x-button href="{{ route('booking.create') }}">+ Buat Reservasi</x-button>
            @endif
        </div>

        <x-card>
            <x-card-header>
                <x-card-title class="text-base">Reservasi Aktif</x-card-title>
                <x-card-description>Masih berjalan: menunggu konfirmasi, terkonfirmasi, atau sedang inap.</x-card-description>
            </x-card-header>
            <x-card-content>
                @if ($activeReservations->isEmpty())
                    <p class="py-6 text-center text-sm text-muted-foreground">Tidak ada reservasi aktif.</p>
                @else
                    <div class="-mx-4 overflow-x-auto px-4">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-xs uppercase text-muted-foreground">
                                    <th class="py-3 pr-4 font-medium">Kode</th>
                                    @if (auth()->user()->isAdmin())
                                        <th class="py-3 pr-4 font-medium">Pelanggan</th>
                                    @endif
                                    <th class="py-3 pr-4 font-medium">Tanggal</th>
                                    <th class="py-3 pr-4 font-medium">Item</th>
                                    <th class="py-3 pr-4 font-medium">Status</th>
                                    <th class="py-3 text-right font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($activeReservations as $reservation)
                                    @include('reservations._row', ['reservation' => $reservation])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card-content>
        </x-card>

        <x-card>
            <x-card-header>
                <x-card-title class="text-base">Riwayat</x-card-title>
                <x-card-description>Reservasi yang telah selesai atau dibatalkan.</x-card-description>
            </x-card-header>
            <x-card-content>
                @if ($historyReservations->isEmpty())
                    <p class="py-6 text-center text-sm text-muted-foreground">Belum ada riwayat reservasi.</p>
                @else
                    <div class="-mx-4 overflow-x-auto px-4">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-xs uppercase text-muted-foreground">
                                    <th class="py-3 pr-4 font-medium">Kode</th>
                                    @if (auth()->user()->isAdmin())
                                        <th class="py-3 pr-4 font-medium">Pelanggan</th>
                                    @endif
                                    <th class="py-3 pr-4 font-medium">Tanggal</th>
                                    <th class="py-3 pr-4 font-medium">Item</th>
                                    <th class="py-3 pr-4 font-medium">Status</th>
                                    <th class="py-3 text-right font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($historyReservations as $reservation)
                                    @include('reservations._row', ['reservation' => $reservation])
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4">
                        {{ $historyReservations->links() }}
                    </div>
                @endif
            </x-card-content>
        </x-card>
    </div>
@endsection