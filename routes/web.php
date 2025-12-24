<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// CSRF Token Refresh Route (for preventing 419 errors on long-open pages)
Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
});

// Availability API Routes (for real-time double-booking prevention)
Route::middleware(['auth'])->prefix('api/availability')->name('api.availability.')->group(function () {
    Route::post('/check', [\App\Http\Controllers\Api\AvailabilityController::class, 'checkAvailability'])->name('check');
    Route::post('/priests', [\App\Http\Controllers\Api\AvailabilityController::class, 'getAvailablePriests'])->name('priests');
    Route::post('/venues', [\App\Http\Controllers\Api\AvailabilityController::class, 'getAvailableVenues'])->name('venues');
    Route::post('/times', [\App\Http\Controllers\Api\AvailabilityController::class, 'getAvailableTimes'])->name('times');
    Route::post('/summary', [\App\Http\Controllers\Api\AvailabilityController::class, 'getDateSummary'])->name('summary');
});

Route::get('/', [\App\Http\Controllers\WelcomeController::class, 'index']);

// Public Calendar Route (accessible to everyone)
Route::get('/calendar', [\App\Http\Controllers\PublicCalendarController::class, 'index'])->name('calendar.public');
Route::get('/calendar/schedules', [\App\Http\Controllers\PublicCalendarController::class, 'getSchedules'])->name('calendar.public.schedules');

// Development helper: allow a logged-in pending user to mark their email
// as verified so they can reach role-specific pages during local testing.
// This route is only registered when the application environment is 'local'.
if (app()->environment('local')) {
    Route::get('/dev/verify-me', function () {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return redirect('/');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
            $user->status = 'active';
            $user->save();

            // Fire the Verified event so any listeners run (for consistency)
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        // Redirect to dashboard which will forward to the role-specific page
        return redirect()->route('dashboard');
    })->middleware('auth')->name('dev.verify');

    // Manual test route to trigger unnoticed reservations check
    Route::get('/dev/test-unnoticed', function () {
        if (Auth::user()?->role !== 'admin') {
            return response('Unauthorized', 403);
        }

        \Illuminate\Support\Facades\Artisan::call('reservations:check-unnoticed', ['--send-notifications' => true]);
        
        return response(
            '<pre>' . htmlspecialchars(\Illuminate\Support\Facades\Artisan::output()) . '</pre>' .
            '<p><a href="' . route('staff.notifications.index') . '">View Staff Notifications</a></p>',
            200,
            ['Content-Type' => 'text/html']
        );
    })->middleware('auth')->name('dev.test-unnoticed');

    // Debug notifications page
    Route::get('/dev/debug-notifications', [\App\Http\Controllers\DebugController::class, 'notifications'])->middleware('auth')->name('dev.debug-notifications');
}

