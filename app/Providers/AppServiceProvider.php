<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\Notification;
use App\Models\OrganizationBookingRequest;
use App\Models\Reservation;
use App\Models\ReservationCancellation;
use App\Models\User;
use App\Observers\MessageObserver;
use App\Observers\NotificationObserver;
use App\Observers\OrganizationBookingRequestObserver;
use App\Observers\ReservationCancellationObserver;
use App\Observers\ReservationObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Model Observers for SSE real-time updates
        Reservation::observe(ReservationObserver::class);
        Message::observe(MessageObserver::class);
        Notification::observe(NotificationObserver::class);
        User::observe(UserObserver::class);
        OrganizationBookingRequest::observe(OrganizationBookingRequestObserver::class);
        ReservationCancellation::observe(ReservationCancellationObserver::class);
    }
}
