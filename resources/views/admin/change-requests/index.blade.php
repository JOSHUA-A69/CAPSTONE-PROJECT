<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-semibold text-xl leading-tight">
            ✏️ Reservation Change Requests
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-heading">Pending and Recent Requests</h3>
                    </div>

                    @if(session('status'))
                        <div class="mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200">{{ session('status') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 p-3 rounded bg-red-50 text-red-700 border border-red-200">{{ session('error') }}</div>
                    @endif

                    @if($changeRequests->count() === 0)
                        <p class="text-muted">No change requests found.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-muted border-b">
                                        <th class="py-3 pr-4">ID</th>
                                        <th class="py-3 pr-4">Reservation</th>
                                        <th class="py-3 pr-4">Requestor</th>
                                        <th class="py-3 pr-4">Status</th>
                                        <th class="py-3 pr-4">Requested</th>
                                        <th class="py-3 pr-4"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($changeRequests as $cr)
                                        <tr class="border-b last:border-0">
                                            <td class="py-3 pr-4 font-semibold text-heading">#{{ $cr->id }}</td>
                                            <td class="py-3 pr-4">#{{ $cr->reservation->reservation_id ?? '—' }} — {{ optional($cr->reservation)->service->service_name ?? 'Service' }}</td>
                                            <td class="py-3 pr-4">{{ $cr->requestor->full_name ?? $cr->requestor->name ?? 'Requestor' }}</td>
                                            <td class="py-3 pr-4">
                                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $cr->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($cr->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($cr->status) }}
                                                </span>
                                            </td>
                                            <td class="py-3 pr-4">{{ \\Carbon\\Carbon::parse($cr->requested_at)->diffForHumans() }}</td>
                                            <td class="py-3 pr-4 text-right">
                                                <a href="{{ route('admin.change-requests.show', $cr->id) }}" class="inline-flex items-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">Review</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $changeRequests->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
