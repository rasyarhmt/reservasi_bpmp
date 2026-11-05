<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reservation_id',
    'processed_by',
    'actual_checkout_time',
    'late_hours',
    'hourly_charge_rate',
    'total_late_charge',
    'unpaid_base_amount',
    'final_settlement_amount',
    'is_settled',
    'notes',
])]
class CheckoutLog extends Model
{
    public const ROOM_DAY_HOURS = 21;

    public const GRACE_MINUTES = 15;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'actual_checkout_time' => 'datetime',
            'total_late_charge' => 'decimal:2',
            'unpaid_base_amount' => 'decimal:2',
            'final_settlement_amount' => 'decimal:2',
            'is_settled' => 'boolean',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
