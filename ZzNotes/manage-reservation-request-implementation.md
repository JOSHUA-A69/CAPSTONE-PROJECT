# Manage Reservation Request - Implementation Guide

## Overview

This implementation follows the **Manage Reservation Request** swim lane diagram from your CAPSTONE PROJECT. The system handles the complete workflow from reservation submission to final confirmation by the priest.

## Workflow Stages

### 1. **Requestor Submits Reservation**

-   Requestor fills out reservation form (service, date, venue, organization, purpose)
-   System creates reservation with status: `pending`
-   Notifications sent to:
    -   ✅ Requestor (confirmation email)
    -   ✅ Organization Adviser (email + SMS)
-   **Controller**: `Requestor\ReservationController@store`

### 2. **Organization Adviser Reviews**

Two possible paths:

#### Path A: Adviser Approves

-   Adviser clicks "Approve" button
-   Status changes: `pending` → `adviser_approved`
-   Notifications sent to:
    -   ✅ Requestor (approval email + SMS)
    -   ✅ CREaM Admin/Staff (email)
-   **Controller**: `Adviser\ReservationController@approve`

#### Path B: Adviser Rejects

-   Adviser provides rejection reason
-   Status changes: `pending` → `rejected`
-   Notifications sent to:
    -   ✅ Requestor (rejection email + SMS)
    -   ✅ CREaM Staff (for record keeping)
-   **Controller**: `Adviser\ReservationController@reject`

### 3. **Unnoticed Request Detection (>24 hours)**

If adviser doesn't respond within 24 hours:

-   Automated system (daily cron job) detects unnoticed requests
-   Follow-up notification sent to adviser (email + SMS)
-   Staff is alerted
-   **Command**: `php artisan reservations:check-unnoticed --send-notifications`
-   **Schedule**: Runs daily at 9:00 AM (routes/console.php)

### 4. **CREaM Admin Assigns Priest**

-   Admin reviews adviser-approved requests
-   Selects priest from dropdown (shows availability)
-   System checks for scheduling conflicts
-   Status changes: `adviser_approved` → `admin_approved`
-   Priest confirmation status: `pending`
-   Notifications sent to:
    -   ✅ Assigned priest (email + SMS)
    -   ✅ Requestor (update email)
-   **Controller**: `Admin\ReservationController@assignPriest`

### 5. **Priest Confirms/Declines**

#### Path A: Priest Confirms

-   Priest clicks "Confirm Availability"
-   Status changes: `admin_approved` → `approved`
-   Priest confirmation: `pending` → `confirmed`
-   Notifications sent to:
    -   ✅ Requestor (final confirmation)
    -   ✅ Organization Adviser
    -   ✅ CREaM Staff
-   **Controller**: `Priest\ReservationController@confirm`

#### Path B: Priest Declines

-   Priest provides reason
-   Status reverts: `admin_approved` → `adviser_approved`
-   Officiant assignment removed
-   Priest confirmation: `pending` → `declined`
-   Notifications sent to:
    -   ✅ CREaM Admin (reassignment needed)
    -   ✅ CREaM Staff
-   **Controller**: `Priest\ReservationController@decline`

### 6. **Cancellation (Any Stage)**

Can be cancelled by:

-   Requestor
-   CREaM Staff
-   CREaM Admin

Process:

-   Cancellation reason required
-   Status changes to: `cancelled`
-   Notifications sent to **ALL** involved parties:
    -   ✅ Requestor
    -   ✅ Organization Adviser
    -   ✅ Assigned Priest (if applicable)
    -   ✅ CREaM Admin/Staff
-   **Controllers**:
    -   `Requestor\ReservationController@cancel`
    -   `Staff\ReservationController@cancel`

## Database Schema Enhancements

### New Columns in `reservations` table:

```php
- officiant_id (foreign key to users)
- adviser_notified_at (timestamp)
- adviser_responded_at (timestamp)
- admin_notified_at (timestamp)
- staff_followed_up_at (timestamp)
- priest_notified_at (timestamp)
- priest_confirmation (enum: pending, confirmed, declined)
- priest_confirmed_at (timestamp)
- cancellation_reason (text)
- cancelled_by (foreign key to users)
```

