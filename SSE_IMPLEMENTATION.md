# SSE (Server-Sent Events) Real-Time Implementation Guide

## Overview

This document outlines the complete Server-Sent Events (SSE) implementation for the eReligiousServices application. SSE provides real-time, bidirectional updates across all actors (Admin, Staff, Priests, Requestors, Advisers) without requiring manual page refreshes.

## Architecture

### Core Components

1. **SSE Client** (`resources/js/sse-client.js`)
   - Global EventSource connection management
   - Automatic reconnection with exponential backoff
   - Event listener pattern for different update types
   - Supports visibility change (pauses SSE when tab is hidden)

2. **SSE Notifications Handler** (`resources/js/sse-notifications.js`)
   - Listens for notification count updates
   - Updates notification badges in real-time
   - Dispatches custom events for notification changes

3. **SSE Dashboard Handler** (`resources/js/sse-dashboard.js`)
   - Listens for dashboard statistics updates
   - Automatically updates all data-stat elements
   - Provides watcher pattern for specific stats

4. **SSE Service** (`app/Services/SSEService.php`)
   - Centralized service for triggering SSE updates
   - Helper methods for different update types
   - Cache-based system for tracking updates

5. **SSE Controller** (`app/Http/Controllers/SSEController.php`)
   - Main streaming endpoint at `/sse/stream`
   - Chat-specific stream at `/sse/chat`
   - Handles connection management and keepalives

6. **Model Observers**
   - Automatically trigger SSE updates when models change
   - Implemented for: Reservation, Message, Notification, User, OrganizationBookingRequest, ReservationCancellation

## Routes

All SSE routes are protected by `auth` middleware and prefixed with `/sse/`:

```
GET    /sse/stream              - Main SSE stream (all updates)
GET    /sse/chat                - Chat-specific stream
GET    /sse/messages/check      - Polling fallback for messages
```

## How It Works

### Connection Flow

1. **Page Load**: SSE Client automatically connects to `/sse/stream`
2. **Fallback**: If SSE fails, polling is used as fallback
3. **Real-Time Updates**: When a model changes, observer triggers SSE update
4. **Browser Event**: SSE sends formatted message to client
5. **Handler Updates**: JavaScript handler updates UI elements

### Update Flow

```
User Action (e.g., create reservation)
    ↓
Model saved (Reservation created)
    ↓
Observer triggered (ReservationObserver::created)
    ↓
SSEService::triggerUpdate called
    ↓
Update stored in cache with timestamp
    ↓
SSE stream detects change and sends to clients
    ↓
JavaScript handler receives update
    ↓
UI elements updated with animation
```

## Implementation by Actor Type

### Admin
- **Notifications**: New users, reservations, cancellations, org bookings
- **Chat**: Real-time message updates from requestors
- **Dashboard**: Stats on pending items, total users
- **Updates via**: All observers + Role-specific updates

### Staff
- **Notifications**: Pending reservations, cancellations, org bookings
- **Chat**: Not available (admin-only feature)
- **Dashboard**: Pending stats, today's reservations
- **Updates via**: Reservation, ReservationCancellation, OrganizationBookingRequest observers

### Priests
- **Notifications**: Pending reservations assigned to them
- **Chat**: Not available
- **Dashboard**: Their pending/upcoming reservations
- **Updates via**: Reservation observer filtering by priest_id

### Requestors
- **Notifications**: Reservation status changes, chat messages
- **Chat**: Real-time messages with admin
- **Dashboard**: Their pending/approved/upcoming reservations
- **Updates via**: Reservation observer, Message observer, User observer (for status)

### Advisers
- **Notifications**: Org booking requests for their organizations
- **Chat**: Not available
- **Dashboard**: Pending/approved bookings for their orgs
- **Updates via**: OrganizationBookingRequest observer

## Feature Implementation

### Real-Time Chat

