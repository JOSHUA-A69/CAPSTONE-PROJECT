# 🔄 User Roles & Workflow Guide - eReligiousServices

Based on your workflow diagram and system requirements.

---

## 👥 User Roles Overview

| Role                     | Dashboard    | Primary Functions                                           |
| ------------------------ | ------------ | ----------------------------------------------------------- |
| **Requestor**            | `/requestor` | Submit reservation requests, view own reservations          |
| **Organization Adviser** | `/adviser`   | Approve/reject organization's reservation requests          |
| **CREaM Staff**          | `/staff`     | Monitor reservations, manage organizations, send follow-ups |
| **CREaM Administrator**  | `/admin`     | Assign priests, manage user accounts, final oversight       |
| **Priest**               | `/priest`    | Confirm/decline assignments, view calendar                  |

---

## 1️⃣ Requestor (Organization Member / Student / Faculty)

### **Dashboard**: http://localhost:8000/requestor

### **What They Can Do:**

✅ **Submit new reservation requests** for services (Mass, retreat, etc.)  
✅ **View status of their requests** (pending, approved, rejected)  
✅ **Cancel their own requests** (before approval)

### **Workflow:**

1. Click "New Reservation"
2. Fill out form:
    - Organization
    - Service type (Mass, retreat, etc.)
    - Venue
    - Schedule date
    - Purpose
    - Number of participants
3. Submit request
4. **System sends email** to requestor (confirmation) and adviser (notification)
5. Wait for adviser approval

### **Dashboard Features:**

-   **My Reservations** - List of all reservation requests
-   **New Reservation** - Submit new request form

---

## 2️⃣ Organization Adviser

### **Dashboard**: http://localhost:8000/adviser

### **What They Can Do:**

✅ **View reservations** submitted by their organization members  
✅ **Approve requests** (forwards to admin)  
✅ **Reject requests** (with reason)  
✅ **See unnoticed requests** (>24 hours pending)

### **Workflow (from your diagram):**

**When Requestor Submits:**

1. Receives email notification
2. Reviews reservation details:
    - Who requested
    - Service type
    - Schedule
    - Purpose
3. **Decision Point:**
    - ✅ **APPROVE** → Forwards to CREaM Admin
    - ❌ **REJECT** → Sends rejection email to requestor with reason

**After Approval:**

-   System sends email to requestor (approved)
-   System sends email to CREaM admin (new task)
-   System updates adviser_responded_at timestamp

### **Dashboard Features:**

-   **Pending Approvals** - Reservations waiting for decision
-   **Unnoticed Requests** badge - Shows requests >24h old
-   **Approve/Reject** buttons with reason field

---

## 3️⃣ CREaM Staff

### **Dashboard**: http://localhost:8000/staff

### **What They Can Do:**

✅ **Monitor ALL reservations** across all organizations  
✅ **Manage organizations** (create, edit, assign advisers)  
✅ **Send follow-up reminders** to advisers for unnoticed requests  
✅ **Cancel reservations** on behalf of users (if needed)

### **Workflow (from your diagram):**

**1. Manage Organizations:**

-   Click "Manage Organizations"
-   View list of all organizations
-   Create new organizations
-   Edit organization details
-   Assign advisers to organizations

**2. Monitor Reservations:**

-   Click "Manage Reservations"
-   View all reservation requests (all organizations)
-   See status: pending, approved, rejected
-   Filter by organization, date, status

**3. Follow-Up on Unnoticed Requests:**

-   Click "Unnoticed Requests" (if >24 hours)
-   Review reservations pending adviser approval
-   Send follow-up email reminder to adviser
-   Limited to 1 follow-up per 24 hours per request

**4. Automated Daily Check:**

-   System runs at 9:00 AM daily
-   Finds requests >24h without adviser response
-   Sends automated follow-up emails
-   Staff receives notification of follow-ups sent

### **Dashboard Features:**

-   **Manage Reservations** - View/monitor all reservations
-   **Manage Organizations** - Create/edit organizations
-   **Unnoticed Requests** - Send follow-ups
-   **Cancel Reservations** - Emergency cancellations

---

## 4️⃣ CREaM Administrator

### **Dashboard**: http://localhost:8000/admin

### **What They Can Do:**

✅ **Assign priests** to approved reservations  
✅ **Manage user accounts** (create, edit, delete users)  
✅ **View all reservations** (final oversight)  
✅ **Reject requests** (admin-level rejection)  
✅ **Check scheduling conflicts** before assigning priest

### **Workflow (from your diagram):**

**When Adviser Approves:**

1. Receives email notification
2. Reviews reservation details
3. **Checks available priests**:
    - System shows priests
    - Marks conflicts (already assigned at same time)
4. **Assigns priest** to reservation:
    - Select from dropdown
    - System checks conflicts
    - Sends email to priest
    - Sends email to requestor (priest assigned)
5. **Wait for priest confirmation**

**Alternative: Admin Rejection:**

