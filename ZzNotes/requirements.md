# eReligiousServices — Requirements (from Thesis Ch. I and III)

## Summary
A centralized, web-based reservation and scheduling system tailored for the Center for Religious Education and Mission (CREaM) at Holy Name University. It digitizes service requests, approvals, officiant assignment, scheduling, notifications, reporting, and public visibility of approved events. It targets internal (HNU) and external requestors, with role-based access for CREaM Administrator, CREaM Staff, Organization Adviser, Requestor, and Priest.

## Objectives (aligned to thesis)
- Improve management of liturgical service requests at CREaM via a web-based system.
- Enhance participation by making services well-organized, transparent, and accessible.
- Reduce administrative workload; enable focus on ministry and outreach.
- Foster an organized, supportive environment for spiritual growth and service.

## In Scope
- Services managed by CREaM: liturgical celebrations (Mass, Lenten services), retreats, recollections, catechetical activities (Bible study, faith sharing, prayer and worship), outreach activities.
- Student religious organizations support: Himig Diwa Chorale, Acolytes and Lectors, Children of Mary, Student Catholic Action, Young Missionaries Club, Catechetical Organization.
- Internal (departments, orgs, advisers, students, alumni, HNU-affiliated) and external requestors (parishioners, alumni families, other parties).
- Public calendar showing approved events (e.g., daily Noon Mass) without authentication.

## Out of Scope / Limitations
- University-wide events handled by University Administration (e.g., Foundation Day, Baccalaureate Mass).
- Guaranteeing availability of choir members or altar servers (confirmation remains via phone/messenger).
- Financial transactions (payments, donations) — excluded.
- E-learning capabilities (online lessons/catechism modules) — excluded.

## Actors and Roles
- CREaM Administrator: final approvals, officiant assignment, service catalog, Noon Mass schedule, reporting, conflict monitoring.
- CREaM Staff: triage/process requests, communications, status updates, conflict checks, Noon Mass updates, generate reservation forms.
- Organization Adviser: review approve/reject for their org; optional announcements.
- Requestor (internal/external): browse times/services, submit requests, track status, receive notifications, cancel if needed.
- Priest (officiant): review assignments, confirm/decline, receive notifications, view details; confirmations/rejections recorded for audit.

## Functional Requirements
1) Authentication and Authorization
- Users can register (requestors) and sign in; email/identity verification as configured.
- RBAC (Admin, Staff, Adviser, Requestor, Priest) governs modules/actions.
- Admin/Staff manage user accounts (create, update, archive/reactivate), set roles, and link to organizations.

2) Organization Management
- Maintain organizations with advisers and metadata (name, description, contact).
- Staff/Admin can add/update/retire organizations; audit changes.

3) Services Catalog (Liturgical/Ministry Services)
- Manage a catalog of services (Mass, Retreat, Recollection, Catechesis, Outreach, etc.) with attributes (duration, lead role required, venue type, required resources, notes).
- Publicly list offered services.

4) Reservation Requests Lifecycle
- Requestor submits reservation: service type, date/time window, venue preference, participant count, purpose/details, organization (if applicable), contact.
- System validates and routes to Organization Adviser for review (when applicable).
- Adviser approves/rejects; rejection requires reason; requestor notified.
- If no adviser response within 1 day, system alerts Staff to follow up.
- Admin (or Staff) performs final approval and assigns an officiant (Priest; optionally other ministers) from availability list.
- Assigned Priest confirms/declines; only upon confirmation does the reservation finalize.
- Support cancellations initiated by Requestor/Admin/Staff with automatic notifications and audit trail.

5) Scheduling and Conflict Detection
- Maintain canonical schedule of approved events.
- Detect conflicts across time, venue, and officiants (overlaps for the same priest or location).
- Provide availability views for advisers, priests, venues, and services.

6) Calendars and Visibility
- Public calendar (no login) for approved events (e.g., Noon Mass) with filters by date range, service, organization, venue.
- Authenticated calendar with more detail for Staff/Admin/Adviser/Priest.

7) Notifications and Communications
- Automated notifications for submission, approvals/rejections, assignments, confirmations, cancellations, and overdue reviews (>1 day).
- Secure in-system message thread (chat-style) per reservation between Requestor and Admin/Staff; messages are part of the audit history.

8) Reporting and Exports
- Reports: approvals/rejections by period, booking summary, reservation statistics, organization activity.
- Filters: date ranges, organizations, services, advisers, priests, venues.
- Export to PDF, CSV, Excel; show “No Data Found” when applicable.

9) Noon Mass Schedule
- Dedicated management of daily Noon Mass; publish publicly; allow updates by Staff/Admin.

10) Audit and History
- Persist key actions (create, status changes, approvals, rejections, assignments, confirmations, cancellations, message exchanges).
- Read-only event history per reservation.

## Non-Functional Requirements
- Security: RBAC, input validation, CSRF, authorization policies, least privilege, auditability.
- Reliability: Durable persistence, queued email/notifications; idempotent actions for retries.
- Performance: Responsive calendar queries and conflict checks for campus-scale usage.
- Usability: Clear workflows per role; accessible public calendar; mobile-friendly pages.
- Maintainability: Modular Laravel code; tests for core workflows; migrations for schema evolution.
- Privacy: Protect personal data; log access to sensitive records.

## Assumptions
- Email is primary notification in development (e.g., MailHog); production SMTP configured later.
- External requestors can register and submit, subject to policies.
- Adviser review applies when a request is associated to an organization; otherwise routed to Staff/Admin.
- Venues and priests have working hours configured by Admin/Staff.

## Risks
- Human dependency: choir/altar server availability remains outside system control.
- Adoption: change management for staff and priests.
- Data quality: incorrect inputs lead to planning conflicts; mitigated by validation and reviews.
- Scope creep: pressure to add payments or e-learning; explicitly out of scope.

## Sample Acceptance Criteria
- Submitting a reservation creates a pending request, sends an acknowledgement email, and makes it visible to the designated adviser if linked to an organization.
- If adviser does not act within 24 hours, Staff receives an alert and the request appears in an “attention needed” queue.
- Admin can assign a priest only when the selected time slot has no conflicts for the priest and venue; conflicts are explained.
- Public calendar renders approved events only, excluding cancelled or pending items; viewable without login.
- Each reservation shows a complete timeline of actions and messages.
