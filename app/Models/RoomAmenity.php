<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['room_id', 'amenity_name'])]
class RoomAmenity extends Model
{
    public const UPDATED_AT = null;

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
