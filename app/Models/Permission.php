<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    public const ACCESS_ADMIN = 'access-admin';
    public const ACCESS_POS = 'access-pos';
    public const MANAGE_USERS = 'manage-users';
    public const MANAGE_SETTINGS = 'manage-settings';
    public const MANAGE_MASTER_DATA = 'manage-master-data';
    public const MANAGE_BOOKINGS = 'manage-bookings';
    public const MANAGE_PAYMENTS = 'manage-payments';

    protected $fillable = [
        'code',
        'name',
        'module',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}
