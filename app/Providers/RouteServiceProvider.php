<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as BaseProvider;

class RouteServiceProvider extends BaseProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Return a path to redirect a user by role.
     */
    public static function redirectTo(?string $role): string
    {
        return match($role) {
            'admin'     => '/admin',
            'staff'     => '/staff',
            'adviser'   => '/adviser',
            'priest'    => '/priest',
            default     => '/requestor',
        };
    }

    /**
     * Get the named route for a role (used for redirect()->route()).
     */
    public static function routeNameForRole(?string $role): string
    {
        return match($role) {
            'admin'   => 'admin.dashboard',
            'staff'   => 'staff.dashboard',
            'adviser' => 'adviser.dashboard',
            'priest'  => 'priest.dashboard',
            default   => 'requestor.dashboard',
        };
    }

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
