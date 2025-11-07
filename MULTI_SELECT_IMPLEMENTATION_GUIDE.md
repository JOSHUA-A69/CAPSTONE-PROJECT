# Multi-Select Organizations & Priests Implementation Guide

## Overview
This guide provides step-by-step instructions to implement multiple organization and priest selection in the reservation system with automatic notifications.

## ✅ Completed Steps

### 1. Database Structure
- **Created pivot table**: `reservation_organization`
  - Tracks which organizations are assigned to each reservation
  - Includes notification status and timestamp
  
- **Created pivot table**: `reservation_priest`
  - Tracks which priests are assigned to each reservation
  - Includes confirmation status, decline reason, and notification tracking

### 2. Model Relationships
- **Updated Reservation Model** with many-to-many relationships:
  - `organizations()` - Returns all assigned organizations
  - `priests()` - Returns all assigned priests
  - Both include pivot data for tracking notifications

## 🔄 Remaining Implementation Steps

### Step 3: Update Reservation Form UI

**File**: `resources/views/requestor/reservations/create.blade.php`

#### A. Replace Single Organization Dropdown with Multi-Select

**Current (around line 750)**:
```blade
<select name="org_id" id="org_id" required>
    <option value="">-- Select Organization --</option>
    @foreach($organizations as $org)
        <option value="{{ $org->org_id }}">{{ $org->org_name }}</option>
    @endforeach
</select>
```

**Replace with**:
```blade
<label>
    Requesting Office/Group<span class="required-indicator">*</span>
    <span class="tooltip help-icon">?
        <span class="tooltiptext">Select all organizations that will participate in this activity. Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</span>
    </span>
</label>
<select 
    name="organization_ids[]" 
    id="organization_ids" 
    multiple
    required
    size="6"
    class="multi-select @error('organization_ids') is-invalid @enderror"
    style="height: 150px;">
    @foreach($organizations as $org)
        <option value="{{ $org->org_id }}">{{ $org->org_name }}</option>
    @endforeach
</select>
<p class="helper-text">💡 Hold Ctrl (Windows) or Cmd (Mac) and click to select multiple organizations</p>
@error('organization_ids')
    <div class="error-message">⚠️ {{ $message }}</div>
@enderror
```

#### B. Update Priest Selection to Multi-Select

**Current (around line 890)**:
```blade
<select name="officiant_id" id="officiant_id">
    <option value="">-- Select Priest/Presider --</option>
    @foreach($priests as $priest)
        <option value="{{ $priest->id }}">{{ $priest->full_name }}</option>
    @endforeach
</select>
```

**Replace with**:
```blade
<label>
    Choose Priest(s)<span class="required-indicator">*</span>
    <span class="tooltip help-icon">?
        <span class="tooltiptext">Select one or more priests for this reservation. All selected priests will be notified.</span>
    </span>
</label>
<select 
    name="priest_ids[]" 
    id="priest_ids" 
    multiple
    required
    size="5"
    class="multi-select @error('priest_ids') is-invalid @enderror"
    style="height: 130px;">
    @foreach($priests as $priest)
        <option value="{{ $priest->id }}">{{ $priest->full_name }}</option>
    @endforeach
</select>
<p class="helper-text">💡 Select multiple priests if co-celebration is needed</p>
@error('priest_ids')
    <div class="error-message">⚠️ {{ $message }}</div>
@enderror
```

#### C. Add CSS for Better Multi-Select Styling

Add to your CSS file:
```css
.multi-select {
    padding: 8px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
}

.multi-select option {
    padding: 8px 12px;
    margin: 2px 0;
    border-radius: 4px;
    cursor: pointer;
}

.multi-select option:hover {
    background-color: #f0f0f0;
}

.multi-select option:checked {
    background: linear-gradient(to right, #4F46E5, #7C3AED);
    color: white;
    font-weight: 600;
}

.helper-text {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
    font-style: italic;
}
```

### Step 4: Update Controller to Handle Multiple Selections

**File**: `app/Http/Controllers/Requestor/ReservationController.php`

**Update the `store()` method**:

