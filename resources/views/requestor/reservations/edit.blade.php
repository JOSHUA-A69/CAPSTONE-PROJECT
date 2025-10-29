@extends('layouts.app')

@section('title', 'Edit Reservation')

@section('content')
<div class="container mx-auto max-w-4xl p-4">
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-xl font-semibold">Edit Reservation #{{ $reservation->reservation_id }}</h1>
            <p class="text-sm text-gray-500">Submit your changes for admin approval. Required fields are marked with <span class="text-red-600">*</span></p>
        </div>
        <form method="POST" action="{{ route('requestor.reservations.update', $reservation->reservation_id) }}" class="p-6 space-y-6">
            @csrf

            @php
                $dt = optional($reservation->schedule_date);
                $dateValue = $dt ? $dt->format('Y-m-d') : '';
                $timeValue = $dt ? $dt->format('H:i') : '';
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Service <span class="text-red-600">*</span></label>
                    <select name="service_id" class="form-input @error('service_id') is-invalid @enderror">
                        @foreach($services as $s)
                            <option value="{{ $s->service_id }}" @selected($reservation->service_id == $s->service_id)>{{ $s->service_name }}</option>
                        @endforeach
                    </select>
                    @error('service_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label">Organization</label>
                    <select name="org_id" class="form-input @error('org_id') is-invalid @enderror">
                        <option value="">— None —</option>
                        @foreach($organizations as $o)
                            <option value="{{ $o->org_id }}" @selected($reservation->org_id == $o->org_id)>{{ $o->org_name }}</option>
                        @endforeach
                    </select>
                    @error('org_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label">Venue <span class="text-red-600">*</span></label>
                    <select name="venue_id" id="venue_id" class="form-input @error('venue_id') is-invalid @enderror" onchange="toggleCustomVenue(this.value)">
                        @foreach($venues as $v)
                            <option value="{{ $v->venue_id }}" @selected($reservation->venue_id == $v->venue_id)>{{ $v->name }}</option>
                        @endforeach
                        <option value="custom" @selected(is_null($reservation->venue_id) && $reservation->custom_venue_name)>Custom (enter below)</option>
                    </select>
                    @error('venue_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div id="custom-venue-wrapper" class="@if(!(is_null($reservation->venue_id) && $reservation->custom_venue_name)) hidden @endif">
                    <label class="form-label">Custom Venue</label>
                    <input type="text" name="custom_venue" class="form-input @error('custom_venue') is-invalid @enderror" value="{{ old('custom_venue', $reservation->custom_venue_name) }}" placeholder="Enter venue name" />
                    @error('custom_venue')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label">Officiant/Priest <span class="text-red-600">*</span></label>
                    <select name="officiant_id" class="form-input @error('officiant_id') is-invalid @enderror">
                        @foreach($priests as $p)
                            <option value="{{ $p->id }}" @selected($reservation->officiant_id == $p->id)>{{ $p->full_name }}</option>
                        @endforeach
                    </select>
                    @error('officiant_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label">Schedule Date <span class="text-red-600">*</span></label>
                    <input type="date" name="schedule_date" class="form-input @error('schedule_date') is-invalid @enderror" value="{{ old('schedule_date', $dateValue) }}" />
                    @error('schedule_date')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label">Schedule Time</label>
                    <input type="time" name="schedule_time" class="form-input @error('schedule_time') is-invalid @enderror" value="{{ old('schedule_time', $timeValue) }}" />
                    @error('schedule_time')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Activity Name <span class="text-red-600">*</span></label>
                    <input type="text" name="activity_name" class="form-input @error('activity_name') is-invalid @enderror" value="{{ old('activity_name', $reservation->activity_name) }}" />
                    @error('activity_name')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Theme</label>
                    <input type="text" name="theme" class="form-input @error('theme') is-invalid @enderror" value="{{ old('theme', $reservation->theme) }}" />
                    @error('theme')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Purpose</label>
                    <input type="text" name="purpose" class="form-input @error('purpose') is-invalid @enderror" value="{{ old('purpose', $reservation->purpose) }}" />
                    @error('purpose')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Details</label>
                    <textarea name="details" rows="4" class="form-input @error('details') is-invalid @enderror">{{ old('details', $reservation->details) }}</textarea>
                    @error('details')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label">Participants Count</label>
                    <input type="number" name="participants_count" min="1" class="form-input @error('participants_count') is-invalid @enderror" value="{{ old('participants_count', $reservation->participants_count) }}" />
                    @error('participants_count')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label">Commentator</label>
                    <input type="text" name="commentator" class="form-input @error('commentator') is-invalid @enderror" value="{{ old('commentator', $reservation->commentator) }}" />
                    @error('commentator')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label">Servers</label>
                    <input type="text" name="servers" class="form-input @error('servers') is-invalid @enderror" value="{{ old('servers', $reservation->servers) }}" />
                    @error('servers')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label">Readers</label>
                    <input type="text" name="readers" class="form-input @error('readers') is-invalid @enderror" value="{{ old('readers', $reservation->readers) }}" />
                    @error('readers')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label">Choir</label>
                    <input type="text" name="choir" class="form-input @error('choir') is-invalid @enderror" value="{{ old('choir', $reservation->choir) }}" />
                    @error('choir')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label">Psalmist</label>
                    <input type="text" name="psalmist" class="form-input @error('psalmist') is-invalid @enderror" value="{{ old('psalmist', $reservation->psalmist) }}" />
                    @error('psalmist')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label">Prayer Leader</label>
                    <input type="text" name="prayer_leader" class="form-input @error('prayer_leader') is-invalid @enderror" value="{{ old('prayer_leader', $reservation->prayer_leader) }}" />
                    @error('prayer_leader')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Notes for Admin (reason for change) <span class="text-red-600">*</span></label>
                <textarea name="notes" rows="4" class="form-input @error('notes') is-invalid @enderror" placeholder="Briefly explain what changes you need and why.">{{ old('notes') }}</textarea>
                @error('notes')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="flex items-center justify-between pt-2 border-t">
                <a href="{{ route('requestor.reservations.show', $reservation->reservation_id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Submit Change Request</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleCustomVenue(value) {
        const wrapper = document.getElementById('custom-venue-wrapper');
        if (value === 'custom') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }
</script>
@endsection
