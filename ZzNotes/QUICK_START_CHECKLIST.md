# Quick Start Checklist - Manage Reservation Request

## ✅ Completed (Backend Implementation)

-   [x] Database migrations created
-   [x] Reservation model enhanced with scopes and relationships
-   [x] All 5 role-based controllers implemented
-   [x] Notification service with email & SMS
-   [x] Email templates designed
-   [x] Automated monitoring command created
-   [x] Task scheduler configured
-   [x] Documentation written

## ⏳ Next Steps (To Complete the System)

### Step 1: Database Setup

```bash
# Run the new migrations
php artisan migrate
```

### Step 2: Environment Configuration

Add to `.env` file:

```env
# SMS Configuration
SMS_ENABLED=false  # Set to true when ready to send SMS
SEMAPHORE_API_KEY=your_semaphore_api_key
SEMAPHORE_SENDER_NAME=CREaM-HNU

# Email (ensure these are configured)
MAIL_FROM_ADDRESS=cream@hnu.edu.ph
MAIL_FROM_NAME="CREaM - HNU"
```

### Step 3: Add Routes

Add to `routes/web.php`:

```php
// Requestor Routes
Route::prefix('requestor')->name('requestor.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':requestor'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Requestor\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create', [\App\Http\Controllers\Requestor\ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [\App\Http\Controllers\Requestor\ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Requestor\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/cancel', [\App\Http\Controllers\Requestor\ReservationController::class, 'cancel'])->name('reservations.cancel');
});

// Adviser Routes
Route::prefix('adviser')->name('adviser.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':adviser'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Adviser\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Adviser\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/approve', [\App\Http\Controllers\Adviser\ReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('/reservations/{reservation_id}/reject', [\App\Http\Controllers\Adviser\ReservationController::class, 'reject'])->name('reservations.reject');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Admin\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Admin\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/assign-priest', [\App\Http\Controllers\Admin\ReservationController::class, 'assignPriest'])->name('reservations.assign-priest');
    Route::post('/reservations/{reservation_id}/reject', [\App\Http\Controllers\Admin\ReservationController::class, 'reject'])->name('reservations.reject');
});

// Staff Routes
Route::prefix('staff')->name('staff.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':staff'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Staff\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/unnoticed', [\App\Http\Controllers\Staff\ReservationController::class, 'unnoticed'])->name('reservations.unnoticed');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Staff\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/follow-up', [\App\Http\Controllers\Staff\ReservationController::class, 'sendFollowUp'])->name('reservations.follow-up');
    Route::post('/reservations/{reservation_id}/cancel', [\App\Http\Controllers\Staff\ReservationController::class, 'cancel'])->name('reservations.cancel');
});

// Priest Routes
Route::prefix('priest')->name('priest.')->middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class . ':priest'])->group(function () {
    Route::get('/reservations', [\App\Http\Controllers\Priest\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation_id}', [\App\Http\Controllers\Priest\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation_id}/confirm', [\App\Http\Controllers\Priest\ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations/{reservation_id}/decline', [\App\Http\Controllers\Priest\ReservationController::class, 'decline'])->name('reservations.decline');
    Route::get('/calendar', [\App\Http\Controllers\Priest\ReservationController::class, 'calendar'])->name('calendar');
});
```

### Step 4: Test Backend API

```bash
# Test the unnoticed reservations command
php artisan reservations:check-unnoticed

# To actually send notifications:
php artisan reservations:check-unnoticed --send-notifications
```

### Step 5: Create Frontend Views

Create Blade views in `resources/views/`:

**Priority Views to Create:**

1. `requestor/reservations/index.blade.php` - List requestor's reservations
2. `requestor/reservations/create.blade.php` - Reservation form
3. `adviser/reservations/index.blade.php` - Adviser's pending approvals
4. `admin/reservations/show.blade.php` - Priest assignment interface (with dropdown)
5. `priest/reservations/index.blade.php` - Priest's assignments

**View Requirements:**

-   Show reservation status with color-coded badges
-   Display action buttons based on status and role
-   Show notification/alert counts
-   Include forms for approval/rejection/cancellation with reason fields
-   Priest dropdown should show availability status

### Step 6: Production Deployment

```bash
# Set up cron job on server
crontab -e

# Add this line:
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Step 7: Get Semaphore API Key

1. Sign up at https://semaphore.co/
2. Get your API key
3. Add to `.env` file
4. Test with a single SMS first

## 🧪 Testing Checklist

### Basic Flow Test

-   [ ] Create test users (requestor, adviser, priest, admin)
-   [ ] Login as requestor → submit reservation
-   [ ] Check email sent to requestor
-   [ ] Check email sent to adviser
-   [ ] Login as adviser → approve request
-   [ ] Check email sent to requestor
-   [ ] Check email sent to admin
-   [ ] Login as admin → assign priest
-   [ ] Check email sent to priest
-   [ ] Login as priest → confirm availability
-   [ ] Check final confirmation emails

### Edge Cases

-   [ ] Test adviser rejection
-   [ ] Test admin rejection
-   [ ] Test priest decline
-   [ ] Test requestor cancellation
-   [ ] Test scheduling conflicts
-   [ ] Test unnoticed request detection (>24h)
-   [ ] Test follow-up notifications

### Notifications

-   [ ] Email delivery works
-   [ ] SMS delivery works (if enabled)
-   [ ] All parties receive correct notifications

## 📊 Monitoring

### Check Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Check for notification errors
grep "SMS" storage/logs/laravel.log
grep "Failed" storage/logs/laravel.log
```

### Database Checks

```sql
-- Check reservation statuses
SELECT status, COUNT(*) FROM reservations GROUP BY status;

-- Check unnoticed requests
SELECT * FROM reservations
WHERE status = 'pending'
AND created_at < NOW() - INTERVAL 1 DAY
AND adviser_responded_at IS NULL;

-- Check notification history
SELECT action, COUNT(*) FROM reservation_history GROUP BY action;
```

## 🎯 Success Criteria

✅ All migrations run without errors
✅ Routes registered and accessible
✅ Controllers return expected responses
✅ Notifications send successfully
✅ Workflow progresses through all stages
✅ Automated monitoring detects unnoticed requests
✅ All parties receive appropriate notifications

## 📚 Documentation Reference

-   Full implementation guide: `docs/manage-reservation-request-implementation.md`
-   Summary: `IMPLEMENTATION_SUMMARY.md`
-   Swim lane diagram: See attached image in thesis

## 🆘 Troubleshooting

### Emails not sending?

-   Check `.env` mail configuration
-   Test with `php artisan tinker` → `Mail::raw('test', fn($m) => $m->to('test@example.com')->subject('test'));`
-   Check `storage/logs/laravel.log`

### SMS not sending?

-   Verify `SMS_ENABLED=true`
-   Check Semaphore API key is correct
-   Check phone number format (should be +639XXXXXXXXX)
-   Review logs for API errors

### Route not found?

-   Run `php artisan route:list` to verify routes are registered
-   Clear route cache: `php artisan route:clear`

### Database errors?

-   Check migrations ran: `php artisan migrate:status`
-   Review foreign key constraints
-   Ensure all related tables exist

---

**Ready to launch!** 🚀

Once you complete the frontend views and add the routes, the complete Manage Reservation Request workflow will be fully operational.
