<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Service extends Model
{
    use HasFactory;

    public const TYPE_TIMED_UNIT = 'timed_unit';

    public const TYPE_MENU_ONLY = 'menu_only';

    public const BILLING_PER_MINUTE = 'per_minute';

    public const BILLING_FLAT = 'flat';

    protected $fillable = [
        'service_category_id',
        'code',
        'name',
        'slug',
        'service_type',
        'billing_type',
        'layout_mode',
        'layout_canvas_width',
        'layout_canvas_height',
        'layout_background_image_path',
        'layout_meta_json',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'layout_meta_json' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(ServiceUnit::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(ServicePricingRule::class);
    }

    public function bookingPolicy(): HasOne
    {
        return $this->hasOne(ServiceBookingPolicy::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function serviceSessions(): HasMany
    {
        return $this->hasMany(ServiceSession::class);
    }
}