-   If request doesn't meet requirements
-   Enters rejection reason
-   System sends email to requestor
-   Status: rejected

### **Dashboard Features:**

-   **Manage Reservations** - View approved requests
-   **Assign Priest** dropdown with conflict indicator
-   **User Accounts** - Manage all system users
-   **Available Priests** list with availability status

---

## 5️⃣ Priest

### **Dashboard**: http://localhost:8000/priest

### **What They Can Do:**

✅ **View assignments** from admin  
✅ **Confirm availability** for assigned reservations  
✅ **Decline assignments** (with reason - admin reassigns)  
✅ **View calendar** of all confirmed reservations

### **Workflow (from your diagram):**

**When Admin Assigns:**

1. Receives email notification
2. Reviews reservation details:
    - Service type
    - Schedule date/time
    - Venue
    - Requestor organization
3. **Decision Point:**
    - ✅ **CONFIRM** → Final approval, sends confirmation to all
    - ❌ **DECLINE** → Returns to admin for reassignment

**After Confirmation:**

-   Status changes to "approved"
-   System sends final confirmation email to:
    -   Requestor
    -   Adviser
    -   Admin
    -   Priest (calendar event)
-   Reservation appears on priest calendar

**After Decline:**

-   System removes priest assignment
-   Returns to "adviser_approved" status
-   Admin receives notification to reassign
-   Requestor notified of delay

### **Dashboard Features:**

-   **My Assignments** - Reservations assigned to this priest
-   **Pending Confirmation** badge - Awaiting response
-   **Confirm/Decline** buttons with reason field
-   **Calendar View** - All confirmed reservations

---

## 📧 Notification Flow Summary

### **1. Submission (Requestor → Adviser)**

-   ✉️ Requestor: Confirmation email
-   ✉️ Adviser: New request notification
-   📱 Adviser: SMS notification (if enabled)

### **2. Adviser Approval (Adviser → Admin)**

-   ✉️ Requestor: Request approved email
-   ✉️ Admin: New task notification
-   📱 Admin: SMS notification (if enabled)

### **3. Adviser Rejection (Adviser → Requestor)**

-   ✉️ Requestor: Rejection email with reason
-   📱 Requestor: SMS notification (if enabled)

### **4. Priest Assignment (Admin → Priest)**

-   ✉️ Priest: Assignment notification
-   ✉️ Requestor: Priest assigned notification
-   📱 Priest: SMS notification (if enabled)

### **5. Priest Confirmation (Priest → All)**

-   ✉️ Requestor: Final confirmation
-   ✉️ Adviser: Completed notification
-   ✉️ Admin: Completed notification
-   ✉️ Priest: Calendar event
-   📱 All: SMS notifications (if enabled)

### **6. Priest Decline (Priest → Admin)**

-   ✉️ Admin: Reassignment needed
-   ✉️ Requestor: Delay notice

### **7. Cancellation (Any → All)**

-   ✉️ All involved parties notified
-   📱 All: SMS notifications (if enabled)

### **8. Follow-Up (Staff → Adviser)**

-   ✉️ Adviser: Reminder email
-   ✉️ Staff: Follow-up confirmation
-   📱 Adviser: SMS reminder (if enabled)

---

## 🔄 Complete Workflow Example

### **Scenario: Student Organization Requests Mass**

**Step 1: Submission**

-   Mark (requestor) logs in: http://localhost:8000/login
-   Goes to: http://localhost:8000/requestor
-   Clicks "New Reservation"
-   Fills form:
    -   Organization: Student Council
    -   Service: Holy Mass
    -   Venue: Main Chapel
    -   Date: October 25, 2025, 9:00 AM
    -   Purpose: Monthly thanksgiving Mass
    -   Participants: 50
-   Submits
-   ✉️ Mark receives confirmation email
-   ✉️ Jane (adviser) receives notification email

**Step 2: Adviser Review**

-   Jane logs in: http://localhost:8000/login
-   Goes to: http://localhost:8000/adviser
-   Sees pending request in list
-   Clicks to review details
-   **Approves** request
-   ✉️ Mark receives approval email
-   ✉️ Admin receives new task email

**Step 3: Admin Assignment**

-   Admin logs in: http://localhost:8000/admin
-   Sees approved requests
-   Clicks on Mark's reservation
-   Checks available priests
-   Sees "Fr. Santos" available (no conflicts)
-   Assigns Fr. Santos
-   ✉️ Fr. Santos receives assignment email
-   ✉️ Mark notified priest assigned

**Step 4: Priest Confirmation**

-   Fr. Santos logs in: http://localhost:8000/priest
-   Sees assignment in pending list
-   Reviews details
-   **Confirms** availability
-   ✉️ Final confirmation sent to:
    -   Mark (requestor)
    -   Jane (adviser)
    -   Admin
    -   Fr. Santos (calendar event)

**Step 5: Service Day**

