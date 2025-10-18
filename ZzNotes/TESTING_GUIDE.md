# 🧪 Complete Testing Guide - eReligiousServices Workflow

## ✅ Migrations Successfully Run!

Your new reservation management tables are now in the database:

-   ✅ `reservations` table enhanced with priest assignment, notification tracking
-   ✅ `reservation_history` actions expanded

---

## 📋 **TESTING ROADMAP - What to Do First**

### **Phase 1: Create Test Users** (Do This First!) 👥

Before you can test the workflow, you need users for each role. Here's how:

#### Option A: Using PHPMyAdmin (Easiest)

1. Open: http://localhost:8080
2. Login with your MySQL credentials
3. Select database: `ereligious_db`
4. Go to `users` table
5. Manually create test users

#### Option B: Using Laravel Tinker (Recommended)

```bash
# Run tinker inside Docker
docker exec -it laravel_app php artisan tinker
```

Then paste this code to create all test users:

```php
// 1. Create Requestor
$requestor = User::create([
    'first_name' => 'John',
    'last_name' => 'Requestor',
    'email' => 'requestor@test.com',
    'phone' => '09171234567',
    'password' => bcrypt('password'),
    'role' => 'requestor',
    'status' => 'active',
    'email_verified_at' => now()
]);
echo "✅ Requestor created: requestor@test.com / password\n";

// 2. Create Adviser
$adviser = User::create([
    'first_name' => 'Jane',
    'last_name' => 'Adviser',
    'email' => 'adviser@test.com',
    'phone' => '09181234567',
    'password' => bcrypt('password'),
    'role' => 'adviser',
    'status' => 'active',
    'email_verified_at' => now()
]);
echo "✅ Adviser created: adviser@test.com / password\n";

// 3. Create Admin
$admin = User::create([
    'first_name' => 'Admin',
    'last_name' => 'CREaM',
    'email' => 'admin@test.com',
    'phone' => '09191234567',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'status' => 'active',
    'email_verified_at' => now()
]);
echo "✅ Admin created: admin@test.com / password\n";

// 4. Create Staff
$staff = User::create([
    'first_name' => 'Staff',
    'last_name' => 'Member',
    'email' => 'staff@test.com',
    'phone' => '09201234567',
    'password' => bcrypt('password'),
    'role' => 'staff',
    'status' => 'active',
    'email_verified_at' => now()
]);
echo "✅ Staff created: staff@test.com / password\n";

// 5. Create Priest
$priest = User::create([
    'first_name' => 'Father',
    'last_name' => 'Priest',
    'email' => 'priest@test.com',
    'phone' => '09211234567',
    'password' => bcrypt('password'),
    'role' => 'priest',
    'status' => 'active',
    'email_verified_at' => now()
]);
echo "✅ Priest created: priest@test.com / password\n";

// 6. Create Test Organization and assign adviser
$org = App\Models\Organization::create([
    'org_name' => 'Test Student Organization',
    'org_desc' => 'A test organization for demo purposes',
    'adviser_id' => $adviser->id
]);
echo "✅ Organization created with adviser assigned\n";

echo "\n📋 TEST CREDENTIALS:\n";
echo "===================\n";
echo "Requestor: requestor@test.com / password\n";
echo "Adviser:   adviser@test.com / password\n";
echo "Admin:     admin@test.com / password\n";
echo "Staff:     staff@test.com / password\n";
echo "Priest:    priest@test.com / password\n";
```

---

### **Phase 2: Test the Complete Workflow** 🔄

Now you can test the entire swim lane diagram workflow!

#### **Step 1: Login as Requestor**

```
URL: http://localhost:8000/login
Email: requestor@test.com
Password: password
```

**What to do:**

1. Go to: http://localhost:8000/requestor/reservations
2. Click "Create New Reservation"
3. Fill out the form:
    - Select Service (e.g., "Mass")
    - Choose Date & Time
    - Select Venue
    - Select Organization: "Test Student Organization"
    - Enter Purpose
    - Add details
4. Submit the form

**Expected Result:**

-   ✅ Reservation created with status: `pending`
-   ✅ You receive confirmation (notification)
-   ✅ Adviser receives notification (check MailHog)

**Check MailHog:**

-   Open: http://localhost:8025
-   You should see 2 emails:
    -   Confirmation to requestor
    -   Notification to adviser

