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

Route::get('/staff', fn () => view('staff.dashboard'))
    ->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':staff'])
    ->name('staff.dashboard');

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