### Reservation History Actions:

```
- submitted
- adviser_notified
- adviser_approved
- adviser_rejected
- admin_notified
- staff_followed_up
- priest_assigned
- priest_notified
- priest_confirmed
- priest_declined
- approved
- rejected
- cancelled
```

## Notification System

### Email Notifications

-   **Service**: `App\Services\ReservationNotificationService`
-   **Mailer Classes**:
    -   `ReservationSubmitted`
    -   `ReservationAdviserApproved`
    -   `ReservationAdviserRejected`
    -   `ReservationPriestAssigned`
    -   `ReservationCancelled`

### SMS Notifications

-   **Provider**: Semaphore (Philippine SMS gateway)
-   **Configuration**: `config/services.php`
-   **Environment Variables**:
    ```
    SMS_ENABLED=true
    SEMAPHORE_API_KEY=your_api_key_here
    SEMAPHORE_SENDER_NAME=CREaM-HNU
    ```

## Controllers Implemented

### 1. Requestor\ReservationController

-   `index()` - View own reservations
-   `create()` - Show reservation form
-   `store()` - Submit new reservation
-   `show()` - View reservation details
-   `cancel()` - Cancel own reservation

### 2. Adviser\ReservationController

-   `index()` - View organization reservations
-   `show()` - View reservation details
-   `approve()` - Approve reservation request
-   `reject()` - Reject reservation request

### 3. Admin\ReservationController

-   `index()` - View all reservations (with filters)
-   `show()` - View reservation details
-   `assignPriest()` - Assign priest to reservation
-   `reject()` - Admin-level rejection

### 4. Staff\ReservationController

-   `index()` - View all reservations
-   `show()` - View reservation details
-   `sendFollowUp()` - Manual follow-up for unnoticed requests
-   `unnoticed()` - View all unnoticed requests
-   `cancel()` - Staff cancellation

### 5. Priest\ReservationController

-   `index()` - View assigned reservations
-   `show()` - View assignment details
-   `confirm()` - Confirm availability
-   `decline()` - Decline assignment
-   `calendar()` - View personal schedule

## Model Enhancements

### Reservation Model Scopes:

```php
- pendingAdviserApproval()
- pendingAdminApproval()
- awaitingPriestConfirmation()
- approved()
- unnoticedByAdviser()
- forOrganization($orgId)
- forPriest($priestId)
- upcoming()
- past()
```

### Helper Methods:

```php
- isPendingAdviser()
- isPendingAdmin()
- isApproved()
- isRejected()
- isCancelled()
- getStatusLabelAttribute()
- getPriestConfirmationLabelAttribute()
```

## Automated Tasks

### Daily Cron Job

**Command**: `reservations:check-unnoticed`
**Schedule**: Daily at 9:00 AM
**Purpose**:

-   Detects pending requests >24 hours old
-   Sends follow-up emails/SMS to advisers
-   Notifies CREaM staff

**Manual Testing**:

```bash
# Dry run (doesn't send notifications)
php artisan reservations:check-unnoticed

# Actually send notifications
php artisan reservations:check-unnoticed --send-notifications
```

## Setup Instructions

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Configure Email (if not already done)

Update `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=cream@hnu.edu.ph
MAIL_FROM_NAME="CREaM - HNU"
```

### 3. Configure SMS

Update `.env`:

```
SMS_ENABLED=true
SEMAPHORE_API_KEY=your_semaphore_api_key
SEMAPHORE_SENDER_NAME=CREaM-HNU
```

### 4. Set Up Task Scheduler

Add to server crontab:

