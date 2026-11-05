@extends('layouts.app')

@section('title', 'Detail '.$reservation->booking_code)

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Detail — {{ $reservation->booking_code }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ $reservation->customerLabel() }}
                    · {{ $reservation->check_in_date->format('d M Y') }}
                    → {{ $reservation->check_out_date->format('d M Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <x-status-badge :reservation="$reservation" />
                <x-badge variant="outline">{{ $reservation->paymentSchemeLabel() }}</x-badge>
                <x-button href="{{ route('reservations.index') }}" variant="outline" size="sm">← Reservasi</x-button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
            <div class="space-y-6">
                <x-card>
                    <x-card-header>
                        <x-card-title class="text-base">Rincian Tagihan</x-card-title>
                    </x-card-header>
                    <x-card-content class="space-y-2 pb-6 text-sm">
                        @foreach ($reservation->items as $item)
                            @php
                                $nights = $reservation->check_in_date->diffInDays($reservation->check_out_date);
                                $extraTotal = $item->extra_guests * (float) $item->extra_person_rate_snapshot * $nights;
                            @endphp

                            <div class="flex justify-between gap-4">
                                <span class="text-muted-foreground">
                                    @if ($item->item_type === 'room')
                                        Kamar {{ $item->room?->room_number }} ·
                                        {{ $nights }} malam ·
                                        {{ $item->guest_count }} tamu
                                        @if ($item->extra_guests > 0)
                                            (+{{ $item->extra_guests }} ekstra)
                                        @endif
                                    @else
                                        Aula {{ $item->hall?->hall_name }} ·
                                        {{ number_format($item->hall_hours, 0, ',', '.') }} jam
                                        ({{ $item->hall_start_time->format('d M Y H:i') }} – {{ $item->hall_end_time->format('H:i') }})
                                    @endif
                                </span>
                                <span class="font-medium">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>

                            @if ($item->extra_guests > 0)
                                <div class="flex justify-between gap-4">
                                    <span class="pl-4 text-muted-foreground">
                                        Ekstra {{ $item->extra_guests }} orang × Rp{{ number_format($item->extra_person_rate_snapshot, 0, ',', '.') }} × {{ $nights }} malam
                                    </span>
                                    <span class="font-medium">Rp{{ number_format($extraTotal, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        @endforeach

                        @foreach ($reservation->lateChargeLines() as $line)
                            <div class="flex justify-between gap-4">
                                <span class="text-muted-foreground">{{ $line['label'] }}</span>
                                <span class="font-medium">Rp{{ number_format($line['amount'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach

                        <div class="flex justify-between border-t pt-2 font-semibold">
                            <span>Total Tagihan Final</span>
                            <span>Rp{{ number_format($reservation->finalTotal(), 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-muted-foreground">
                            <span>Sudah Dibayar</span>
                            <span>Rp{{ number_format($reservation->total_paid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-base font-semibold">
                            <span>Sisa Wajib Bayar</span>
                            <span class="{{ $reservation->remainingAmount() > 0 ? 'text-destructive' : 'text-success' }}">
                                Rp{{ number_format($reservation->remainingDue(), 0, ',', '.') }}
                            </span>
                        </div>
                    </x-card-content>
                </x-card>

                @if ($reservation->checkoutLog)
                    <x-card>
                        <x-card-header>
                            <x-card-title class="text-base">Check-out</x-card-title>
                        </x-card-header>
                        <x-card-content class="space-y-2 pb-6 text-sm text-muted-foreground">
                            <div class="flex justify-between gap-4">
                                <span>Waktu check-out</span>
                                <span>{{ $reservation->checkoutLog->actual_checkout_time->format('d M Y H:i') }}</span>
                            </div>
                            @if ($reservation->checkoutLog->late_hours > 0)
                                <div class="flex justify-between gap-4">
                                    <span>Keterlambatan</span>
                                    <span>{{ $reservation->checkoutLog->late_hours }} jam (Rp{{ number_format($reservation->checkoutLog->total_late_charge, 0, ',', '.') }})</span>
                                </div>
                            @endif
                        </x-card-content>
                    </x-card>
                @endif

                <x-card>
                    <x-card-header>
                        <x-card-title class="text-base">Riwayat Pembayaran</x-card-title>
                    </x-card-header>
                    <x-card-content>
                        @if ($reservation->payments->isEmpty())
                            <p class="py-4 text-center text-sm text-muted-foreground">Belum ada pembayaran.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b text-left text-muted-foreground">
                                            <th class="py-2 pr-4 font-medium">Waktu</th>
                                            <th class="py-2 pr-4 font-medium">Tahap</th>
                                            <th class="py-2 pr-4 font-medium">Metode</th>
                                            <th class="py-2 pr-4 font-medium">Bukti</th>
                                            <th class="py-2 text-right font-medium">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reservation->payments as $payment)
                                            <tr class="border-b last:border-0">
                                                <td class="py-2 pr-4 whitespace-nowrap">
                                                    {{ $payment->transaction_date->format('d M Y H:i') }}
                                                </td>
                                                <td class="py-2 pr-4">
                                                    <x-badge variant="outline">{{ $payment->stageLabel() }}</x-badge>
                                                </td>
                                                <td class="py-2 pr-4">{{ $payment->methodLabel() }}</td>
                                                <td class="py-2 pr-4">
                                                    @if ($payment->proofUrl())
                                                        <a href="{{ $payment->proofUrl() }}" target="_blank" rel="noopener"
                                                            class="text-xs font-medium underline-offset-4 hover:underline">
                                                            Lihat
                                                        </a>
                                                    @else
                                                        <span class="text-muted-foreground">—</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 text-right font-medium">
                                                    Rp{{ number_format($payment->amount, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </x-card-content>
                </x-card>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-20 lg:self-start">
                @if (auth()->user()->isAdmin() && $reservation->remainingAmount() > 0)
                    <x-card>
                        <x-card-header>
                            <x-card-title class="text-base">Tindakan</x-card-title>
                        </x-card-header>
                        <x-card-content class="space-y-2 pb-6">
                            <x-button href="{{ route('cashier.show', $reservation) }}" class="w-full">
                                Buka Kasir
                            </x-button>
                        </x-card-content>
                    </x-card>
                @endif

                @if (auth()->user()->isAdmin())
                    @foreach ($reservation->costBreakdowns() as $itemType => $data)
                        <x-cost-breakdown
                            :title="$itemType === 'room' ? 'Komponen Perhitungan Kamar' : 'Komponen Perhitungan Aula'"
                            :items="$data['items'][0]['components']"
                            :total="$data['base_price']"
                            :total-label="$itemType === 'room' ? 'Total per malam' : 'Total tarif per hari'"
                            note="Rincian biaya operasional per {{ $itemType === 'room' ? 'malam per kamar' : 'tarif per hari' }} — hanya untuk admin." />
                    @endforeach
                @endif
            </aside>
        </div>
    </div>
@endsection