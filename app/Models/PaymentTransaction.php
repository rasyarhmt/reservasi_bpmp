<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reservation_id',
    'checkout_log_id',
    'received_by',
    'payment_stage',
    'amount',
    'payment_method',
    'payment_proof',
    'transaction_date',
    'notes',
])]
class PaymentTransaction extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function stageLabel(): string
    {
        return match ($this->payment_stage) {
            'settlement' => 'Pelunasan',
            'late_charge_settlement' => 'Pelunasan Denda',
            default => 'DP',
        };
    }

    public function methodLabel(): string
    {
        return match ($this->payment_method) {
            'bank_transfer' => 'Transfer Bank',
            default => 'Tunai',
        };
    }

    public function proofUrl(): ?string
    {
        if (! $this->payment_proof) {
            return null;
        }

        return asset('storage/'.$this->payment_proof);
    }
}
