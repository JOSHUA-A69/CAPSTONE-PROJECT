# Reservation Cancellation System - Implementation Progress

## ✅ Phase 1 COMPLETED (Requestor Cancellation Request)

### Database & Models

-   ✅ Created `reservation_cancellations` table with tracking for:
    -   Cancellation details (reason, status)
    -   Confirmation timestamps for each role (staff, admin, adviser, priest)
    -   Notification tracking (when notified, when escalated)
-   ✅ Created `ReservationCancellation` model with relationships and helper methods
-   ✅ Added cancellation action types to `reservation_history` ENUM:
    -   `cancellation_requested`
    -   `cancellation_confirmed_by_staff`
    -   `cancellation_confirmed_by_admin`
    -   `cancellation_confirmed_by_adviser`
    -   `cancellation_confirmed_by_priest`
    -   `cancellation_completed`

### Requestor Interface

-   ✅ Updated `requestor/reservations/index.blade.php`:
    -   Added "Actions" column to reservation table
    -   Added "Cancel" button with 7-day validation
    -   Shows "Too late" message if within 7 days
    -   Shows status badges (color-coded)
-   ✅ Created cancellation modal with:
    -   Warning message about notifying all parties
    -   Service and schedule display
    -   Required reason textarea
    -   Confirm/Keep buttons

### Controller Logic

-   ✅ Updated `RequestorReservationController@cancel`:
    -   Validates 7-day minimum notice requirement
    -   Checks for existing cancellation requests
    -   Creates `ReservationCancellation` record
    -   Creates history entry
    -   Calls notification service

### Notification Service

-   ✅ Created `CancellationNotificationService` with methods:
    -   `notifyCancellationRequest()` - Main orchestrator
    -   `notifyStaffAndAdmin()` - Notifies all staff/admin
    -   `notifyAdviser()` - Notifies organization adviser
    -   `notifyPriest()` - Notifies assigned priest
    -   `notifyStaffOfUnresponsive()` - Escalation after 1-minute timeout
    -   `notifyCancellationCompleted()` - Final confirmation to all

### Notifications Sent

For each cancellation request, the following notifications are sent:

1. **Email** - All parties receive email with reservation details and reason
2. **In-App Notification** - System notifications with action buttons
3. **SMS** - (optional, framework in place)

---

## ⏳ Phase 2 TODO (Confirmation Workflow)

### Staff/Admin Confirmation

-   [ ] Create `CancellationController` for staff/admin
-   [ ] Add `confirmCancellation()` method
-   [ ] Create notification views showing cancellation details
-   [ ] Add "Confirm Cancellation" button

### Adviser Confirmation

-   [ ] Create adviser cancellation confirmation view
-   [ ] Add confirmation endpoint
-   [ ] Show contact info to staff if unresponsive

### Priest Confirmation

-   [ ] Create priest cancellation confirmation view
-   [ ] Add confirmation endpoint
-   [ ] Show contact info to staff if unresponsive

### Timeout System (1-minute escalation)

-   [ ] Create scheduled job `CheckUnresponsiveCancellations`
-   [ ] Run every minute checking for:
    -   Adviser notified > 1 minute ago without confirmation
    -   Priest notified > 1 minute ago without confirmation
-   [ ] Call `notifyStaffOfUnresponsive()` with contact details

### Contact Info Display

-   [ ] Create view component showing:
    -   Name, email, phone number
    -   "Call" and "Email" action buttons
    -   Last notification time

### Final Processing

-   [ ] After all confirmations, update reservation status to 'cancelled'
-   [ ] Set cancellation reason and cancelled_by
-   [ ] Mark cancellation as 'completed'
-   [ ] Notify requestor of completion

---

## Testing Checklist

### Requestor Actions

-   [x] Can see "Cancel" button for reservations >= 7 days away
-   [x] Cannot see "Cancel" for reservations < 7 days
-   [x] Modal shows with warning and reason input
-   [ ] Cancellation request creates database record
-   [ ] All parties receive notifications

### Staff/Admin

-   [ ] Receives notification immediately
-   [ ] Can view cancellation details
-   [ ] Can confirm cancellation
-   [ ] Sees adviser/priest confirmation status

### Adviser

-   [ ] Receives notification immediately
-   [ ] Can view cancellation details
-   [ ] Can confirm cancellation
-   [ ] After 1 minute, staff is notified with contact info

### Priest

-   [ ] Receives notification immediately
-   [ ] Can view cancellation details
-   [ ] Can confirm cancellation
-   [ ] After 1 minute, staff is notified with contact info

### System Behavior

-   [ ] Countdown timer showing time left for adviser/priest response
-   [ ] Staff sees contact info for unresponsive parties
-   [ ] Final confirmation sent to requestor
-   [ ] Reservation marked as cancelled
-   [ ] History properly recorded

---

## Database Schema

### reservation_cancellations

```
- cancellation_id (PK)
- reservation_id (FK → reservations)
- requestor_id (FK → users)
- reason (text)
- status (enum)
- staff_confirmed_at
- staff_confirmed_by
- admin_confirmed_at
- admin_confirmed_by
- adviser_confirmed_at
- adviser_confirmed_by
- priest_confirmed_at
- priest_confirmed_by
- adviser_notified_at
- priest_notified_at
- staff_escalated_adviser_at
- staff_escalated_priest_at
- created_at, updated_at
```

---

## Routes Added

-   POST `/requestor/reservations/{id}/cancel` - Submit cancellation request

## Routes Needed

-   POST `/staff/cancellations/{id}/confirm` - Staff confirms
-   POST `/admin/cancellations/{id}/confirm` - Admin confirms
-   POST `/adviser/cancellations/{id}/confirm` - Adviser confirms
-   POST `/priest/cancellations/{id}/confirm` - Priest confirms
-   GET `/staff/cancellations/{id}` - View cancellation details
-   GET `/admin/cancellations/{id}` - View cancellation details
-   GET `/adviser/cancellations/{id}` - View cancellation details
-   GET `/priest/cancellations/{id}` - View cancellation details

---

## Next Steps

1. **Create confirmation controllers** for each role
2. **Create notification views** with cancellation details
3. **Implement timeout job** to check unresponsive parties
4. **Add routes** for confirmation endpoints
5. **Test complete workflow** end-to-end

---

## Notes

-   **7-day minimum** enforced at controller level
-   **All notifications** sent via Email + In-App
-   **Contact info** displayed when escalating to staff
-   **Status tracking** at each step of the process
-   **History records** for audit trail
