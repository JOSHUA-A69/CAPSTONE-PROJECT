<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cancellation Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">@if(($status ?? 'pending') === 'completed') Completed @else Pending @endif Cancellation Requests</h3>
                        <div class="space-x-2">
                            <a href="{{ route('staff.cancellations.index', ['status' => 'pending']) }}" class="px-3 py-1 rounded text-sm {{ ($status ?? 'pending') === 'pending' ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700' }}">Pending</a>
                            <a href="{{ route('staff.cancellations.index', ['status' => 'completed']) }}" class="px-3 py-1 rounded text-sm {{ ($status ?? 'pending') === 'completed' ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700' }}">Completed</a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reservation</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requestor</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Confirmations</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($cancellations as $c)
                                <tr>
                                    <td class="px-4 py-3 text-sm">#{{ $c->cancellation_id }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium">{{ $c->reservation->activity_name ?? 'Reservation #'.$c->reservation_id }}</div>
                                        <div class="text-gray-500 text-xs">{{ optional($c->reservation->schedule_date)->format('M d, Y h:i A') }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium">{{ $c->requestor->name }}</div>
                                        <div class="text-gray-500 text-xs">{{ $c->requestor->email }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div>{{ $c->created_at->diffForHumans() }}</div>
                                        <div class="text-gray-500 text-xs">{{ $c->created_at->format('M d, Y h:i A') }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex flex-wrap gap-1">
                                            <span class="px-2 py-0.5 rounded text-xs {{ $c->isStaffConfirmed() ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">Staff</span>
                                            <span class="px-2 py-0.5 rounded text-xs {{ $c->isAdminConfirmed() ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">Admin</span>
                                            <span class="px-2 py-0.5 rounded text-xs {{ $c->isAdviserConfirmed() ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">Adviser</span>
                                            <span class="px-2 py-0.5 rounded text-xs {{ $c->isPriestConfirmed() ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">Priest</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('staff.cancellations.show', $c->cancellation_id) }}" class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">No records found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $cancellations->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
