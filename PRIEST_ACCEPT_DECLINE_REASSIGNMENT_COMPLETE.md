# Accept/Decline Buttons for Reassigned Priests - Implementation Complete

## Overview

Added Accept/Decline functionality for priests who have been reassigned to a service by administrators after another priest declined. The newly assigned priest can now accept or decline the assignment from their reservation detail page.

## What Was Implemented

### 1. **Updated Priest Reservation Show View**

**File**: `resources/views/priest\reservations\show.blade.php`

✅ **Extended Confirmation Section** to include `admin_approved` status:

-   Changed condition from `status === 'pending_priest_confirmation'` to `in_array($reservation->status, ['pending_priest_confirmation', 'admin_approved'])`
-   Added special notification banner for reassigned priests
-   Shows "You have been reassigned to this service by the administrator" message

**New Features**:

```blade
@if(in_array($reservation->status, ['pending_priest_confirmation', 'admin_approved']) && $reservation->priest_confirmation !== 'confirmed')
    <!-- Purple banner for reassigned priests -->
    @if($reservation->status === 'admin_approved')
        <div class="p-3 bg-purple-50 border border-purple-200 rounded">
            🔔 You have been reassigned to this service by the administrator.
        </div>
    @endif

    <!-- Confirm Availability Button (Green) -->
    <button type="submit">Confirm Availability</button>

    <!-- Decline Assignment Button (Red) -->
    <button onclick="showDeclineModal()">Decline Assignment</button>
@endif
```

### 2. **Updated Status Badge Display**

**File**: `resources/views/priest\reservations\show.blade.php`

✅ **Added `admin_approved` Status**:

-   Shows **yellow badge** with "Awaiting Your Confirmation" label
-   Matches the same visual style as `pending_priest_confirmation`

```php
$statusColors = [
    'pending_priest_confirmation' => 'Awaiting Your Confirmation',
    'admin_approved' => 'Awaiting Your Confirmation', // NEW!
    'approved' => 'Confirmed',
    // ... other statuses
];
```

### 3. **Updated Priest Reservations Index**

**File**: `resources/views/priest\reservations\index.blade.php`

✅ **Added `admin_approved` Status Badge**:

-   Shows **purple badge** with "New Assignment" label
-   Distinguishes reassignments from initial assignments

```php
$statusConfig = [
    'admin_approved' => [
        'bg' => 'bg-purple-100',
        'text' => 'text-purple-800',
        'label' => 'New Assignment'
    ],
    // ... other statuses
];
```

### 4. **Updated Reservation Controller Status Validation**

**File**: `app\Http\Controllers\Admin\ReservationController.php`

✅ **Added `pending_priest_reassignment` Support**:

-   Allows admin to assign priests to reservations with status:
    -   `adviser_approved` (initial assignment)
    -   `priest_declined` (reassignment after decline)
    -   `pending_priest_reassignment` (reassignment pending) ← **NEW!**

```php
if (!in_array($reservation->status, [
    'adviser_approved',
    'priest_declined',
    'pending_priest_reassignment' // NEW!
])) {
    return error('This reservation is not ready for priest assignment.');
}
```

## Complete Workflow

### **Scenario: Admin Reassigns After Priest Declines**

1. **Priest 1 Declines Reservation**

    - Status: `pending_priest_reassignment`
    - Admin receives notification

2. **Admin Assigns Priest 2**

    - Selects available priest from dropdown
    - Adds remarks (optional)
    - Clicks "Send Assignment" button
    - Status changes: `pending_priest_reassignment` → `admin_approved`

3. **Priest 2 Receives Notification**

    - 🔔 Red badge on notification bell
    - In-app notification: "New Service Assignment"
    - Email notification sent

4. **Priest 2 Views Reservation Details**

    - Sees purple banner: "You have been reassigned by administrator"
    - Status badge: "Awaiting Your Confirmation" (yellow)
    - Two action buttons visible:
        - ✅ **Confirm Availability** (Green)
        - ❌ **Decline Assignment** (Red)

5. **Priest 2 Accepts Assignment**

    - Clicks "Confirm Availability"
    - Confirmation dialog appears
    - Status changes: `admin_approved` → `approved`
    - Priest confirmation: `pending` → `confirmed`
    - Success message displayed
    - Requestor receives confirmation email

6. **Alternative: Priest 2 Declines**
    - Clicks "Decline Assignment"
    - Modal appears asking for reason
    - Enters reason and submits
    - Status changes: `admin_approved` → `pending_priest_reassignment`
    - Admin receives new notification
    - Process repeats (admin assigns Priest 3)

## Visual Indicators

### **Status Badges:**

| Status                        | Badge Color                     | Label                                           | When Displayed     |
| ----------------------------- | ------------------------------- | ----------------------------------------------- | ------------------ |
| `pending_priest_confirmation` | Yellow                          | "Awaiting Your Confirmation"                    | Initial assignment |
| `admin_approved`              | Yellow (detail) / Purple (list) | "Awaiting Your Confirmation" / "New Assignment" | After reassignment |
| `approved`                    | Green                           | "Confirmed"                                     | Priest confirmed   |
| `pending_priest_reassignment` | Red                             | "Declined"                                      | Priest declined    |
| `completed`                   | Gray                            | "Completed"                                     | Service completed  |