// Default dashboard: redirect users to their role-specific dashboard
Route::get('/dashboard', function () {
    $user = Auth::user();
    if (! $user) {
        return redirect('/');
    }

    // Prefer the `role` attribute (enum). Fallback to user_role_id mapping if needed.
    $role = $user->role ?? null;

    // If role is missing but user_role_id exists, try to resolve it.
    if (! $role && $user->user_role_id) {
        $role = optional(DB::table('user_roles')->where('user_role_id', $user->user_role_id)->first())->role_name;
    }

    switch ($role) {
        case 'admin':
            return redirect()->route('admin.dashboard');
        case 'staff':
            return redirect()->route('staff.dashboard');
        case 'adviser':
            return redirect()->route('adviser.dashboard');
        case 'priest':
            return redirect()->route('priest.dashboard');
        case 'requestor':
            return redirect()->route('requestor.dashboard');
        default:
            // If role is unknown, redirect to home (no generic dashboard page)
            return redirect('/');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes (for authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/picture', [ProfileController::class, 'uploadPicture'])->name('profile.picture.upload');
    Route::delete('/profile/picture', [ProfileController::class, 'removePicture'])->name('profile.picture.remove');
});

// ==========================
// Chat Routes (Requestor & Admin)
// ==========================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{userId}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/send', [ChatController::class, 'store'])->name('chat.send');
    // FAQ auto-reply endpoint used by chat index view
    Route::post('/chat/send-faq', [ChatController::class, 'sendFaqAutoReply'])->name('chat.send-faq');
    Route::get('/chat/messages/{userId}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::get('/chat/unread/count', [ChatController::class, 'unreadCount'])->name('chat.unread.count');
    Route::post('/chat/mark-read/{userId}', [ChatController::class, 'markAsRead'])->name('chat.mark-read');
    // Temporary debug endpoint to inspect unread sources (admin-only)
    Route::get('/dev/chat/unread/debug', [ChatController::class, 'debugUnread'])->name('chat.unread.debug');
    // Temporary debug endpoint to mark ALL unread as read for current user (admin-only)
    Route::post('/dev/chat/unread/mark-all', [ChatController::class, 'debugMarkAllUnreadAsRead'])->name('chat.unread.mark-all');
});

// Authentication routes (Laravel Breeze/Jetstream/etc.)
require __DIR__.'/auth.php';

// ==========================
// Role-based dashboards
// ==========================
// Use the middleware class name with parameter here instead of the alias string
// to avoid Laravel attempting to resolve the alias as a class during middleware
// termination (which can produce "Target class [role] does not exist.").
Route::get('/admin', fn () => view('admin.dashboard'))
    ->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':admin'])
    ->name('admin.dashboard');

Route::get('/staff', fn () => view('staff.dashboard'))
    ->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':staff'])
    ->name('staff.dashboard');

