<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">User Management</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Users</h3>
                        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded">Create User</a>
                    </div>

                    @if(session('status'))
                        <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
                    @endif

                    <table class="w-full table-auto">
                        <thead>
                            <tr class="text-left">
                                <th class="px-2 py-1">Name</th>
                                <th class="px-2 py-1">Email</th>
                                <th class="px-2 py-1">Role</th>
                                <th class="px-2 py-1">Status</th>
                                <th class="px-2 py-1">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-t">
                                    <td class="px-2 py-2">{{ $user->full_name }}</td>
                                    <td class="px-2 py-2">{{ $user->email }}</td>
                                    <td class="px-2 py-2">{{ $user->role }}</td>
                                    <td class="px-2 py-2">{{ $user->status }}</td>
                                    <td class="px-2 py-2">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-600 mr-2">Edit</a>

                                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600" onclick="return confirm('Delete this user?')">Delete</button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