```javascript
// Automatic with SSE
window.sseClient.connectChat(conversationWithId);
window.sseClient.on('chat:new_messages', (data) => {
    // Messages automatically appear
});
```

### Real-Time Notifications

```html
<!-- Notification badge auto-updates -->
<span data-notification-count>5</span>
```

### Real-Time Dashboard

```html
<!-- Stats auto-update -->
<span data-stat="pending_reservations">12</span>
```

## Testing SSE

### Browser Console

```javascript
// Check if SSE is connected
window.sseClient.isConnected

// Enable debug logging
window.sseClient.debug = true

// Check notification handler
window.sseNotificationsHandler.getNotificationCount()

// Check dashboard handler
window.sseDashboardHandler.getAllStats()
```

### Manual Testing Steps

#### For Chat (Admin/Requestor)
1. Open chat on one device/tab
2. Open chat on another device/tab
3. Send a message from device 1
4. Device 2 should show message in real-time (no refresh needed)

#### For Notifications (All Users)
1. Keep dashboard open on one tab
2. Create an event that triggers notification (e.g., new reservation)
3. Badge should update in real-time

#### For Dashboard (All Users)
1. Keep dashboard open
2. Create events that change stats (e.g., new pending reservation)
3. Stats should update in real-time

## Performance Considerations

### Keepalive
- Sent every 15 seconds if no updates
- Prevents connection timeout
- Uses `:` comment (ignored by browsers)

### Iteration Limit
- 300 iterations = ~5 minutes max connection time
- Client automatically reconnects after timeout
- Prevents long-running connections on server

### Cache TTL
- Updates cached for 60 seconds
- Only notifies users who need the update
- Reduces redundant notifications

## Fallback Mechanism

If SSE fails:
1. Chat: Falls back to polling every 3 seconds
2. Notifications: Relies on page refresh (consider adding timer)
3. Dashboard: Browser reload required

## Browser Compatibility

### Supported
- Chrome/Edge 26+
- Firefox 6+
- Safari 5.1+
- Opera 11+
- iOS Safari 5+

### Fallback Required
- IE 11 and below (use polling)

## Security

### Authentication
- All routes require `auth()` middleware
- User identity verified before sending updates
- Users only receive updates relevant to them

### CSRF Protection
- SSE stream endpoint doesn't require CSRF token (streaming)
- Other endpoints protected with typical CSRF

### Authorization
- SSEService only sends to specific user IDs
- Model observers check user roles before notifying
- Advisers only see their org's bookings

## Troubleshooting

### SSE Not Working

1. **Check browser console for errors**
   ```javascript
   window.sseClient.debug = true
   ```

2. **Verify server is running and accessible**
   - SSE requires persistent connection
   - Check with: `curl http://localhost/sse/stream`

3. **Check Network tab in DevTools**
   - Should see `/sse/stream` with 200 status
   - Should see streaming data (event messages)

4. **Verify model observers are registered**
   - Check AppServiceProvider.php
   - Ensure all observers are registered

### Chat Not Updating

1. Verify SSE client is connected: `window.sseClient.isConnected`
2. Check message observer exists
3. Verify chat stream is active: `window.sseClient.isChatConnected`
4. Check browser console for listener errors

### Dashboard Stats Not Updating

1. Verify data-stat attributes exist in HTML
2. Check sseDashboardHandler: `window.sseDashboardHandler.getAllStats()`
3. Verify SSEService returns correct stats
4. Check CSS has stat-updated animation

## Future Enhancements

1. **WebSockets** - For lower latency (if scaling requires)
2. **Message Queue** - For high-volume updates (Redis/RabbitMQ)
3. **Presence Detection** - Show who's online
4. **Typing Indicators** - Show when someone is typing
5. **Read Receipts** - More granular message status tracking

## References

- SSE MDN: https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events
- EventSource API: https://developer.mozilla.org/en-US/docs/Web/API/EventSource
- Laravel SSE Guide: https://laravel.com/docs/streaming
