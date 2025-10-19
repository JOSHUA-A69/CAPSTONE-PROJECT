# Cancellation System Implementation Summary

## Overview

Comprehensive multi-actor cancellation system with notification and confirmation workflow for reservation cancellations.

## Date: October 19, 2025

## Features Implemented

### 1. Database Structure

-   **Table**: `reservation_cancellations`
    -   Tracks cancellation requests with multi-actor confirmation
    -   Columns for each role's confirmation timestamp and user ID
    -   Escalation tracking timestamps for unresponsive actors
    -   Status ENUM: pending, confirmed_by_staff, confirmed_by_admin, confirmed_by_adviser, confirmed_by_priest, completed, rejected

### 2. Models

-   **ReservationCancellation Model** (`app/Models/ReservationCancellation.php`)
    -   Relationships: requestor, staffConfirmer, adminConfirmer, adviserConfirmer, priestConfirmer
    -   Helper methods:
        -   `isStaffConfirmed()`, `isAdminConfirmed()`, `isAdviserConfirmed()`, `isPriestConfirmed()`
        -   `isFullyConfirmed()` - checks if all required parties have confirmed
        -   `needsAdviserConfirmation()`, `needsPriestConfirmation()` - checks if role needs to confirm

### 3. Services

-   **CancellationNotificationService** (`app/Services/CancellationNotificationService.php`)
    -   `notifyCancellationRequest($cancellation, $reservation)` - Main orchestrator
    -   `notifyStaffAndAdmin()` - Sends to all staff and admin users
    -   `notifyAdviser()` - Sends to organization adviser
    -   `notifyPriest()` - Sends to assigned priest
    -   `notifyStaffOfUnresponsive($role, $cancellation, $reservation)` - Escalation with contact info
    -   `notifyCancellationCompleted()` - Final confirmation to requestor
    -   Supports: Email + In-app notifications + SMS framework

### 4. Controllers

#### Requestor

-   **RequestorReservationController** - Updated `cancel()` method
    -   Validates 7-day minimum notice requirement
    -   Prevents duplicate cancellation requests
    -   Creates cancellation record and history entry
    -   Triggers notification service

#### Staff

-   **StaffCancellationController** (`app/Http/Controllers/Staff/CancellationController.php`)
    -   `show($id)` - Display cancellation details
    -   `confirm($id)` - Confirm cancellation, check if all confirmed, complete if yes

#### Admin

-   **AdminCancellationController** (`app/Http/Controllers/Admin/CancellationController.php`)
    -   `show($id)` - Display cancellation details
    -   `confirm($id)` - Confirm cancellation, check if all confirmed, complete if yes

#### Adviser

-   **AdviserCancellationController** (`app/Http/Controllers/Adviser/CancellationController.php`)
    -   `show($id)` - Display cancellation details (with organization check)
    -   `confirm($id)` - Confirm cancellation with 1-minute timeout warning
    -   Authorization: Only adviser from the same organization can access

#### Priest

-   **PriestCancellationController** (`app/Http/Controllers/Priest/CancellationController.php`)
    -   `show($id)` - Display cancellation details (with priest assignment check)
    -   `confirm($id)` - Confirm cancellation with 1-minute timeout warning
    -   Authorization: Only assigned priest can access

#### Notifications

-   **AdviserNotificationController** - Updated `show()` method to route to cancellation view

### 5. Views

#### Requestor

-   **resources/views/requestor/reservations/index.blade.php**
    -   Cancel button with 7-day validation
    -   Cancel modal with warning, service details, reason textarea
    -   "Too late to cancel" message for reservations < 7 days away

#### Staff

-   **resources/views/staff/cancellations/show.blade.php**
    -   Cancellation details card (requestor, reason, requested at)
    -   Reservation details card (service type, schedule, organization, priest)
    -   Confirmation status for all roles
    -   Confirm button (if not already confirmed)
    -   Success message (if already confirmed)

#### Admin

-   **resources/views/admin/cancellations/show.blade.php**
    -   Same structure as staff view
    -   Admin-specific confirmation tracking

#### Adviser

-   **resources/views/adviser/cancellations/show.blade.php**
    -   Highlighted "Adviser (You)" section in confirmation status
    -   1-minute timeout warning in alert banner
    -   Organization-specific details

#### Priest

-   **resources/views/priest/cancellations/show.blade.php**
    -   Highlighted "Priest (You)" section in confirmation status
    -   1-minute timeout warning in alert banner
    -   "Your Assignment" badge

#### Notifications

