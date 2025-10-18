# eReligiousServices — Roadmap (Thesis-Aligned)

This roadmap breaks the thesis into incremental, testable releases.

## Milestone 0 — Foundations (Day 0–2)

-   Ensure Docker/host setup; env configured; key generated; base app boots.
-   Seed roles (admin, staff, adviser, requestor, priest) and an admin user.
-   Basic auth (register/login) and role assignment UI (admin only).

## Milestone 1 — Organizations & Services (Week 1)

-   CRUD: organizations with adviser link.
-   CRUD: services catalog with attributes (duration, requires priest, notes).
-   Public: list services page; basic public home.
-   Tests: model factories + policies for orgs/services.

## Milestone 2 — Reservations (Week 2)

-   Requestor can submit reservations (service, date/time, org, purpose, participants).
-   Adviser review flow (approve/reject + reason), 24h overdue reminder to staff.
-   Admin/staff approval; assign priest from availability dropdown.
-   Notifications: submission, adviser decision, admin assignment.
-   Tests: reservation lifecycle + permission checks.

## Milestone 3 — Priest Confirmations & Conflicts (Week 3)

-   Priest portal: view assigned, confirm/decline.
-   Conflict detection: prevent overlaps on priest and venue; validation messages.
-   Calendar: internal calendar for roles.
-   Tests: conflict rules and priest confirmation.

## Milestone 4 — Public Calendar & Noon Mass (Week 4)

-   Public calendar of approved/confirmed events; filters.
-   Noon Mass module: CRUD + public listing.
-   Tests: calendar visibility rules; noon mass publishing.

## Milestone 5 — Messaging & Reporting (Week 5)

-   In-reservation message thread (requestor ↔ staff/admin); stored in audit history.
-   Reporting: approvals/rejections, booking summary, org activity; export CSV/PDF/Excel.
-   Tests: messaging permissions; report filters + no-data case.

## Milestone 6 — Polish & Docs (Week 6)

-   UX polish, accessibility pass, mobile layout.
-   Logging/audit improvements; admin dashboards.
-   README/docs update; onboarding script; smoke tests.

## Risks & Mitigations

-   Availability confirmation (choir/altar): keep outside scope; provide notes fields and reminders.
-   Adoption: training materials for staff/priests.
-   Performance: index schedule and reservation tables; paginate heavy lists.
-   Data quality: add strong validation and guided forms.

## Success Criteria

-   End-to-end reservation flow operates across all five roles with no double-booking.
-   Public calendar shows only approved/confirmed events.
-   Reports export correctly and match filtered data.
-   Core tests pass in CI.