// Staff organization management
Route::prefix('staff')->name('staff.')->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':staff'])->group(function () {
    Route::get('/organizations', [\App\Http\Controllers\Staff\OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/archives', [\App\Http\Controllers\Staff\OrganizationController::class, 'archives'])->name('organizations.archives');
    Route::get('/organizations/create', [\App\Http\Controllers\Staff\OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [\App\Http\Controllers\Staff\OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/{org_id}/edit', [\App\Http\Controllers\Staff\OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::put('/organizations/{org_id}', [\App\Http\Controllers\Staff\OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('/organizations/{org_id}', [\App\Http\Controllers\Staff\OrganizationController::class, 'destroy'])->name('organizations.destroy');
    Route::post('/organizations/{org_id}/restore', [\App\Http\Controllers\Staff\OrganizationController::class, 'restore'])->name('organizations.restore');
    Route::delete('/organizations/{org_id}/force-delete', [\App\Http\Controllers\Staff\OrganizationController::class, 'forceDestroy'])->name('organizations.force-destroy');
    
    // Notification Routes
    Route::get('/notifications', [\App\Http\Controllers\Staff\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/archived', [\App\Http\Controllers\Staff\NotificationController::class, 'archived'])->name('notifications.archived');
    Route::get('/notifications/count', [\App\Http\Controllers\Staff\NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Staff\NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Staff\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/clear-all', [\App\Http\Controllers\Staff\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::post('/notifications/{id}/archive', [\App\Http\Controllers\Staff\NotificationController::class, 'archive'])->name('notifications.archive');
    Route::post('/notifications/{id}/restore', [\App\Http\Controllers\Staff\NotificationController::class, 'restore'])->name('notifications.restore');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Staff\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/{id}', [\App\Http\Controllers\Staff\NotificationController::class, 'show'])->name('notifications.show');
    
    // Calendar Management Routes
    Route::get('/calendar', [\App\Http\Controllers\Staff\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/schedules', [\App\Http\Controllers\Staff\CalendarController::class, 'getSchedules'])->name('calendar.schedules');
    Route::post('/calendar', [\App\Http\Controllers\Staff\CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/{id}', [\App\Http\Controllers\Staff\CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{id}', [\App\Http\Controllers\Staff\CalendarController::class, 'destroy'])->name('calendar.destroy');
});

Route::get('/adviser', fn () => view('adviser.dashboard'))
    ->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':adviser'])
    ->name('adviser.dashboard');

Route::get('/priest', fn () => view('priest.dashboard'))
    ->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':priest'])
    ->name('priest.dashboard');

Route::get('/requestor', fn () => view('requestor.dashboard'))
    ->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':requestor'])
    ->name('requestor.dashboard');

// Admin user management route - only admins can delete other user accounts
Route::delete('/admin/users/{id}', [\App\Http\Controllers\Admin\UserManagementController::class, 'destroy'])
    ->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin'])
    ->name('admin.users.destroy');

Route::post('/admin/users/{id}/approve', [\App\Http\Controllers\Admin\UserApprovalController::class, 'approve'])
    ->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin'])
    ->name('admin.users.approve');

// Admin user CRUD (list, create, store, edit, update)
Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/archives', [\App\Http\Controllers\Admin\UserManagementController::class, 'archives'])->name('users.archives');
    Route::get('/users/create', [\App\Http\Controllers\Admin\UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [\App\Http\Controllers\Admin\UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [\App\Http\Controllers\Admin\UserManagementController::class, 'update'])->name('users.update');
    Route::post('/users/{id}/restore', [\App\Http\Controllers\Admin\UserManagementController::class, 'restore'])->name('users.restore');
    Route::delete('/users/{id}/force-delete', [\App\Http\Controllers\Admin\UserManagementController::class, 'forceDestroy'])->name('users.force-destroy');
});

// ==========================
// Reservation Management Routes
// ==========================

// Requestor Reservation Routes
Route::prefix('requestor')->name('requestor.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':requestor'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Requestor\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/calendar', [\App\Http\Controllers\Requestor\ReservationController::class, 'calendar'])->name('reservations.calendar');
    Route::get('/reservations/create', [\App\Http\Controllers\Requestor\ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [\App\Http\Controllers\Requestor\ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Requestor\ReservationController::class, 'show'])->name('reservations.show');
    Route::get('/reservations/{reservation_id}/edit', [\App\Http\Controllers\Requestor\ReservationController::class, 'edit'])->name('reservations.edit');
    Route::post('/reservations/{reservation_id}/update', [\App\Http\Controllers\Requestor\ReservationController::class, 'update'])->name('reservations.update');
    Route::get('/reservations/{reservation_id}/confirm/{token}', [\App\Http\Controllers\Requestor\ReservationController::class, 'showConfirmation'])->name('reservations.show-confirmation');
    Route::post('/reservations/{reservation_id}/confirm/{token}', [\App\Http\Controllers\Requestor\ReservationController::class, 'confirmReservation'])->name('reservations.confirm-reservation');
    Route::post('/reservations/{reservation_id}/decline/{token}', [\App\Http\Controllers\Requestor\ReservationController::class, 'declineReservation'])->name('reservations.decline-reservation');
    Route::post('/reservations/{reservation_id}/cancel', [\App\Http\Controllers\Requestor\ReservationController::class, 'cancel'])->name('reservations.cancel');

    // Notification Routes
    Route::get('/notifications', [\App\Http\Controllers\Requestor\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/archived', [\App\Http\Controllers\Requestor\NotificationController::class, 'archived'])->name('notifications.archived');
    Route::get('/notifications/count', [\App\Http\Controllers\Requestor\NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Requestor\NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Requestor\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/clear-all', [\App\Http\Controllers\Requestor\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::post('/notifications/{id}/archive', [\App\Http\Controllers\Requestor\NotificationController::class, 'archive'])->name('notifications.archive');
    Route::post('/notifications/{id}/restore', [\App\Http\Controllers\Requestor\NotificationController::class, 'restore'])->name('notifications.restore');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Requestor\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::get('/notifications/{id}', [\App\Http\Controllers\Requestor\NotificationController::class, 'show'])->name('notifications.show');

    // Organization Booking Routes
    Route::get('/organization-bookings', [\App\Http\Controllers\Requestor\OrganizationBookingController::class, 'index'])->name('organization-bookings.index');
    Route::get('/organization-bookings/create', [\App\Http\Controllers\Requestor\OrganizationBookingController::class, 'create'])->name('organization-bookings.create');
    Route::post('/organization-bookings', [\App\Http\Controllers\Requestor\OrganizationBookingController::class, 'store'])->name('organization-bookings.store');
    Route::get('/organization-bookings/{organizationBookingRequest}', [\App\Http\Controllers\Requestor\OrganizationBookingController::class, 'show'])->name('organization-bookings.show');
    Route::get('/organization-bookings/{organizationBookingRequest}/edit', [\App\Http\Controllers\Requestor\OrganizationBookingController::class, 'edit'])->name('organization-bookings.edit');
    Route::put('/organization-bookings/{organizationBookingRequest}', [\App\Http\Controllers\Requestor\OrganizationBookingController::class, 'update'])->name('organization-bookings.update');
    Route::delete('/organization-bookings/{organizationBookingRequest}', [\App\Http\Controllers\Requestor\OrganizationBookingController::class, 'destroy'])->name('organization-bookings.destroy');
    Route::get('/organizations/{organization}/details', [\App\Http\Controllers\Requestor\OrganizationBookingController::class, 'getOrganizationDetails'])->name('organizations.details');
});

// Adviser Reservation Routes
Route::prefix('adviser')->name('adviser.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':adviser'])->group(function () {
    // Notification Routes
    Route::get('/notifications', [\App\Http\Controllers\Adviser\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/archived', [\App\Http\Controllers\Adviser\NotificationController::class, 'archived'])->name('notifications.archived');
    Route::get('/notifications/count', [\App\Http\Controllers\Adviser\NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Adviser\NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Adviser\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/clear-all', [\App\Http\Controllers\Adviser\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::post('/notifications/{id}/archive', [\App\Http\Controllers\Adviser\NotificationController::class, 'archive'])->name('notifications.archive');
    Route::post('/notifications/{id}/restore', [\App\Http\Controllers\Adviser\NotificationController::class, 'restore'])->name('notifications.restore');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Adviser\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::get('/notifications/{id}', [\App\Http\Controllers\Adviser\NotificationController::class, 'show'])->name('notifications.show');

    // Cancellation Routes
    Route::get('/cancellations/{id}', [\App\Http\Controllers\Adviser\CancellationController::class, 'show'])->name('cancellations.show');
    Route::post('/cancellations/{id}/confirm', [\App\Http\Controllers\Adviser\CancellationController::class, 'confirm'])->name('cancellations.confirm');

    // Reservation Routes
    Route::get('/reservations', [\App\Http\Controllers\Adviser\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/calendar', [\App\Http\Controllers\Adviser\ReservationController::class, 'calendar'])->name('reservations.calendar');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Adviser\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/approve', [\App\Http\Controllers\Adviser\ReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('/reservations/{reservation_id}/reject', [\App\Http\Controllers\Adviser\ReservationController::class, 'reject'])->name('reservations.reject');
    Route::post('/reservations/{reservation_id}/cancel-approval', [\App\Http\Controllers\Adviser\ReservationController::class, 'cancelApproval'])->name('reservations.cancel-approval');
    Route::post('/reservations/{reservation_id}/cancel', [\App\Http\Controllers\Adviser\ReservationController::class, 'cancel'])->name('reservations.cancel');

    // Organization Booking Routes
    Route::get('/organization-bookings', [\App\Http\Controllers\Adviser\OrganizationBookingController::class, 'index'])->name('organization-bookings.index');
    Route::get('/organization-bookings/calendar', [\App\Http\Controllers\Adviser\OrganizationBookingController::class, 'calendar'])->name('organization-bookings.calendar');
    Route::get('/organization-bookings/calendar-data', [\App\Http\Controllers\Adviser\OrganizationBookingController::class, 'calendarData'])->name('organization-bookings.calendar-data');
    Route::get('/organization-bookings/summary', [\App\Http\Controllers\Adviser\OrganizationBookingController::class, 'summary'])->name('organization-bookings.summary');
    Route::get('/organization-bookings/{organizationBookingRequest}', [\App\Http\Controllers\Adviser\OrganizationBookingController::class, 'show'])->name('organization-bookings.show');
    Route::post('/organization-bookings/{organizationBookingRequest}/approve', [\App\Http\Controllers\Adviser\OrganizationBookingController::class, 'approve'])->name('organization-bookings.approve');
    Route::post('/organization-bookings/{organizationBookingRequest}/reject', [\App\Http\Controllers\Adviser\OrganizationBookingController::class, 'reject'])->name('organization-bookings.reject');
});

// Admin Reservation Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':admin'])->group(function () {
    // Cancellations Index
    Route::get('/cancellations', [\App\Http\Controllers\Admin\CancellationController::class, 'index'])->name('cancellations.index');
    Route::get('/reservations', [\App\Http\Controllers\Admin\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Admin\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/assign-priest', [\App\Http\Controllers\Admin\ReservationController::class, 'assignPriest'])->name('reservations.assign-priest');
    Route::post('/reservations/{reservation_id}/reject', [\App\Http\Controllers\Admin\ReservationController::class, 'reject'])->name('reservations.reject');
    Route::post('/reservations/{reservation_id}/cancel', [\App\Http\Controllers\Admin\ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::post('/reservations/{reservation_id}/confirm-external', [\App\Http\Controllers\Admin\ReservationController::class, 'confirmExternal'])->name('reservations.confirm-external');
    Route::post('/reservations/{reservation_id}/final-approve', [\App\Http\Controllers\Admin\ReservationController::class, 'finalApprove'])->name('reservations.final-approve');

    // Admin Service Routes (when admin is assigned as priest)
    Route::get('/services', [\App\Http\Controllers\Admin\ServiceController::class, 'index'])->name('services.index');
    // Unified calendar route replaces old services.calendar for admin
    Route::get('/calendar', [\App\Http\Controllers\Admin\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/services/declined', [\App\Http\Controllers\Admin\ServiceController::class, 'declined'])->name('services.declined');
    
    // Service Management Routes (MUST be before parameterized routes)
    Route::get('/services/manage', [\App\Http\Controllers\Admin\ServiceManagementController::class, 'index'])->name('services.manage');
    Route::post('/services/manage', [\App\Http\Controllers\Admin\ServiceManagementController::class, 'store'])->name('services.manage.store');
    Route::put('/services/manage/{id}', [\App\Http\Controllers\Admin\ServiceManagementController::class, 'update'])->name('services.manage.update');
    Route::delete('/services/manage/{id}', [\App\Http\Controllers\Admin\ServiceManagementController::class, 'destroy'])->name('services.manage.destroy');
    
    // Parameterized service routes (MUST be after specific routes)
    Route::post('/services/{reservation_id}/confirm', [\App\Http\Controllers\Admin\ServiceController::class, 'confirm'])->name('services.confirm');
    Route::post('/services/{reservation_id}/decline', [\App\Http\Controllers\Admin\ServiceController::class, 'decline'])->name('services.decline');
    Route::get('/services/{reservation_id}', [\App\Http\Controllers\Admin\ServiceController::class, 'show'])->name('services.show');

    // Cancellation Routes
    Route::get('/cancellations/{id}', [\App\Http\Controllers\Admin\CancellationController::class, 'show'])->name('cancellations.show');
    Route::post('/cancellations/{id}/confirm', [\App\Http\Controllers\Admin\CancellationController::class, 'confirm'])->name('cancellations.confirm');

    // Change Request Routes (for reservation edits)
    Route::get('/change-requests', [\App\Http\Controllers\Admin\ChangeRequestController::class, 'index'])->name('change-requests.index');
    Route::get('/change-requests/{id}', [\App\Http\Controllers\Admin\ChangeRequestController::class, 'show'])->name('change-requests.show');
    Route::post('/change-requests/{id}/approve', [\App\Http\Controllers\Admin\ChangeRequestController::class, 'approve'])->name('change-requests.approve');
    Route::post('/change-requests/{id}/reject', [\App\Http\Controllers\Admin\ChangeRequestController::class, 'reject'])->name('change-requests.reject');

    // Organizations (read-only admin view)
    Route::get('/organizations', [\App\Http\Controllers\Admin\OrganizationController::class, 'index'])->name('organizations.index');

    // Organization Booking Routes (Admin oversight and management)
    Route::get('/organization-bookings', [\App\Http\Controllers\Admin\OrganizationBookingController::class, 'index'])->name('organization-bookings.index');
    Route::get('/organization-bookings/reports', [\App\Http\Controllers\Admin\OrganizationBookingController::class, 'reports'])->name('organization-bookings.reports');
    Route::get('/organization-bookings/{organizationBookingRequest}', [\App\Http\Controllers\Admin\OrganizationBookingController::class, 'show'])->name('organization-bookings.show');
    Route::post('/organization-bookings/{organizationBookingRequest}/reassign-adviser', [\App\Http\Controllers\Admin\OrganizationBookingController::class, 'reassignAdviser'])->name('organization-bookings.reassign-adviser');
});

// Shared Notification Routes for Admin and Staff (same controller, broader access)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':admin,staff'])->group(function () {
    // Notification Routes (specific routes first, then parameterized)
    Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [\App\Http\Controllers\Admin\NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Admin\NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::get('/notifications/archived', [\App\Http\Controllers\Admin\NotificationController::class, 'archived'])->name('notifications.archived');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/clear-all', [\App\Http\Controllers\Admin\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::post('/notifications/{id}/archive', [\App\Http\Controllers\Admin\NotificationController::class, 'archive'])->name('notifications.archive');
    Route::post('/notifications/{id}/restore', [\App\Http\Controllers\Admin\NotificationController::class, 'restore'])->name('notifications.restore');
    Route::get('/notifications/{id}/priest-declined', [\App\Http\Controllers\Admin\NotificationController::class, 'showPriestDeclined'])->name('notifications.priest-declined');
    Route::get('/notifications/{id}/priest-action', [\App\Http\Controllers\Admin\NotificationController::class, 'showPriestAction'])->name('notifications.priest-action');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::get('/notifications/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'show'])->name('notifications.show');
});

// Staff Reservation Routes (additional to existing staff routes)
Route::prefix('staff')->name('staff.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':staff'])->group(function () {
    // Cancellations Index
    Route::get('/cancellations', [\App\Http\Controllers\Staff\CancellationController::class, 'index'])->name('cancellations.index');
    Route::get('/reservations', [\App\Http\Controllers\Staff\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/calendar', [\App\Http\Controllers\Staff\ReservationController::class, 'calendar'])->name('reservations.calendar');
    Route::get('/reservations/unnoticed', [\App\Http\Controllers\Staff\ReservationController::class, 'unnoticed'])->name('reservations.unnoticed');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Staff\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/mark-contacted', [\App\Http\Controllers\Staff\ReservationController::class, 'markContacted'])->name('reservations.mark-contacted');
    Route::post('/reservations/{reservation_id}/approve', [\App\Http\Controllers\Staff\ReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('/reservations/{reservation_id}/not-available', [\App\Http\Controllers\Staff\ReservationController::class, 'notAvailable'])->name('reservations.not-available');
    Route::post('/reservations/{reservation_id}/finalize', [\App\Http\Controllers\Staff\ReservationController::class, 'finalize'])->name('reservations.finalize');
    Route::post('/reservations/{reservation_id}/assign-priest', [\App\Http\Controllers\Staff\ReservationController::class, 'assignPriest'])->name('reservations.assign-priest');
    Route::post('/reservations/{reservation_id}/follow-up', [\App\Http\Controllers\Staff\ReservationController::class, 'sendFollowUp'])->name('reservations.follow-up');
    Route::post('/reservations/{reservation_id}/cancel', [\App\Http\Controllers\Staff\ReservationController::class, 'cancel'])->name('reservations.cancel');

    // Cancellation Routes
    Route::get('/cancellations/{id}', [\App\Http\Controllers\Staff\CancellationController::class, 'show'])->name('cancellations.show');
    Route::post('/cancellations/{id}/confirm', [\App\Http\Controllers\Staff\CancellationController::class, 'confirm'])->name('cancellations.confirm');

    // Services Management (Staff can edit only; no add/delete)
    Route::get('/services/manage', [\App\Http\Controllers\Staff\ServiceManagementController::class, 'index'])->name('services.manage');
    Route::put('/services/manage/{id}', [\App\Http\Controllers\Staff\ServiceManagementController::class, 'update'])->name('services.manage.update');

    // Organization Booking Routes (Staff monitoring and reminder system)
    Route::get('/organization-bookings', [\App\Http\Controllers\Staff\OrganizationBookingController::class, 'index'])->name('organization-bookings.index');
    Route::get('/organization-bookings/overdue', [\App\Http\Controllers\Staff\OrganizationBookingController::class, 'overdue'])->name('organization-bookings.overdue');
    Route::get('/organization-bookings/{organizationBookingRequest}', [\App\Http\Controllers\Staff\OrganizationBookingController::class, 'show'])->name('organization-bookings.show');
    Route::post('/organization-bookings/{organizationBookingRequest}/send-reminder', [\App\Http\Controllers\Staff\OrganizationBookingController::class, 'sendReminder'])->name('organization-bookings.send-reminder');
});

// Priest Reservation Routes (specific routes BEFORE parameterized ones)
Route::prefix('priest')->name('priest.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':priest'])->group(function () {
    // Notification routes
    Route::get('/notifications', [\App\Http\Controllers\Priest\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/archived', [\App\Http\Controllers\Priest\NotificationController::class, 'archived'])->name('notifications.archived');
    Route::get('/notifications/count', [\App\Http\Controllers\Priest\NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Priest\NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Priest\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/clear-all', [\App\Http\Controllers\Priest\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::post('/notifications/{id}/archive', [\App\Http\Controllers\Priest\NotificationController::class, 'archive'])->name('notifications.archive');
    Route::post('/notifications/{id}/restore', [\App\Http\Controllers\Priest\NotificationController::class, 'restore'])->name('notifications.restore');
    Route::get('/notifications/{id}/assignment', [\App\Http\Controllers\Priest\NotificationController::class, 'showAssignment'])->name('notifications.assignment');
    Route::get('/notifications/{id}', [\App\Http\Controllers\Priest\NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Priest\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');

    // Cancellation Routes
    Route::get('/cancellations/{id}', [\App\Http\Controllers\Priest\CancellationController::class, 'show'])->name('cancellations.show');
    Route::post('/cancellations/{id}/confirm', [\App\Http\Controllers\Priest\CancellationController::class, 'confirm'])->name('cancellations.confirm');

    // History routes
    Route::post('/history/{historyId}/archive', [\App\Http\Controllers\Priest\HistoryController::class, 'archive'])->name('history.archive');
    Route::post('/history/reservation/{reservationId}/clear-all', [\App\Http\Controllers\Priest\HistoryController::class, 'clearAll'])->name('history.clearAll');
    Route::get('/history/archived', [\App\Http\Controllers\Priest\HistoryController::class, 'archived'])->name('history.archived');
    Route::post('/history/{historyId}/restore', [\App\Http\Controllers\Priest\HistoryController::class, 'restore'])->name('history.restore');

    // Reservation routes
    Route::get('/reservations', [\App\Http\Controllers\Priest\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/calendar', [\App\Http\Controllers\Priest\ReservationController::class, 'calendar'])->name('reservations.calendar');
    Route::get('/reservations/declined', [\App\Http\Controllers\Priest\ReservationController::class, 'declined'])->name('reservations.declined');
    // POST routes for actions
    Route::post('/reservations/{reservation_id}/confirm', [\App\Http\Controllers\Priest\ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations/{reservation_id}/decline', [\App\Http\Controllers\Priest\ReservationController::class, 'decline'])->name('reservations.decline');
    Route::post('/reservations/{reservation_id}/undecline', [\App\Http\Controllers\Priest\ReservationController::class, 'undecline'])->name('reservations.undecline');
    // GET route for individual reservation (must be last among /reservations/* routes)
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Priest\ReservationController::class, 'show'])->name('reservations.show');
});

// Admin Venue Management
Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('venues', \App\Http\Controllers\Admin\VenueController::class);
});
