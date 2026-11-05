<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reservation_id',
    'item_type',
    'room_id',
    'hall_id',
    'unit_price_snapshot',
    'cost_components',
    'guest_count',
    'extra_guests',
    'extra_person_rate_snapshot',
    'hall_start_time',
    'hall_end_time',
    'hall_hours',
    'subtotal',
])]
class ReservationItem extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'cost_components' => 'array',
            'hall_start_time' => 'datetime',
            'hall_end_time' => 'datetime',
            'subtotal' => 'decimal:2',
        ];
    }

    public function costComponents(): array
    {
        return $this->cost_components
            ?: ($this->item_type === 'room' ? Room::DEFAULT_COST_COMPONENTS : Hall::DEFAULT_COST_COMPONENTS);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }
}