-   **resources/views/adviser/notifications/index.blade.php**
    -   Paginated notification list
    -   Empty state with icon
    -   Cancellation request notifications highlighted in red
    -   Click to view cancellation details

### 6. Routes

All routes registered in `routes/web.php`:

**Staff Routes:**

-   GET `/staff/cancellations/{id}` - View cancellation
-   POST `/staff/cancellations/{id}/confirm` - Confirm cancellation

**Admin Routes:**

-   GET `/admin/cancellations/{id}` - View cancellation
-   POST `/admin/cancellations/{id}/confirm` - Confirm cancellation

**Adviser Routes:**

-   GET `/adviser/cancellations/{id}` - View cancellation
-   POST `/adviser/cancellations/{id}/confirm` - Confirm cancellation
-   GET `/adviser/notifications` - Notification index
-   GET `/adviser/notifications/count` - Badge count
-   GET `/adviser/notifications/recent` - Dropdown
-   POST `/adviser/notifications/{id}/mark-read` - Mark as read
-   POST `/adviser/notifications/mark-all-read` - Mark all as read
-   GET `/adviser/notifications/{id}` - View notification (routes to cancellation if type matches)

**Priest Routes:**

-   GET `/priest/cancellations/{id}` - View cancellation
-   POST `/priest/cancellations/{id}/confirm` - Confirm cancellation

**Requestor Routes:**

-   POST `/requestor/reservations/{id}/cancel` - Request cancellation

### 7. Notification Bell (Navigation)

-   **resources/views/layouts/navigation.blade.php**
    -   Adviser added to role check: `['admin', 'staff', 'priest', 'adviser']`
    -   Alpine.js dropdown with auto-refresh every 30 seconds
    -   Badge shows unread count
    -   Dropdown shows last 5 notifications with icons

### 8. Workflow

1. **Requestor cancels reservation** (≥7 days before event)

    - System creates `ReservationCancellation` record
    - Creates history entry with action `cancellation_requested`
    - Triggers `CancellationNotificationService`

2. **Notifications sent immediately to:**

    - All staff users (email + in-app)
    - All admin users (email + in-app)
    - Organization adviser (email + in-app) - `adviser_notified_at` timestamp set
    - Assigned priest (email + in-app) - `priest_notified_at` timestamp set

3. **Each actor receives notification with:**

    - Requestor name and reason
    - Service type and schedule
    - Link to cancellation details view
    - Notification type: "Cancellation Request"

4. **Each actor confirms:**

    - Clicks notification → routes to cancellation view
    - Reviews details
    - Clicks "Confirm Cancellation" button
    - System updates: `{role}_confirmed_at` timestamp and `{role}_confirmed_by` user ID
    - Creates history entry: `cancellation_confirmed_by_{role}`

5. **Auto-completion:**

    - After each confirmation, system checks `isFullyConfirmed()`
    - If all required parties confirmed:
        - Updates cancellation status to `completed`
        - Updates reservation status to `cancelled`
        - Creates history entry: `cancellation_completed`
        - TODO: Sends confirmation notification to requestor

6. **Timeout escalation (TODO):**
    - Scheduled job checks every minute
    - If `adviser_notified_at` > 1 minute ago AND `adviser_confirmed_at` is null:
        - Calls `notifyStaffOfUnresponsive('adviser', ...)`
        - Sends notification to staff with adviser contact info (email, phone)
        - Sets `staff_escalated_adviser_at` timestamp
    - Same logic for priest timeout

### 9. Database Migrations

Three migrations created and run:

1. **2025_10_19_034759_create_reservation_cancellations_table.php**

    - Creates reservation_cancellations table

2. **2025_10_19_040950_add_cancellation_actions_to_history.php**

    - Expands reservation_history.action ENUM to 23 values
    - Adds: cancellation_requested, cancellation_confirmed_by_staff, cancellation_confirmed_by_admin, cancellation_confirmed_by_adviser, cancellation_confirmed_by_priest, cancellation_completed

3. **2025_10_19_042632_add_cancellation_notification_types.php**
    - Expands notifications.type ENUM to 8 values
    - Adds: Cancellation Request

## Validation Rules

1. **7-Day Minimum Notice**

    - Calculated as: `now()->diffInDays($reservation->schedule_date, false) >= 7`
    - Cancel button hidden if < 7 days
    - "Too late to cancel" message shown

2. **Duplicate Prevention**

    - Checks for existing `pending` cancellation request before creating new one

3. **Status Checks**
    - Cannot cancel if reservation status is `cancelled` or `rejected`

