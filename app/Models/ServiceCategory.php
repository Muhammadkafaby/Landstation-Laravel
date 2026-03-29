<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use HasFactory;

    public const CAFE = 'cafe';

    public const BILLIARD = 'billiard';

    public const PLAYSTATION = 'playstation';

    public const RENTAL_RC = 'rental-rc';

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
