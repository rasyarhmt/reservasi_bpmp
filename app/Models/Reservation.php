<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'booking_code',
    'customer_id',
    'customer_name',
    'customer_phone',
    'check_in_date',
    'check_out_date',
    'scheduled_checkout_time',
    'status',
    'payment_scheme',
    'down_payment_amount',
    'total_amount',
    'total_paid',
    'payment_proof',
    'created_by',
])]
class Reservation extends Model
{
    /**
     * Nominal DP terkecil yang boleh dibayar (Rupiah).
     */
    public const MIN_DOWN_PAYMENT = 100000;

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date:Y-m-d',
            'check_out_date' => 'date:Y-m-d',
            'down_payment_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'total_paid' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customerLabel(): string
    {
        return $this->customer?->name ?? $this->customer_name ?? 'Walk-in';
    }

    public function paymentProofUrl(): ?string
    {
        if (!$this->payment_proof) {
            return null;
        }

        return asset('storage/' . $this->payment_proof);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function checkoutLog(): HasOne
    {
        return $this->hasOne(CheckoutLog::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function lateCharge(): float
    {
        return (float) ($this->checkoutLog?->total_late_charge ?? 0);
    }

    /**
     * Baris denda agregat per jenis item untuk halaman kasir & rincian reservasi.
     *
     * @return array<int, array{label: string, amount: float}>
     */
    public function lateChargeLines(): array
    {
        $log = $this->checkoutLog;

        if (!$log || (float) $log->late_hours <= 0 || (float) $log->total_late_charge <= 0) {
            return [];
        }

        $hours = (int) $log->late_hours;

        return collect(['room', 'hall'])
            ->map(function (string $type) use ($hours): ?array {
                $grouped = $this->items->where('item_type', $type);

                if ($grouped->isEmpty()) {
                    return null;
                }

                $rate = $type === 'room'
                    ? round((float) $grouped->first()->unit_price_snapshot / CheckoutLog::ROOM_DAY_HOURS)
                    : (float) $grouped->first()->unit_price_snapshot;

                $word = $type === 'room' ? 'kamar' : 'aula';

                return [
                    'label' => "Denda {$word} ({$hours} jam × Rp" . number_format($rate, 0, ',', '.') . ' × ' . $grouped->count() . " {$word})",
                    'amount' => (float) $hours * $rate * $grouped->count(),
                ];
            })
            ->filter(fn(?array $line) => $line !== null)
            ->values()
            ->all();
    }

    public function costBreakdowns(): array
    {
        return $this->items
            ->map(fn(ReservationItem $item): array => [
                'item_type' => $item->item_type,
                'name' => $item->item_type === 'room'
                    ? 'Kamar ' . ($item->room?->room_number ?? '-')
                    : ($item->hall?->hall_name ?? 'Aula'),
                'components' => $item->costComponents(),
            ])
            ->groupBy('item_type')
            ->map(function ($group): array {
                $first = $group->first();

                return [
                    'items' => $group->values()->all(),
                    'base_price' => $first['item_type'] === 'room'
                        ? (float) ($this->items->firstWhere('item_type', 'room')?->unit_price_snapshot ?? 0)
                        : (float) ($this->items->firstWhere('item_type', 'hall')?->hall?->base_price
                            ?? $this->items->firstWhere('item_type', 'hall')?->unit_price_snapshot
                            ?? 0),
                ];
            })
            ->all();
    }

    public function finalTotal(): float
    {
        return (float) $this->total_amount + $this->lateCharge();
    }

    public function remainingAmount(): float
    {
        return max(0, $this->finalTotal() - (float) $this->total_paid);
    }

    /**
     * Sisa tagihan yang harus dikumpulkan (dibulatkan ke atas), supaya
     * nominal pelunasan sama dengan yang ditampilkan dan pembayaran
     * penuh bisa dicatat tanpa sisa pecahan.
     */
    public function remainingDue(): float
    {
        return (float) ceil($this->remainingAmount());
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'booked' => 'Terbooking',
            'checked_in' => 'Sedang Inap',
            'checked_out' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Terbooking',
        };
    }

    public function paymentSchemeLabel(): string
    {
        if ($this->remainingAmount() <= 0) {
            return 'Lunas';
        }

        if ($this->payment_scheme !== 'dp') {
            return 'Lunas';
        }

        if ($this->down_payment_amount !== null) {
            return 'DP Rp' . number_format((float) $this->down_payment_amount, 0, ',', '.');
        }

        return 'DP';
    }
}
