# In-App Notification System for Priest Decline

## Overview

Implemented a complete in-app notification system that displays priest decline notifications in the admin interface. When a priest declines a reservation, admins will see:

-   A notification bell icon with badge counter in the navigation bar
-   Real-time notifications in a dropdown menu
-   Detailed notification pages with full priest decline information

## Features Implemented

### 1. Notification Bell Icon

-   **Location**: Admin/Staff navigation bar (top right)
-   **Red badge**: Shows count of unread notifications
-   **Auto-refresh**: Updates count every 30 seconds
-   **Dropdown**: Shows 5 most recent notifications when clicked

### 2. Notification Types

-   **Priest Declined**: Red alert badge, highest priority
-   **Approval**: For reservation approvals
-   **Reminder**: For follow-ups
-   **System Alert**: General system notifications
-   **Assignment**: Priest assignments
-   **Update**: General updates

### 3. Notification Detail Page

When admin clicks on a "Priest Declined" notification, they see:

-   ⚠️ Alert header with red styling
-   Complete reservation details (ID, service, requestor, date, venue)
-   Priest who declined and their information
-   Reason for decline in highlighted box
-   "View Reservation & Assign Priest" action button
-   Next steps guidance

## Files Created

### 1. Model

**`app/Models/Notification.php`**

-   Notification model with relationships
-   Helper methods: `markAsRead()`, `isUnread()`
-   Scopes for filtering: `unread()`, `read()`

### 2. Migration

**`database/migrations/2025_10_18_114106_add_data_column_to_notifications_table.php`**

-   Adds `data` JSON column for storing additional notification data
-   Updates enum type to include new notification types

### 3. Controller

**`app/Http/Controllers/Admin/NotificationController.php`**

-   `index()` - List all notifications
-   `getUnreadCount()` - API endpoint for badge count
-   `getRecent()` - API endpoint for dropdown (5 recent)
-   `show()` - View single notification
-   `showPriestDeclined()` - Special view for priest decline
-   `markAsRead()` - Mark single as read
-   `markAllAsRead()` - Mark all as read

### 4. Views

**`resources/views/admin/notifications/index.blade.php`**

-   List all notifications with pagination
-   Filter by read/unread status
-   "Mark All as Read" button
-   Visual indicators for unread notifications

**`resources/views/admin/notifications/priest-declined.blade.php`**

-   Detailed priest decline notification view
-   Matches email template design
-   Red alert styling
-   Action button to assign new priest
-   Next steps guidance

### 5. Updated Files

**`resources/views/layouts/navigation.blade.php`**

-   Added notification bell icon
-   Badge with unread count
-   Dropdown menu for recent notifications
-   Auto-refresh functionality

**`app/Services/ReservationNotificationService.php`**

-   Updated `notifyPriestDeclined()` method
-   Creates in-app notification for each admin/staff
-   Stores decline reason and priest info in JSON data

**`routes/web.php`**

-   Added notification routes under admin prefix

## Database Schema

### Existing `notifications` table:

```sql
- notification_id (PRIMARY KEY)
- user_id (FOREIGN KEY to users)
- reservation_id (FOREIGN KEY to reservations, nullable)
- message (TEXT)
- type (ENUM: 'Approval', 'Reminder', 'System Alert', 'Priest Declined', 'Assignment', 'Update')
- data (JSON, nullable) -- NEW
- sent_at (DATETIME)
- read_at (DATETIME, nullable)
- timestamps
```

### Data JSON Structure for Priest Declined:

```json
{
    "reason": "I have another commitment",
    "priest_name": "Fr. John Doe",
    "priest_id": 5,
    "service_name": "Holy Mass",
    "schedule_date": "2025-10-29 08:00:00",
    "requestor_name": "Mark Galimba",
    "venue": "San Isidro"
}
```

## Routes Added

```php
GET  /admin/notifications                           - List all notifications
GET  /admin/notifications/count                     - Get unread count (API)
GET  /admin/notifications/recent                    - Get 5 recent (API)
GET  /admin/notifications/{id}                      - View notification
GET  /admin/notifications/{id}/priest-declined      - Priest decline detail
POST /admin/notifications/{id}/mark-read            - Mark as read
POST /admin/notifications/mark-all-read             - Mark all as read
```

## How It Works

### When Priest Declines:

1. **Priest** submits decline form with reason
2. **System** updates reservation status to `pending_priest_reassignment`
3. **System** calls `notifyPriestDeclined()` in ReservationNotificationService
4. **Service** creates in-app notification for each admin/staff user
5. **Notification** includes decline reason and all relevant data
6. **Admin** sees red badge on notification bell
7. **Admin** clicks bell to see dropdown with recent notifications
8. **Admin** clicks "Priest Declined" notification
9. **System** shows detailed priest-declined page
10. **Admin** clicks "View Reservation & Assign Priest" button
11. **System** redirects to reservation detail page for reassignment

