# Priest Decline Notification Flow

## Complete Workflow Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    PRIEST DECLINE WORKFLOW                               │
└─────────────────────────────────────────────────────────────────────────┘

1️⃣ INITIAL STATE
   ┌──────────────────┐
   │  Reservation     │
   │  Status:         │
   │  pending_priest_ │
   │  confirmation    │
   │                  │
   │  Officiant:      │
   │  Fr. John Doe    │
   └──────────────────┘

2️⃣ PRIEST REVIEWS ASSIGNMENT
   ┌──────────────────┐
   │   Priest View    │
   │  /priest/        │
   │  reservations    │
   │                  │
   │  [View Details]  │
   │  [Confirm]       │
   │  [Decline] ←───  │ Priest clicks decline
   └──────────────────┘
            │
            ↓
   ┌──────────────────┐
   │  Decline Form    │
   │                  │
   │  Reason:         │
   │  [Text Input]    │
   │  "I have another │
   │   commitment"    │
   │                  │
   │  [Submit]        │
   └──────────────────┘

3️⃣ SYSTEM PROCESSES DECLINE
   ┌─────────────────────────────────────────────────────────┐
   │  PriestReservationController::decline()                 │
   │                                                          │
   │  ✓ Validate reason                                      │
   │  ✓ Create PriestDecline record                          │
   │  ✓ Update Reservation:                                  │
   │    - status → 'pending_priest_reassignment'            │
   │    - officiant_id → NULL                               │
   │    - priest_confirmation → 'declined'                   │
   │  ✓ Create history entry                                 │
   │  ✓ Call notificationService.notifyPriestDeclined()     │
   └─────────────────────────────────────────────────────────┘
            │
            ↓
4️⃣ NOTIFICATIONS SENT
   ┌──────────────────────────────────────────────────────────┐
   │  ReservationNotificationService::notifyPriestDeclined()  │
   └──────────────────────────────────────────────────────────┘
            │
            ├─────────────────┬─────────────────────┐
            ↓                 ↓                     ↓
   ┌────────────────┐  ┌────────────────┐  ┌────────────────┐
   │  EMAIL to      │  │  EMAIL to      │  │  SMS to        │
   │  Admin #1      │  │  Admin #2      │  │  Admin #1      │
   │                │  │                │  │                │
   │  Subject:      │  │  Subject:      │  │  "URGENT:      │
   │  Priest        │  │  Priest        │  │  Priest        │
   │  Declined -    │  │  Declined -    │  │  declined      │
   │  Reassignment  │  │  Reassignment  │  │  reservation   │
   │  Needed #123   │  │  Needed #123   │  │  #123..."      │
   │                │  │                │  │                │
   │  [View & Assign│  │  [View & Assign│  │                │
   │   Priest]      │  │   Priest]      │  │                │
   └────────────────┘  └────────────────┘  └────────────────┘

5️⃣ ADMIN RECEIVES NOTIFICATION
   ┌─────────────────────────────────────────────────┐
   │  📧 Email Inbox                                 │
   │  ┌─────────────────────────────────────────┐   │
   │  │ ⚠️ Priest Declined - Reassignment Needed│   │
   │  │                                          │   │
   │  │ Dear CREaM Administrator,                │   │
   │  │                                          │   │
   │  │ A priest has declined their assignment  │   │
   │  │                                          │   │
   │  │ Reservation ID: #123                     │   │
   │  │ Service: Holy Mass                       │   │
   │  │ Date: Oct 25, 2025 - 10:00 AM          │   │
   │  │ Venue: Main Chapel                       │   │
   │  │                                          │   │
   │  │ Declined By: Fr. John Doe                │   │
   │  │ Reason: I have another commitment        │   │
   │  │                                          │   │
   │  │ [View Reservation & Assign Priest] ←───  │   │
   │  └─────────────────────────────────────────┘   │
   └─────────────────────────────────────────────────┘

