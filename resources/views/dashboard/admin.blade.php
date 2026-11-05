@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Operasional Harian</h1>
            <p class="text-sm text-muted-foreground">
                Kelola konfirmasi, check-in, check-out, dan pembayaran tamu.
            </p>
        </div>

        <section class="space-y-4">
            <h2 class="text-lg font-semibold">Ringkasan</h2>
            <!-- Ubah lg:grid-cols-4 menjadi lg:grid-cols-5 agar muat 5 card -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <x-card>
                    <x-card-header>
                        <x-card-title class="text-base">Total Reservasi</x-card-title>
                    </x-card-header>
                    <x-card-content class="pb-6">
                        <p class="text-2xl font-semibold tabular-nums">{{ $stats['totalReservations'] }}</p>
                        <p class="text-xs text-muted-foreground">sepanjang waktu</p>
                    </x-card-content>
                </x-card>

                <x-card>
                    <x-card-header>
                        <x-card-title class="text-base">Terbooking</x-card-title>
                    </x-card-header>
                    <x-card-content class="pb-6">
                        <p class="text-2xl font-semibold tabular-nums">{{ $stats['booked'] }}</p>
                        <p class="text-xs text-muted-foreground">menunggu check-in</p>
                    </x-card-content>
                </x-card>

                <x-card>
                    <x-card-header>
                        <x-card-title class="text-base">Sedang Menginap</x-card-title>
                    </x-card-header>
                    <x-card-content class="pb-6">
                        <p class="text-2xl font-semibold tabular-nums">{{ $stats['checkedIn'] }}</p>
                        <p class="text-xs text-muted-foreground">status check-in aktif</p>
                    </x-card-content>
                </x-card>

                <!-- Card KPI Pendapatan Lain-lain (Belum Lunas) -->
                <x-card>
                    <x-card-header>
                        <x-card-title class="text-base">Pendapatan Lain-lain</x-card-title>
                    </x-card-header>
                    <x-card-content class="pb-6">
                        <p class="text-2xl font-semibold tabular-nums text-blue-600">
                            Rp{{ number_format($stats['dpRevenue'], 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-muted-foreground">kumpulan DP (belum lunas)</p>
                    </x-card-content>
                </x-card>

                <!-- Card KPI Pendapatan (Sudah Lunas) -->
                <x-card>
                    <x-card-header>
                        <x-card-title class="text-base">Pendapatan</x-card-title>
                    </x-card-header>
                    <x-card-content class="pb-6">
                        <p class="text-2xl font-semibold tabular-nums text-green-600">
                            Rp{{ number_format($stats['doneRevenue'], 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-muted-foreground">pembayaran masuk (lunas)</p>
                    </x-card-content>
                </x-card>
            </div>
        </section>
        <section class="space-y-4">
            <h2 class="text-lg font-semibold">Jadwal Pegawai Aktif - {{ $currentDay }}</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($todaySchedule as $shift => $employees)
                    <x-card>
                        <x-card-header>
                            <x-card-title class="text-base">Shift {{ $shift }}</x-card-title>
                        </x-card-header>
                        <x-card-content class="pb-6">
                            <ul class="space-y-1">
                                @foreach ($employees as $employee)
                                    <li class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                        {{ $employee }}
                                    </li>
                                @endforeach
                            </ul>
                        </x-card-content>
                    </x-card>
                @endforeach
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-end justify-between gap-4">
                <h2 class="text-lg font-semibold">Reservasi Aktif</h2>
                <x-button href="{{ route('reservations.index') }}" variant="outline" size="sm">Lihat semua</x-button>
            </div>

            @if ($activeReservations->isEmpty())
                <x-card>
                    <x-card-content class="py-8 text-center text-sm text-muted-foreground">
                        Tidak ada reservasi aktif.
                    </x-card-content>
                </x-card>
            @else
                <x-card>
                    <x-card-content>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b text-left text-muted-foreground">
                                        <th class="py-3 pr-4 font-medium">Kode</th>
                                        <th class="py-3 pr-4 font-medium">Pelanggan</th>
                                        <th class="py-3 pr-4 font-medium">Tanggal</th>
                                        <th class="py-3 pr-4 font-medium">Item</th>
                                        <th class="py-3 pr-4 font-medium">Status</th>
                                        <th class="py-3 pr-4 text-right font-medium">Tagihan</th>
                                        <th class="py-3 text-right font-medium">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activeReservations as $reservation)
                                        <tr class="border-b align-top last:border-0">
                                            <td class="py-3 pr-4 font-mono text-xs">
                                                <a href="{{ route('reservations.show', $reservation) }}"
                                                    class="text-primary hover:underline">{{ $reservation->booking_code }}</a>
                                            </td>
                                            <td class="py-3 pr-4">{{ $reservation->customerLabel() }}</td>
                                            <td class="py-3 pr-4 whitespace-nowrap">
                                                {{ $reservation->check_in_date->format('d M Y') }}
                                                → {{ $reservation->check_out_date->format('d M Y') }}
                                            </td>
                                            <td class="py-3 pr-4">
                                                {{ $reservation->items->map(fn ($item) => $item->item_type === 'room'
                                                    ? 'Kamar '.($item->room?->room_number ?? '-')
                                                    : 'Aula '.($item->hall?->hall_name ?? '-'))->implode(' + ') }}
                                            </td>
                                            <td class="py-3 pr-4">
                                                <x-status-badge :reservation="$reservation" />
                                            </td>
                                            <td class="py-3 pr-4 text-right whitespace-nowrap">
                                                Rp{{ number_format($reservation->total_amount, 0, ',', '.') }}
                                                <span class="block text-xs text-muted-foreground">
                                                    dibayar Rp{{ number_format($reservation->total_paid, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="py-3 text-right">
                                                <div class="flex flex-wrap justify-end gap-2">
                                                    @if ($reservation->status === 'booked')
                                                        @if (now()->toDateString() >= $reservation->check_in_date->toDateString())
                                                            <form method="POST" action="{{ route('reservations.check-in', $reservation) }}">
                                                                @csrf
                                                                <x-button type="submit" size="sm">Check-in</x-button>
                                                            </form>
                                                        @else
                                                            <span class="text-xs text-muted-foreground">
                                                                Check-in {{ $reservation->check_in_date->format('d M Y') }}
                                                            </span>
                                                        @endif
                                                    @endif

                                                    @if ($reservation->status === 'checked_in')
                                                        <form method="POST" action="{{ route('reservations.check-out', $reservation) }}">
                                                            @csrf
                                                            <x-button type="submit" size="sm" variant="destructive"
                                                                onclick="return confirm('Proses check-out sekarang? Denda dihitung otomatis dari jam server.')">
                                                                Check-out
                                                            </x-button>
                                                        </form>
                                                    @endif

                                                    @if ($reservation->remainingAmount() > 0)
                                                        <x-button href="{{ route('cashier.show', $reservation) }}"
                                                            size="sm" variant="outline">Kasir</x-button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-card-content>
                </x-card>
            @endif
        </section>

        <section class="space-y-4">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Menunggu Pelunasan</h2>
                    <p class="text-sm text-muted-foreground">3 tagihan teratas yang belum dilunasi.</p>
                </div>
                <x-button href="{{ route('reservations.index') }}" variant="outline" size="sm">Lihat semua</x-button>
            </div>

            @if ($unsettledReservations->isEmpty())
                <x-card>
                    <x-card-content class="py-8 text-center text-sm text-muted-foreground">
                        Tidak ada tagihan yang menunggu pelunasan.
                    </x-card-content>
                </x-card>
            @else
                <x-card>
                    <x-card-content>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b text-left text-muted-foreground">
                                        <th class="py-3 pr-4 font-medium">Kode</th>
                                        <th class="py-3 pr-4 font-medium">Pelanggan</th>
                                        <th class="py-3 pr-4 text-right font-medium">Denda</th>
                                        <th class="py-3 pr-4 text-right font-medium">Sisa Tagihan</th>
                                        <th class="py-3 text-right font-medium">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($unsettledReservations as $reservation)
                                        <tr class="border-b align-top last:border-0">
                                            <td class="py-3 pr-4 font-mono text-xs">
                                                <a href="{{ route('reservations.show', $reservation) }}"
                                                    class="text-primary hover:underline">{{ $reservation->booking_code }}</a>
                                            </td>
                                            <td class="py-3 pr-4">{{ $reservation->customerLabel() }}</td>
                                            <td class="py-3 pr-4 text-right">
                                                Rp{{ number_format($reservation->lateCharge(), 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 pr-4 text-right font-medium">
                                                Rp{{ number_format($reservation->remainingDue(), 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 text-right">
                                                <x-button href="{{ route('cashier.show', $reservation) }}" size="sm">
                                                    Buka Kasir
                                                </x-button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-card-content>
                </x-card>
            @endif
        </section>
    </div>
@endsection