```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Update Routes

Add to `routes/web.php`:

```php
// Requestor routes
Route::prefix('requestor')->name('requestor.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':requestor'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Requestor\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create', [\App\Http\Controllers\Requestor\ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [\App\Http\Controllers\Requestor\ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Requestor\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/cancel', [\App\Http\Controllers\Requestor\ReservationController::class, 'cancel'])->name('reservations.cancel');
});

// Adviser routes
Route::prefix('adviser')->name('adviser.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':adviser'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Adviser\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Adviser\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/approve', [\App\Http\Controllers\Adviser\ReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('/reservations/{reservation_id}/reject', [\App\Http\Controllers\Adviser\ReservationController::class, 'reject'])->name('reservations.reject');
});

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Admin\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Admin\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/assign-priest', [\App\Http\Controllers\Admin\ReservationController::class, 'assignPriest'])->name('reservations.assign-priest');
    Route::post('/reservations/{reservation_id}/reject', [\App\Http\Controllers\Admin\ReservationController::class, 'reject'])->name('reservations.reject');
});

// Staff routes
Route::prefix('staff')->name('staff.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':staff'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Staff\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/unnoticed', [\App\Http\Controllers\Staff\ReservationController::class, 'unnoticed'])->name('reservations.unnoticed');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Staff\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/follow-up', [\App\Http\Controllers\Staff\ReservationController::class, 'sendFollowUp'])->name('reservations.follow-up');
    Route::post('/reservations/{reservation_id}/cancel', [\App\Http\Controllers\Staff\ReservationController::class, 'cancel'])->name('reservations.cancel');
});

// Priest routes
Route::prefix('priest')->name('priest.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':priest'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Priest\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Priest\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/confirm', [\App\Http\Controllers\Priest\ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations/{reservation_id}/decline', [\App\Http\Controllers\Priest\ReservationController::class, 'decline'])->name('reservations.decline');
    Route::get('/calendar', [\App\Http\Controllers\Priest\ReservationController::class, 'calendar'])->name('calendar');
});
```

## Testing Workflow

### 1. Create Test Data

```bash
# Create users with different roles
php artisan tinker

User::create([
    'first_name' => 'John',
    'last_name' => 'Requestor',
    'email' => 'requestor@test.com',
    'phone' => '09171234567',
    'password' => bcrypt('password'),
    'role' => 'requestor',
    'status' => 'active',
    'email_verified_at' => now()
]);

User::create([
    'first_name' => 'Jane',
    'last_name' => 'Adviser',
    'email' => 'adviser@test.com',
    'phone' => '09181234567',
    'password' => bcrypt('password'),
    'role' => 'adviser',
    'status' => 'active',
    'email_verified_at' => now()
]);

User::create([
    'first_name' => 'Father',
    'last_name' => 'Priest',
    'email' => 'priest@test.com',
    'phone' => '09191234567',
    'password' => bcrypt('password'),
    'role' => 'priest',
    'status' => 'active',
    'email_verified_at' => now()
]);
```

### 2. Test Complete Flow

1. Login as Requestor → Submit reservation
2. Login as Adviser → Approve request
3. Login as Admin → Assign priest from dropdown
4. Login as Priest → Confirm availability
5. Check emails at each stage

### 3. Test Unnoticed Requests

1. Create reservation
2. Don't respond as adviser
3. Run: `php artisan reservations:check-unnoticed --send-notifications`
4. Check follow-up emails sent

## Status Flow Diagram

```
pending
  ↓
  ├─→ rejected (by adviser)
  └─→ adviser_approved
        ↓
        ├─→ rejected (by admin)
        └─→ admin_approved (priest assigned)
              ↓
              ├─→ adviser_approved (priest declined, needs reassignment)
              └─→ approved (priest confirmed) ✅

cancelled (can happen at any stage)
```

## Next Steps

1. ✅ Database migrations
2. ✅ Notification system (email + SMS)
3. ✅ Enhanced Reservation model
4. ✅ All role-based controllers
5. ✅ Automated monitoring system
6. ✅ Email templates
7. ⏳ **Create Blade views for dashboards** (next task)
8. ⏳ Add routes to web.php
9. ⏳ Test complete workflow
10. ⏳ Deploy to production

## Support & Documentation

For questions or issues:

-   Contact CREaM office
-   Email: cream@hnu.edu.ph
-   Refer to swim lane diagram in thesis documentation

---

**Developed for**: Holy Name University - Center for Religious Education and Mission (CREaM)
**Project**: eReligiousServices - Capstone Project 2025
