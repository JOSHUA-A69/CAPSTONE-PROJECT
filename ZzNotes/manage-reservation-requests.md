# Manage Reservation Requests — Use Case Narrative

## Goal
Provide a single, traceable workflow to request, review, assign, confirm, and (if needed) cancel religious service reservations with complete audit history and role-aware notifications.

## Actors
- Requestor — submits the reservation on behalf of an organization.
- Organization Adviser — reviews and approves/rejects requests.
- CREaM Staff — follows up on stalled requests and coordinates communications.
- CREaM Administrator — assigns the officiant (priest) and oversees operations.
- Priest — accepts or declines the assigned reservation.
- System — records state changes, maintains history, and sends notifications.

## Key Data
- reservations: org_id, venue_id, custom_venue_name, service_id, schedule_date (datetime), purpose, status, officiant_id (nullable), adviser_responded_at, staff_followed_up_at, priest_confirmation, priest_confirmed_at, cancellation_reason, cancelled_by, timestamps.
- reservation_history: reservation_id, action (enum), notes/meta, performed_by, created_at.
- notifications (custom table): notification_id, user_id, reservation_id, message (string/HTML), type (enum), data (JSON), sent_at, read_at, timestamps.

Statuses (non-exhaustive, most used)
- pending → adviser_approved → admin_approved/pending_priest_confirmation → approved → completed
- Terminal: rejected, cancelled
- Reassignment: pending_priest_reassignment (after priest decline)

History actions (examples)
- submitted, adviser_approved, adviser_rejected
- admin_assigned_priest, priest_confirmed, priest_declined, priest_undeclined
- staff_follow_up_logged, cancelled

Notification types (examples)
- Approval, Reminder, System Alert, Assignment, Update, Urgent, Priest Declined

## Main Success Scenario
1) Request submission (Requestor)
   - Inputs: organization, service, schedule_date, venue or custom_venue_name, purpose.
   - System sets status=pending; writes history=submitted; notifies Adviser.

2) Adviser review
   - Adviser Approves → status=adviser_approved; history=adviser_approved; notifies Admin/Staff.
   - Adviser Rejects → status=rejected; history=adviser_rejected; notifies Requestor.

3) Admin assignment
   - Admin selects an officiant (dropdown of priests); sets officiant_id; status=pending_priest_confirmation or admin_approved (depending on flow); history=admin_assigned_priest; notifies the Priest (Assignment) and Staff.

4) Priest decision
   - Priest Confirms → status=approved; history=priest_confirmed; sets priest_confirmation/priest_confirmed_at; notifies Requestor, Adviser, Admin, Staff; dashboards reflect change.
   - Priest Declines → status=pending_priest_reassignment; history=priest_declined; clears or changes officiant_id per UI; notifies Admin/Staff with a reassignment screen.

5) Completion
   - After service fulfillment (outside this flow) the record may be marked completed; history entry recorded (if implemented).

## Alternate/Exception Flows
- A1: Adviser no response (>24h)
  - Staff manually follows up; history=staff_follow_up_logged; optional Reminder notification to Adviser/Requestor.
- A2: Admin reassigns priest after decline
  - Admin selects a new priest; history=admin_assigned_priest; notifies new Priest; status returns to pending_priest_confirmation.
- A3: Requestor-initiated cancellation
  - Requestor cancels; status=cancelled; history=canceled; notifies Adviser, Staff, Priest; Staff coordinates if unnoticed.
- A4: Validation/constraints
  - Schedule and required fields validated; officiant_id nullable until assignment; purpose required; custom_venue_name used when venue_id is not set.

## Business Rules & Notes
- Reservations are the source of truth; all transitions mirrored in reservation_history.
- Notifications are role-aware; links route to the correct role pages (admin vs staff views).
- Admin and Staff lists support search by purpose, requestor name, organization, and service.
- Priest decline logic is history-driven (no separate declines table).

## Known Gaps (as of now)
- No automatic 24-hour adviser follow-up/escalation (Staff follow-up is manual).
- No enforced priest availability/conflict check on assignment (no overlap guard in dropdown or on submit yet).
- No automated "unacknowledged cancellation" watcher (Staff can still review and follow up manually).
- Requestor confirmation token flow intentionally removed (Option B).

