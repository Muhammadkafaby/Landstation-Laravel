<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CHECKED_IN = 'checked_in';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    public const SOURCE_PUBLIC = 'public';

    public const SOURCE_ADMIN = 'admin';

    public const SOURCE_POS = 'pos';

    protected $fillable = [
        'booking_code',
        'customer_id',
        'service_id',
        'service_unit_id',
        'status',
        'booking_source',
        'start_at',
        'end_at',
        'duration_minutes',
        'pricing_snapshot_json',
        'notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'pricing_snapshot_json' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ServiceUnit::class, 'service_unit_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function serviceSessions(): HasMany
    {
        return $this->hasMany(ServiceSession::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
