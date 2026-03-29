<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePricingRule extends Model
{
    use HasFactory;

    public const MODEL_PER_INTERVAL = 'per_interval';

    public const MODEL_FLAT = 'flat';

    protected $fillable = [
        'service_id',
        'service_unit_id',
        'pricing_model',
        'billing_interval_minutes',
        'base_price_rupiah',
        'price_per_interval_rupiah',
        'minimum_charge_rupiah',
        'starts_at',
        'ends_at',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ServiceUnit::class, 'service_unit_id');
    }
}
