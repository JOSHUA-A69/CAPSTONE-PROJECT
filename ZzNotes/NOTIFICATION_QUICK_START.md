# 🚀 Quick Start Checklist - Notification System

## ✅ Pre-Flight Checklist

Before testing the new notification system, make sure:

-   [ ] Database is running
-   [ ] Laravel app is running (`php artisan serve`)
-   [ ] You have admin user credentials
-   [ ] You have priest user credentials

## 📝 Setup Steps

### 1. Run Database Migration

```bash
cd /c/Users/Hannah/Desktop/CAPSTONE-PROJECT
php artisan migrate
```

Expected output:

```
Migrating: 2025_10_18_114106_add_data_column_to_notifications_table
Migrated:  2025_10_18_114106_add_data_column_to_notifications_table
```

### 2. Clear Cache (Optional but recommended)

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 3. Verify Routes

```bash
php artisan route:list --path=admin/notifications
```

Should show:

```
admin.notifications.index
admin.notifications.count
admin.notifications.recent
admin.notifications.show
admin.notifications.priest-declined
admin.notifications.mark-read
admin.notifications.mark-all-read
```

## 🧪 Testing Checklist

### Test 1: Priest Decline Flow

-   [ ] Login as priest
-   [ ] Navigate to `/priest/reservations`
-   [ ] Find an assigned reservation
-   [ ] Click "Decline" button
-   [ ] Enter reason: "I have another commitment"
-   [ ] Submit the decline
-   [ ] See success message
-   [ ] Logout

### Test 2: Admin Notification Bell

-   [ ] Login as admin
-   [ ] Look at top navigation bar
-   [ ] Verify bell icon (🔔) is visible
-   [ ] Check if red badge appears with number (1)
-   [ ] Badge should show unread count

### Test 3: Notification Dropdown

-   [ ] Click the bell icon
-   [ ] Dropdown menu should open
-   [ ] See "Priest Declined" notification with red badge
-   [ ] See blue dot indicating unread
-   [ ] See timestamp ("X minutes ago")
-   [ ] Notification should show brief message

### Test 4: Priest Declined Detail Page

-   [ ] Click on "Priest Declined" notification
-   [ ] Redirected to priest-declined detail page
-   [ ] Verify page shows:
    -   [ ] ⚠️ Red alert header
    -   [ ] "Priest Declined Assignment" title
    -   [ ] "Action Required: Reassignment Needed"
    -   [ ] "Dear CREaM Administrator" greeting
    -   [ ] 🚨 Alert message
    -   [ ] Reservation Details section with:
        -   [ ] Reservation ID
        -   [ ] Service Type
        -   [ ] Requestor name
        -   [ ] Date & Time
        -   [ ] Venue
    -   [ ] Declined By section with priest name
    -   [ ] Reason for Decline box with the reason
    -   [ ] "View Reservation & Assign Priest" button (purple)
    -   [ ] Next Steps list (4 items)
    -   [ ] Footer with CREaM branding

### Test 5: Action Button

-   [ ] Click "View Reservation & Assign Priest" button
-   [ ] Redirected to reservation detail page
-   [ ] Verify reservation shows `pending_priest_reassignment` status
-   [ ] Verify officiant is cleared (empty)
-   [ ] Priest assignment dropdown is available

### Test 6: Assign New Priest

-   [ ] Select a new priest from dropdown
-   [ ] Click "Assign Priest" button
-   [ ] Verify success message
-   [ ] Verify new priest is assigned
-   [ ] (New priest should receive email notification)

### Test 7: Mark as Read

-   [ ] Go to `/admin/notifications`
-   [ ] See all notifications list
-   [ ] Unread notification has blue background
-   [ ] Click "Mark All as Read" button
-   [ ] Page refreshes
-   [ ] Blue backgrounds removed
-   [ ] Go back to navigation
-   [ ] Badge count should be 0
-   [ ] Badge should disappear

### Test 8: Auto-Refresh (Wait Test)

-   [ ] Create another priest decline
-   [ ] Login as admin (keep page open)
-   [ ] Wait 30 seconds
-   [ ] Badge count should update automatically
-   [ ] No page refresh needed

## 🐛 Troubleshooting

### Issue: Bell icon not showing

**Solution:**

-   Clear browser cache
-   Check if logged in as admin/staff
-   Verify navigation.blade.php has the changes

### Issue: Badge count always 0

**Solution:**

```bash
# Check if notifications exist
php artisan tinker
>>> App\Models\Notification::count()
>>> App\Models\Notification::whereNull('read_at')->count()
```

### Issue: Dropdown shows "Loading..."

**Solution:**

-   Check browser console for errors
-   Verify route exists: `/admin/notifications/recent`
-   Check if JavaScript is enabled

### Issue: Detail page blank or error

**Solution:**

-   Verify notification has reservation_id
-   Check if reservation exists
-   Verify data column has valid JSON

### Issue: Routes not found

**Solution:**

```bash
php artisan route:clear
php artisan cache:clear
```

### Issue: Migration fails

**Solution:**

```bash
# Check if database is running
php artisan migrate:status

# If already migrated:
php artisan migrate:rollback --step=1
php artisan migrate
```

## 📊 Verification Queries

Check database after test:

```sql
-- Check notifications created
SELECT * FROM notifications
WHERE type = 'Priest Declined'
ORDER BY sent_at DESC
LIMIT 5;

-- Check unread count for admin
SELECT COUNT(*)
FROM notifications
WHERE user_id = [ADMIN_USER_ID]
AND read_at IS NULL;

-- Check notification data
SELECT
  notification_id,
  user_id,
  reservation_id,
  message,
  type,
  data,
  read_at,
  sent_at
FROM notifications
WHERE type = 'Priest Declined';
```

## ✅ Success Criteria

The notification system is working if:

✅ Bell icon appears for admin/staff
✅ Badge shows correct unread count
✅ Dropdown loads recent notifications
✅ Clicking notification opens detail page
✅ Detail page shows all required information
✅ Format matches your requested example
✅ Action button redirects to reservation
✅ Mark as read functionality works
✅ Badge count updates automatically

## 🎯 Quick Demo Script

For a fast demo:

```bash
# 1. Setup
php artisan migrate
php artisan cache:clear

# 2. As Priest (Browser 1)
- Login as priest
- Decline reservation with reason: "secret"

# 3. As Admin (Browser 2)
- Login as admin
- Click bell icon 🔔(1)
- Click "Priest Declined" notification
- Review the detail page ✨
- Click "View Reservation & Assign Priest"
- Assign new priest
```

Total time: ~2 minutes

## 📁 Files to Review

If something doesn't work, check these files:

1. **Model**: `app/Models/Notification.php`
2. **Controller**: `app/Http/Controllers/Admin/NotificationController.php`
3. **Service**: `app/Services/ReservationNotificationService.php`
4. **Routes**: `routes/web.php` (search for "notifications")
5. **Navigation**: `resources/views/layouts/navigation.blade.php`
6. **Views**:
    - `resources/views/admin/notifications/index.blade.php`
    - `resources/views/admin/notifications/priest-declined.blade.php`

## 🆘 Need Help?

Check documentation:

-   `IN_APP_NOTIFICATIONS_COMPLETE.md` - Full technical docs
-   `NOTIFICATION_VISUAL_GUIDE.md` - What to expect visually
-   `NOTIFICATION_IMPLEMENTATION_SUMMARY.md` - High-level overview

## 🎉 You're Ready!

Everything is set up. Just follow the testing checklist above and you'll see the notification system in action! 🚀
