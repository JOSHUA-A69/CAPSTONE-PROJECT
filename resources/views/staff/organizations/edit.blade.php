<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-start gap-4">
            <!-- Left: Title Section -->
            <div class="flex-1">
                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                    Edit Organization
                </p>
                <h2 class="font-bold text-base sm:text-xl lg:text-2xl text-gray-800 dark:text-gray-200 truncate">
                    {{ $organization->org_name }}
                </h2>
            </div>

            <!-- Right: Back Button -->
            <a href="{{ route('staff.organizations.index') }}"
               class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-600 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-gray-700 dark:hover:bg-gray-600 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 whitespace-nowrap flex-shrink-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back</span>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('staff.organizations.update', $organization->org_id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Current Organization Info Banner -->
                        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm text-blue-700 dark:text-blue-300">
                                    Editing: <strong>{{ $organization->org_name }}</strong>
                                    @if($organization->adviser)
                                        • Current Adviser: <strong>{{ $organization->adviser->full_name }}</strong>
                                    @else
                                        • <span class="text-amber-600">No adviser assigned</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Organization Name -->
                        <div class="mb-6">
                            <label for="org_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Organization Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="org_name"
                                   name="org_name"
                                   value="{{ old('org_name', $organization->org_name) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="{{ $organization->org_name }}"
                                   required>
                            @error('org_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Organization Description -->
                        <div class="mb-6">
                            <label for="org_desc" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Organization Description
                            </label>
                            <textarea id="org_desc"
                                      name="org_desc"
                                      rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="Brief description of the organization's purpose and activities">{{ old('org_desc', $organization->org_desc) }}</textarea>
                            @error('org_desc')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Adviser Selection -->
                        <div class="mb-6">
                            <label for="adviser_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Assign Adviser
                            </label>
                            <select id="adviser_id"
                                    name="adviser_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- No adviser assigned --</option>
                                @foreach($advisers as $adv)
                                    <option value="{{ $adv->id }}" @if(old('adviser_id', $organization->adviser_id) == $adv->id) selected @endif>
                                        {{ $adv->full_name }} ({{ $adv->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('adviser_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @if($advisers->count() > 0)
                                    Change or assign an adviser to supervise this organization
                                @else
                                    <span class="text-amber-600">⚠️ No advisers available. Create adviser accounts first.</span>
                                @endif
                            </p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('staff.organizations.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>

                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Update Organization
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
