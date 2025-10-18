# 🎯 Quick Reference - What to Do First

## ✅ **Current Status**

-   ✅ Migrations ran successfully
-   ✅ Routes added and working
-   ✅ Controllers ready
-   ✅ Notifications configured
-   ⏳ Need to create test users
-   ⏳ Need to create frontend views

---

## 📋 **STEP-BY-STEP: What to Do Right Now**

### **STEP 1: Create Test Users** (5 minutes)

```bash
# Enter Laravel Tinker inside Docker
docker exec -it laravel_app php artisan tinker
```

Then paste this code (all at once):

```php
// Create test users
User::create(['first_name'=>'John','last_name'=>'Requestor','email'=>'requestor@test.com','phone'=>'09171234567','password'=>bcrypt('password'),'role'=>'requestor','status'=>'active','email_verified_at'=>now()]);
User::create(['first_name'=>'Jane','last_name'=>'Adviser','email'=>'adviser@test.com','phone'=>'09181234567','password'=>bcrypt('password'),'role'=>'adviser','status'=>'active','email_verified_at'=>now()]);
User::create(['first_name'=>'Admin','last_name'=>'CREaM','email'=>'admin@test.com','phone'=>'09191234567','password'=>bcrypt('password'),'role'=>'admin','status'=>'active','email_verified_at'=>now()]);
User::create(['first_name'=>'Staff','last_name'=>'Member','email'=>'staff@test.com','phone'=>'09201234567','password'=>bcrypt('password'),'role'=>'staff','status'=>'active','email_verified_at'=>now()]);
$priest = User::create(['first_name'=>'Father','last_name'=>'Priest','email'=>'priest@test.com','phone'=>'09211234567','password'=>bcrypt('password'),'role'=>'priest','status'=>'active','email_verified_at'=>now()]);
$adviser = User::where('email','adviser@test.com')->first();
App\Models\Organization::create(['org_name'=>'Test Organization','org_desc'=>'Test','adviser_id'=>$adviser->id]);
echo "✅ All test users created!\n";
```

Type `exit` to leave Tinker.

---

### **STEP 2: Try Logging In**

1. Go to: http://localhost:8000/login
2. Try: `requestor@test.com` / `password`
3. You'll see dashboard (or error if view doesn't exist yet)

---

### **STEP 3: Test Routes**

Visit these URLs (after logging in as appropriate role):

| URL                                          | Expected Result                                               |
| -------------------------------------------- | ------------------------------------------------------------- |
| http://localhost:8000/requestor/reservations | View not found (need to create view) OR see reservations list |
| http://localhost:8000/adviser/reservations   | View not found OR see pending approvals                       |
| http://localhost:8000/admin/reservations     | View not found OR see all reservations                        |

**If you see "View not found"** → This is GOOD! Routes work, just need to create views.
**If you see "Forbidden" or "Unauthorized"** → Check you're logged in as correct role.

---

## 🚨 **Common Issues & Solutions**

### Issue: "SQLSTATE[HY000] [2002] No such host"

**Solution**: Use Docker commands:

```bash
docker exec laravel_app php artisan migrate
```

### Issue: "View [requestor.reservations.index] not found"

**Solution**: This is EXPECTED! You haven't created views yet. This means routes ARE working.

### Issue: "Route not found"

**Solution**: Check routes:

```bash
docker exec laravel_app php artisan route:list --name=reservations
```

### Issue: Can't login

**Solution**: Make sure you created test users (Step 1 above).

---

## 🎯 **Testing Order (After Creating Users)**

### **Test 1: Can you submit a reservation?**