6️⃣ ADMIN TAKES ACTION
   Admin clicks link ──→ /admin/reservations/123

   ┌──────────────────────────────────────────┐
   │  Reservation Details Page                 │
   │                                           │
   │  Status: 🟡 Pending Priest Reassignment  │
   │                                           │
   │  History:                                 │
   │  • Priest declined (Fr. John Doe)        │
   │    Reason: I have another commitment     │
   │  • Priest assigned (Fr. John Doe)        │
   │  • Adviser approved                       │
   │  • Submitted by requestor                 │
   │                                           │
   │  Assign New Priest:                       │
   │  [Select Priest ▼]                        │
   │  [Assign] ←────────── Admin assigns      │
   └──────────────────────────────────────────┘
            │
            ↓
7️⃣ NEW PRIEST ASSIGNED
   ┌──────────────────────────────────────────┐
   │  System sends notification to:            │
   │  • New priest (assignment email)          │
   │  • Requestor (update email)               │
   │  • Updates reservation status             │
   └──────────────────────────────────────────┘

8️⃣ FINAL STATE
   ┌──────────────────┐
   │  Reservation     │
   │  Status:         │
   │  pending_priest_ │
   │  confirmation    │
   │                  │
   │  Officiant:      │
   │  Fr. Jane Smith  │
   │  (NEW PRIEST)    │
   └──────────────────┘
```

## Database Changes During Flow

```
┌────────────────────────────────────────────────────────┐
│  RESERVATIONS TABLE                                    │
├────────────────────────────────────────────────────────┤
│  Before Decline:                                       │
│  - reservation_id: 123                                 │
│  - status: 'pending_priest_confirmation'              │
│  - officiant_id: 5 (Fr. John Doe)                     │
│  - priest_confirmation: 'pending'                      │
│                                                        │
│  After Decline:                                        │
│  - reservation_id: 123                                 │
│  - status: 'pending_priest_reassignment'              │
│  - officiant_id: NULL ← Cleared                       │
│  - priest_confirmation: 'declined'                     │
└────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────┐
│  PRIEST_DECLINES TABLE (New Record Created)           │
├────────────────────────────────────────────────────────┤
│  - id: 1                                               │
│  - reservation_id: 123                                 │
│  - priest_id: 5                                        │
│  - reason: "I have another commitment"                 │
│  - declined_at: 2025-10-18 14:30:00                   │
│  - reservation_activity_name: "Holy Mass"              │
│  - reservation_schedule_date: 2025-10-25 10:00:00     │
│  - reservation_venue: "Main Chapel"                    │
└────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────┐
│  RESERVATION_HISTORY TABLE (New Entry)                │
├────────────────────────────────────────────────────────┤
│  - reservation_id: 123                                 │
│  - performed_by: 5 (Fr. John Doe)                      │
│  - action: 'priest_declined'                           │
│  - remarks: "Priest declined availability. Reason:     │
│             I have another commitment"                 │
│  - performed_at: 2025-10-18 14:30:00                  │
└────────────────────────────────────────────────────────┘
```

## Key Points

✅ **Automatic Notification**: Admin is instantly notified via email and SMS
✅ **Clear Status**: Reservation status clearly indicates need for reassignment
✅ **Audit Trail**: Complete history maintained in database
✅ **Easy Reassignment**: Admin can quickly assign new priest
✅ **Seamless Flow**: New priest notification happens automatically

## Status Values Explained

| Status                        | Description                                       |
| ----------------------------- | ------------------------------------------------- |
| `pending_priest_confirmation` | Priest assigned, waiting for confirmation         |
| `pending_priest_reassignment` | Priest declined, admin needs to assign new priest |
| `confirmed`                   | Priest confirmed, service scheduled               |

## Files Involved

1. **Controller**: `app/Http/Controllers/Priest/ReservationController.php`
2. **Service**: `app/Services/ReservationNotificationService.php`
3. **Mailable**: `app/Mail/ReservationPriestDeclined.php`
4. **View**: `resources/views/emails/reservations/priest-declined.blade.php`
5. **Model**: `app/Models/PriestDecline.php`
6. **Migration**: `database/migrations/2025_10_18_060117_create_priest_declines_table.php`
