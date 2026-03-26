@extends('layouts.app')

@section('content')
<style>
    /* ===== UNIVERSAL RESPONSIVE DESIGN FOR SERVICES PAGE ===== */
    
    /* Base styles - Desktop first */
    .services-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1.5rem;
        min-height: calc(100vh - 80px);
        display: flex;
        flex-direction: column;
    }
    
    .services-header h1 {
        font-size: 1.875rem;
        font-weight: 700;
        line-height: 1.2;
    }
    
    .services-header p {
        font-size: 1rem;
        color: #6b7280;
    }
    
    .services-table-container {
        flex: 1;
        overflow: hidden;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .services-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .services-table th {
        padding: 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .services-table td {
        padding: 1rem;
        font-size: 0.875rem;
    }
    
    .edit-btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.2s;
    }
    
    /* ===== LARGE SCREENS (1280px+) ===== */
    @media (min-width: 1280px) {
        .services-container {
            padding: 2rem 3rem;
        }
        
        .services-header h1 {
            font-size: 2rem;
        }
        
        .services-table th,
        .services-table td {
            padding: 1.25rem 1.5rem;
        }
    }
    
    /* ===== MEDIUM SCREENS / TABLETS (768px - 1023px) ===== */
    @media (max-width: 1023px) and (min-width: 768px) {
        .services-container {
            padding: 1rem;
            height: calc(100vh - 80px);
            overflow: hidden;
        }
        
        .services-header h1 {
            font-size: 1.5rem;
        }
        
        .services-header p {
            font-size: 0.875rem;
        }
        
        .services-table-container {
            flex: 1;
            overflow-y: auto;
        }
        
        .services-table th {
            padding: 0.75rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .services-table td {
            padding: 0.75rem 0.5rem;
            font-size: 0.8125rem;
        }
    }
    
    /* ===== TABLET (640px - 767px) ===== */
    @media (max-width: 767px) and (min-width: 640px) {
        .services-container {
            padding: 0.75rem;
            height: calc(100vh - 70px);
            overflow: hidden;
        }
        
        .services-header {
            margin-bottom: 0.75rem !important;
        }
        
        .services-header h1 {
            font-size: 1.375rem;
            margin-bottom: 0.25rem;
        }
        
        .services-header p {
            font-size: 0.8125rem;
        }
        
        .services-table-container {
            flex: 1;
            overflow-y: auto;
            border-radius: 8px;
        }
    }
    
    /* ===== MOBILE (481px - 639px) ===== */
    @media (max-width: 639px) and (min-width: 481px) {
        .services-container {
            padding: 0.5rem;
            height: calc(100vh - 65px);
            overflow: hidden;
        }
        
        .services-header {
            margin-bottom: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }
        
        .services-header h1 {
            font-size: 1.25rem;
            margin-bottom: 0.125rem;
        }
        
        .services-header p {
            font-size: 0.75rem;
        }
        
        .services-table-container {
            flex: 1;
            overflow-y: auto;
            border-radius: 8px;
        }
    }
    
    /* ===== SMALL MOBILE (376px - 480px) ===== */
    @media (max-width: 480px) and (min-width: 376px) {
        .services-container {
            padding: 0.375rem;
            height: calc(100vh - 60px);
            overflow: hidden;
        }
        
        .services-header {
            margin-bottom: 0.375rem !important;
            padding-bottom: 0.25rem !important;
        }
        
        .services-header h1 {
            font-size: 1.125rem;
        }
        
        .services-header p {
            font-size: 0.6875rem;
        }
        
        .services-table-container {
            flex: 1;
            overflow-y: auto;
            border-radius: 6px;
        }
    }
    
    /* ===== EXTRA SMALL MOBILE (320px - 375px) ===== */
    @media (max-width: 375px) and (min-width: 321px) {
        .services-container {
            padding: 0.25rem;
            height: calc(100vh - 56px);
            overflow: hidden;
        }
        
        .services-header {
            margin-bottom: 0.25rem !important;
            padding-bottom: 0.125rem !important;
        }
        
        .services-header h1 {
            font-size: 1rem;
        }
        
        .services-header p {
            font-size: 0.625rem;
        }
        
        .services-table-container {
            flex: 1;
            overflow-y: auto;
            border-radius: 6px;
        }
    }
    
    /* ===== ULTRA SMALL MOBILE (320px and below) ===== */
    @media (max-width: 320px) {
        .services-container {
            padding: 0.125rem;
            height: calc(100vh - 52px);
            overflow: hidden;
        }
        
        .services-header {
            margin-bottom: 0.125rem !important;
            padding-bottom: 0 !important;
        }
        
        .services-header h1 {
            font-size: 0.9375rem;
        }
        
        .services-header p {
            font-size: 0.5625rem;
            display: none;
        }
        
        .services-table-container {
            flex: 1;
            overflow-y: auto;
            border-radius: 4px;
        }
    }
    
    /* ===== MODAL RESPONSIVE STYLES ===== */
    #editModal .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }
    
    @media (max-width: 640px) {
        #editModal > div:last-child {
            margin: 0.5rem;
            max-width: calc(100vw - 1rem);
        }
        
        #editModal .px-6 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        #editModal .py-4 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        
        #editModal input,
        #editModal select,
        #editModal textarea {
            font-size: 16px;
        }
    }
    
    @media (max-width: 375px) {
        #editModal > div:last-child {
            margin: 0.25rem;
            max-width: calc(100vw - 0.5rem);
        }
        
        #editModal .text-xl {
            font-size: 1rem;
        }
        
        #editModal label {
            font-size: 0.75rem;
        }
        
        #editModal input,
        #editModal select,
        #editModal textarea {
            padding: 0.5rem;
            font-size: 16px;
        }
    }
