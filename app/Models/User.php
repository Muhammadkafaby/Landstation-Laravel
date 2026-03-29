<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function createdBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'created_by_user_id');
    }

    public function startedServiceSessions(): HasMany
    {
        return $this->hasMany(ServiceSession::class, 'started_by_user_id');
    }

    public function closedServiceSessions(): HasMany
    {
        return $this->hasMany(ServiceSession::class, 'closed_by_user_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isStaff(): bool
    {
        return $this->isActive() && $this->role_id !== null;
    }

    public function hasRole(string $roleCode): bool
    {
        $this->loadMissing('role');

        return $this->role?->code === $roleCode;
    }

    public function permissionCodes(): array
    {
        $this->loadMissing('role.permissions');

        if ($this->role === null) {
            return [];
        }

        return $this->role->permissions
            ->pluck('code')
            ->values()
            ->all();
    }

    public function hasPermission(string $permissionCode): bool
    {
        if (! $this->isStaff()) {
            return false;
        }

        if ($this->hasRole(Role::SUPER_ADMIN)) {
            return true;
        }

        return in_array($permissionCode, $this->permissionCodes(), true);
    }

    public function canAccessAdmin(): bool
    {
        return $this->hasPermission(Permission::ACCESS_ADMIN);
    }

    public function canAccessPos(): bool
    {
        return $this->hasPermission(Permission::ACCESS_POS);
    }

    public function defaultLandingRouteName(): string
    {
        if ($this->canAccessAdmin()) {
            return 'dashboard';
        }

        if ($this->canAccessPos()) {
            return 'pos.index';
        }

        return 'profile.edit';
    }
}
