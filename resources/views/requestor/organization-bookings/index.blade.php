@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Organization Booking Requests</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Submit requests for organization-based activities and services</p>
            </div>
            <a href="{{ route('requestor.organization-bookings.create') }}"
               class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                New Request
            </a>
        </div>
    </div>

    <!-- Status Toast -->
    @if (session('status'))
        <div id="bookingSuccessToast" class="fixed top-5 right-5 z-50 max-w-md w-[calc(100vw-2.5rem)] sm:w-full opacity-0 translate-y-2 pointer-events-none transition-all duration-500" role="status" aria-live="polite">
            <div class="rounded-xl border border-emerald-200 dark:border-emerald-700 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md shadow-2xl overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">Booking Request Submitted</p>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ session('message', 'Your request was submitted successfully.') }}</p>
                        </div>
                        <button type="button" id="dismissBookingSuccessToast" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors" aria-label="Dismiss success message">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <noscript>
            <div class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800">
                <span class="text-green-800 dark:text-green-200 font-medium">{{ session('message', 'Action completed successfully.') }}</span>
            </div>
        </noscript>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-800 dark:text-red-200 font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Requests List -->
    @if($requests->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Desktop View -->
            <div class="hidden md:block">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Activity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Organization</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($requests as $request)
                            @php
                                $isCancelledByRequestor = $request->status === 'cancelled'
                                    || str_contains(strtolower((string) $request->rejection_reason), 'cancelled by requestor');
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $request->activity_name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ Str::limit($request->purpose, 60) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $requestOrganizations = $request->organizations ?? collect();
                                    @endphp
                                    @if($requestOrganizations->isNotEmpty())
                                        <div class="space-y-1.5">
                                            @foreach($requestOrganizations as $org)
                                                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $org->org_name }}</div>
                                                @if($org->adviser)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 -mt-1">
                                                        Adviser: {{ $org->adviser->full_name ?? $org->adviser->name }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ $request->organization?->org_name ?? 'Unknown Organization' }}
                                        </div>
                                        @if($request->organization?->adviser)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Adviser: {{ $request->organization->adviser->full_name ?? $request->organization->adviser->name }}
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $request->requested_date->format('M j, Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $request->requested_date->format('g:i A') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $request->status_badge_class }}">
                                            {{ ucfirst($request->status) }}
                                            @if($request->is_overdue && $request->status === 'pending')
                                                ⚠️
                                            @endif
                                        </span>
                                        @if($isCancelledByRequestor)
                                            <span class="inline-flex px-2 py-1 text-[11px] font-medium rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">
                                                Cancelled by requestor
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $request->created_at->format('M j, Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $request->created_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @php
                                        $canCancel = $request->canRequestCancellation();
                                        $requiresApproval = $request->requiresCancellationApproval();
                                        $hasPendingCancellation = $request->pendingCancellation !== null;
                                    @endphp
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('requestor.organization-bookings.show', $request) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0c0 1.657-3.582 6-9 6s-9-4.343-9-6 3.582-6 9-6 9 4.343 9 6z"/>
                                            </svg>
                                            View
                                        </a>
                                        @if($request->status === 'pending')
                                            <a href="{{ route('requestor.organization-bookings.edit', $request) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </a>
                                        @endif
                                        @if($canCancel)
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold {{ $hasPendingCancellation ? 'text-amber-700 bg-amber-100 cursor-not-allowed' : 'text-red-700 bg-red-100 hover:bg-red-200 transition-colors' }}"
                                                {{ $hasPendingCancellation ? 'disabled' : '' }}
                                                data-cancel-btn
                                                data-cancel-url="{{ route('requestor.organization-bookings.request-cancellation', $request) }}"
                                                data-activity="{{ $request->activity_name }}"
                                                data-requires-approval="{{ $requiresApproval ? '1' : '0' }}"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                {{ $hasPendingCancellation ? 'Pending Cancellation' : 'Cancel' }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="md:hidden space-y-4 p-4">
                @foreach($requests as $request)
                    @php
                        $isCancelledByRequestor = $request->status === 'cancelled'
                            || str_contains(strtolower((string) $request->rejection_reason), 'cancelled by requestor');
                    @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow duration-200">
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $request->activity_name }}
                                    </h3>
                                        @php
                                            $requestOrganizations = $request->organizations ?? collect();
                                        @endphp
                                        @if($requestOrganizations->isNotEmpty())
                                            <div class="mt-1 space-y-0.5">
                                                @foreach($requestOrganizations as $org)
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $org->org_name }}</p>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                {{ $request->organization?->org_name ?? 'Unknown Organization' }}
                                            </p>
                                        @endif
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $request->status_badge_class }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                    @if($isCancelledByRequestor)
                                        <span class="inline-flex px-2 py-1 text-[11px] font-medium rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">
                                            Cancelled by requestor
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-4">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $request->requested_date->format('M j, Y \a\t g:i A') }}
                            </div>

                            <div class="flex space-x-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                                @php
                                    $canCancel = $request->canRequestCancellation();
                                    $requiresApproval = $request->requiresCancellationApproval();
                                    $hasPendingCancellation = $request->pendingCancellation !== null;
                                @endphp
                                <a href="{{ route('requestor.organization-bookings.show', $request) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-4 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0c0 1.657-3.582 6-9 6s-9-4.343-9-6 3.582-6 9-6 9 4.343 9 6z"/>
                                    </svg>
                                    View Details
                                </a>
                                @if($request->status === 'pending')
                                    <a href="{{ route('requestor.organization-bookings.edit', $request) }}"
                                       class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-4 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                @endif
                                @if($canCancel)
                                    <button
                                        type="button"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-4 text-sm font-medium rounded-lg {{ $hasPendingCancellation ? 'text-amber-700 bg-amber-100 cursor-not-allowed' : 'text-red-700 bg-red-100 hover:bg-red-200 transition-colors' }}"
                                        {{ $hasPendingCancellation ? 'disabled' : '' }}
                                        data-cancel-btn
                                        data-cancel-url="{{ route('requestor.organization-bookings.request-cancellation', $request) }}"
                                        data-activity="{{ $request->activity_name }}"
                                        data-requires-approval="{{ $requiresApproval ? '1' : '0' }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        {{ $hasPendingCancellation ? 'Pending' : 'Cancel' }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($requests->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No requests yet</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first organization booking request.</p>
            <div class="mt-6">
                <a href="{{ route('requestor.organization-bookings.create') }}"
                   class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    New Request
                </a>
            </div>
        </div>
    @endif
