<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit User</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if(session('status'))
                        <div class="mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-3 rounded bg-red-50 text-red-700 border border-red-200">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 p-3 rounded bg-yellow-50 text-yellow-800 border border-yellow-200">
                            <div class="font-semibold mb-1">Please fix the following:</div>
                            <ul class="list-disc ml-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label>First name</label>
                                <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" class="w-full" />
                                @error('first_name')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label>Middle name</label>
                                <input type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}" class="w-full" />
                                @error('middle_name')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label>Last name</label>
                                <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="w-full" />
                                @error('last_name')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label>Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full" />
                                @error('email')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label>Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full" />
                                @error('phone')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label>Role</label>
                                <select name="role" class="w-full">
                                    <option value="admin" {{ old('role', $user->role)==='admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="staff" {{ old('role', $user->role)==='staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="adviser" {{ old('role', $user->role)==='adviser' ? 'selected' : '' }}>Adviser</option>
                                    <option value="priest" {{ old('role', $user->role)==='priest' ? 'selected' : '' }}>Priest</option>
                                    <option value="requestor" {{ old('role', $user->role)==='requestor' ? 'selected' : '' }}>Requestor</option>
                                </select>
                                @error('role')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label>User Role Mapping (optional)</label>
                                <select name="user_role_id" class="w-full">
                                    <option value="">-- select --</option>
                                    @foreach($userRoles as $r)
                                        <option value="{{ $r->user_role_id }}" {{ (int)old('user_role_id', $user->user_role_id) === (int)$r->user_role_id ? 'selected' : '' }}>{{ $r->role_name }}</option>
                                    @endforeach
                                </select>
                                @error('user_role_id')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label>Status</label>
                                <select name="status" class="w-full">
                                    <option value="active" {{ old('status', $user->status)==='active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ old('status', $user->status)==='pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="suspended" {{ old('status', $user->status)==='suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                                @error('status')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label>New Password (leave blank to keep current)</label>
                                <input type="password" name="password" class="w-full" />
                                @error('password')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label>Confirm Password</label>
                                <input type="password" name="password_confirmation" class="w-full" />
                            </div>

                            <div>
                                <button class="px-4 py-2 bg-green-600 text-white rounded">Update</button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
