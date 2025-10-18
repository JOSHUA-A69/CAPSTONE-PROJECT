# eReligiousServices — Architecture (Initial)

This outlines a pragmatic Laravel architecture reflecting the thesis requirements (Ch. I and III), focusing on clear modules, database entities, and request flows.

## High-Level Modules

-   Auth & RBAC: Laravel auth + Policies/Gates; roles: admin, staff, adviser, requestor, priest.
-   Organizations: CRUD, adviser assignment.
-   Services Catalog: CRUD for liturgical/ministry services.
-   Reservations: submission, adviser review, admin approval, officiant assignment, confirmations, cancellations.
-   Scheduling & Conflicts: canonical events table, conflict checks across venue and officiants.
-   Calendar & Public Pages: public read-only calendar; authenticated calendar views.
-   Notifications: email (dev via Mailhog), optional in-app notifications; overdue review reminders.
-   Messaging: reservation-scoped message thread between requestor and staff/admin.
-   Reporting: filtered reports and exports (PDF/CSV/Excel).
-   Noon Mass: dedicated management and public publishing.
-   Audit Trail: event history timeline: status changes, approvals, assignments, confirmations, messages.

## Suggested Database Entities (initial ERD)

-   users (Laravel default + extensions: first_name, last_name, phone, status, role_id, etc.)
-   roles (admin, staff, adviser, requestor, priest)
-   organizations (name, description, adviser_user_id, status)
-   services (name, category, description, duration_minutes, requires_priest, venue_type, notes, is_active)
-   venues (name, location, capacity, is_active)
-   reservations (
    id, code, requester_user_id, organization_id?, service_id, venue_id?, title, purpose, details,
    start_at, end_at, participants_count, status [pending, adviser_approved, admin_approved, assigned, confirmed, declined, cancelled],
    adviser_id?, adviser_reviewed_at?, adviser_notes?, admin_id?, admin_reviewed_at?, cancellation_reason?,
    created_at, updated_at
    )
-   reservation_officiants (reservation_id, user_id [priest or other minister], role, status [assigned, confirmed, declined], responded_at)
-   reservation_messages (reservation_id, sender_user_id, message, created_at)
-   reservation_audits (reservation_id, actor_user_id?, action, meta_json, created_at)
-   schedules (denormalized final schedule of approved/confirmed events; could be the same as reservations when status confirmed)
-   noon_masses (date, start_at, end_at, celebrant_user_id?, notes, is_published)
-   reports_cache (optional for heavy reports/materialized views)

Notes:

-   Alternatively, a polymorphic activity log could replace reservation_audits.
-   If venues are simple, venue_id can be optional; otherwise model rooms/locations explicitly.

## Core Flows

1. Reservation Submission

-   Requestor -> POST reservation -> status=pending -> notify adviser (if org-bound) or staff/admin.

2. Adviser Review

-   Adviser approves -> status=adviser_approved; else declines with reason -> notify requestor.
-   If no action in 24h -> system reminder to staff.

3. Admin Approval & Assignment

-   Admin/staff reviews -> checks conflicts -> assigns priest -> tentative assignment -> notify priest.

4. Priest Confirmation

-   Priest confirms -> status=confirmed; decline -> back to admin/staff to reassign.

5. Cancellation

-   Any party (per policy) cancels -> status=cancelled -> notify all -> keep audit trail.

6. Calendars

-   Public: approved/confirmed events only. Private: full details per role.

## API/Routes (sketch)

-   Public
    -   GET / (home), GET /calendar (public events)
    -   GET /services (catalog)
-   Auth
    -   POST /register, POST /login, POST /logout
-   Reservations
    -   GET /reservations (index by role), POST /reservations (create)
    -   GET /reservations/{id}, PATCH /reservations/{id} (update details/status)
    -   POST /reservations/{id}/assign (admin/staff)
    -   POST /reservations/{id}/confirm (priest)
    -   POST /reservations/{id}/messages (thread)
    -   POST /reservations/{id}/cancel
-   Organizations
    -   CRUD /organizations, PATCH /organizations/{id}/adviser
-   Services
    -   CRUD /services
-   Calendar
    -   GET /calendar/private (role-filtered)
-   Reports
    -   GET/POST /reports (filters) -> download/export
-   Noon Mass
    -   CRUD /noon-masses, GET /noon-masses/public

## Policies & Permissions

-   Requestor: manage own reservations, read own messages, view public calendar.
-   Adviser: view org-linked reservations pending review, approve/reject; view org calendar subset.
-   Staff: manage reservations triage, generate forms, manage Noon Mass, run reports.
-   Admin: full access to all modules; user/role management.
-   Priest: view assigned reservations; confirm/decline; view own schedule.

## Implementation Notes

-   Use Laravel Policies for granular authorization.
-   Use Queues for emails/notifications.
-   Use database constraints + custom validators to enforce time-range conflicts.
-   Expose a simple ICS feed for public calendar (optional enhancement).
-   Blade with Tailwind for views; Livewire or Alpine.js for interactivity; keep it simple.
-   Add seeders for roles, basic users, sample services, and sample calendar.

## Testing Strategy

-   Feature tests for reservation lifecycle: submit -> adviser approve -> admin assign -> priest confirm -> calendar visible.
-   Conflict detection unit tests for overlapping times by priest and venue.
-   Policy tests covering role permissions.
-   Report generation tests (happy path + no data path).
