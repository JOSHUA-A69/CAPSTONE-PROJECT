<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <!-- Notifications -->
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

            @if(session('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-500 p-4 rounded-r-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Please fix the following:</h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-all duration-300">
                
                <!-- Header Section -->
                <div class="p-8 pb-6 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mb-2">
                                Edit User
                            </h2>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">
                                Update user information and permissions.
                            </p>
                        </div>
                        <div>
                             <!-- Optional: Add user avatar or badge here if desired -->
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">

                    <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Personal Info -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-l-4 border-blue-500 pl-3">Personal Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                                </div>
                                <div>
                                    <label for="middle_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Middle Name</label>
                                    <input type="text" name="middle_name" id="middle_name" value="{{ old('middle_name', $user->middle_name) }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                                </div>
                            </div>
                        </div>

                        <!-- Role & Status -->
                        <div class="space-y-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-l-4 border-blue-500 pl-3">Role & Permissions</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="roleSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                                    <select name="role" id="roleSelect"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                                        <option value="admin" {{ old('role', $user->role)==='admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="staff" {{ old('role', $user->role)==='staff' ? 'selected' : '' }}>Staff</option>
                                        <option value="adviser" {{ old('role', $user->role)==='adviser' ? 'selected' : '' }}>Adviser</option>
                                        <option value="priest" {{ old('role', $user->role)==='priest' ? 'selected' : '' }}>Priest</option>
                                        <option value="requestor" {{ old('role', $user->role)==='requestor' ? 'selected' : '' }}>Requestor</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Status</label>
                                    <select name="status" id="status"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors">
                                        <option value="active" {{ old('status', $user->status)==='active' ? 'selected' : '' }}>Active</option>
                                        <option value="pending" {{ old('status', $user->status)==='pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="suspended" {{ old('status', $user->status)==='suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Organization Selection (shown only for Adviser role) -->
                            <div id="organizationSection" class="{{ old('role', $user->role) === 'adviser' ? '' : 'hidden' }}">
                                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign to Organizations</label>
                                    <div class="bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-lg border p-4 max-h-60 overflow-y-auto space-y-2">
                                        @if(isset($organizations) && $organizations->count() > 0)
                                            @php
                                                $selectedOrgIds = old('organization_ids', $userOrganizationIds ?? []);
                                            @endphp
                                            @foreach($organizations as $org)
                                                <label class="flex items-start p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md cursor-pointer transition-colors">
                                                    <input type="checkbox" 
                                                           name="organization_ids[]" 
                                                           value="{{ $org->org_id }}" 
                                                           class="w-4 h-4 mt-1 text-blue-600 rounded border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600"
                                                           {{ in_array($org->org_id, $selectedOrgIds) ? 'checked' : '' }}>
                                                    <span class="ml-3 text-sm text-gray-900 dark:text-gray-300">
                                                        {{ $org->org_name }}
                                                        @if($org->adviser_id && $org->adviser_id != $user->id)
                                                            <span class="block text-xs text-orange-500 dark:text-orange-400 mt-0.5">Adviser: {{ $org->adviser?->name ?? 'Unknown (ID: '.$org->adviser_id.')' }}</span>
                                                        @elseif($org->adviser_id == $user->id)
                                                            <span class="block text-xs text-green-500 dark:text-green-400 mt-0.5">Currently assigned</span>
                                                        @endif
                                                    </span>
                                                </label>
                                            @endforeach
                                        @else
                                            <p class="text-sm text-gray-500 dark:text-gray-400">No organizations available.</p>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Select the organizations this adviser will manage.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Security -->
                        <div class="space-y-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-l-4 border-blue-500 pl-3">Security</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password <span class="text-xs font-normal text-gray-500">(leave blank to keep current)</span></label>
                                    <input type="password" name="password" id="password"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800 shadow-md transform transition-all hover:-translate-y-0.5">
                                Update User
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('roleSelect');
            const organizationSection = document.getElementById('organizationSection');

            function toggleOrganizationSection() {
                if (roleSelect.value === 'adviser') {
                    organizationSection.classList.remove('hidden');
                } else {
                    organizationSection.classList.add('hidden');
                }
            }

            // Listen for changes
            roleSelect.addEventListener('change', toggleOrganizationSection);
        });
    </script>
</x-app-layout>
