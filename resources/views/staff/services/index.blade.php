@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Services</h1>
        <div>
            <a href="{{ route('staff.services.create') }}" class="btn btn-primary">New Service</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="GET" class="mb-4">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-sm font-medium">Search</label>
                <input type="text" name="q" value="{{ $search ?? '' }}" class="form-input mt-1" placeholder="name or description">
            </div>
            <div>
                <label class="block text-sm font-medium">Category</label>
                <select name="category" class="form-select mt-1">
                    <option value="">-- all --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @if(($category ?? '')===$cat) selected @endif>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('staff.services.index') }}" class="btn">Reset</a>
            </div>
        </div>
    </form>

    <div class="bg-white shadow rounded">
        <div class="overflow-x-auto">
        @if($services->isEmpty())
            <div class="p-6 text-center">
                <p class="text-gray-600">No services found. Create the first one.</p>
            </div>
        @else
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Category</th>
                    <th class="px-4 py-2 text-left">Duration (min)</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @foreach($services as $s)
                    <tr>
                        <td class="px-4 py-2">{{ $s->service_name }}</td>
                        <td class="px-4 py-2">{{ $s->service_category ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $s->duration ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('staff.services.edit', $s->service_id) }}" class="text-blue-600 mr-2">Edit</a>
                            <form action="{{ route('staff.services.destroy', $s->service_id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600" onclick="return confirm('Delete this service?')">Delete</button>
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
        {{ $services->links() }}
    </div>
</div>
@endsection
