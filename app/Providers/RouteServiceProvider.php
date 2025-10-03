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
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