### Real-time Updates:

-   Notification count refreshes every 30 seconds
-   Dropdown loads fresh data when opened
-   Badge shows instantly when new notification arrives

## UI/UX Features

### Notification Bell:

-   🔔 Bell icon in navigation bar
-   🔴 Red badge with number when unread notifications exist
-   Hover effect for better UX
-   Click to open dropdown

### Dropdown Menu:

-   Shows 5 most recent notifications
-   Visual indicators:
    -   Blue dot for unread notifications
    -   Blue background for unread items
    -   Color-coded badges by type
    -   Timestamp (e.g., "2 minutes ago")
-   "View All Notifications" link at bottom

### Notification List Page:

-   All notifications with pagination
-   Unread notifications have blue background
-   "Mark All as Read" button
-   Filter options
-   Visual type badges

### Priest Declined Detail Page:

-   Red alert styling (matches email)
-   Complete reservation information
-   Priest information
-   Highlighted reason box
-   Large action button
-   Next steps list
-   Back navigation

## Styling

### Color Scheme:

-   **Priest Declined**: Red theme (#dc2626, #fee2e2)
-   **Unread**: Blue theme (#2563eb, #dbeafe)
-   **General**: Gray theme for neutral items

### Responsive:

-   Works on desktop and mobile
-   Dropdown adjusts to screen size
-   Notification pages fully responsive

## Testing Steps

1. **Create Test Data**:

    - Ensure you have admin and priest users
    - Create a reservation assigned to a priest

2. **Test Decline Flow**:

    - Login as priest
    - Decline an assigned reservation
    - Provide a reason

3. **Check Admin Notification**:

    - Login as admin
    - See red badge on notification bell
    - Click bell to see dropdown
    - Verify notification appears

4. **Click Notification**:

    - Click on "Priest Declined" notification
    - Verify redirect to detail page
    - Check all information displays correctly

5. **Test Action Button**:

    - Click "View Reservation & Assign Priest"
    - Verify redirect to reservation page
    - Assign new priest

6. **Test Mark as Read**:
    - Go back to notifications
    - Click "Mark All as Read"
    - Verify badge clears

## Configuration

### Required:

-   Migration must be run: `php artisan migrate`
-   Notifications table must exist (from earlier migration)
-   User authentication must be working

### Optional:

-   Configure notification retention period
-   Add notification cleanup job
-   Implement WebSocket for instant notifications

## Integration Points

### ReservationNotificationService:

```php
// When priest declines, also creates in-app notification
Notification::create([
    'user_id' => $admin->id,
    'reservation_id' => $reservation->reservation_id,
    'message' => "Priest declined assignment for {$service_name}. Reassignment needed.",
    'type' => 'Priest Declined',
    'sent_at' => now(),
    'data' => [...] // Additional data
]);
```

### Notification Bell (Alpine.js):

```javascript
x-data="{
    open: false,
    count: 0,
    loadNotifications() {...},
    updateCount() {...}
}"
```

## Future Enhancements

1. **Real-time with WebSockets**: Use Laravel Broadcasting for instant notifications
2. **Browser Notifications**: Use Web Notification API
3. **Sound Alerts**: Add optional sound for important notifications
4. **Email Digest**: Send daily summary of notifications
5. **Notification Preferences**: Let admins customize which notifications they receive
6. **Mobile App**: Extend to mobile app with push notifications
7. **Notification History**: Archive old notifications
8. **Search & Filter**: Advanced search in notifications
9. **Notification Templates**: Customize notification messages
10. **Priority Levels**: Add urgent/high/normal/low priorities

## Security Considerations

✅ **Authorization**: Only admins/staff can access notifications
✅ **User Isolation**: Users only see their own notifications
✅ **CSRF Protection**: POST requests use CSRF tokens
✅ **Input Validation**: All inputs validated
✅ **XSS Prevention**: Blade escapes output by default

## Performance

-   **Efficient Queries**: Uses eager loading with relationships
-   **Pagination**: Large notification lists are paginated
-   **Caching**: Count endpoint can be cached if needed
-   **Indexing**: Database indexes on user_id and read_at

## Status: ✅ Complete

The in-app notification system is fully implemented and ready for testing!

Admins will now receive both:

1. **Email notifications** (detailed, with formatting)
2. **In-app notifications** (instant, with bell icon and badge)

This provides multiple channels for admins to stay informed about priest declines and take quick action to reassign services.
