<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    public const CASH = 'cash';

    public const QRIS_MANUAL = 'qris_manual';

    protected $fillable = [
        'code',
        'name',
        'channel',
        'is_active',
        'sort_order',
        'metadata_json',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata_json' => 'array',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_method_code', 'code');
    }
}
