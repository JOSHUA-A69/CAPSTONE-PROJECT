@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">Edit Venue</h1>
    
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('admin.venues.update', $venue->venue_id) }}">
            @csrf
            @method('PUT')
            <div class="mb-6">
                <label for="name" class="form-label">Venue Name</label>
                <input type="text" name="name" id="name" class="form-input" required value="{{ old('name', $venue->name) }}">
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Update Venue</button>
                <a href="{{ route('admin.venues.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