-   October 25, 2025, 9:00 AM
-   Mass proceeds as scheduled
-   ✅ Reservation complete

---

## ⚠️ Common Issues & Solutions

### **Issue: Clicking "Manage Organizations" goes to Requestor dashboard**

**Possible Causes:**

1. **Browser cache** - Old page cached
2. **Multiple tabs** - Logged in with different roles
3. **Session issue** - Role not properly set

**Solutions:**

**1. Clear Browser Cache:**

```
Press: Ctrl + Shift + R (hard refresh)
Or: Ctrl + Shift + Delete (clear cache)
```

**2. Check Current User Role:**

-   Look at the top navigation
-   Should say "jeff" (your name)
-   Click dropdown → Profile
-   Verify role is "staff"

**3. Clear Laravel Caches:**

```bash
docker exec laravel_app php artisan optimize:clear
```

**4. Logout and Login Again:**

-   Click "LOG OUT" button
-   Login again with staff credentials
-   Go to: http://localhost:8000/staff

**5. Verify Route in Browser:**
After clicking "Manage Organizations", check URL:

-   ✅ **Correct**: `http://localhost:8000/staff/organizations`
-   ❌ **Wrong**: `http://localhost:8000/requestor`

**6. Check Console for Errors:**

-   Press F12 (Developer Tools)
-   Click "Console" tab
-   Look for JavaScript errors
-   Look for network errors

---

## 🧪 Testing Each Role

### **Create Test Users:**

```bash
docker exec -it laravel_app php artisan tinker
```

Then paste:

```php
// Requestor
User::create(['first_name'=>'Mark','last_name'=>'Requestor','email'=>'mark@test.com','phone'=>'09171234567','password'=>bcrypt('password'),'role'=>'requestor','status'=>'active','email_verified_at'=>now()]);

// Adviser
User::create(['first_name'=>'Jane','last_name'=>'Adviser','email'=>'jane@test.com','phone'=>'09181234567','password'=>bcrypt('password'),'role'=>'adviser','status'=>'active','email_verified_at'=>now()]);

// Staff
User::create(['first_name'=>'Staff','last_name'=>'Member','email'=>'staff@test.com','phone'=>'09201234567','password'=>bcrypt('password'),'role'=>'staff','status'=>'active','email_verified_at'=>now()]);

// Admin
User::create(['first_name'=>'Admin','last_name'=>'CREaM','email'=>'admin@test.com','phone'=>'09191234567','password'=>bcrypt('password'),'role'=>'admin','status'=>'active','email_verified_at'=>now()]);

// Priest
User::create(['first_name'=>'Fr.','last_name'=>'Santos','email'=>'priest@test.com','phone'=>'09211234567','password'=>bcrypt('password'),'role'=>'priest','status'=>'active','email_verified_at'=>now()]);

// Test Organization
$adviser = User::where('email','jane@test.com')->first();
Organization::create(['org_name'=>'Student Council','org_desc'=>'University student government','adviser_id'=>$adviser->id]);
```

### **Test Workflow:**

1. **Login as Mark** (requestor@test.com / password)

    - Go to: http://localhost:8000/requestor
    - Submit reservation

2. **Login as Jane** (adviser@test.com / password)

    - Go to: http://localhost:8000/adviser
    - Approve reservation

3. **Login as Staff** (staff@test.com / password)

    - Go to: http://localhost:8000/staff
    - Click "Manage Organizations"
    - Should see: http://localhost:8000/staff/organizations ✅

4. **Login as Admin** (admin@test.com / password)

    - Go to: http://localhost:8000/admin
    - Assign priest

5. **Login as Priest** (priest@test.com / password)
    - Go to: http://localhost:8000/priest
    - Confirm assignment

---

## 📞 Quick Reference

| Action              | URL                                       |
| ------------------- | ----------------------------------------- |
| Login               | http://localhost:8000/login               |
| Requestor Dashboard | http://localhost:8000/requestor           |
| Adviser Dashboard   | http://localhost:8000/adviser             |
| Staff Dashboard     | http://localhost:8000/staff               |
| Staff Organizations | http://localhost:8000/staff/organizations |
| Staff Reservations  | http://localhost:8000/staff/reservations  |
| Admin Dashboard     | http://localhost:8000/admin               |
| Priest Dashboard    | http://localhost:8000/priest              |
| Logout              | Click red "LOG OUT" button                |

---

## ✅ Current Status

**What's Working:**
✅ All 5 dashboards created  
✅ Staff can manage organizations  
✅ Staff can manage reservations  
✅ Logout buttons on admin & staff dashboards  
✅ Role-based access control  
✅ Email notifications configured

**What's Pending:**
⏳ Frontend Blade views for reservation workflow  
⏳ SMS integration testing (currently disabled)  
⏳ Calendar view for priest

---

_Last Updated: October 17, 2025_  
_Based on: Capstone workflow diagram_  
_System: eReligiousServices - Holy Name University CREaM_
