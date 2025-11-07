@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-heading text-3xl font-bold text-gray-900 dark:text-white mb-2">Manage Services</h1>
            <p class="text-muted dark:text-gray-400">Add, edit, or delete service types available for reservations</p>
        </div>
        <button onclick="openAddModal()" class="btn-primary">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add New Service
        </button>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Services Table -->
    <div class="card">
        <div class="overflow-x-auto">
            @if($services->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                <p class="text-muted dark:text-gray-400 text-lg">No services found. Add your first service!</p>
            </div>
            @else
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Service Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Mass Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Duration</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($services as $service)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $service->service_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-700 dark:text-gray-300">{{ $service->service_category ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-700 dark:text-gray-300">{{ $service->duration !== null ? ($service->duration . ' min') : '—' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 dark:text-gray-300">{{ $service->description ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                            <button 
                                data-name="{{ e($service->service_name) }}"
                                data-category="{{ e($service->service_category) }}"
                                data-description="{{ e($service->description) }}"
                                data-duration="{{ e($service->duration) }}"
                                data-id="{{ $service->service_id }}"
                                class="btn-edit-service text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                Edit
                            </button>
                            <button 
                                data-name="{{ e($service->service_name) }}"
                                data-id="{{ $service->service_id }}"
                                class="btn-delete-service text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                Delete
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Add New Service</h3>
        </div>
        <form method="POST" action="{{ route('admin.services.manage.store') }}">
            @csrf
            <div class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service Name <span class="text-red-600">*</span></label>
                    <input type="text" name="service_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., Wedding, Baptism, Mass">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mass Category</label>
                    <select name="service_category" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">— Select Mass Type —</option>
                        <option value="Institutional Mass">⛪ Institutional Mass</option>
                        <option value="Non-Institutional Mass">✝️ Non-Institutional Mass</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional. Select if this is an Institutional or Non-Institutional Mass.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Duration (minutes)</label>
                    <input type="number" name="duration" min="0" step="5" placeholder="e.g., 60" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional. Total duration of the service in minutes.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description (Optional)</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white" placeholder="Brief description of the service"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex gap-3">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                    Add Service
                </button>
                <button type="button" onclick="closeAddModal()" class="flex-1 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Service Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Service</h3>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service Name <span class="text-red-600">*</span></label>
                    <input type="text" id="edit_service_name" name="service_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mass Category</label>
                    <select id="edit_service_category" name="service_category" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">— Select Mass Type —</option>
                        <option value="Institutional Mass">⛪ Institutional Mass</option>
                        <option value="Non-Institutional Mass">✝️ Non-Institutional Mass</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Duration (minutes)</label>
                    <input type="number" id="edit_duration" name="duration" min="0" step="5" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description (Optional)</label>
                    <textarea id="edit_description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex gap-3">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                    Update Service
                </button>
                <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Form (hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function openEditModalFrom(el, id) {
    const name = el.dataset.name || '';
    const description = el.dataset.description || '';
    const category = el.dataset.category || '';
    const duration = el.dataset.duration || '';
    openEditModal(id, name, description, category, duration);
}

function confirmDeleteFrom(el, id) {
    const name = el.dataset.name || '';
    confirmDelete(id, name);
}

function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}

function openEditModal(id, name, description, category, duration) {
    document.getElementById('edit_service_name').value = name;
    document.getElementById('edit_description').value = description;
    const catSelect = document.getElementById('edit_service_category');
    if (catSelect) {
        catSelect.value = category || '';
    }
    const durInput = document.getElementById('edit_duration');
    if (durInput) {
        durInput.value = duration || '';
    }
    document.getElementById('editForm').action = '{{ route("admin.services.manage.update", ":id") }}'.replace(':id', id);
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function confirmDelete(id, name) {
    if (confirm('Are you sure you want to delete "' + name + '"?\n\nThis action cannot be undone. The service will only be deleted if it is not being used in any reservations.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route("admin.services.manage.destroy", ":id") }}'.replace(':id', id);
        form.submit();
    }
}

// Close modals on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
        closeEditModal();
    }
});

// Close modals on outside click
document.getElementById('addModal').addEventListener('click', closeAddModal);
document.getElementById('editModal').addEventListener('click', closeEditModal);

// Bind action buttons (no inline JS to avoid quoting issues)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-edit-service').forEach(btn => {
        btn.addEventListener('click', function() {
            openEditModalFrom(this, this.dataset.id);
        });
    });
    document.querySelectorAll('.btn-delete-service').forEach(btn => {
        btn.addEventListener('click', function() {
            confirmDeleteFrom(this, this.dataset.id);
        });
    });
});
</script>
@endsection
