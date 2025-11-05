<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-semibold text-xl leading-tight">
            ✏️ Review Change Request #{{ $changeRequest->id }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.change-requests.index') }}" class="text-sm text-blue-600 hover:underline">← Back to Change Requests</a>
            </div>

            @if(session('error'))
                <div class="mb-4 p-3 rounded bg-red-50 text-red-700 border border-red-200">{{ session('error') }}</div>
            @endif
            @if(session('status'))
                <div class="mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200">{{ session('status') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Reservation Summary -->
                <div class="lg:col-span-1">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-lg font-semibold text-heading mb-3">Reservation</h3>
                            <dl class="text-sm text-muted space-y-2">
                                <div class="flex justify-between"><dt>ID</dt><dd class="text-heading font-medium">#{{ $changeRequest->reservation->reservation_id ?? '—' }}</dd></div>
                                <div class="flex justify-between"><dt>Service</dt><dd>{{ optional($changeRequest->reservation->service)->service_name ?? '—' }}</dd></div>
                                <div class="flex justify-between"><dt>Venue</dt><dd>{{ optional($changeRequest->reservation->venue)->name ?? '—' }}</dd></div>
                                <div class="flex justify-between"><dt>Organization</dt><dd>{{ optional($changeRequest->reservation->organization)->org_name ?? '—' }}</dd></div>
                                <div class="flex justify-between"><dt>Requested by</dt><dd>{{ $changeRequest->requestor->full_name ?? $changeRequest->requestor->name ?? '—' }}</dd></div>
                                <div class="flex justify-between"><dt>Status</dt><dd><span class="px-2 py-1 rounded text-xs font-semibold {{ $changeRequest->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($changeRequest->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst($changeRequest->status) }}</span></dd></div>
                                <div class="flex justify-between"><dt>Requested</dt><dd>{{ \\Carbon\\Carbon::parse($changeRequest->requested_at)->toDayDateTimeString() }}</dd></div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Changes Diff -->
                <div class="lg:col-span-2">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-lg font-semibold text-heading mb-3">Requested Changes</h3>
                            @php
                                $changes = $changeRequest->changes_requested ?? [];
                            @endphp
                            @if(empty($changes))
                                <p class="text-muted">No change details available.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="text-left text-muted border-b">
                                                <th class="py-2 pr-4">Field</th>
                                                <th class="py-2 pr-4">Current</th>
                                                <th class="py-2 pr-4">Requested</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($changes as $field => $diff)
                                                <tr class="border-b last:border-0">
                                                    <td class="py-2 pr-4 font-medium text-heading">{{ ucwords(str_replace('_',' ', $field)) }}</td>
                                                    <td class="py-2 pr-4 text-gray-600">{{ is_array($diff['old'] ?? null) ? json_encode($diff['old']) : ($diff['old'] ?? '—') }}</td>
                                                    <td class="py-2 pr-4 text-heading font-semibold">{{ is_array($diff['new'] ?? null) ? json_encode($diff['new']) : ($diff['new'] ?? '—') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($changeRequest->status === 'pending')
                        <div class="mt-6 flex flex-col sm:flex-row gap-3">
                            <form action="{{ route('admin.change-requests.approve', $changeRequest->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg">Approve Changes</button>
                            </form>

                            <form action="{{ route('admin.change-requests.reject', $changeRequest->id) }}" method="POST" class="flex-1">
                                @csrf
                                <div class="flex items-center gap-3">
                                    <input type="text" name="rejection_reason" placeholder="Reason for rejection" class="w-full rounded-lg border-gray-300" required minlength="10" maxlength="1000" />
                                    <button type="submit" class="px-5 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg">Reject</button>
                                </div>
                                @error('rejection_reason')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
