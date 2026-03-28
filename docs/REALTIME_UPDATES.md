# Real-Time Updates Implementation Guide

## Overview
This application now has comprehensive real-time updates for all CRUD operations. Users no longer need to refresh pages to see updates from other users' actions.

## Architecture

### Components
1. **SSE Client** (`resources/js/sse-client.js`)
   - Establishes Server-Sent Events connections
   - Handles notifications, chat, and CRUD updates
   - Auto-reconnects with exponential backoff

2. **Real-Time Updates Handler** (`resources/js/realtime-updates.js`)
   - Manages CRUD event listeners
   - Dispatches custom events for specific entity types
   - Integrates with Alpine.js components

3. **SSEController** (`app/Http/Controllers/SSEController.php`)
   - Streams real-time events via SSE
   - Monitors cache for CRUD updates
   - Sends notifications, chat, and dashboard updates

4. **RealtimeUpdateService** (`app/Services/RealtimeUpdateService.php`)
   - Broadcasts CRUD events via cache
   - Triggered by Model Observers
   - Supports role-based and user-based broadcasting

5. **Model Observers**
   - ReservationObserver
   - NotificationObserver
   - OrganizationBookingRequestObserver
   - Automatically trigger real-time updates on CRUD operations

## Features

### ✅ Real-Time Chat Messages
- Messages appear instantly in both sender and recipient's views
- Includes profile pictures, attachments, timestamps
- No page refresh required

### ✅ Reservation Updates
- Create, update, delete operations trigger instant updates
- Broadcasts to affected users (requestor, assigned priest, adviser, admin, staff)
- Dashboard stats update automatically

### ✅ Notification Updates
- Newly created notifications appear on all user screens
- Mark-as-read updates in real-time
- Notification counts update without refresh

### ✅ Organization Booking Updates
- New org bookings appear instantly
- Status changes trigger updates for all relevant users
- Adviser, admin, staff all see updates simultaneously

### ✅ Dashboard Stats
- Pending reservations count updates automatically
- Cancellation count updates
- Org booking count updates
- No polling delays - instant updates via SSE

## Usage in Blade Views

### Method 1: Using Alpine Real-Time Helpers (Recommended)

#### For a List of Items:
```blade
<div x-data="realtimeList({
    entity: 'Reservation',
    url: '/api/reservations',
    idField: 'id'
})">
    <template x-for="item in items">
        <div x-text="item.status"></div>
    </template>
</div>
```

#### With Custom Handlers:
```blade
<div x-data="realtimeList({
    entity: 'Reservation',
    url: '/api/reservations',
    onItemCreated: (item) => {
        // Show toast notification
        console.log('New reservation:', item);
    },
    onItemUpdated: (item) => {
        // Refresh specific UI elements
        console.log('Reservation updated:', item);
    }
})">
    <!-- Your template here -->
</div>
```

#### For a Single Item:
```blade
<div x-data="realtimeItem({
    entity: 'Reservation',
    itemId: {{ $reservation->id }},
    onUpdated: (item) => {
        alert('Reservation updated!');
    }
})">
    <div x-text="item?.status"></div>
</div>
```

### Method 2: Manual Event Listeners

```blade
<script>
    // Listen for reservation creations
    window.realtimeUpdates.on('Reservation:create', (data) => {
        console.log('New reservation:', data);
        // Update your UI here
    });

    // Listen for reservation updates
    window.realtimeUpdates.on('Reservation:update', (data) => {
        console.log('Reservation updated:', data);
        // Update your UI here
    });

    // Listen for all reservation events
    window.realtimeUpdates.on('Reservation', (event) => {
        console.log(event.type, event.data);
    });
</script>
```

### Method 3: Custom Event Listener

```blade
<script>
    window.addEventListener('realtime-update', (e) => {
        const { entity, type, data } = e.detail;
        if (entity === 'Reservation') {
            console.log(`${type}:`, data);
            // Update your UI
        }
    });
</script>
```

## Chat Implementation Details

