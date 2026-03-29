<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceUnit extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_OCCUPIED = 'occupied';

    public const STATUS_RESERVED = 'reserved';

    public const STATUS_MAINTENANCE = 'maintenance';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'service_id',
        'code',
        'name',
        'zone',
        'status',
        'capacity',
        'is_bookable',
        'is_active',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(ServicePricingRule::class, 'service_unit_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'service_unit_id');
    }

    public function serviceSessions(): HasMany
    {
        return $this->hasMany(ServiceSession::class, 'service_unit_id');
    }
}
