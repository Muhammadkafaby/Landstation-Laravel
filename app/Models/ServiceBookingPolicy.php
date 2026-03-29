<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceBookingPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'slot_interval_minutes',
        'min_duration_minutes',
        'max_duration_minutes',
        'lead_time_minutes',
        'max_advance_days',
        'requires_unit_assignment',
        'walk_in_allowed',
        'online_booking_allowed',
    ];

    protected function casts(): array
    {
        return [
            'requires_unit_assignment' => 'boolean',
            'walk_in_allowed' => 'boolean',
            'online_booking_allowed' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
