# Use Case Narrative: Reservation Workflow

## Actors
- Requestor – submits and manages their reservation request.
- Adviser – reviews the request for the Requestor’s organization; approves or rejects with a reason.
- Staff – contacts the Requestor, can mark as contacted, optionally cancel with reason, and reschedule.
- Admin – assigns (or reassigns) a priest based on the Requestor’s preference, can reject or reschedule.
- Priest – confirms or declines the assigned reservation; may cancel a previously confirmed acceptance.

## Preconditions
- The user is authenticated and has a verified account.
- Required master data exists: Services, Venues, Organizations, Users (with roles).

## Main Success Scenario (Happy Path)
1) Requestor submits a reservation
   - Status: pending
   - System notifies the Organization Adviser and the Requestor.
   - History: submitted

2) Adviser approves with optional remarks
   - Status: adviser_approved
   - System notifies Admin/Staff and the Requestor.
   - History: adviser_approved (with remarks)

3) Staff contacts Requestor
   - Staff marks as contacted (no token/confirmation step).
   - Status stays adviser_approved
   - History: contacted_requestor

4) Admin assigns a Priest
   - Admin selects a Priest (conflict checks run).
   - Status: pending_priest_confirmation; priest_confirmation: pending
   - System notifies the assigned Priest and the Requestor.
   - History: priest_assigned (or priest_reassigned)

5) Priest confirms availability
   - Status: approved; priest_confirmation: confirmed
   - System notifies Admin/Staff and the Requestor.
   - History: priest_confirmed

6) Event occurs; Staff finalizes
   - After the scheduled date/time, Staff marks as completed.
   - Status: completed
   - History: completed (with remarks, optional)

## Alternate/Exception Flows
- A1) Requestor cancels with reason
  - Allowed up to the policy threshold (e.g., 7 days before event).
  - Status: cancelled
  - History: cancelled (with reason)
  - Notifications: Requestor, Adviser, assigned Priest (if any), Admin/Staff.

- A2) Adviser rejects with reason
  - Status: rejected
  - History: adviser_rejected (with reason)
  - Notifications: Requestor and Admin/Staff.

- A3) Staff marks “Not Available” / cancels with reason
  - Not Available: Status becomes rejected; History: marked_not_available (reason)
  - Cancel: Status becomes cancelled; History: cancelled (reason)
  - Notifications: Requestor and relevant parties.

- A4) Admin rejects with reason
  - Status: rejected
  - History: rejected (with reason)
  - Notifications: Requestor; Admin/Staff for record.

- A5) Priest declines before confirming
  - Status: pending_priest_reassignment; officiant cleared
  - History: priest_declined (with reason)
  - Notifications: Admin/Staff to reassign; Requestor informed as needed.

- A6) Priest cancels after confirming
  - Status: pending_priest_reassignment; officiant cleared
  - History: priest_cancelled_confirmation (with reason)
  - Notifications: Admin/Staff (urgent); Requestor informed.

- A7) Reschedule (by Staff/Admin)
  - Status: stays or becomes pending_priest_confirmation if a priest is assigned (confirmation resets)
  - Conflict checks are performed for the assigned Priest.
  - History: status_updated (remarks include old→new time)
  - Notifications: Requestor, Adviser, Admin/Staff, and Priest (if assigned). Priest receives a fresh assignment notification to reconfirm.

## Data captured per step
- Reservation History (reservation_history.action)
  - submitted
  - adviser_approved | adviser_rejected
  - staff_followed_up (when used)
  - contacted_requestor
  - priest_assigned | priest_reassigned
  - priest_confirmed | priest_declined | priest_cancelled_confirmation
  - status_updated (e.g., rescheduled)
  - approved | rejected | cancelled | completed | updated (generic)

- Notifications (in-app)
  - Types: Update, Assignment, Action Required, Priest Declined, Urgent (plus legacy Approval/Reminder/System Alert)
  - Recipients by step:
    - Submit: Adviser, Requestor
    - Adviser Approved: Admin/Staff, Requestor
    - Contacted: (usually internal, may display in UI only)
    - Assign Priest: Priest (Assignment), Requestor (Update)
    - Priest Confirmed: Admin/Staff (Update), Requestor (Update)
    - Decline/Cancel: Admin/Staff (Priest Declined/Urgent), Requestor/Adviser/Priest as applicable
    - Reschedule: All involved parties (Update)

## Business Rules
- Requestor chooses preferred priest; Admin assigns based on that preference.
- Staff cannot assign/reassign priests (Admin handles assignment).
- Contacted step is a simple stamp (no token or confirmation from Requestor).
- Declines and cancellations are unified through the reservation_history audit trail.
- Timezone is standardized to Asia/Manila across app and database.

## Postconditions
- Every user-facing action creates an audit entry in reservation_history with actor, action, remarks, and performed_at.
- Notifications are created for the right recipients with type and optional JSON data for deep links/details.

## Edge Cases and Notes
- Rescheduling resets priest confirmation when a priest is assigned; the priest must reconfirm.
- Assignment and confirmation check schedule conflicts within a configured time window.
- Some statuses (cancelled, rejected, completed) block further changes.
- History ordering uses performed_at when available; falls back to created_at.

## Quick Test Checklist
- Submit → Adviser Approves → Staff Contacts → Admin Assigns → Priest Confirms → Staff Finalizes
- Submit → Adviser Rejects (with reason)
- Submit → Adviser Approves → Staff Cancels (with reason)
- Submit → Adviser Approves → Admin Assigns → Priest Declines (with reason) → Admin Reassigns → Priest Confirms
- Reschedule path (with assigned priest) → Priest re-confirms
- Verify each step:
  - reservation_history has a new entry with correct action/remarks
  - notifications exist for intended recipients with appropriate type and timestamps