```php
public function store(ReservationRequest $request)
{
    try {
        DB::beginTransaction();
        
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';
        
        // Keep the first organization for backwards compatibility
        $organizationIds = $request->input('organization_ids', []);
        $data['org_id'] = !empty($organizationIds) ? $organizationIds[0] : null;
        
        // Handle priest selection
        $priestIds = $request->input('priest_ids', []);
        if (!empty($priestIds)) {
            $data['officiant_id'] = $priestIds[0]; // First priest as primary
        }
        
        // Create the reservation
        $reservation = Reservation::create($data);
        
        // Attach all selected organizations
        if (!empty($organizationIds)) {
            $reservation->organizations()->attach($organizationIds, [
                'notified' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Send notifications to all organizations
            $this->notifyOrganizations($reservation, $organizationIds);
        }
        
        // Attach all selected priests
        if (!empty($priestIds)) {
            $priestData = [];
            foreach ($priestIds as $priestId) {
                $priestData[$priestId] = [
                    'confirmation_status' => 'pending',
                    'notified' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            $reservation->priests()->attach($priestData);
            
            // Send notifications to all priests
            $this->notifyPriests($reservation, $priestIds);
        }
        
        DB::commit();
        
        return redirect()->route('requestor.reservations.confirm', $reservation->reservation_id)
            ->with('success', 'Reservation created! Notifications sent to all assigned organizations and priests.');
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Reservation creation failed: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Failed to create reservation. Please try again.');
    }
}

/**
 * Send notifications to all assigned organizations
 */
protected function notifyOrganizations($reservation, $organizationIds)
{
    foreach ($organizationIds as $orgId) {
        $organization = Organization::with('adviser')->find($orgId);
        
        if ($organization && $organization->adviser) {
            // Create notification for organization adviser
            Notification::create([
                'user_id' => $organization->adviser->id,
                'type' => 'organization_assignment',
                'title' => '📋 Organization Assignment',
                'message' => "Your organization '{$organization->org_name}' has been assigned to participate in a reservation: {$reservation->activity_name}",
                'related_type' => 'App\Models\Reservation',
                'related_id' => $reservation->reservation_id,
                'read' => false
            ]);
            
            // Update pivot table
            $reservation->organizations()->updateExistingPivot($orgId, [
                'notified' => true,
                'notified_at' => now()
            ]);
        }
    }
}

/**
 * Send notifications to all assigned priests
 */
protected function notifyPriests($reservation, $priestIds)
{
    foreach ($priestIds as $priestId) {
        $priest = User::find($priestId);
        
        if ($priest) {
            // Create notification for priest
            Notification::create([
                'user_id' => $priestId,
                'type' => 'priest_assignment',
                'title' => '⛪ New Priest Assignment',
                'message' => "You have been assigned to: {$reservation->activity_name} on " . $reservation->schedule_date->format('M d, Y'),
                'related_type' => 'App\Models\Reservation',
                'related_id' => $reservation->reservation_id,
                'read' => false,
                'action_required' => true
            ]);
            
            // Update pivot table
            $reservation->priests()->updateExistingPivot($priestId, [
                'notified' => true,
                'notified_at' => now()
            ]);
        }
    }
}
```

### Step 5: Update Validation Rules

**File**: `app/Http/Requests/ReservationRequest.php`

Update validation rules to handle arrays:

```php
public function rules()
{
    return [
        // ... other rules ...
        'organization_ids' => 'required|array|min:1',
        'organization_ids.*' => 'exists:organizations,org_id',
        'priest_ids' => 'nullable|array',
        'priest_ids.*' => 'exists:users,id',
        // ... other rules ...
    ];
}

public function messages()
{
    return [
        'organization_ids.required' => 'Please select at least one organization',
        'organization_ids.min' => 'Please select at least one organization',
        'priest_ids.*.exists' => 'One or more selected priests are invalid',
    ];
}
```

### Step 6: Update Reservation Display Views

**Show multiple organizations and priests in reservation details**:

```blade
<!-- Multiple Organizations -->
<div class="detail-row">
    <strong>📋 Assigned Organizations:</strong>
    <div class="organization-list">
        @foreach($reservation->organizations as $org)
            <span class="badge badge-primary">{{ $org->org_name }}</span>
        @endforeach
    </div>
</div>

<!-- Multiple Priests -->
<div class="detail-row">
    <strong>⛪ Assigned Priests:</strong>
    <div class="priest-list">
        @foreach($reservation->priests as $priest)
            <span class="badge badge-success">
                {{ $priest->full_name }}
                @if($priest->pivot->confirmation_status === 'confirmed')
                    ✓
                @elseif($priest->pivot->confirmation_status === 'declined')
                    ✗
                @else
                    ⏳
                @endif
            </span>
        @endforeach
    </div>
</div>
```