</div>

<div id="cancelBookingModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black/45" data-cancel-modal-close></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-700">
            <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Cancel Organization Booking</h3>
                <p id="cancelBookingModalHint" class="mt-1 text-sm text-gray-600 dark:text-gray-300"></p>
            </div>

            <form id="cancelBookingForm" method="POST" action="">
                @csrf
                <div class="px-6 py-4 space-y-3">
                    <label for="cancelReason" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Reason for cancellation</label>
                    <textarea
                        id="cancelReason"
                        name="reason"
                        rows="4"
                        required
                        maxlength="1000"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Please provide a clear reason for cancelling this booking."
                    ></textarea>
                    <p id="cancelBookingModalNote" class="text-xs text-gray-500 dark:text-gray-400"></p>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/40 rounded-b-2xl flex items-center justify-end gap-2">
                    <button type="button" data-cancel-modal-close class="px-4 py-2 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Keep Booking
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 transition-colors">
                        Submit Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toast = document.getElementById('bookingSuccessToast');
        const dismiss = document.getElementById('dismissBookingSuccessToast');

        if (toast) {
            const showToast = () => {
                toast.classList.remove('opacity-0', 'translate-y-2', 'pointer-events-none');
                toast.classList.add('opacity-100', 'translate-y-0');
            };

            const hideToast = () => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-2', 'pointer-events-none');
                setTimeout(() => toast.remove(), 450);
            };

            showToast();
            const autoHideTimer = setTimeout(hideToast, 5000);

            if (dismiss) {
                dismiss.addEventListener('click', function () {
                    clearTimeout(autoHideTimer);
                    hideToast();
                });
            }
        }

        const cancelModal = document.getElementById('cancelBookingModal');
        const cancelForm = document.getElementById('cancelBookingForm');
        const cancelHint = document.getElementById('cancelBookingModalHint');
        const cancelNote = document.getElementById('cancelBookingModalNote');
        const cancelReason = document.getElementById('cancelReason');

        if (!cancelModal || !cancelForm || !cancelHint || !cancelNote || !cancelReason) {
            return;
        }

        const openCancelModal = (button) => {
            const activity = button.getAttribute('data-activity') || 'this booking';
            const actionUrl = button.getAttribute('data-cancel-url');
            const requiresApproval = button.getAttribute('data-requires-approval') === '1';

            cancelForm.setAttribute('action', actionUrl || '#');
            cancelReason.value = '';
            cancelHint.textContent = `You are requesting cancellation for "${activity}".`;

            if (requiresApproval) {
                cancelNote.textContent = 'This reservation is already confirmed. Cancellation will only proceed after an authorized adviser approves your request.';
            } else {
                cancelNote.textContent = 'This reservation is still pending and can be cancelled immediately after submission.';
            }

            cancelModal.classList.remove('hidden');
            cancelModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            cancelReason.focus();
        };

        const closeCancelModal = () => {
            cancelModal.classList.add('hidden');
            cancelModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelectorAll('[data-cancel-btn]').forEach((button) => {
            button.addEventListener('click', () => openCancelModal(button));
        });

        document.querySelectorAll('[data-cancel-modal-close]').forEach((closeEl) => {
            closeEl.addEventListener('click', closeCancelModal);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !cancelModal.classList.contains('hidden')) {
                closeCancelModal();
            }
        });
    });
</script>
@endpush
@endsection
