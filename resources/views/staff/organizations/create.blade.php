@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold mb-4">New Organization</h1>

    <form method="POST" action="{{ route('staff.organizations.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium">Organization Name</label>
            <select name="org_name" class="form-select mt-1 block w-full" required>
                <option value="">-- choose organization --</option>
                <option value="Himig Diwa Chorale" @if(old('org_name') == 'Himig Diwa Chorale') selected @endif>Himig Diwa Chorale</option>
                <option value="Acolytes and Lectors" @if(old('org_name') == 'Acolytes and Lectors') selected @endif>Acolytes and Lectors</option>
                <option value="Children of Mary" @if(old('org_name') == 'Children of Mary') selected @endif>Children of Mary</option>
                <option value="Student Catholic Action" @if(old('org_name') == 'Student Catholic Action') selected @endif>Student Catholic Action</option>
                <option value="Young Missionaries Club" @if(old('org_name') == 'Young Missionaries Club') selected @endif>Young Missionaries Club</option>
                <option value="Catechetical Organization" @if(old('org_name') == 'Catechetical Organization') selected @endif>Catechetical Organization</option>
            </select>
            @error('org_name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Organization Description</label>
            <textarea name="org_desc" class="form-textarea mt-1 block w-full" rows="3">{{ old('org_desc') }}</textarea>
            @error('org_desc') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Adviser</label>
            <select name="adviser_id" class="form-select mt-1 block w-full">
                <option value="">-- choose adviser (optional) --</option>
                @foreach($advisers as $adv)
                    <option value="{{ $adv->id }}">{{ $adv->first_name }} {{ $adv->last_name }} &lt;{{ $adv->email }}&gt;</option>
                @endforeach
            </select>
            @error('adviser_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-2">
            <button class="btn btn-primary">Create</button>
        </div>
    </form>
</div>
@endsection
