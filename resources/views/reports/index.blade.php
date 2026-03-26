@extends('layouts.app')

@section('content')

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Validation Errors -->
        @if ($errors->any())
        <div class="mb-6 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">There were problems with your input:</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(session('status'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('status') }}</p>
                </div>
            </div>
        </div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-2xl">
            
            <!-- Header Section -->
            <div class="p-8 text-center border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="flex flex-col items-center">
                    <div class="mb-4">
                        <div class="w-24 h-24 bg-blue-600 rounded-full flex items-center justify-center shadow-lg p-1">
                            <div class="w-full h-full bg-white dark:bg-gray-800 rounded-full flex items-center justify-center p-2">
                                <img src="/images/ers-logo.png" alt="eReligiousServices" class="w-full h-full object-contain" />
                            </div>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mb-2">
                        Generate Reports
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 text-sm max-w-xl mx-auto">
                        Select a report type and date range to export system data. Advanced filters are available for detailed analysis.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('reports.generate') }}" class="p-6 md:p-8 space-y-8">
                @csrf

                <!-- Section 1: Main Configuration -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-l-4 border-blue-500 pl-3">Report Configuration</h3>
                    
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Report Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors p-2.5">
                            @foreach($types as $key => $info)
                                <option value="{{ $key }}">{{ $info['label'] }}</option>
                            @endforeach
                        </select>
                        
                        <div class="mt-2 flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 p-2 rounded border border-blue-100 dark:border-blue-800/30">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>
                                @if($role === 'adviser')
                                    Advisers can generate organization-focused reports (Approvals, Bookings, Activities).
                                @elseif($role === 'staff')
                                    Staff can generate operational and organization summaries.
                                @elseif($role === 'administrator')
                                    Admins can generate system-wide reports including quarterly and performance metrics.
                                @else
                                    Select the type of report you wish to generate.
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Date From
                            </label>
                            <input type="date" name="date_from" id="date_from" value="{{ $defaultFrom }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Date To
                            </label>
                            <input type="date" name="date_to" id="date_to" value="{{ $defaultTo }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Advanced Filters (Collapsible) -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <details class="group bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <summary class="flex items-center justify-between p-4 cursor-pointer select-none">
                            <span class="flex items-center gap-2 font-semibold text-gray-800 dark:text-gray-200">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                Advanced Filters (Optional)
                            </span>
                            <span class="transition-transform duration-300 group-open:rotate-180">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </summary>
                        
                        <div class="markdown-body p-4 pt-0 border-t border-gray-200 dark:border-gray-700 mt-0">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                <!-- Organization Filter -->
                                <div>
                                    <label for="organizations" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ $role === 'adviser' ? 'My Organizations' : 'Organization' }}
                                    </label>
                                    <select name="organizations" id="organizations"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                                        <option value="">All Organizations</option>
                                        @if($role === 'adviser')
                                            @foreach($myOrganizations as $org)
                                                <option value="{{ $org->org_id }}">{{ $org->org_name }}</option>
                                            @endforeach
                                        @else
                                            @foreach(($allOrganizations ?? collect()) as $org)
                                                <option value="{{ $org->org_id }}">{{ $org->org_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- Service Filter -->
                                <div>
                                    <label for="services" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Service Type
                                    </label>
                                    <select name="services" id="services"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                                        <option value="">All Services</option>
                                        @foreach($services as $svc)
                                            <option value="{{ $svc->service_id }}">{{ $svc->service_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Adviser Filter (Not for advisers) -->
                                @if($role !== 'adviser')
                                <div>
                                    <label for="adviser_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Adviser
                                    </label>
                                    <select name="adviser_id" id="adviser_id"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                                        <option value="">All Advisers</option>
                                        @foreach(($advisers ?? collect()) as $adv)
                                            <option value="{{ $adv->id }}">{{ trim(($adv->first_name ?? '').' '.($adv->last_name ?? '')) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <!-- Status Filter -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Request Status
                                    </label>
                                    <select name="status" id="status"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                                        <option value="">Any Status</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>

                <!-- Section 3: Output Format & Actions -->
                <div class="space-y-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <div>
                        <label for="format" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Output Format <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            @foreach($formats as $fmt)
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="format" value="{{ $fmt }}" class="peer sr-only" @if($loop->first) checked @endif>
                                    <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 peer-checked:text-blue-600 dark:peer-checked:text-blue-400 transition-all text-center">
                                        <div class="font-semibold uppercase tracking-wider text-sm">{{ $fmt }}</div>
                                    </div>
                                    <div class="absolute top-2 right-2 hidden peer-checked:block text-blue-500">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex items-center h-5">
                            <input type="checkbox" id="async" name="async" value="1"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <div class="ml-2 text-sm">
                            <label for="async" class="font-medium text-gray-700 dark:text-gray-300">Generate in background</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Recommended for reports covering large date ranges.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-8 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-end items-center gap-4">
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800 shadow-md transform transition-all hover:-translate-y-0.5 relative overflow-hidden group">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Generate Report
                        </span>
                    </button>
                </div>

            </form>
        </div>
        
        <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            <p>Tip: Start with the default 30-day range. Use Advanced Filters only if needed to narrow down results.</p>
        </div>
    </div>
</div>

@endsection
