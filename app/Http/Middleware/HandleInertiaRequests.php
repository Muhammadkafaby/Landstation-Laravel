<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user()?->loadMissing('role.permissions');

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user
                    ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'status' => $user->status,
                        'last_login_at' => $user->last_login_at,
                        'role' => $user->role
                            ? [
                                'id' => $user->role->id,
                                'code' => $user->role->code,
                                'name' => $user->role->name,
                            ]
                            : null,
                        'permissions' => $user->permissionCodes(),
                        'capabilities' => [
                            'accessAdmin' => $user->canAccessAdmin(),
                            'accessPos' => $user->canAccessPos(),
                            'manageUsers' => $user->hasPermission(Permission::MANAGE_USERS),
                            'manageSettings' => $user->hasPermission(Permission::MANAGE_SETTINGS),
                            'manageMasterData' => $user->hasPermission(Permission::MANAGE_MASTER_DATA),
                            'manageBookings' => $user->hasPermission(Permission::MANAGE_BOOKINGS),
                            'managePayments' => $user->hasPermission(Permission::MANAGE_PAYMENTS),
                        ],
                    ]
                    : null,
            ],
        ];
    }
}