1. Login as: `requestor@test.com` / `password`
2. Go to: http://localhost:8000/requestor/reservations/create
3. Fill form and submit
4. Check: MailHog (http://localhost:8025) for email

**If you see "View not found"** → You need to create the create.blade.php view first.

### **Test 2: Can adviser see the request?**

1. Logout (http://localhost:8000/logout)
2. Login as: `adviser@test.com` / `password`
3. Go to: http://localhost:8000/adviser/reservations
4. Should see pending reservation (if view exists)

### **Test 3: Can admin assign priest?**

1. Login as: `admin@test.com` / `password`
2. Go to: http://localhost:8000/admin/reservations
3. Click reservation → assign priest dropdown

### **Test 4: Can priest confirm?**

1. Login as: `priest@test.com` / `password`
2. Go to: http://localhost:8000/priest/reservations
3. Confirm availability

---

## 📊 **What You Can Test WITHOUT Views**

Even without frontend views, you can test the backend using:

### Option 1: Direct Database

Use PHPMyAdmin (http://localhost:8080) to:

-   Insert test reservation directly into `reservations` table
-   Check status changes
-   View `reservation_history` entries

### Option 2: API Testing (Postman/Insomnia)

Send POST requests to:

-   `/requestor/reservations` (submit)
-   `/adviser/reservations/{id}/approve` (approve)
-   `/admin/reservations/{id}/assign-priest` (assign)

### Option 3: Tinker

```bash
docker exec -it laravel_app php artisan tinker

# Create a test reservation
$reservation = Reservation::create([
    'user_id' => 1,
    'org_id' => 1,
    'service_id' => 1,
    'venue_id' => 1,
    'schedule_date' => now()->addDays(7),
    'status' => 'pending',
    'purpose' => 'Test Mass',
    'participants_count' => 50
]);
```

---

## 🎨 **When to Create Views?**

You have two options:

### **Option A: Test Backend First** (Recommended)

1. ✅ Create test users
2. ✅ Use PHPMyAdmin/Tinker to create test data
3. ✅ Verify notifications work (MailHog)
4. ✅ Verify status transitions work
5. ✅ Verify history tracking works
6. **Then** create views

**Advantage**: You know backend works before building UI.

### **Option B: Create Views First**

1. Create basic views for all roles
2. Then test complete workflow through UI

**Advantage**: Better user experience during testing.

---

## ✅ **What to Answer Your Question:**

> "Should I log in as requestor and submit a reservation to see if others have same functionalities?"

### **SHORT ANSWER:**

**YES, but you need to create the views first!**

### **HERE'S THE ORDER:**

1. ✅ **Create test users** (Step 1 above) ← Do this NOW
2. ⏳ **Create basic views** (at least the "index" and "show" views for each role)
3. ✅ **Test workflow**: Requestor → Adviser → Admin → Priest
4. ✅ **Verify notifications** in MailHog
5. ✅ **Check database** for status changes

### **WHAT YOU CAN DO RIGHT NOW (No Views Needed):**

1. Create test users (Step 1)
2. Login and verify authentication works
3. Check MailHog is working (http://localhost:8025)
4. Use PHPMyAdmin to verify database tables exist
5. Run: `docker exec laravel_app php artisan route:list --name=reservations`

---

## 📞 **Quick Commands Reference**

```bash
# Run migrations
docker exec laravel_app php artisan migrate

# Create test data
docker exec -it laravel_app php artisan tinker

# Check routes
docker exec laravel_app php artisan route:list

# Check unnoticed reservations
docker exec laravel_app php artisan reservations:check-unnoticed

# View logs
docker exec laravel_app tail -f storage/logs/laravel.log

# Clear cache
docker exec laravel_app php artisan cache:clear
docker exec laravel_app php artisan config:clear
docker exec laravel_app php artisan route:clear
```

---

## 🎉 **What's Ready to Test:**

✅ **Backend**:

-   Database schema
-   Controllers
-   Notification system
-   Email templates
-   Routes
-   Middleware
-   Role-based access

⏳ **Frontend** (Need to create):

-   Blade views
-   Forms
-   UI/UX
-   JavaScript interactions

---

**RECOMMENDATION**: Create test users NOW, then decide if you want to build views first or test backend directly through database/Tinker. Either way works!

**See full details in**: `TESTING_GUIDE.md`