---

#### **Step 2: Login as Adviser**

```
URL: http://localhost:8000/login
Email: adviser@test.com
Password: password
```

**What to do:**

1. Go to: http://localhost:8000/adviser/reservations
2. You should see the pending reservation
3. Click on it to view details
4. Choose one:
    - **Approve** (enter optional remarks)
    - **Reject** (enter reason required)

**Expected Result if Approved:**

-   ✅ Status changes: `pending` → `adviser_approved`
-   ✅ Requestor gets approval email
-   ✅ Admin gets notification
-   ✅ SMS sent (if enabled)

**Expected Result if Rejected:**

-   ✅ Status changes: `pending` → `rejected`
-   ✅ Requestor gets rejection email with reason
-   ✅ Workflow stops here

---

#### **Step 3: Login as Admin**

```
URL: http://localhost:8000/login
Email: admin@test.com
Password: password
```

**What to do:**

1. Go to: http://localhost:8000/admin/reservations
2. Find reservations with status: `adviser_approved`
3. Click to view details
4. **Assign a Priest:**
    - Select from dropdown (should show: Father Priest)
    - Dropdown should show availability
    - Enter optional remarks
    - Click "Assign Priest"

**Expected Result:**

-   ✅ Status changes: `adviser_approved` → `admin_approved`
-   ✅ Priest receives assignment notification
-   ✅ Requestor gets update
-   ✅ `priest_confirmation` set to: `pending`

---

#### **Step 4: Login as Priest**

```
URL: http://localhost:8000/login
Email: priest@test.com
Password: password
```

**What to do:**

1. Go to: http://localhost:8000/priest/reservations
2. You should see assigned reservation
3. Click to view details
4. Choose one:
    - **Confirm Availability** (enter optional remarks)
    - **Decline** (enter reason - will need reassignment)

**Expected Result if Confirmed:**

-   ✅ Status changes: `admin_approved` → `approved` ✅
-   ✅ `priest_confirmation`: `pending` → `confirmed`
-   ✅ All parties receive final confirmation
-   ✅ **WORKFLOW COMPLETE!**

**Expected Result if Declined:**

-   ✅ Status reverts: `admin_approved` → `adviser_approved`
-   ✅ Priest assignment removed
-   ✅ Admin notified to assign different priest

---

#### **Step 5: Check Staff Monitoring**

```
URL: http://localhost:8000/login
Email: staff@test.com
Password: password
```

**What to do:**