---

# How to Test Effectively

## Pre-requisites
- Run migrations and seed inside Docker (host DB hostnames can cause issues otherwise).

PowerShell (Windows) — optional reference:
```powershell
# Start services
docker compose up -d

# Run migrations and seed in the container (service name may be "app")
docker compose exec app php artisan migrate --seed

# Visit the app
# http://localhost:8000
```

Seeded users (from DatabaseSeeder)
- Admin: alice.admin@example.com / password
- Staff: bob.staff@example.com / password
- Adviser: cecilia.adviser@example.com / password
- Priest: daniel.priest@example.com / password
- Requestor: eve.requestor@example.com / password

Seeded data
- Services catalog and common venues.
- Sample organizations with the Adviser assigned.

## Test Scenarios

1) Happy path — submit → approve → assign → confirm
- Login as Requestor, create a reservation with valid schedule_date, service, and venue/custom venue; expect status=pending and a history entry.
- Login as Adviser, approve; expect status=adviser_approved; history updated; Admin/Staff receive notifications.
- Login as Admin, assign a Priest via dropdown; expect status=pending_priest_confirmation; history updated; Priest receives Assignment notification.
- Login as Priest, Confirm; expect status=approved; history=priest_confirmed; all roles receive updates; dashboards reflect change.
- Verify: notifications appear on each role’s dashboard and list; reservation_history shows the complete chain.

2) Adviser rejects
- Login as Adviser, Reject the pending request.
- Expect status=rejected; history=adviser_rejected; Requestor gets a notification and sees the outcome in dashboard/show.

3) No adviser response (>24h) — manual follow-up
- Create a pending request and simulate time passage (or test immediately):
  - Login as Staff, open the request and record a follow-up (mark contacted/follow-up).
  - Expect history=staff_follow_up_logged and optional reminders; no auto scheduler yet.

4) Priest declines & reassignment
- With a request in pending_priest_confirmation, login as Priest and Decline.
- Expect status=pending_priest_reassignment; history=priest_declined; Admin/Staff receive a "Priest Declined" notification with reassignment UI.
- Login as Admin, assign a different Priest; expect a fresh pending_priest_confirmation; history added; new Priest notified.

5) Requestor cancels
- Login as Requestor, cancel a reservation (any non-terminal state permitted by UI/policy).
- Expect status=cancelled; history=cancelled; Adviser, Staff, and assigned Priest get notifications.

6) Search and filters (Admin/Staff)
- As Admin and Staff, search by purpose, requestor name (first/last/full), org name, and service name; verify results update accordingly and sorting prioritizes actionable states (Admin).

7) Notifications — navigation & content
- Trigger multiple actions to generate notifications.
- Verify:
  - Staff dashboard shows message content and timestamp.
  - Navigation bell dropdown shows recent notifications for the role.
  - "View Details" routes to correct admin/staff pages (role-aware); priest-specific routes work for the Priest.

## Acceptance Checks
- Every action creates a reservation_history entry with performed_by.
- Status transitions follow the flow and never skip from pending directly to approved without required steps (adviser approval, priest confirmation when required).
- Notifications are delivered to relevant roles and link to role-appropriate pages.
- Cancels and declines are visible to Admin/Staff and can be acted on (reassignment/coordination).

## Troubleshooting Tips
- Migrations: run inside Docker to avoid host DB name/hostname mismatches.
- ENUM truncation errors: ensure the latest enum-adjustment migrations have been applied.
- Staff dashboard notifications: the app uses a custom notifications table (user_id/sent_at), not the Laravel default (no notifiable_id column).

## Future Enhancements (recommended)
- Scheduler to auto-flag/noify Adviser non-response after 24h (and show a "Needs follow-up" filter).
- Availability-aware priest assignment (exclude overlapping commitments; validate on submit to avoid race conditions).
- Optional watcher for unacknowledged cancellations to re-notify or badge items for Staff.
