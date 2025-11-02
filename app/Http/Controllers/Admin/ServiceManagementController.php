<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceManagementController extends Controller
{
    /**
     * Display a listing of all services.
     */
    public function index()
    {
        \Log::info('ServiceManagementController index method called');
        $services = Service::orderBy('service_name', 'asc')->get();
        \Log::info('Services count: ' . $services->count());
        return view('admin.services.manage', compact('services'));
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:255|unique:services,service_name',
            'description' => 'nullable|string|max:500',
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
            'description' => 'nullable|string|max:500',
        ]);

        $service->update($validated);

        return redirect()->route('admin.services.manage')
            ->with('success', 'Service updated successfully!');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        
        // Check if service is being used in any reservations
        $reservationCount = DB::table('reservations')
            ->where('service_id', $id)
            ->count();

        if ($reservationCount > 0) {
            return redirect()->route('admin.services.manage')
                ->with('error', 'Cannot delete this service. It is currently being used in ' . $reservationCount . ' reservation(s).');
        }

        $service->delete();

        return redirect()->route('admin.services.manage')
            ->with('success', 'Service deleted successfully!');
    }
}