1. Go to: http://localhost:8000/staff/reservations
2. View all reservations
3. Go to: http://localhost:8000/staff/reservations/unnoticed
4. See requests pending >24 hours (won't have any yet in fresh test)

---

### **Phase 3: Test Edge Cases** ⚠️

#### **Test 1: Cancellation**

-   Login as Requestor
-   Go to your reservation
-   Click "Cancel"
-   Enter reason
-   **Expected**: All parties notified (adviser, priest if assigned, admin, staff)

#### **Test 2: Scheduling Conflict**

-   Create 2 reservations with same date/time
-   Approve both as adviser
-   Try to assign same priest to both
-   **Expected**: Second assignment blocked with error message

#### **Test 3: Unnoticed Request Detection**

You need to wait 24 hours OR manually test:

```bash
# Run the command manually
docker exec laravel_app php artisan reservations:check-unnoticed --send-notifications
```

**Expected**: Follow-up sent to adviser, staff notified

---

### **Phase 4: Check Notifications** 📧

#### **Email Testing (MailHog)**

Open: http://localhost:8025

You should see emails for:

-   ✅ Reservation submitted
-   ✅ Adviser approved
-   ✅ Priest assigned
-   ✅ Priest confirmed
-   ✅ Cancellations

#### **SMS Testing**

Currently set to: `SMS_ENABLED=false`

To enable:

1. Get Semaphore API key from https://semaphore.co/
2. Update `.env` in Docker:
    ```
    SMS_ENABLED=true
    SEMAPHORE_API_KEY=your_key_here
    ```
3. Restart containers

---

### **Phase 5: Database Verification** 🗄️

Open PHPMyAdmin: http://localhost:8080

**Check these tables:**

1. **`reservations` table**

    - Check status progression
    - Check notification timestamps
    - Check priest assignment (`officiant_id`)
    - Check `priest_confirmation` status

2. **`reservation_history` table**
    - Check all actions logged
    - Check timestamps
    - Check `performed_by` user IDs

**Sample Query:**

```sql
-- View complete reservation with history
SELECT
    r.reservation_id,
    r.status,
    r.priest_confirmation,
    s.service_name,
    u.first_name as requestor_name,
    p.first_name as priest_name
FROM reservations r
LEFT JOIN services s ON r.service_id = s.service_id
LEFT JOIN users u ON r.user_id = u.id
LEFT JOIN users p ON r.officiant_id = p.id;

-- View all history
SELECT
    rh.action,
    rh.remarks,
    rh.performed_at,
    u.first_name as performed_by_name
FROM reservation_history rh
LEFT JOIN users u ON rh.performed_by = u.id
ORDER BY rh.performed_at DESC;
```

---

## 🎯 **Testing Checklist**

Use this checklist to track your testing:

-   [ ] **Setup**

    -   [ ] Migrations ran successfully
    -   [ ] Test users created (all 5 roles)
    -   [ ] Test organization created
    -   [ ] Adviser assigned to organization

-   [ ] **Happy Path (Approval Flow)**

    -   [ ] Requestor submits reservation
    -   [ ] Adviser receives notification
    -   [ ] Adviser approves request
    -   [ ] Admin receives notification
    -   [ ] Admin assigns priest
    -   [ ] Priest receives notification
    -   [ ] Priest confirms availability
    -   [ ] Final status = `approved`

-   [ ] **Rejection Flows**

    -   [ ] Adviser rejects → Requestor notified
    -   [ ] Admin rejects → Requestor notified
    -   [ ] Priest declines → Goes back to admin

-   [ ] **Cancellations**

    -   [ ] Requestor cancels own reservation
    -   [ ] Staff cancels reservation
    -   [ ] All parties notified

-   [ ] **Notifications**

    -   [ ] Emails sent (check MailHog)
    -   [ ] SMS sent (if enabled)
    -   [ ] All parties receive correct emails

-   [ ] **Edge Cases**

    -   [ ] Scheduling conflicts prevented
    -   [ ] Unnoticed request detection works
    -   [ ] Follow-up notifications sent

-   [ ] **Database**
    -   [ ] Status transitions correct
    -   [ ] Timestamps recorded
    -   [ ] History logged
    -   [ ] Relationships work

---

## 🚨 **Important Notes**

### Running Commands in Docker

Always use `docker exec` for Laravel commands:

```bash
# ✅ Correct
docker exec laravel_app php artisan migrate
docker exec laravel_app php artisan tinker
docker exec laravel_app php artisan reservations:check-unnoticed

# ❌ Wrong (won't connect to Docker database)
php artisan migrate
```

### Accessing Your Application

-   **Laravel App**: http://localhost:8000
-   **PHPMyAdmin**: http://localhost:8080
-   **MailHog**: http://localhost:8025 (email testing)

### Creating Views

Remember, you still need to create the Blade view files. The routes and controllers are ready, but visiting the URLs will show "View not found" errors until you create the views.

---

## 📊 **Expected Status Flow**

```
[Requestor submits]
        ↓
    pending
        ↓
[Adviser approves]
        ↓
adviser_approved
        ↓
[Admin assigns priest]
        ↓
admin_approved (priest_confirmation: pending)
        ↓
[Priest confirms]
        ↓
    approved ✅ (priest_confirmation: confirmed)
```

---

## 🎓 **What You're Testing**

This testing validates your complete capstone thesis implementation:

1. ✅ Digital workflow automation
2. ✅ Multi-role coordination
3. ✅ Automated notifications
4. ✅ Scheduling conflict prevention
5. ✅ Audit trail/history
6. ✅ Follow-up monitoring

---

## 💡 **Next Steps After Testing**

Once basic workflow testing is complete:

1. **Create Frontend Views** - So you have actual UI instead of errors
2. **Style with Tailwind/Bootstrap** - Make it look professional
3. **Add JavaScript** - For interactivity, dropdowns, confirmations
4. **Create Dashboards** - Summary cards, statistics, calendar views
5. **Add Validation** - Client-side form validation
6. **Testing** - User acceptance testing with real users
7. **Documentation** - User guides, admin manual

---

**Ready to start testing!** Follow the steps above and you'll see your entire reservation workflow in action! 🚀