### Step 7: Enhanced Notification System Features

#### A. Notification Content Template

```php
// For Organizations
"Your organization '{$orgName}' has been assigned to participate in:
📅 Event: {$activityName}
📍 Date: {$date}
⏰ Time: {$time}
📍 Venue: {$venue}
👤 Contact: {$contactPerson}
Please coordinate with your members for participation."

// For Priests
"You have been assigned as priest for:
📅 Event: {$activityName}
📍 Date: {$date}
⏰ Time: {$time}
📍 Venue: {$venue}
👥 Expected Participants: {$count}
⛪ Mass Type: {$massType}
Please confirm your availability."
```

#### B. Bulk Notification Status Tracking

Add to reservation view:
```blade
<div class="notification-status">
    <h4>📬 Notification Status</h4>
    <table>
        <tr>
            <th>Type</th>
            <th>Recipient</th>
            <th>Status</th>
            <th>Sent At</th>
        </tr>
        @foreach($reservation->organizations as $org)
            <tr>
                <td>Organization</td>
                <td>{{ $org->org_name }}</td>
                <td>
                    @if($org->pivot->notified)
                        <span class="status-sent">✓ Sent</span>
                    @else
                        <span class="status-pending">⏳ Pending</span>
                    @endif
                </td>
                <td>{{ $org->pivot->notified_at?->format('M d, Y h:i A') ?? '-' }}</td>
            </tr>
        @endforeach
        
        @foreach($reservation->priests as $priest)
            <tr>
                <td>Priest</td>
                <td>{{ $priest->full_name }}</td>
                <td>
                    @if($priest->pivot->notified)
                        <span class="status-sent">✓ Sent</span>
                    @else
                        <span class="status-pending">⏳ Pending</span>
                    @endif
                </td>
                <td>{{ $priest->pivot->notified_at?->format('M d, Y h:i A') ?? '-' }}</td>
            </tr>
        @endforeach
    </table>
</div>
```

## 🚀 Testing Checklist

- [ ] Select multiple organizations (2-3) and verify form submission
- [ ] Select multiple priests and verify form submission
- [ ] Check that all organizations receive notifications
- [ ] Check that all priests receive notifications
- [ ] Verify notification timestamps are recorded in pivot tables
- [ ] Test with single selection (ensure backward compatibility)
- [ ] Test validation (no selection, invalid IDs)
- [ ] Check reservation details show all assigned organizations and priests
- [ ] Verify priest confirmation works for each assigned priest
- [ ] Test notification status tracking view

## 📊 Scalability Considerations

1. **Performance**: Uses eager loading to prevent N+1 queries
2. **Transaction Safety**: All inserts wrapped in DB transactions
3. **Notification Queue**: Consider using Laravel queues for bulk notifications
4. **Error Handling**: Graceful failure with rollback
5. **Audit Trail**: Timestamps tracked for all notifications

## 🔐 Security Notes

- Validate all organization IDs exist before attachment
- Validate all priest IDs exist and have 'priest' role
- Use mass assignment protection
- Sanitize user inputs
- Check user permissions before showing multi-select options

## 📝 Additional Enhancements (Optional)

1. **Select2 Integration**: For better multi-select UX with search
2. **Real-time Notifications**: Using broadcasting/websockets
3. **Notification Preferences**: Allow users to set notification preferences
4. **Reminder System**: Send reminders to those who haven't confirmed
5. **Analytics Dashboard**: Track notification delivery and response rates

---

## Quick Start Implementation

1. Run migrations (✅ Already done)
2. Update Reservation model (✅ Already done)
3. Update create.blade.php form (Step 3)
4. Update ReservationController store method (Step 4)
5. Update validation rules (Step 5)
6. Update display views (Step 6)
7. Test thoroughly (Testing Checklist)

**Estimated Implementation Time**: 4-6 hours

**Priority**: High - Enhances communication and coordination significantly
