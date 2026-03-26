<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ServiceManagementController extends Controller
{
    /**
     * Display a listing of all services.
     */
    public function index(Request $request)
    {
        Log::info('ServiceManagementController index method called with params: ', $request->all());

        if ($request->has('archived')) {
            // Get only archived (soft deleted) services with pagination
            $services = Service::onlyTrashed()
                ->orderBy('service_name', 'asc')
                ->paginate(10)
                ->withQueryString();
            $showingArchived = true;
            Log::info('Showing archived services. Page count: ' . $services->count() . ', total: ' . $services->total());
        } else {
            // Get only active (non-deleted) services with pagination
            $services = Service::orderBy('service_name', 'asc')
                ->paginate(10)
                ->withQueryString();
            $showingArchived = false;
            Log::info('Showing active services. Page count: ' . $services->count() . ', total: ' . $services->total());
        }

        $categories = $this->getCategories();
        return view('admin.services.manage', compact('services', 'categories', 'showingArchived'));
    }

    /**
     * Central list of allowed service categories (keep in sync with ServiceRequest)
     */
    protected function getCategories(): array
    {
        return [
            'Liturgical Celebrations',
            'Retreats and Recollections',
            'Prayer Services',
            'Outreach Activities',
            'Daily Noon Mass',
            'Catechetical Activities',
            'Institutional Mass',
            'Non-Institutional Mass',
        ];
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:255|unique:services,service_name',
            'service_category' => ['nullable', 'string', Rule::in($this->getCategories())],
            'description' => 'nullable|string|max:500',
            'duration' => 'nullable|integer|min:0|max:300',
        ]);

        Service::create($validated);

        return redirect()->route('admin.services.manage')
            ->with('success', 'Service created successfully!');
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'service_name' => 'required|string|max:255|unique:services,service_name,' . $id . ',service_id',
            'service_category' => ['nullable', 'string', Rule::in($this->getCategories())],
            'description' => 'nullable|string|max:500',
            'duration' => 'nullable|integer|min:0|max:300',
        ]);

        $service->update($validated);

        return redirect()->route('admin.services.manage')
            ->with('success', 'Service updated successfully!');
    }

    /**
     * Archive the specified service.
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);

        // Archiving (soft delete) allows keeping history, so we don't need to block
        // if reservations exist.
        $service->delete();

        return redirect()->route('admin.services.manage')
            ->with('success', 'Service archived successfully!');
    }

    /**
     * Restore the specified archived service.
     */
    public function restore($id)
    {
        $service = Service::onlyTrashed()->findOrFail($id);
        $service->restore();

        return redirect()->route('admin.services.manage', ['archived' => 1])
            ->with('success', 'Service restored successfully!');
    }
}
