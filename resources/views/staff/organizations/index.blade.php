@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Organizations</h1>
        <div>
            <a href="{{ route('staff.organizations.create') }}" class="btn btn-primary">New Organization</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow rounded">
        <div class="overflow-x-auto">
        @if($organizations->isEmpty())
            <div class="p-6 text-center">
                <p class="text-gray-600">No organizations have been created yet.</p>
                <p class="mt-2 text-sm text-gray-500">Tip: assign an adviser from the list of registered users who have the 'adviser' role. Use the "Manage Organizations" button in the header to create a new organization.</p>
            </div>
        @else
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Type</th>
                    <th class="px-4 py-2 text-left">Adviser</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @foreach($organizations as $org)
                    <tr>
                        <td class="px-4 py-2">{{ $org->org_name }}</td>
                        <td class="px-4 py-2">{{ 
                            Str::limit($org->org_desc ?? '—', 80)
                        }}</td>
                        <td class="px-4 py-2">{{ optional($org->adviser)->first_name ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('staff.organizations.edit', $org->org_id) }}" class="text-blue-600 mr-2">Edit</a>
                            <form action="{{ route('staff.organizations.destroy', $org->org_id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600" onclick="return confirm('Delete this organization?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        </div>
    </div>

    <div class="mt-4">
        {{ $organizations->links() }}
    </div>
</div>
@endsection
