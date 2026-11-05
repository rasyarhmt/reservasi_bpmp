@extends('layouts.app')

@section('title', 'Kasir '.$reservation->booking_code)

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Kasir — {{ $reservation->booking_code }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ $reservation->customerLabel() }}
                    · {{ $reservation->check_in_date->format('d M Y') }}
                    → {{ $reservation->check_out_date->format('d M Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <x-status-badge :reservation="$reservation" />
                <x-badge variant="outline">{{ $reservation->paymentSchemeLabel() }}</x-badge>
                <x-button href="{{ route('dashboard') }}" variant="outline" size="sm">← Dashboard</x-button>
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
                            <div class="flex justify-between gap-4">
                                <span class="text-muted-foreground">
                                    @if ($item->item_type === 'room')
                                        Kamar {{ $item->room?->room_number }} ·
                                        {{ $reservation->check_in_date->diffInDays($reservation->check_out_date) }} malam ·
                                        {{ $item->guest_count }} tamu
                                        @if ($item->extra_guests > 0)
                                            (+{{ $item->extra_guests }} ekstra)
                                        @endif
                                    @else
                                        Aula {{ $item->hall?->hall_name }} ·
                                        {{ number_format($item->hall_hours, 0, ',', '.') }} jam
                                    @endif
                                </span>
                                <span class="font-medium">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach

                        @if ($reservation->checkoutLog && $reservation->checkoutLog->total_late_charge > 0)
                            @php
                                $lateHours = $reservation->checkoutLog->late_hours;
                                $roomItems = $reservation->items->where('item_type', 'room');
                                $hallItems = $reservation->items->where('item_type', 'hall');
                                $roomRate = $roomItems->isNotEmpty()
                                    ? round((float) $roomItems->first()->unit_price_snapshot / \App\Models\CheckoutLog::ROOM_DAY_HOURS)
                                    : 0;
                                $hallRate = $hallItems->isNotEmpty()
                                    ? (float) $hallItems->first()->unit_price_snapshot
                                    : 0;
                            @endphp
                            @if ($roomItems->isNotEmpty())
                                <div class="flex justify-between gap-4">
                                    <span class="text-muted-foreground">
                                        Denda kamar
                                        ({{ $lateHours }} jam × Rp{{ number_format($roomRate, 0, ',', '.') }} × {{ $roomItems->count() }} kamar)
                                    </span>
                                    <span class="font-medium">
                                        Rp{{ number_format($lateHours * $roomRate * $roomItems->count(), 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                            @if ($hallItems->isNotEmpty())
                                <div class="flex justify-between gap-4">
                                    <span class="text-muted-foreground">
                                        Denda aula
                                        ({{ $lateHours }} jam × Rp{{ number_format($hallRate, 0, ',', '.') }} × {{ $hallItems->count() }} aula)
                                    </span>
                                    <span class="font-medium">
                                        Rp{{ number_format($lateHours * $hallRate * $hallItems->count(), 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                        @endif

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
                                            <th class="py-2 pr-4 font-medium">Petugas</th>
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
                                                <td class="py-2 pr-4">{{ $payment->receivedBy?->name }}</td>
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

            <aside class="lg:sticky lg:top-20 lg:self-start">
                <x-card>
                    <x-card-header>
                        <x-card-title class="text-base">Terima Pembayaran</x-card-title>
                        <x-card-description>
                            Catat pembayaran tunai atau transfer.
                        </x-card-description>
                    </x-card-header>
                    <x-card-content class="pb-6">
                        @if ($reservation->remainingAmount() <= 0)
                            <x-alert type="success">Tagihan sudah lunas.</x-alert>
                        @else
                            <form method="POST" action="{{ route('cashier.pay', $reservation) }}" enctype="multipart/form-data"
                                class="space-y-4" x-data="{ method: '{{ old('payment_method', 'cash') }}' }">
                                @csrf

                                <div class="space-y-2">
                                    <x-label>Nominal (Rp)</x-label>
                                    <x-input name="amount" type="number" min="1"
                                        max="{{ (int) $reservation->remainingDue() }}"
                                        value="{{ old('amount', (int) $reservation->remainingDue()) }}" required />
                                    <x-input-error name="amount" />
                                </div>

                                <div class="space-y-2">
                                    <x-label>Metode</x-label>
                                    <x-select name="payment_method" x-model="method" required>
                                        <option value="cash">Tunai</option>
                                        <option value="bank_transfer">Transfer Bank</option>
                                    </x-select>
                                    <x-input-error name="payment_method" />
                                </div>

                                <div class="space-y-2" x-show="method === 'bank_transfer'" x-cloak>
                                    <x-label>Bukti Transfer <span class="font-normal text-muted-foreground">(wajib)</span></x-label>
                                    <input type="file" name="payment_proof" accept="image/png,image/jpeg,application/pdf"
                                        x-bind:required="method === 'bank_transfer'"
                                        class="block w-full cursor-pointer rounded-md border border-input text-sm file:mr-3 file:border-0 file:bg-muted file:px-3 file:py-2 file:text-sm file:font-medium file:text-foreground hover:file:bg-accent" />
                                    <x-input-error name="payment_proof" />
                                    <p class="text-xs text-muted-foreground">JPG/PNG/PDF, maks. 2 MB.</p>
                                </div>

                                <div class="space-y-2">
                                    <x-label>Catatan <span class="font-normal text-muted-foreground">(opsional)</span></x-label>
                                    <x-input name="notes" placeholder="Misal: transfer BCA" />
                                    <x-input-error name="notes" />
                                </div>

                                <x-button type="submit" class="w-full">Simpan Pembayaran</x-button>
                            </form>
                        @endif
                    </x-card-content>
                </x-card>
            </aside>
        </div>
    </div>
@endsection
