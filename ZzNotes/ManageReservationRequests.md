# Manage Reservation Requests – Process, Fields, Statuses, Notifications

This document explains how the reservation workflow is implemented in the app to match the swimlane diagram "3.1.2.2 Manage Reservation Requests".

## Roles
- Requestor: submits a reservation and can cancel before event.
- Organization Adviser: reviews and approves or rejects.
- CREaM Staff/Admin: contacts requestor, assigns priest, completes event, may cancel.
- Priest: receives assignment, confirms or declines.
- System: sends notifications, updates dashboards, records history.

## Reservation fields (key)
- activity_name (string, optional)
- purpose (string, max 150, required) – short statement of purpose
- theme (string, optional)
- service_id (int, required)
- venue_id (int, required unless custom)
- custom_venue_name (string, required when venue_id == 'custom')
- schedule_date (datetime, required) – date & time in one field
- org_id (int, optional)
- participants_count (int, optional)
- status (string) – see Statuses below
- contacted_at, requestor_confirmed_at – contact/confirmation anchors
- priest fields: officiant_id (nullable), priest_notified_at, priest_confirmation('pending'|'confirmed'|'declined'), priest_confirmed_at
- cancellation fields: cancellation_reason, cancelled_by

Note: The legacy `details` column was dropped. Remarks are captured through history records and short `purpose`.

## Statuses and transitions
- pending → adviser_approved | rejected
- adviser_approved → contacted_at set → requestor_confirmed_at set → pending_priest_assignment (implicit UI state)
- assign priest → pending_priest_confirmation (priest_notified_at)
- pending_priest_confirmation → confirmed (priest accepts) | back to assignment if priest declines
- confirmed → completed (post-event) | cancelled
- rejected and cancelled are terminal

Model helpers/scopes are provided in `App\Models\Reservation`.

## UI and forms
- Requestor create form (`resources/views/requestor/reservations/create.blade.php`):
  - Uses a single Date & Time (datetime-local) bound to `schedule_date`.
  - Provides Venue selection with a Custom Location toggle writing to `custom_venue_name`.
  - Includes `purpose` (required) and `theme` (optional).
  - Does not include priest selection – priests are assigned by Staff/Admin later.
  - Does not include legacy details.

## Notifications
Implemented in `App\Services\ReservationNotificationService`.

- On submission: `notifyReservationSubmitted($reservation)`
  - Requestor: email + in-app
  - Adviser: email + SMS
- Adviser approves: `notifyAdviserApproved($reservation, $remarks)`
  - Requestor: email + in-app
  - Admin/Staff: email
- Staff contact requestor: `notifyRequestorConfirmation($reservation, $confirmationUrl)`
  - Requestor: email with confirmation link + in-app "Action required"
- Requestor confirms: `notifyRequestorConfirmed($reservation)`
  - Admin/Staff: email + in-app prompt to assign priest
- Priest assigned: `notifyPriestAssigned($reservation)`
  - Priest: in-app + email + SMS
  - Requestor: email update
- Priest declines: `notifyPriestDeclined($reservation, $reason, $priestId)`
  - Admin/Staff: email + in-app + optional SMS
- Priest confirms: `notifyPriestConfirmed($reservation, $priestId)`
  - Admin/Staff: email + in-app; Requestor: in-app
- Cancellation: `notifyCancellation($reservation, $reason, $cancelledBy)`
  - Requestor, Adviser, Priest (if assigned), Admin/Staff: email; key parties get SMS

## Controllers and key actions
- Requestor\ReservationController
  - store(): sets status `pending`, logs history, calls `notifyReservationSubmitted()`
  - confirmReservation(): sets `requestor_confirmed_at`, logs history, calls `notifyRequestorConfirmed()`
  - cancel(): sets `cancelled`, logs history, calls `notifyCancellation()`
- Adviser\ReservationController
  - approve(): sets `adviser_approved`, logs history, calls `notifyAdviserApproved()`
  - reject(): sets `rejected`, logs history, calls `notifyAdviserRejected()`
- Staff\ReservationController
  - markContacted(): sets `contacted_at`, generates token+URL, logs history, calls `notifyRequestorConfirmation()`
  - assignPriest(): sets priest + `pending_priest_confirmation` and `priest_confirmation=pending`, logs history, calls `notifyPriestAssigned()`
  - approve(): for flows where priest already set, sets `pending_priest_confirmation`, logs history, calls `notifyPriestAssigned()`
  - cancel(): sets `cancelled`, logs history, calls `notifyCancellation()`

## History and audit
- Every state change creates a `reservation_history` record with performed_by, action, remarks, performed_at.
- Typical actions: submitted, adviser_approved, adviser_rejected, contacted_requestor, confirmed_by_requestor, priest_assigned, approved_by_staff, cancelled, completed, etc.

## Edge cases and reminders
- "If unnoticed" adviser: Staff view exposes an "Unnoticed" list (older than 24h); staff can send follow-ups via `sendFollowUp()` which emails adviser and logs history.
- Late cancellations: requestor cannot cancel within 7 days of event; staff/admin can still cancel when necessary with reason.
- Conflict detection: staff assignment checks for priest schedule conflicts.

## Extending
- Add more notification channels by extending the service methods.
- For stricter transitions, add a small policy/state-machine layer to validate allowed status moves per role.
- Add scheduled jobs to auto-remind priests on pending confirmations.

## Schema notes
- `details` column dropped by migration `2025_10_24_160000_drop_details_from_reservations_table`.
- Volunteer/ministry fields removed from reservations; handled via events/event_roles/event_assignments (not covered here).

