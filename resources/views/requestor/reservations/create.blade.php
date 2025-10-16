@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold mb-4">New Reservation</h1>

    <form method="POST" action="{{ route('requestor.reservations.store') }}">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium">Service</label>
            <select name="service_id" class="form-select mt-1 w-full" required>
                <option value="">-- choose service --</option>
                @foreach($services as $s)
                    <option value="{{ $s->service_id }}" @if(old('service_id')==$s->service_id) selected @endif>{{ $s->service_name }} ({{ $s->service_category ?? 'Other' }})</option>
                @endforeach
            </select>
            @error('service_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Venue</label>
            <select name="venue_id" class="form-select mt-1 w-full" required>
                <option value="">-- choose venue --</option>
                @foreach($venues as $v)
                    <option value="{{ $v->venue_id }}" @if(old('venue_id')==$v->venue_id) selected @endif>{{ $v->name }}</option>
                @endforeach
            </select>
            @error('venue_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Organization (optional)</label>
            <select name="org_id" class="form-select mt-1 w-full">
                <option value="">-- none --</option>
                @foreach($organizations as $o)
                    <option value="{{ $o->org_id }}" @if(old('org_id')==$o->org_id) selected @endif>{{ $o->org_name }}</option>
                @endforeach
            </select>
            @error('org_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Schedule Date</label>
            <input type="datetime-local" name="schedule_date" class="form-input mt-1 w-full" value="{{ old('schedule_date') }}" required>
            @error('schedule_date') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Purpose</label>
            <input type="text" name="purpose" class="form-input mt-1 w-full" value="{{ old('purpose') }}" maxlength="150">
            @error('purpose') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Details</label>
            <textarea name="details" class="form-textarea mt-1 w-full" rows="3">{{ old('details') }}</textarea>
            @error('details') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium">Participants (optional)</label>
            <input type="number" name="participants_count" class="form-input mt-1 w-full" value="{{ old('participants_count') }}" min="1">
            @error('participants_count') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-2">
            <button class="btn btn-primary">Submit</button>
            <a href="{{ route('requestor.reservations.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
