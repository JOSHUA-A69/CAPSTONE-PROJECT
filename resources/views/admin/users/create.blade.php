<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Create User</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label>First name</label>
                                <input name="first_name" value="{{ old('first_name') }}" class="w-full" />
                            </div>

                            <div>
                                <label>Middle name</label>
                                <input name="middle_name" value="{{ old('middle_name') }}" class="w-full" />
                            </div>

                            <div>
                                <label>Last name</label>
                                <input name="last_name" value="{{ old('last_name') }}" class="w-full" />
                            </div>

                            <div>
                                <label>Email</label>
                                <input name="email" value="{{ old('email') }}" class="w-full" />
                            </div>

                            <div>
                                <label>Phone</label>
                                <input name="phone" value="{{ old('phone') }}" class="w-full" />
                            </div>

                            <div>
                                <label>Role</label>
                                <select name="role" id="roleSelect" class="w-full">
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="adviser" {{ old('role') == 'adviser' ? 'selected' : '' }}>Adviser</option>
                                    <option value="priest" {{ old('role') == 'priest' ? 'selected' : '' }}>Priest</option>
                                    <option value="requestor" {{ old('role') == 'requestor' ? 'selected' : '' }}>Requestor</option>
                                </select>
                            </div>

                            <!-- Organization Selection (shown only for Adviser role) -->
                            <div id="organizationSection" class="hidden">
                                <label class="block mb-2">Assign to Organizations</label>
                                <div class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-4 max-h-60 overflow-y-auto">
                                    @if(isset($organizations) && $organizations->count() > 0)
                                        @foreach($organizations as $org)
                                            <div class="flex items-center mb-2">
                                                <input type="checkbox" 
                                                       name="organization_ids[]" 
                                                       value="{{ $org->org_id }}" 
                                                       id="org_{{ $org->org_id }}"
                                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                       {{ in_array($org->org_id, old('organization_ids', [])) ? 'checked' : '' }}>
                                                <label for="org_{{ $org->org_id }}" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                                    {{ $org->org_name }}
                                                    @if($org->adviser_id)
                                                        <span class="text-xs text-orange-500 dark:text-orange-400">(Adviser - {{ $org->adviser->name }})</span>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-sm text-gray-500 dark:text-gray-400">No organizations available.</p>
                                    @endif
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    Select the organizations this adviser will manage. This will automatically assign them as the adviser for the selected organizations.
                                </p>
                            </div>

                            <div>
                                <label>Status</label>
                                <select name="status" class="w-full">
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>

                            <div>
                                <label>Password</label>
                                <input type="password" name="password" class="w-full" />
                            </div>

                            <div>
                                <label>Confirm Password</label>
                                <input type="password" name="password_confirmation" class="w-full" />
                            </div>

                            <div>
                                <button class="px-4 py-2 bg-green-600 text-white rounded">Create</button>
                            </div>
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
                    // Uncheck all checkboxes when hiding
                    const checkboxes = organizationSection.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(cb => cb.checked = false);
                }
            }

            // Initial check
            toggleOrganizationSection();

            // Listen for changes
            roleSelect.addEventListener('change', toggleOrganizationSection);
        });
    </script>
</x-app-layout>
