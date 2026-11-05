<tr class="border-b align-top last:border-0">
    <td class="py-3 pr-4">
        <a href="{{ route('reservations.show', $reservation) }}"
            class="inline-flex items-center gap-1 font-mono text-xs font-semibold text-foreground underline-offset-4 hover:underline"
            title="Lihat detail {{ $reservation->booking_code }}">
            {{ $reservation->booking_code }}
        </a>
    </td>
    @if (auth()->user()->isAdmin())
        <td class="py-3 pr-4">{{ $reservation->customerLabel() }}</td>
    @endif
    <td class="py-3 pr-4 whitespace-nowrap">
        {{ $reservation->check_in_date->format('d M Y') }}
        <span class="text-muted-foreground">→</span>
        {{ $reservation->check_out_date->format('d M Y') }}
    </td>
    <td class="py-3 pr-4">
        @foreach ($reservation->items as $index => $item)
            <div class="{{ $index > 0 ? 'mt-1' : '' }}">
                @if ($item->item_type === 'room')
                    <span class="text-muted-foreground">Kamar</span>
                    {{ $item->room?->room_number }}
                    <span class="text-muted-foreground">·
                        {{ $reservation->check_in_date->diffInDays($reservation->check_out_date) }} malam ·
                        {{ $item->guest_count }} tamu
                        @if ($item->extra_guests > 0)
                            (+{{ $item->extra_guests }} ekstra)
                        @endif
                    </span>
                @else
                    <span class="text-muted-foreground">Aula</span>
                    {{ $item->hall?->hall_name }}
                    <span class="text-muted-foreground">·
                        {{ number_format($item->hall_hours, 0, ',', '.') }} jam ·
                        {{ $item->hall_start_time->format('d M Y H:i') }} – {{ $item->hall_end_time->format('H:i') }}
                    </span>
                @endif
                <span class="font-medium">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
        @endforeach
        <div class="mt-1 border-t pt-1">
            <span class="text-muted-foreground">Total</span>
            <span class="font-semibold">Rp{{ number_format($reservation->total_amount, 0, ',', '.') }}</span>
        </div>
    </td>
    <td class="py-3 pr-4">
        <div class="flex flex-wrap gap-1">
            <x-status-badge :reservation="$reservation" />
            <x-badge variant="outline">{{ $reservation->paymentSchemeLabel() }}</x-badge>
        </div>
        <div class="mt-1 text-xs text-muted-foreground">
            Dibayar Rp{{ number_format($reservation->total_paid, 0, ',', '.') }}
        </div>
    </td>
    <td class="py-3 text-right">
        <div class="flex flex-wrap justify-end gap-1">
            @if (auth()->user()->isAdmin())
                @if ($reservation->remainingAmount() > 0)
                    <x-button href="{{ route('cashier.show', $reservation) }}" variant="ghost" size="icon-sm" title="Proses Pembayaran">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="2" y="6" width="20" height="12" rx="2" />
                            <circle cx="12" cy="12" r="2" />
                            <path d="M6 12h.01M18 12h.01" />
                        </svg>
                    </x-button>
                @endif

                @if ($reservation->status === 'booked')
                    @if (now()->toDateString() >= $reservation->check_in_date->toDateString())
                        <form method="POST" action="{{ route('reservations.check-in', $reservation) }}">
                            @csrf
                            <x-button type="submit" variant="ghost" size="icon-sm" title="Check-in">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9 14l6-6" />
                                    <path d="M3 12a9 9 0 1018 0 9 9 0 00-18 0" />
                                    <path d="M13 6l6 6" />
                                </svg>
                            </x-button>
                        </form>
                    @endif
                @endif

                @if ($reservation->status === 'checked_in')
                    <form method="POST" action="{{ route('reservations.check-out', $reservation) }}"
                        onsubmit="return confirm('Proses check-out sekarang? Denda dihitung otomatis dari jam server.')">
                        @csrf
                        <x-button type="submit" variant="ghost" size="icon-sm" title="Check-out">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <path d="M16 17l5-5-5-5" />
                                <path d="M21 12H9" />
                            </svg>
                        </x-button>
                    </form>
                @endif
            @endif

            @if ($reservation->paymentProofUrl())
                <x-button href="{{ $reservation->paymentProofUrl() }}" target="_blank" rel="noopener" variant="ghost" size="icon-sm" title="Bukti transfer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                    </svg>
                </x-button>
            @endif
        </div>
    </td>
</tr>