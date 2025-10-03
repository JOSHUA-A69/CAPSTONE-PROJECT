<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Default dashboard (if needed)
Route::get('/dashboard', function () {
    return view('dashboard');
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
Route::middleware(['auth', 'role:admin'])->get('/admin', fn () => view('admin.dashboard'));
Route::middleware(['auth', 'role:staff'])->get('/staff', fn () => view('staff.dashboard'));
Route::middleware(['auth', 'role:adviser'])->get('/adviser', fn () => view('adviser.dashboard'));
Route::middleware(['auth', 'role:priest'])->get('/priest', fn () => view('priest.dashboard'));
Route::middleware(['auth', 'role:requestor'])->get('/requestor', fn () => view('requestor.dashboard'));

