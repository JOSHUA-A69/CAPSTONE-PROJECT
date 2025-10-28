# Manage Reservations – Updates (2025-10-25)

This document summarizes workflow, policy, and UI/backend updates applied on Oct 25, 2025 for the Manage Reservations feature set.

## Overview

- Requestors now select a preferred priest; only Admin assigns the officiant (based on the requestor’s choice and availability).
- Staff can review, follow up, and mark as contacted; they cannot assign/reassign priests.
- Admin assignment triggers priest confirmation and reminders with escalation.
- Rescheduling enforces conflict checks and re-notifies the assigned priest.
- Cancellations and declines are normalized into reservation history.
- Notifications are centralized with role-aware deep links.

Additional policy and workflow changes (Oct 25, 2025):
- Admin assignment now sets status to `pending_priest_confirmation` for consistency across dashboards and reminders.
- Staff “Mark as Contacted” stamps `contacted_at` and writes a history entry.
- Requestor-facing form includes Preferred Priest dropdown.
- Admin and staff dashboards/views compacted and copy aligned; staff cannot see assign/reassign controls.

> Hotfix (Oct 26, 2025)
> - A previous cleanup migration inadvertently dropped `reservations.contacted_at`, which is still required for Staff “Mark as Contacted”.
> - Added migration `2025_10_26_020642_add_contacted_at_to_reservations_table.php` to re-add the column (`DATETIME NULL AFTER adviser_responded_at`).
> - Action: run database migrations.

## Why these changes

- Reduce back-and-forth when event dates need to move
- Prevent double-booking priests via time‑window conflict checks
- Nudge priests to confirm and alert Admin/Staff when confirmations stall
- Keep Admin/Staff aware of recent cancellations
- Enforce governance: requestor chooses; staff contacts; admin assigns

---

## Changes by Area

### 0) Preferred Priest Policy (requestor → admin)

- New column: `reservations.preferred_officiant_id` (nullable FK to `users.id`).
- Requestor create form includes a "Preferred Priest" dropdown.
- Staff cannot assign or reassign priests; any attempt is blocked server-side and the route is disabled.
- Admin assignment UI preselects the requestor’s preferred priest when available.
- Admin assignment sets status to `pending_priest_confirmation`.

Impacted files (high-level):
- Model: `app/Models/Reservation.php` (fillable + `preferredOfficiant()` relation)
- Migration: `database/migrations/2025_10_25_120000_add_preferred_officiant_to_reservations.php`
- Request: `app/Http/Requests/ReservationRequest.php` (+ preferred_officiant_id validation)
- Requestor form: `resources/views/requestor/reservations/create.blade.php`
- Admin show: `resources/views/admin/reservations/show.blade.php` (preselect preference)
- Admin notification (declined): `resources/views/admin/notifications/priest-declined.blade.php` (preselect preference)
- Staff controller: `app/Http/Controllers/Staff/ReservationController.php` (block assign; adjust approve; markContacted stamps `contacted_at`)
- Routes: `routes/web.php` (staff assign-priest route disabled)

### 1) Rescheduling Flow (Admin & Staff)

- New endpoints
  - Admin: `POST /admin/reservations/{reservation_id}/reschedule` → route name `admin.reservations.reschedule`
  - Staff: `POST /staff/reservations/{reservation_id}/reschedule` → route name `staff.reservations.reschedule`
- Controllers
  - Admin: `app/Http/Controllers/Admin/ReservationController.php` → `reschedule()`
  - Staff: `app/Http/Controllers/Staff/ReservationController.php` → `reschedule()`
- Views
  - Admin: `resources/views/admin/reservations/show.blade.php` → added Reschedule card
  - Staff: `resources/views/staff/reservations/show.blade.php` → added Reschedule card (when pending priest confirmation)
- Notifications
  - New method: `App/Services/ReservationNotificationService::notifyReservationRescheduled($reservation, $oldDate, $remarks)`
  - Sends in‑app notifications to requestor, adviser, assigned priest (if any), and admin/staff with old/new time and remarks
  - If a priest is assigned, the system also re‑notifies the priest via `notifyPriestAssigned()` so they can confirm the new time