</style>

<div class="services-container max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="services-header mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-heading text-3xl font-bold text-gray-900 dark:text-white mb-2">Manage Services</h1>
            <p class="text-muted dark:text-gray-400">Staff can update existing service definitions</p>
        </div>
        <!-- No Add button for staff -->
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="flash-message mb-6 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="flash-message mb-6 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Services List - Mobile Card Layout & Desktop Table -->
    @if($services->isEmpty())
    <div class="p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
        </svg>
        <p class="text-muted dark:text-gray-400 text-lg">No services found.</p>
    </div>
    @else
    <!-- Desktop Table View (768px+) -->
    <div class="services-table-container card hidden md:block">
        <div class="overflow-x-auto">
            <table class="services-table min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Service</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Duration</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($services as $service)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                        <td class="px-6 py-4">
                            <div class="service-name text-sm font-medium text-gray-900 dark:text-white" title="{{ $service->service_name }}">{{ $service->service_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="service-category text-sm text-gray-700 dark:text-gray-300" title="{{ $service->service_category ?? '—' }}">{{ $service->service_category ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="service-duration text-sm text-gray-700 dark:text-gray-300">{{ $service->duration !== null ? ($service->duration . ' min') : '—' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="service-description text-sm text-gray-600 dark:text-gray-300" title="{{ $service->description ?? '—' }}">{{ $service->description ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button 
                                data-name="{{ e($service->service_name) }}"
                                data-description="{{ e($service->description) }}"
                                data-category="{{ e($service->service_category) }}"
                                data-duration="{{ e($service->duration) }}"
                                data-id="{{ $service->service_id }}"
                                class="btn-edit-service edit-btn text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View (below 768px) - Full Text Display -->
    <div class="services-table-container card md:hidden flex flex-col gap-2 p-0">
        <style>
            @media (max-width: 767px) {
                .service-card {
                    display: flex;
                    flex-direction: column;
                    gap: 0.5rem;
                    padding: 0.75rem;
                    border-bottom: 1px solid #e5e7eb;
                    background: white;
                    border-radius: 0;
                }

                .dark .service-card {
                    background: #1f2937;
                    border-bottom-color: #374151;
                }

                .service-card:last-child {
                    border-bottom: none;
                }

                .service-card-field {
                    display: flex;
                    flex-direction: column;
                    gap: 0.25rem;
                }

                .service-card-label {
                    font-size: 0.625rem;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    color: #6b7280;
                }

                .dark .service-card-label {
                    color: #9ca3af;
                }

                .service-card-value {
                    font-size: 0.8125rem;
                    font-weight: 500;
                    color: #111827;
                    line-height: 1.4;
                    word-break: break-word;
                }

                .dark .service-card-value {
                    color: #f3f4f6;
                }

                .service-card-actions {
                    margin-top: 0.5rem;
                    display: flex;
                    gap: 0.5rem;
                }

                .service-card-actions button {
                    flex: 1;
                    padding: 0.5rem;
                    font-size: 0.8125rem;
                    font-weight: 600;
                    border-radius: 6px;
                    border: none;
                    cursor: pointer;
                    transition: all 0.2s;
                }
            }
        </style>

        @foreach($services as $service)
        <div class="service-card">
            <div class="service-card-field">
                <span class="service-card-label">Service Name</span>
                <span class="service-card-value">{{ $service->service_name }}</span>
            </div>

            <div class="service-card-field">
                <span class="service-card-label">Category</span>
                <span class="service-card-value">{{ $service->service_category ?? '—' }}</span>
            </div>

            <div class="service-card-field">
                <span class="service-card-label">Duration</span>
                <span class="service-card-value">{{ $service->duration !== null ? ($service->duration . ' minutes') : '—' }}</span>
            </div>

            @if($service->description)
            <div class="service-card-field">
                <span class="service-card-label">Description</span>
                <span class="service-card-value">{{ $service->description }}</span>
            </div>
            @endif

            <div class="service-card-actions">
                <button 
                    data-name="{{ e($service->service_name) }}"
                    data-description="{{ e($service->description) }}"
                    data-category="{{ e($service->service_category) }}"
                    data-duration="{{ e($service->duration) }}"
                    data-id="{{ $service->service_id }}"
                    class="btn-edit-service bg-indigo-600 hover:bg-indigo-700 text-white">
                    Edit Service
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<!-- Edit Service Modal (Staff only updates) -->
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service Category</label>
                    <select id="edit_service_category" name="service_category" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">— Select category —</option>
                        @foreach(($categories ?? []) as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
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

<script>
function openEditModal(id, name, description, category, duration) {
    document.getElementById('edit_service_name').value = name || '';
    document.getElementById('edit_description').value = description || '';
    const catSelect = document.getElementById('edit_service_category');
    if (catSelect) catSelect.value = category || '';
    const durInput = document.getElementById('edit_duration');
    if (durInput) durInput.value = duration || '';
    document.getElementById('editForm').action = '{{ route("staff.services.manage.update", ":id") }}'.replace(':id', id);
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// ESC to close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEditModal();
});

// Close on outside click
document.getElementById('editModal').addEventListener('click', closeEditModal);

// Bind edit buttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-edit-service').forEach(btn => {
        btn.addEventListener('click', function() {
            openEditModal(this.dataset.id, this.dataset.name, this.dataset.description, this.dataset.category, this.dataset.duration);
        });
    });
});
</script>
@endsection
