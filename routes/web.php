<?php

use App\Http\Controllers\ProfileController;
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

Route::get('/', function () {
    return view('welcome');
});

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

Route::get('/staff', function () {
    $user = Auth::user();
    // Fetch the latest notifications for the logged-in staff user (limit to 5 for dashboard)
    $recentNotifications = collect();
    if ($user) {
        $recentNotifications = \App\Models\Notification::where('user_id', $user->id)
            ->orderBy('sent_at', 'desc')
            ->limit(5)
            ->get();
    }

    return view('staff.dashboard', compact('recentNotifications'));
})
    ->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':staff'])
    ->name('staff.dashboard');

// Staff organization management
Route::prefix('staff')->name('staff.')->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':staff'])->group(function () {
    Route::get('/organizations', [\App\Http\Controllers\Staff\OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/create', [\App\Http\Controllers\Staff\OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [\App\Http\Controllers\Staff\OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/{org_id}/edit', [\App\Http\Controllers\Staff\OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::put('/organizations/{org_id}', [\App\Http\Controllers\Staff\OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('/organizations/{org_id}', [\App\Http\Controllers\Staff\OrganizationController::class, 'destroy'])->name('organizations.destroy');
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
    Route::get('/users/create', [\App\Http\Controllers\Admin\UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [\App\Http\Controllers\Admin\UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [\App\Http\Controllers\Admin\UserManagementController::class, 'update'])->name('users.update');
});

// ==========================
// Reservation Management Routes
// ==========================

// Requestor Reservation Routes
Route::prefix('requestor')->name('requestor.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':requestor'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Requestor\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create', [\App\Http\Controllers\Requestor\ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [\App\Http\Controllers\Requestor\ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Requestor\ReservationController::class, 'show'])->name('reservations.show');
    // Confirmation step removed: no token-based confirm/decline routes
    Route::post('/reservations/{reservation_id}/cancel', [\App\Http\Controllers\Requestor\ReservationController::class, 'cancel'])->name('reservations.cancel');

    // Notification Routes
    Route::get('/notifications', [\App\Http\Controllers\Requestor\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [\App\Http\Controllers\Requestor\NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Requestor\NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Requestor\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Requestor\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::get('/notifications/{id}', [\App\Http\Controllers\Requestor\NotificationController::class, 'show'])->name('notifications.show');
});

// Adviser Reservation Routes
Route::prefix('adviser')->name('adviser.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':adviser'])->group(function () {
    // Notification Routes
    Route::get('/notifications', [\App\Http\Controllers\Adviser\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [\App\Http\Controllers\Adviser\NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Adviser\NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Adviser\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Adviser\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::get('/notifications/{id}', [\App\Http\Controllers\Adviser\NotificationController::class, 'show'])->name('notifications.show');

    // Cancellation Routes
    Route::get('/cancellations/{id}', [\App\Http\Controllers\Adviser\CancellationController::class, 'show'])->name('cancellations.show');
    Route::post('/cancellations/{id}/confirm', [\App\Http\Controllers\Adviser\CancellationController::class, 'confirm'])->name('cancellations.confirm');

    // Reservation Routes
    Route::get('/reservations', [\App\Http\Controllers\Adviser\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Adviser\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/approve', [\App\Http\Controllers\Adviser\ReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('/reservations/{reservation_id}/reject', [\App\Http\Controllers\Adviser\ReservationController::class, 'reject'])->name('reservations.reject');
});

// Admin Reservation Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Admin\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Admin\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/assign-priest', [\App\Http\Controllers\Admin\ReservationController::class, 'assignPriest'])->name('reservations.assign-priest');
    Route::post('/reservations/{reservation_id}/reject', [\App\Http\Controllers\Admin\ReservationController::class, 'reject'])->name('reservations.reject');
    Route::post('/reservations/{reservation_id}/reschedule', [\App\Http\Controllers\Admin\ReservationController::class, 'reschedule'])->name('reservations.reschedule');

    // Cancellation Routes
    Route::get('/cancellations/{id}', [\App\Http\Controllers\Admin\CancellationController::class, 'show'])->name('cancellations.show');
    Route::post('/cancellations/{id}/confirm', [\App\Http\Controllers\Admin\CancellationController::class, 'confirm'])->name('cancellations.confirm');

    // Notification Routes (specific routes first, then parameterized)
    Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [\App\Http\Controllers\Admin\NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Admin\NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/{id}/priest-declined', [\App\Http\Controllers\Admin\NotificationController::class, 'showPriestDeclined'])->name('notifications.priest-declined');
    Route::get('/notifications/{id}/priest-action', [\App\Http\Controllers\Admin\NotificationController::class, 'showPriestAction'])->name('notifications.priest-action');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::get('/notifications/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'show'])->name('notifications.show');
});

// Staff Reservation Routes (additional to existing staff routes)
Route::prefix('staff')->name('staff.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':staff'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Staff\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/unnoticed', [\App\Http\Controllers\Staff\ReservationController::class, 'unnoticed'])->name('reservations.unnoticed');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Staff\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/mark-contacted', [\App\Http\Controllers\Staff\ReservationController::class, 'markContacted'])->name('reservations.mark-contacted');
    Route::post('/reservations/{reservation_id}/approve', [\App\Http\Controllers\Staff\ReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('/reservations/{reservation_id}/not-available', [\App\Http\Controllers\Staff\ReservationController::class, 'notAvailable'])->name('reservations.not-available');
    Route::post('/reservations/{reservation_id}/finalize', [\App\Http\Controllers\Staff\ReservationController::class, 'finalize'])->name('reservations.finalize');
    // Policy: Staff cannot assign or reassign priests (handled by Admin based on requestor's choice)
    // Route::post('/reservations/{reservation_id}/assign-priest', [\App\Http\Controllers\Staff\ReservationController::class, 'assignPriest'])->name('reservations.assign-priest');
    Route::post('/reservations/{reservation_id}/reschedule', [\App\Http\Controllers\Staff\ReservationController::class, 'reschedule'])->name('reservations.reschedule');
    Route::post('/reservations/{reservation_id}/follow-up', [\App\Http\Controllers\Staff\ReservationController::class, 'sendFollowUp'])->name('reservations.follow-up');
    Route::post('/reservations/{reservation_id}/cancel', [\App\Http\Controllers\Staff\ReservationController::class, 'cancel'])->name('reservations.cancel');

    // Cancellation Routes
    Route::get('/cancellations/{id}', [\App\Http\Controllers\Staff\CancellationController::class, 'show'])->name('cancellations.show');
    Route::post('/cancellations/{id}/confirm', [\App\Http\Controllers\Staff\CancellationController::class, 'confirm'])->name('cancellations.confirm');

    // Notification Routes for Staff (reuse Admin NotificationController)
    Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [\App\Http\Controllers\Admin\NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Admin\NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/{id}/priest-declined', [\App\Http\Controllers\Admin\NotificationController::class, 'showPriestDeclined'])->name('notifications.priest-declined');
    Route::get('/notifications/{id}/priest-action', [\App\Http\Controllers\Admin\NotificationController::class, 'showPriestAction'])->name('notifications.priest-action');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::get('/notifications/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'show'])->name('notifications.show');
});

// Priest Reservation Routes (specific routes BEFORE parameterized ones)
Route::prefix('priest')->name('priest.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':priest'])->group(function () {
    // Notification routes
    Route::get('/notifications', [\App\Http\Controllers\Priest\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [\App\Http\Controllers\Priest\NotificationController::class, 'getUnreadCount'])->name('notifications.count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Priest\NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::get('/notifications/{id}/assignment', [\App\Http\Controllers\Priest\NotificationController::class, 'showAssignment'])->name('notifications.assignment');
    Route::get('/notifications/{id}', [\App\Http\Controllers\Priest\NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Priest\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Priest\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Cancellation Routes
    Route::get('/cancellations/{id}', [\App\Http\Controllers\Priest\CancellationController::class, 'show'])->name('cancellations.show');
    Route::post('/cancellations/{id}/confirm', [\App\Http\Controllers\Priest\CancellationController::class, 'confirm'])->name('cancellations.confirm');

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