- Rules
  - Not allowed when status is `cancelled`, `rejected`, or `completed`
  - If a priest is assigned, the new schedule must not conflict (see conflict window)
  - On success: updates `schedule_date`; if priest is assigned, resets `priest_confirmation` to `pending`, sets status to `pending_priest_confirmation`, stamps `priest_notified_at`, logs history, and notifies all parties

### 2) Priest Confirmation Reminders & Escalation

- Command: `app/Console/Commands/RemindPendingPriestConfirmations.php`
  - Scan reservations where status = `pending_priest_confirmation` and `priest_confirmation = pending`
  - Remind: after 24h from `priest_notified_at`, send in‑app reminder to the assigned priest and log a history entry
  - Escalate: after 48h, send an in‑app notification to Admin/Staff and log a history entry
- Schedule: `routes/console.php`
  - Daily at 11:00 → `reservations:remind-priest-confirmations --remind-hours=24 --escalate-hours=48`

### 3) Conflict Window for Priest Availability (referenced by assignment/reschedule)

- Config: `config/reservations.php`
  - `conflict_minutes` (env: `RESERVATION_CONFLICT_MINUTES`, default `120`)
- Usage
  - Controllers enforce conflicts using schedule ± conflict window (minutes)
  - Admin: `assignPriest()` + `getAvailablePriests()`
  - Staff: `show()` provides `$availablePriests` for the select; `assignPriest()` enforces the same check
  - Views annotate the select with a helper note showing the configured window

### 4) Daily Cancellations Digest (for completeness)

- Command: `app/Console/Commands/CheckUnacknowledgedCancellations.php`
  - Sends in‑app digest to Admin/Staff summarizing cancellations within the last N hours (default 24h)
- Schedule: `routes/console.php`
  - Daily at 10:00 → `reservations:check-cancellations --since=24`

### 5) Notifications Overhaul

- Centralized notifications table (custom): role-aware links to the appropriate views.
- Copy updated to reflect the new policy: staff contacts; admin assigns based on requestor’s choice.
- Null-venue fix in SMS when using custom locations (see Troubleshooting below).

### 6) UI/UX Polishing (dashboards + details)

- Compact cards across all dashboards; unified profile card design.
- Staff Notifications card shows only the two most recent items.
- Removed dashboard stat links and large placeholders for consistency.
- Staff Reservation Show:
  - Removed requestor-confirmation step (no longer used)
  - Shows Contacted state based on `contacted_at`
  - Indicates that Admin will assign a priest based on the requestor’s choice

---

## Data Model & Migrations

- Added: `preferred_officiant_id` (nullable FK) to `reservations`.
- Run migrations (Docker):

```powershell
# From project root on host
docker compose exec app php artisan migrate --force
```

---

## Files Touched

- Controllers
  - `app/Http/Controllers/Admin/ReservationController.php` (assignment status alignment, available priests, reschedule)
  - `app/Http/Controllers/Staff/ReservationController.php` (block assign; markContacted; reschedule)
- Views
  - `resources/views/admin/reservations/show.blade.php`
  - `resources/views/staff/reservations/show.blade.php`
  - `resources/views/requestor/reservations/create.blade.php`
  - `resources/views/admin/notifications/priest-declined.blade.php`
- Notifications Service
  - `app/Services/ReservationNotificationService.php` (reschedule notify; venue null fix; copy updates)
- Commands
  - `app/Console/Commands/RemindPendingPriestConfirmations.php`
  - `app/Console/Commands/CheckUnacknowledgedCancellations.php`
- Scheduling
  - `routes/console.php` (schedules for 09:00, 10:00, 11:00)
- Config
  - `config/reservations.php`
- Model & Request
  - `app/Models/Reservation.php` (preferredOfficiant)
  - `app/Http/Requests/ReservationRequest.php` (validation)
- Migration
  - `database/migrations/2025_10_25_120000_add_preferred_officiant_to_reservations.php`
- Routes
  - `routes/web.php` (disable staff assign-priest)

---

## Staff vs Admin Responsibilities

- Staff
  - Follow up unnoticed requests (>24h pending)
  - Mark as Contacted (stamps `contacted_at` and logs history)
  - Reschedule and Cancel (with notifications)
  - Approve logs review only; does not notify priests or move to confirmation
