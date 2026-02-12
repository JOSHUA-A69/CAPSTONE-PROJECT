<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Create New Organization
            </h2>

            <a href="{{ route('staff.organizations.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Organizations
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('staff.organizations.store') }}">
                        @csrf

                        <!-- Organization Name -->
                        <div class="mb-6">
                            <label for="org_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Organization Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="org_name"
                                   name="org_name"
                                   value="{{ old('org_name') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Enter organization name"
                                   required>
                            @error('org_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter any organization name</p>
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
                                      placeholder="Brief description of the organization's purpose and activities">{{ old('org_desc') }}</textarea>
                            @error('org_desc')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional: Provide details about the organization</p>
                        </div>

                        <!-- Adviser Selection -->
                        <div class="mb-6">
                            <label for="adviser_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Assign Adviser
                            </label>
                            <select id="adviser_id"
                                    name="adviser_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- No adviser assigned yet --</option>
                                @foreach($advisers as $adv)
                                    <option value="{{ $adv->id }}" @if(old('adviser_id') == $adv->id) selected @endif>
                                        {{ $adv->full_name }} ({{ $adv->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('adviser_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @if($advisers->count() > 0)
                                    Select an adviser to supervise this organization (optional)
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
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Create Organization
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
