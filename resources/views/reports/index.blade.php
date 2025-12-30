@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-4xl">
    <h1 class="text-2xl font-semibold mb-2">Generate Report</h1>
    <p class="text-gray-600 mb-4">Select a report type and a simple date range. Optional filters are available under Advanced.</p>

    @if(session('status'))
        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('reports.generate') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block font-medium">Report Type</label>
            <select name="type" class="mt-1 w-full border rounded p-2" required>
                @foreach($types as $key => $info)
                    <option value="{{ $key }}">{{ $info['label'] }}</option>
                @endforeach
            </select>
            @if($role === 'adviser')
                <p class="text-xs text-gray-500 mt-1">Advisers can generate organization-focused reports (Approvals/Rejections, Booking Summary, Activities).</p>
            @elseif($role === 'staff')
                <p class="text-xs text-gray-500 mt-1">Staff can generate operational and organization summaries.</p>
            @elseif($role === 'administrator')
                <p class="text-xs text-gray-500 mt-1">Admins can generate system-wide reports including quarterly and performance.</p>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Date From</label>
                <input type="date" name="date_from" value="{{ $defaultFrom }}" class="mt-1 w-full border rounded p-2" />
            </div>
            <div>
                <label class="block font-medium">Date To</label>
                <input type="date" name="date_to" value="{{ $defaultTo }}" class="mt-1 w-full border rounded p-2" />
            </div>
        </div>

        <details class="rounded border p-3 bg-gray-50">
            <summary class="cursor-pointer font-medium">Advanced Filters (optional)</summary>
            <div class="mt-3 space-y-3">
                @if($role === 'adviser')
                    <div>
                        <label class="block text-sm">My Organizations</label>
                        <select name="organizations" class="mt-1 w-full border rounded p-2">
                            <option value="">All</option>
                            @foreach($myOrganizations as $org)
                                <option value="{{ $org->org_id }}">{{ $org->org_name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div>
                        <label class="block text-sm">Organization IDs</label>
                        <input type="text" name="organizations" placeholder="e.g. 1,2,3" class="mt-1 w-full border rounded p-2" />
                    </div>
                @endif

                <div>
                    <label class="block text-sm">Service</label>
                    <select name="services" class="mt-1 w-full border rounded p-2">
                        <option value="">All</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->service_id }}">{{ $svc->service_name }}</option>
                        @endforeach
                    </select>
                </div>

                @if($role !== 'adviser')
                <div>
                    <label class="block text-sm">Adviser ID</label>
                    <input type="number" name="adviser_id" placeholder="User ID" class="mt-1 w-full border rounded p-2" />
                </div>
                @endif

                <div>
                    <label class="block text-sm">Status</label>
                    <select name="status" class="mt-1 w-full border rounded p-2">
                        <option value="">Any</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>
        </details>

        <div>
            <label class="block font-medium">Format</label>
            <select name="format" class="mt-1 w-full border rounded p-2" required>
                @foreach($formats as $fmt)
                    <option value="{{ $fmt }}">{{ strtoupper($fmt) }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" id="async" name="async" value="1" />
            <label for="async">Generate in background (faster for large reports)</label>
        </div>

        <div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Generate Report</button>
        </div>
    </form>

    <p class="mt-6 text-sm text-gray-600">Tip: Start with the default 30-day range. Use Advanced only if needed.</p>
</div>
@endsection
