@extends('layouts.app')

@section('content')
<div class="py-4 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Mobile-First Header -->
        <div class="mb-6">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                Manage Services
                @if(isset($showingArchived) && $showingArchived)
                    <span class="text-base font-medium text-orange-600 dark:text-orange-400">(Archived)</span>
                @endif
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                @if(isset($showingArchived) && $showingArchived)
                    View and restore archived service types.
                @else
                    Configure service types and their settings available for reservations.
                @endif
            </p>
            
            <!-- Action Buttons - Stacked on mobile, side by side on desktop -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                @if(isset($showingArchived) && $showingArchived)
                    <a href="{{ route('admin.services.manage') }}" 
                       class="inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                        <svg class="w-5 h-5 mr-2 -ml-1 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Active Services
                    </a>
                @else
                    <a href="{{ route('admin.services.manage', ['archived' => 1]) }}" 
                       class="inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                        <svg class="w-5 h-5 mr-2 -ml-1 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                        View Archived Services
                    </a>
                @endif
                @if(!isset($showingArchived) || !$showingArchived)
                    <button onclick="openAddModal()" 
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-md transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add New Service
                    </button>
                @endif
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 rounded-r-lg shadow-sm flex items-start gap-3">
                <div class="flex-shrink-0 text-green-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-green-800 dark:text-green-200">Success</p>
                    <p class="text-sm text-green-600 dark:text-green-300 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-r-lg shadow-sm flex items-start gap-3">
                <div class="flex-shrink-0 text-red-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-red-800 dark:text-red-200">Error</p>
                    <p class="text-sm text-red-600 dark:text-red-300 mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Services Content -->
        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            {{-- Debug Information --}}
            @if(config('app.debug'))
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border-b text-sm">
                    <strong>Debug:</strong> Showing {{ isset($showingArchived) && $showingArchived ? 'archived' : 'active' }} services. 
                    Services count: {{ $services->count() }}
                    @if(isset($showingArchived))
                        | showingArchived: {{ $showingArchived ? 'true' : 'false' }}
                    @endif
                </div>
            @endif
            
            @if($services->isEmpty())
            <div class="p-8 sm:p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No services found</h3>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Get started by creating a new service type.</p>
            </div>
            @else
                <!-- Desktop Table View (hidden on mobile) -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Service Name</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Duration</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($services as $service)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    {{ $service->service_name }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($service->service_category)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ str_contains($service->service_category, 'Non-Institutional') ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                            {{ $service->service_category }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ $service->duration ? $service->duration . ' mins' : '—' }}
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ $service->description ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    @include('admin.services.partials.actions', ['service' => $service])
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (visible only on mobile and tablet) -->
                <div class="lg:hidden">
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($services as $service)
                        <div class="p-4 sm:p-6">
                            <!-- Service Header -->
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white break-words">
                                        {{ $service->service_name }}
                                    </h3>
                                    @if($service->service_category)
                                        <div class="mt-2">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ str_contains($service->service_category, 'Non-Institutional') ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                                {{ $service->service_category }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Service Details -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                @if($service->duration)
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">{{ $service->duration }} minutes</span>
                                </div>
                                @endif
                                
                                @if($service->description)
                                <div class="sm:col-span-2">
                                    <div class="flex items-start text-sm">
                                        <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-gray-600 dark:text-gray-400 break-words">{{ $service->description }}</span>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-2 pt-2">
                                @if(isset($showingArchived) && $showingArchived)
                                    <form action="{{ route('admin.services.manage.restore', $service->service_id) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to restore this service?');">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-800 rounded-lg text-sm font-medium text-green-700 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                            Restore Service
                                        </button>
                                    </form>
                                @else
                                    <button 
                                        data-name="{{ e($service->service_name) }}"
                                        data-category="{{ e($service->service_category) }}"
                                        data-description="{{ e($service->description) }}"
                                        data-duration="{{ e($service->duration) }}"
                                        data-id="{{ $service->service_id }}"
                                        class="btn-edit-service flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-blue-50 dark:bg-blue-900/20 border border-blue-300 dark:border-blue-800 rounded-lg text-sm font-medium text-blue-700 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        Edit
                                    </button>
                                    <button 
                                        data-name="{{ e($service->service_name) }}"
                                        data-id="{{ $service->service_id }}"
                                        class="btn-delete-service flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-800 rounded-lg text-sm font-medium text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Archive
                                    </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div id="addModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeAddModal()" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full mx-4 sm:mx-0 border border-gray-100 dark:border-gray-700 relative z-[101]">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                            Add New Service
                        </h3>
                        <div class="mt-4">
                            <form method="POST" action="{{ route('admin.services.manage.store') }}" id="addServiceForm" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Service Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="service_name" required 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                        placeholder="e.g., Wedding, Baptism">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mass Category</label>
                                    <select name="service_category" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                                        <option value="">— Select Type (Optional) —</option>
                                        <option value="Institutional Mass">Institutional Mass</option>
                                        <option value="Non-Institutional Mass">Non-Institutional Mass</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duration (minutes)</label>
                                    <input type="number" name="duration" min="5" step="5" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                        placeholder="60">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                    <textarea name="description" rows="3" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                        placeholder="Brief details about this service..."></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse gap-3">
                <button type="button" onclick="document.getElementById('addServiceForm').submit()" 
                    class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm transition-colors">
                    Create Service
                </button>
                <button type="button" onclick="closeAddModal()" 
                    class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div id="editModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeEditModal()" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full mx-4 sm:mx-0 border border-gray-100 dark:border-gray-700 relative z-[101]">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="edit-modal-title">
                            Edit Service
                        </h3>
                        <div class="mt-4">
                            <form id="editForm" method="POST" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Service Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="edit_service_name" name="service_name" required 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mass Category</label>
                                    <select id="edit_service_category" name="service_category" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                                        <option value="">— Select Type (Optional) —</option>
                                        <option value="Institutional Mass">Institutional Mass</option>
                                        <option value="Non-Institutional Mass">Non-Institutional Mass</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duration (minutes)</label>
                                    <input type="number" id="edit_duration" name="duration" min="5" step="5" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                    <textarea id="edit_description" name="description" rows="3" 
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse gap-3">
                <button type="button" onclick="document.getElementById('editForm').submit()" 
                    class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm transition-colors">
                    Save Changes
                </button>
                <button type="button" onclick="closeEditModal()" 
                    class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeDeleteModal()" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full mx-4 sm:mx-0 border border-gray-100 dark:border-gray-700 relative z-[101]">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="delete-modal-title">
                            Archive Service?
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Are you sure you want to archive <strong id="delete_service_name_span"></strong>? It will be hidden from new reservations but kept for history.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse gap-3">
                <form id="deleteForm" method="POST" class="w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm transition-colors">
                        Yes, Archive It
                    </button>
                </form>
                <button type="button" onclick="closeDeleteModal()" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
    }

    // Modal logic for Edit
    const editBtns = document.querySelectorAll('.btn-edit-service');
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');
    const editName = document.getElementById('edit_service_name');
    const editCategory = document.getElementById('edit_service_category');
    const editDuration = document.getElementById('edit_duration');
    const editDesc = document.getElementById('edit_description');

    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const cat = btn.getAttribute('data-category');
            const desc = btn.getAttribute('data-description');
            const dur = btn.getAttribute('data-duration');

            editForm.action = `/admin/services/manage/${id}`;
            editName.value = name;
            editCategory.value = cat || "";
            editDuration.value = dur || "";
            editDesc.value = desc || "";
            
            editModal.classList.remove('hidden');
        });
    });

    function closeEditModal() {
        editModal.classList.add('hidden');
    }

    // Modal logic for Delete
    const deleteBtns = document.querySelectorAll('.btn-delete-service');
    const deleteModal = document.getElementById('deleteModal');
    const deleteForm = document.getElementById('deleteForm');
    const deleteNameSpan = document.getElementById('delete_service_name_span');

    deleteBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            deleteForm.action = `/admin/services/manage/${id}`;
            deleteNameSpan.textContent = name;
            deleteModal.classList.remove('hidden');
        });
    });

    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
    }
</script>
@endsection
