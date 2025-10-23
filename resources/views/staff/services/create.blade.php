@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold mb-4">New Service</h1>

    <form method="POST" action="{{ route('staff.services.store') }}">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium">Service Name</label>
            <input type="text" name="service_name" class="form-input mt-1 w-full" value="{{ old('service_name') }}" required>
            @error('service_name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Category</label>
            <select name="service_category" class="form-select mt-1 w-full">
                <option value="">-- none --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" @if(old('service_category')===$cat) selected @endif>{{ $cat }}</option>
                @endforeach
            </select>
            @error('service_category') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Duration (minutes)</label>
            <input type="number" name="duration" class="form-input mt-1 w-full" value="{{ old('duration') }}" min="0" max="10080">
            @error('duration') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Description</label>
            <textarea name="description" class="form-textarea mt-1 w-full" rows="3">{{ old('description') }}</textarea>
            @error('description') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-2">
            <button class="btn btn-primary">Create</button>
            <a href="{{ route('staff.services.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