## Authorization

-   **Staff/Admin**: Can view any cancellation
-   **Adviser**: Can only view cancellations for their organization
-   **Priest**: Can only view cancellations for reservations where they are assigned

## Pending Implementation

### 1. Timeout Scheduled Job

**File to create:** `app/Console/Commands/CheckUnresponsiveCancellations.php`

```php
protected function handle()
{
    // Find cancellations where adviser was notified > 1 min ago, not confirmed
    $unresponsiveAdviser = ReservationCancellation::whereNotNull('adviser_notified_at')
        ->whereNull('adviser_confirmed_at')
        ->whereNull('staff_escalated_adviser_at')
        ->where('adviser_notified_at', '<=', now()->subMinute())
        ->get();

    foreach ($unresponsiveAdviser as $cancellation) {
        $service->notifyStaffOfUnresponsive('adviser', $cancellation, $cancellation->reservation);
    }

    // Same for priest...
}
```

**Register in:** `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('cancellations:check-unresponsive')->everyMinute();
}
```

### 2. Completion Notification

Update `completeCancellation()` methods in all CancellationControllers to call:

```php
app(CancellationNotificationService::class)->notifyCancellationCompleted($cancellation);
```

### 3. Contact Info Display

**Component:** `resources/views/components/contact-info-card.blade.php`

-   Display when staff receives unresponsive notification
-   Shows adviser/priest: name, email, phone, role, last notified timestamp
-   Action buttons: "Call" and "Email"

## Testing Checklist

-   [ ] Requestor can cancel reservation ≥7 days before event
-   [ ] Cancel button hidden for reservations <7 days before event
-   [ ] Notifications sent to all actors (staff, admin, adviser, priest)
-   [ ] Notification bell shows unread count for adviser
-   [ ] Clicking notification routes to cancellation view
-   [ ] Staff can confirm cancellation
-   [ ] Admin can confirm cancellation
-   [ ] Adviser can only access their organization's cancellations
-   [ ] Priest can only access their assigned reservations' cancellations
-   [ ] Confirmation status updates after each confirmation
-   [ ] Reservation cancelled after all confirmations
-   [ ] History entries created for each action
-   [ ] Timeout job notifies staff after 1 minute (when implemented)
-   [ ] Contact info displayed in escalation notification (when implemented)

## Files Created

### Models

-   `app/Models/ReservationCancellation.php`

### Controllers

-   `app/Http/Controllers/Staff/CancellationController.php`
-   `app/Http/Controllers/Admin/CancellationController.php`
-   `app/Http/Controllers/Adviser/CancellationController.php`
-   `app/Http/Controllers/Priest/CancellationController.php`
-   `app/Http/Controllers/Adviser/NotificationController.php`

### Services

-   `app/Services/CancellationNotificationService.php`

### Views

-   `resources/views/staff/cancellations/show.blade.php`
-   `resources/views/admin/cancellations/show.blade.php`
-   `resources/views/adviser/cancellations/show.blade.php`
-   `resources/views/priest/cancellations/show.blade.php`
-   `resources/views/adviser/notifications/index.blade.php`

### Migrations

-   `database/migrations/2025_10_19_034759_create_reservation_cancellations_table.php`
-   `database/migrations/2025_10_19_040950_add_cancellation_actions_to_history.php`
-   `database/migrations/2025_10_19_042632_add_cancellation_notification_types.php`

## Files Modified

-   `app/Http/Controllers/Requestor/ReservationController.php` - Added cancel() method
-   `app/Http/Controllers/Adviser/NotificationController.php` - Updated show() to route to cancellation view
-   `resources/views/requestor/reservations/index.blade.php` - Added cancel button and modal
-   `resources/views/layouts/navigation.blade.php` - Added adviser to notification bell
-   `routes/web.php` - Added cancellation routes for all roles

## Configuration

-   **Email**: Uses Laravel Mail facade (configured in config/mail.php)
-   **SMS**: Framework in place, requires SMS provider configuration
-   **Timeout**: Configurable via scheduled job frequency (default: 1 minute)
-   **Minimum Notice**: Hardcoded to 7 days (can be moved to config)

## Next Steps

1. Implement timeout scheduled job
2. Implement completion notification to requestor
3. Create contact info display component
4. Add SMS provider configuration
5. Test complete workflow end-to-end
6. Add admin panel for viewing all active cancellation requests
7. Add reporting/analytics for cancellation trends
8. Consider adding cancellation history to reservation details view
