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
                                <select name="role" class="w-full">
                                    <option value="admin">Admin</option>
                                    <option value="staff">Staff</option>
                                    <option value="adviser">Adviser</option>
                                    <option value="priest">Priest</option>
                                    <option value="requestor">Requestor</option>
                                </select>
                            </div>

                            <div>
                                <label>User Role Mapping (optional)</label>
                                <select name="user_role_id" class="w-full">
                                    <option value="">-- select --</option>
                                    @foreach($userRoles as $r)
                                        <option value="{{ $r->user_role_id }}">{{ $r->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label>Status</label>
                                <select name="status" class="w-full">
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="suspended">Suspended</option>
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
</x-app-layout>
