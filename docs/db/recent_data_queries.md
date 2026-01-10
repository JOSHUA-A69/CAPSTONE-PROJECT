# Recent Data Queries Documentation

This document provides SQL and Eloquent queries to retrieve the most recent records from the database tables in the application.

## Common Tables

### 1. Users
Get the 5 most recently registered users.

**SQL:**
```sql
SELECT * FROM users ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
User::latest()->take(5)->get();
```

### 2. User Roles
Get the recent role assignments.

**SQL:**
```sql
SELECT * FROM user_roles ORDER BY id DESC LIMIT 5;
```

**Eloquent:**
```php
UserRole::orderBy('id', 'desc')->take(5)->get();
```

### 3. Organizations
Get the recently added organizations.

**SQL:**
```sql
SELECT * FROM organizations ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
Organization::latest()->take(5)->get();
```

### 4. Services
Get the recently added services.

**SQL:**
```sql
SELECT * FROM services ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
Service::latest()->take(5)->get();
```

### 5. Venues
Get the recently added venues.

**SQL:**
```sql
SELECT * FROM venues ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
Venue::latest()->take(5)->get();
```

## Reservations & Scheduling

### 6. Reservations
Get the most recent reservations.

**SQL:**
```sql
SELECT * FROM reservations ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
Reservation::latest()->take(5)->get();
```

### 7. Reservation History
Get the most recent actions taken on reservations.

**SQL:**
```sql
SELECT * FROM reservation_history ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
ReservationHistory::latest()->take(5)->get();
```

### 8. Reservation Changes
Get details of recent changes made to reservations.

**SQL:**
```sql
SELECT * FROM reservation_changes ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
ReservationChange::latest()->take(5)->get();
```

### 9. Reservation Cancellations
Get the recent cancellations.

**SQL:**
```sql
SELECT * FROM reservation_cancellations ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
ReservationCancellation::latest()->take(5)->get();
```

### 10. Priest Declines
Get the recent declines by priests.

**SQL:**
```sql
SELECT * FROM priest_declines ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
PriestDecline::latest()->take(5)->get();
```

### 11. Liturgical Schedules
Get the recently scheduled liturgical events.

**SQL:**
```sql
SELECT * FROM liturgical_schedules ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
LiturgicalSchedule::latest()->take(5)->get();
```

### 12. Organization Booking Requests
Get the recent booking requests for organizations.

**SQL:**
```sql
SELECT * FROM organization_booking_requests ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
OrganizationBookingRequest::latest()->take(5)->get();
```

### 13. Reservation Organization (Pivot)
Get recent organization assignments to reservations.

**SQL:**
```sql
SELECT * FROM reservation_organization ORDER BY id DESC LIMIT 5;
```

**Eloquent (via DB facade as it is a pivot):**
```php
DB::table('reservation_organization')->orderBy('id', 'desc')->take(5)->get();
```

### 14. Reservation Priest (Pivot)
Get recent priest assignments to reservations.

**SQL:**
```sql
SELECT * FROM reservation_priest ORDER BY id DESC LIMIT 5;
```

**Eloquent (via DB facade as it is a pivot):**
```php
DB::table('reservation_priest')->orderBy('id', 'desc')->take(5)->get();
```

## Communication & Notifications

### 15. Messages
Get the most recent chat messages.

**SQL:**
```sql
SELECT * FROM messages ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
Message::latest()->take(5)->get();
```

### 16. Notifications
Get the most recent notifications sent.

**SQL:**
```sql
SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
Notification::latest()->take(5)->get();
```

### 17. Chat Resets
Get recent chat reset requests/logs.

**SQL:**
```sql
SELECT * FROM chat_resets ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
ChatReset::latest()->take(5)->get();
```

## System & Support

### 18. Report Requests
Get recent report generation requests.

**SQL:**
```sql
SELECT * FROM report_requests ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
ReportRequest::latest()->take(5)->get();
```

### 19. System Settings
Get recent system settings changes (or all settings).

**SQL:**
```sql
SELECT * FROM system_settings ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
SystemSetting::latest()->take(5)->get();
```

### 20. FAQs
Get recently added FAQs.

**SQL:**
```sql
SELECT * FROM faqs ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
Faq::latest()->take(5)->get();
```

### 21. Sessions
Get recent active sessions.

**SQL:**
```sql
SELECT * FROM sessions ORDER BY last_activity DESC LIMIT 5;
```

**Eloquent:**
```php
// Assuming no Session model exists by default, use DB
DB::table('sessions')->orderBy('last_activity', 'desc')->take(5)->get();
```

### 22. Jobs
Get recently queued jobs.

**SQL:**
```sql
SELECT * FROM jobs ORDER BY created_at DESC LIMIT 5;
```

**Eloquent:**
```php
DB::table('jobs')->orderBy('created_at', 'desc')->take(5)->get();
```