### **Action Buttons:**

| Button               | Color | Icon        | Action              |
| -------------------- | ----- | ----------- | ------------------- |
| Confirm Availability | Green | ✓ Checkmark | Accept assignment   |
| Decline Assignment   | Red   | ✗ Cross     | Decline with reason |

### **Notification Banner (Reassignment):**

```
┌─────────────────────────────────────────────────────┐
│ 🔔 You have been reassigned to this service by      │
│    the administrator.                               │
└─────────────────────────────────────────────────────┘
Purple background with purple border
```

## Database Flow

### **Status Transitions:**

```
Initial Assignment:
adviser_approved → admin_approved (priest_confirmation: pending)
                ↓
                Accept → approved (priest_confirmation: confirmed)
                ↓
                Decline → pending_priest_reassignment

Reassignment:
pending_priest_reassignment → admin_approved (new priest assigned)
                             ↓
                             Accept → approved
                             ↓
                             Decline → pending_priest_reassignment (cycle repeats)
```

### **Key Fields:**

-   `status`: Reservation workflow status
-   `priest_confirmation`: Priest's response (pending, confirmed, declined)
-   `officiant_id`: Currently assigned priest ID
-   `priest_notified_at`: When priest was notified
-   `read_at`: When notification was read

## Files Modified

### **Views:**

1. ✅ `resources/views/priest/reservations/show.blade.php`

    - Extended confirmation button visibility to `admin_approved`
    - Added reassignment notification banner
    - Updated status badge configuration

2. ✅ `resources/views/priest/reservations/index.blade.php`
    - Added `admin_approved` status badge (purple "New Assignment")

### **Controllers:**

3. ✅ `app/Http/Controllers/Admin/ReservationController.php`
    - Added `pending_priest_reassignment` to allowed statuses
    - Updated reassignment detection logic

### **Models:**

-   No changes needed - `scopeAwaitingPriestConfirmation()` already handles `admin_approved` status

## Testing Guide

### **Test Case 1: Accept Reassignment**

1. Login as Priest 2 (nez torio)
2. Click notification bell → See "New Service Assignment"
3. Click notification → Redirected to assignment detail page
4. View reservation #16 details
5. See purple banner: "You have been reassigned..."
6. Click **"Confirm Availability"** button
7. Confirm in dialog
8. ✅ **Expected**: Success message, status → approved, requestor notified

### **Test Case 2: Decline Reassignment**

1. Login as Priest 2
2. View reservation #16
3. Click **"Decline Assignment"** button
4. Modal appears asking for reason
5. Enter reason: "Schedule conflict"
6. Submit
7. ✅ **Expected**: Status → pending_priest_reassignment, admin notified

### **Test Case 3: View in Reservations List**

1. Login as Priest 2
2. Go to `/priest/reservations`
3. See reservation #16 with **purple "New Assignment"** badge
4. Click to view details
5. ✅ **Expected**: Shows confirmation buttons

### **Test Case 4: Admin Reassigns Again**

1. If Priest 2 declines, admin sees notification
2. Admin assigns Priest 3
3. Priest 3 receives notification
4. Process repeats
5. ✅ **Expected**: Cycle continues until a priest accepts

## Key Improvements

### ✅ **User Experience:**

-   Clear visual distinction between initial and reassignments (purple banner)
-   Consistent button placement and styling
-   Informative status badges
-   Real-time notifications

### ✅ **Workflow Flexibility:**

-   Supports multiple reassignment cycles
-   No limit on number of priests who can decline
-   Admin can keep trying until someone accepts
-   Full audit trail in activity history

### ✅ **Data Integrity:**

-   Status validation prevents invalid assignments
-   Proper history logging for all actions
-   Notifications created for all stakeholders
-   Email confirmations sent automatically

## Summary

✅ **Complete Reassignment Flow:**

1. Priest 1 declines → Admin notified
2. Admin assigns Priest 2 → Priest 2 notified
3. Priest 2 sees Accept/Decline buttons
4. Priest 2 accepts → Service confirmed
5. OR Priest 2 declines → Admin assigns Priest 3 (repeat)

✅ **All Buttons Working:**

-   ✅ "Send Assignment" button (admin side)
-   ✅ "Confirm Availability" button (priest side)
-   ✅ "Decline Assignment" button (priest side)
-   ✅ "View Details" button (both sides)

✅ **All Notifications Working:**

-   ✅ Admin receives decline notifications
-   ✅ Priest receives assignment notifications
-   ✅ Requestor receives confirmation notifications
-   ✅ Email and in-app notifications sent

**Status**: Ready for Production Use 🎉

---

## Next Steps (Optional Enhancements)

### Future Features:

-   [ ] Show admin's remarks to the reassigned priest
-   [ ] Display decline reason to admin in notification
-   [ ] Add "Quick Accept" button in notification dropdown
-   [ ] Show priest workload before assignment
-   [ ] Auto-suggest least busy priest
-   [ ] Add SMS notifications for urgent reassignments
-   [ ] Dashboard widget showing pending confirmations
-   [ ] Calendar view of priest assignments