- Admin
  - Assign priest (conflict-checked), sets status to `pending_priest_confirmation`
  - Reschedule with conflict checks; re-notify the priest
  - Reject with reason

---

## Routes (affected)

- Admin
  - `admin.reservations.assign-priest` (POST /admin/reservations/{id}/assign-priest) – preselects preference; sets status to `pending_priest_confirmation`.
  - `admin.reservations.reschedule` (POST) – conflict-check + re-notify.
  - `admin.reservations.reject` (POST).
- Staff
  - `staff.reservations.mark-contacted` (POST) – stamps `contacted_at` and logs history.
  - `staff.reservations.assign-priest` – DISABLED per policy.
  - `staff.reservations.approve` – logs review only; no priest notify.
  - `staff.reservations.reschedule` – conflict-check + re-notify if applicable.
  - `staff.reservations.cancel` – cancels + notifies.
- Requestor
  - `requestor.reservations.store` – supports `preferred_officiant_id`.

### Backward compatibility

- Legacy confirmation link shim: `GET /requestor/reservations/{reservation_id}/confirm/{token?}`
  - Renders a friendly notice that requestor confirmation is no longer required.
  - Prevents 404s when users open old email links.

---

## Status Transitions (simplified)

- Request submitted → `pending`
- Adviser Approved → `adviser_approved`
  - Staff: contact requestor (stamps `contacted_at`), review
  - Admin: assign priest → `pending_priest_confirmation`
- Priest confirms → `approved` (or ‘confirmed’ depending on existing labels)
- Priest declines → `pending_priest_reassignment`
- Terminal: `rejected`, `cancelled`, `completed`

---

## How to Use

### Reschedule (Admin/Staff)
1. Open a reservation details page.
2. Use the Reschedule form to select a new date & time; add remarks (optional).
3. Submit. If a priest is assigned, the system checks for conflicts and then sets confirmation back to pending and re-notifies the priest.

### Pending Confirmation Reminders
- The scheduler runs daily to:
  - Remind priests at 24h of no response
  - Escalate to Admin/Staff at 48h of no response

---

## Configuration

- Conflict window: set `RESERVATION_CONFLICT_MINUTES` in `.env` (default 120)
- Ensure your cron (or platform scheduler) runs Laravel’s scheduler every minute:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

---

## Quick Tests

- Rebuild/clear caches (optional):
```powershell
# Windows PowerShell (Docker)
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear
```

- Verify routes:
```powershell
docker compose exec app php artisan route:list | findstr /C:"reschedule"
```

- Verify schedules:
```powershell
docker compose exec app php artisan schedule:list
```

- Simulate reminders now:
```powershell
docker compose exec app php artisan reservations:remind-priest-confirmations --remind-hours=0 --escalate-hours=0
```

- Simulate cancellations digest now (48h window):
```powershell
docker compose exec app php artisan reservations:check-cancellations --since=48
```

---

## Troubleshooting / Known Fixes

- Null venue in SMS for custom locations: SMS now uses `custom_venue_name` or a safe fallback.
- Status alignment: Admin assignment sets status to `pending_priest_confirmation` to match priest dashboards and reminders.
- Legacy token-based requestor confirmation screens remain in the repo but are not used by the current process.

---

## Known Limitations

- Venue/resource conflict checks are not yet enforced (only priests)
- Single‑datetime + ±window conflict model; no explicit durations/interval overlap
- No automated reassignment when a priest declines/cancels; manual reassignment only
- Notifications are synchronous (not queued) and rely on the scheduler being active

---

## Rollback / Maintenance

- Conflict window: Edit `.env` → `RESERVATION_CONFLICT_MINUTES` → `php artisan config:clear`
- Temporarily disable a schedule: comment out the corresponding entry in `routes/console.php`
- Git revert: revert the last commits that introduced these changes (see commit messages containing `feat(reservations)` and `feat(manage-reservations)`)

---

## Changelog Tags

- feat: preferred priest policy (requestor selects; admin assigns; staff restricted)
- feat: reschedule flow (Admin/Staff), priest confirmation reminders & escalation
- feat: conflict‑window availability filtering for priests
- feat: daily cancellations digest
