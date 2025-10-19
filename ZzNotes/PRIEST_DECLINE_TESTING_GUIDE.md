# Quick Testing Guide - Priest Decline Notification

## Prerequisites

-   Laravel application must be running
-   Database must have test data (users with different roles, reservations)
-   Email configuration must be set in `.env`

## Step-by-Step Testing

### 1. Setup Test Environment

Create test users if not already present:

```bash
php artisan tinker
```

Then in tinker:

```php
// Create admin user
$admin = User::create([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'email' => 'admin@test.com',
    'phone' => '09123456789', // For SMS testing
    'password' => bcrypt('password'),
    'role' => 'admin',
    'email_verified_at' => now(),
]);

// Create priest user
$priest = User::create([
    'first_name' => 'Father',
    'last_name' => 'Priest',
    'email' => 'priest@test.com',
    'password' => bcrypt('password'),
    'role' => 'priest',
    'email_verified_at' => now(),
]);
```

### 2. Create Test Reservation with Priest Assignment

```php
// In tinker
$service = App\Models\Service::first(); // Or use specific service
$venue = App\Models\Venue::first(); // Or use specific venue
$requestor = User::where('role', 'requestor')->first();

$reservation = Reservation::create([
    'user_id' => $requestor->id,
    'service_id' => $service->id,
    'venue_id' => $venue->id,
    'schedule_date' => now()->addDays(7),
    'status' => 'pending_priest_confirmation',
    'officiant_id' => $priest->id, // Assign priest
    'priest_notified_at' => now(),
    'priest_confirmation' => 'pending',
]);
```

### 3. Test Priest Decline Flow

#### Via Browser:

1. Log in as the priest user:
    - Email: `priest@test.com`
    - Password: `password`
2. Navigate to: `/priest/reservations`

3. Find the assigned reservation and click "Decline"

4. Enter a decline reason: "I have another commitment at this time"

5. Submit the form

#### Expected Results:

✅ Priest is redirected with success message
✅ Status message: "You have declined this assignment. CREaM administrators have been notified to assign another priest."
✅ Reservation status changes to `pending_priest_reassignment`
✅ Officiant ID is cleared from reservation
✅ Entry created in `priest_declines` table
✅ History entry created with action `priest_declined`

### 4. Verify Admin Notification

#### Check Email:

1. Check the admin email inbox (`admin@test.com`)
2. Look for email with subject: "Priest Declined - Reassignment Needed for Reservation #[ID]"
3. Verify email contains:
    - Reservation details (ID, service, requestor, date, venue)
    - Priest who declined
    - Decline reason
    - Action button linking to reservation

#### Check SMS (if configured):

1. Check phone number registered for admin
2. Should receive SMS: "URGENT: Priest declined reservation #[ID] for [Service] on [Date]. Please assign another presider."

### 5. Test Admin Reassignment

1. Log in as admin:

    - Email: `admin@test.com`
    - Password: `password`

2. Click the link in email OR navigate to `/admin/reservations/[reservation_id]`

3. Verify:

    - Status shows as `pending_priest_reassignment`
    - Officiant field is empty
    - History shows priest decline with reason
    - Option to assign new priest is available

4. Assign a new priest from the dropdown

5. Submit assignment

#### Expected Results:

✅ New priest receives assignment email
✅ Reservation status updates appropriately
✅ New history entry created

## Quick Database Checks

### Check reservation status:

```sql
SELECT reservation_id, status, officiant_id, priest_confirmation
FROM reservations
WHERE reservation_id = [YOUR_RESERVATION_ID];
```

### Check decline record:

```sql
SELECT * FROM priest_declines
WHERE reservation_id = [YOUR_RESERVATION_ID];
```

### Check history:

```sql
SELECT * FROM reservation_history
WHERE reservation_id = [YOUR_RESERVATION_ID]
ORDER BY performed_at DESC;
```

## Email Configuration Check

Verify `.env` has proper email settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io  # Or your email provider
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@cream.edu
MAIL_FROM_NAME="${APP_NAME}"
```

### Test Email Without Sending:

Use Laravel's log driver for testing:

```env
MAIL_MAILER=log
```

Then check `storage/logs/laravel.log` for email content.

## Common Issues & Solutions

### Issue: Email not received

**Solution:**

-   Check `.env` email configuration
-   Verify admin user has valid email address
-   Check `storage/logs/laravel.log` for email errors
-   Use `MAIL_MAILER=log` for testing

### Issue: SMS not sent

**Solution:**

-   Check if SMS is enabled: `config('services.semaphore.enabled')`
-   Verify API key is set in `.env`
-   Check admin has valid phone number
-   Review logs for SMS errors

### Issue: Decline button not showing

**Solution:**

-   Verify reservation status is `pending_priest_confirmation`
-   Ensure logged-in priest matches `officiant_id`
-   Check priest hasn't already confirmed

### Issue: Route not found error

**Solution:**

-   Run: `php artisan route:cache`
-   Or: `php artisan route:clear`

## Manual Test Checklist

-   [ ] Priest can see assigned reservations
-   [ ] Decline form appears and accepts reason
-   [ ] Decline submission succeeds with success message
-   [ ] Reservation status updates to `pending_priest_reassignment`
-   [ ] Officiant ID is cleared
-   [ ] Decline record created in database
-   [ ] History entry created
-   [ ] Admin receives email with correct information
-   [ ] Email link works and goes to reservation page
-   [ ] Admin receives SMS (if phone configured)
-   [ ] Admin can view reservation details
-   [ ] Admin can assign new priest
-   [ ] Complete workflow works end-to-end

## Quick Test Command

If you have Laravel Tinker and want to trigger directly:

```php
$reservation = Reservation::find([YOUR_ID]);
$notificationService = app(\App\Services\ReservationNotificationService::class);
$notificationService->notifyPriestDeclined($reservation, 'Test decline reason');
```

This will immediately send notifications without going through the UI.