### SSE Chat Stream
- **Endpoint**: `/sse/chat?with={userId}`
- **Purpose**: Real-time message delivery for specific conversations
- **Features**:
  - Instant message delivery
  - Profile pictures included
  - Attachment support
  - Typing indicators support

### Chat Setup in Views:
```blade
<div x-data="chatApp({{ $otherUser->id }}, {{ auth()->id() }})">
    <!-- Messages displayed here -->
    <template x-for="message in messages">
        <div>
            <img :src="message.sender.profile_picture_url">
            <p x-text="message.message"></p>
        </div>
    </template>
</div>

<script>
    function chatApp(otherUserId, currentUserId) {
        return {
            messages: [],
            
            init() {
                this.loadMessages();
                this.setupBroadcasting();
            },
            
            setupBroadcasting() {
                if (window.sseClient) {
                    window.sseClient.connectChat(otherUserId);
                    
                    window.sseClient.on('chat:new_messages', (data) => {
                        // Add new messages to the list
                        this.messages.push(...data.messages);
                        this.scrollToBottom();
                    });
                }
            }
        };
    }
</script>
```

## Debugging

### Enable SSE Client Logging:
```javascript
window.sseClient.debug = true;
```

### Check SSE Connection Status:
```javascript
window.sseClient.isConnected  // true/false
window.sseClient.isChatConnected  // true/false
```

### Monitor Real-Time Activity:
```javascript
// Listen to all events
window.realtimeUpdates.on('crud', (event) => {
    console.log('[CRUD Event]', event.type, event.entity, event.data);
});
```

### Check Notification Count:
```javascript
// In browser console
window.sseClient.updateNotificationBadge(5);
```

## Configuration

### SSE Stream Timeout
- Located in: `SSEController::stream()`
- Default: 300 iterations (5 minutes)
- Can be adjusted based on requirements

### SSE Chat Stream Timeout
- Located in: `SSEController::chatStream()`
- Default: 300 iterations (5 minutes)
- Independent from main stream

### Cache TTL for CRUD Updates
- Default: 30 seconds
- Configured in: `RealtimeUpdateService::broadcast()`
- Messages not retrieved within 30 seconds are discarded

## Testing

### Test Chat Messages:
1. Open admin chat with a requestor
2. Send message as requestor
3. Message should appear instantly on admin's screen without refresh

### Test Reservations:
1. Create a new reservation as requestor
2. Admin/staff should see it appear instantly
3. Update reservation status
4. All affected users should see update without refresh

### Test Notifications:
1. Create notification via observer
2. Should appear on user's screen instantly
3. Mark as read - count should update

## Troubleshooting

### Messages Not Appearing
1. Check browser console for errors
2. Enable SSE client debugging: `window.sseClient.debug = true`
3. Verify SSE endpoint is accessible
4. Check user authentication

### Real-Time Updates Not Working
1. Check if RealtimeUpdateService is imported in observers
2. Verify cache is working: `php artisan tinker`
   ```php
   Cache::put('test', 'value', 30);
   Cache::get('test'); // Should return 'value'
   ```
3. Check SSEController is checking for CRUD updates
4. Verify real-time-updates.js is loaded in app

### Chat SSE Not Connecting
1. Verify `/sse/chat?with={userId}` endpoint
2. Check user authentication
3. Ensure correct other_user_id is passed to connectChat()
4. Check browser network tab for SSE connection

## Performance Considerations

- SSE connections are persistent, reduce server load
- Cache-based updates efficient for distributed systems
- Model Observers keep code DRY and maintainable
- No database polling required for real-time updates

## Next Steps

1. Test all real-time features thoroughly
2. Add error handling and retry logic if needed
3. Monitor SSE connection stability
4. Consider adding pagination for large datasets
5. Add animations for real-time updates (optional)

## Support

For issues or questions about real-time updates:
1. Check this documentation
2. Review `resources/js/realtime-updates.js` for available methods
3. Check `app/Services/RealtimeUpdateService.php` for implementation details
4. Enable debugging and check browser console
