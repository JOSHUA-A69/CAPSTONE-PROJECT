# Priest Reassignment Feature - Implementation Complete

## Overview

Added direct priest reassignment functionality to the "Priest Declined Notification" detail page. Admins can now assign a new priest directly from the notification without navigating to the reservation details page.

## What Was Implemented

### 1. **Updated Notification Controller** (`app/Http/Controllers/Admin/NotificationController.php`)

-   ✅ Added `Reservation` and `User` model imports
-   ✅ Updated `showPriestDeclined()` method to fetch available priests
-   ✅ Added `getAvailablePriests()` private method to check priest availability based on reservation date/time
-   ✅ Filters out priests already assigned to other reservations at the same time
-   ✅ Marks priests as "available" or "unavailable" in the dropdown

**Key Logic:**

```php
private function getAvailablePriests($scheduleDate, $excludeReservationId = null)
{
    // Get all active priests
    $allPriests = User::where('role', 'priest')
        ->where('status', 'active')
        ->orderBy('first_name')
        ->get();

    // Get priests already assigned at this time
    $assignedPriestIds = Reservation::where('schedule_date', $scheduleDate)
        ->whereNotIn('status', ['cancelled', 'rejected'])
        ->when($excludeReservationId, function ($q) use ($excludeReservationId) {
            $q->where('reservation_id', '!=', $excludeReservationId);
        })
        ->pluck('officiant_id')
        ->filter()
        ->toArray();

    // Mark availability
    return $allPriests->map(function ($priest) use ($assignedPriestIds) {
        $priest->is_available = !in_array($priest->id, $assignedPriestIds);
        return $priest;
    });
}
```

### 2. **Updated Reservation Controller** (`app/Http/Controllers/Admin/ReservationController.php`)

-   ✅ Modified `assignPriest()` method to allow reassignment for reservations with `priest_declined` status
-   ✅ Added logic to differentiate between initial assignment and reassignment
-   ✅ Creates different history actions: `priest_assigned` vs `priest_reassigned`
-   ✅ Maintains conflict detection to prevent double-booking priests

**Updated Status Check:**

```php
// Allow if status is adviser_approved OR priest_declined (reassignment)
if (!in_array($reservation->status, ['adviser_approved', 'priest_declined'])) {
    return Redirect::back()
        ->with('error', 'This reservation is not ready for priest assignment.');
}
```

### 3. **Updated Priest Declined Notification View** (`resources/views/admin/notifications/priest-declined.blade.php`)

-   ✅ Replaced "View Reservation & Assign Priest" button with inline assignment form
-   ✅ Added priest dropdown with availability indicators
-   ✅ Shows "Available ✓" or "Unavailable - Already Assigned" for each priest
-   ✅ Disables unavailable priests in the dropdown
-   ✅ Added optional remarks field for assignment notes
-   ✅ Displays reservation date/time for context
-   ✅ Shows success/error messages after assignment
-   ✅ Added "View Details" button for full reservation page

**Form Features:**

-   Smart dropdown with real-time availability
-   Visual indicators (✓ for available, disabled for unavailable)
-   Validation feedback
-   Success/error message display
-   Two-button layout: "Assign Priest" (primary) and "View Details" (secondary)

## User Workflow

### Admin Perspective:

1. **Receive Notification**: Admin sees red badge on notification bell
2. **View Notification**: Click bell → See "Priest Declined Assignment" in dropdown
3. **Access Detail Page**: Click notification → Redirected to priest-declined detail page
4. **View Available Priests**: Dropdown shows all priests with availability status
5. **Select & Assign**: Choose available priest → Add optional remarks → Click "Assign Priest"
6. **Confirmation**: Success message appears → New priest is notified → Status updated to `admin_approved`

### Technical Workflow:

```
Priest Declines → Admin Notification Created → Admin Views Detail Page
    ↓
Loads Available Priests (filters by date/time) → Admin Selects Priest
    ↓
Validates Selection → Checks Conflicts → Updates Reservation
    ↓
Status: priest_declined → admin_approved
    ↓
History: priest_reassigned → Email Sent to New Priest → Admin Sees Success
```

## Database Updates Completed

### Notifications Table:

-   ✅ `type` ENUM updated: `('Approval', 'Reminder', 'System Alert', 'Priest Declined', 'Assignment', 'Update')`
-   ✅ `data` JSON column added for storing additional notification details

**SQL Executed:**

```sql
ALTER TABLE notifications
MODIFY COLUMN type ENUM('Approval', 'Reminder', 'System Alert', 'Priest Declined', 'Assignment', 'Update') NULL;

ALTER TABLE notifications
ADD COLUMN data JSON NULL AFTER type;
```

## Priest Availability Logic

### How It Works:

1. Fetches all active priests from database
2. Queries reservations table for the same date/time
3. Excludes cancelled and rejected reservations
4. Excludes the current reservation being reassigned
5. Gets list of priest IDs already assigned
6. Marks each priest as available/unavailable

### Displayed in Dropdown:

-   **Available**: `John Doe ✓ Available` (selectable, black text)
-   **Unavailable**: `Jane Smith (Unavailable - Already Assigned)` (disabled, gray text)

## Routes Used

| Route Name                            | Method | URL                                         | Purpose                       |
| ------------------------------------- | ------ | ------------------------------------------- | ----------------------------- |
| `admin.notifications.priest-declined` | GET    | `/admin/notifications/{id}/priest-declined` | Display priest decline detail |
| `admin.reservations.assign-priest`    | POST   | `/admin/reservations/{id}/assign-priest`    | Assign/reassign priest        |

## Success Messages

### When Assignment Succeeds:

```
✅ Priest assigned successfully. Awaiting priest confirmation.
```

### When Assignment Fails:

```
❌ This priest already has an assignment at this date and time.
❌ This reservation is not ready for priest assignment.
```

## Testing Guide

### Test Case 1: Assign Available Priest

1. Login as priest → Decline a reservation
2. Login as admin → Click notification bell → Select priest decline notification
3. In dropdown, select an available priest (marked with ✓)
4. Click "Assign Priest"
5. **Expected**: Success message, priest notified, notification marked as read

### Test Case 2: Attempt Unavailable Priest

1. Follow steps 1-2 above
2. Try to select a priest marked as "Unavailable"
3. **Expected**: Option is disabled, cannot be selected

### Test Case 3: Conflict Detection

1. Manually try to assign a priest who has another reservation at the same time
2. **Expected**: Error message about scheduling conflict

### Test Case 4: Add Remarks

1. Follow Test Case 1
2. Add remarks in the "Remarks" field (e.g., "Emergency reassignment")
3. Check reservation history
4. **Expected**: History shows remarks

## Files Modified

1. ✅ `app/Http/Controllers/Admin/NotificationController.php`
2. ✅ `app/Http/Controllers/Admin/ReservationController.php`
3. ✅ `resources/views/admin/notifications/priest-declined.blade.php`
4. ✅ Database: `notifications` table (ENUM type, JSON data column)

## Next Steps (Optional Enhancements)

### Future Features:

-   [ ] Show priest workload/schedule in dropdown (e.g., "John Doe - 2 assignments this week")
-   [ ] Add priest filtering by expertise/specialization
-   [ ] Real-time availability check via AJAX
-   [ ] Bulk reassignment for multiple declined reservations
-   [ ] Auto-suggest best priest based on availability and workload
-   [ ] SMS notification to newly assigned priest

## Notes

### Important Details:

-   Only active priests appear in dropdown
-   Priests with conflicting assignments are disabled
-   Reassignment creates `priest_reassigned` history action (not `priest_assigned`)
-   Status changes from `priest_declined` → `admin_approved`
-   New priest receives email notification automatically
-   Notification is marked as read when viewed

### Validation Rules:

-   `officiant_id`: required, must exist in users table
-   `remarks`: optional, max 500 characters
-   Must be a user with role 'priest'
-   Cannot assign priest with scheduling conflict

---

## Summary

✅ **Feature Complete**: Admins can now reassign priests directly from the notification detail page  
✅ **Smart Dropdown**: Shows only available priests based on date/time  
✅ **Conflict Prevention**: Automatic detection of scheduling conflicts  
✅ **User-Friendly**: Clear visual indicators and success/error messages  
✅ **Audit Trail**: Creates history records for reassignments

**Status**: Ready for Production Use 🚀
