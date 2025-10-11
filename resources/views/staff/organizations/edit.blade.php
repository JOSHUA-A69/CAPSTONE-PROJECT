@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold mb-4">Edit Organization</h1>

    <form method="POST" action="{{ route('staff.organizations.update', $organization->org_id) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium">Organization Name</label>
            <select name="org_name" class="form-select mt-1 block w-full" required>
                <option value="">-- choose organization --</option>
                @php
                    $options = [
                        'Himig Diwa Chorale',
                        'Acolytes and Lectors',
                        'Children of Mary',
                        'Student Catholic Action',
                        'Young Missionaries Club',
                        'Catechetical Organization',
                    ];
                @endphp
                @foreach($options as $opt)
                    <option value="{{ $opt }}" @if(old('org_name', $organization->org_name) == $opt) selected @endif>{{ $opt }}</option>
                @endforeach
            </select>
            @error('org_name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Organization Description</label>
            <textarea name="org_desc" class="form-textarea mt-1 block w-full" rows="3">{{ old('org_desc', $organization->org_desc) }}</textarea>
            @error('org_desc') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Adviser</label>
            <select name="adviser_id" class="form-select mt-1 block w-full">
                <option value="">-- choose adviser (optional) --</option>
                @foreach($advisers as $adv)
                    <option value="{{ $adv->id }}" @if(old('adviser_id', $organization->adviser_id) == $adv->id) selected @endif>{{ $adv->first_name }} {{ $adv->last_name }} &lt;{{ $adv->email }}&gt;</option>
                @endforeach
            </select>
            @error('adviser_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-2">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('staff.organizations.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
