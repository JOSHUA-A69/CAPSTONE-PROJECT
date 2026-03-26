<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceManagementController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('service_name', 'asc')->get();
        $categories = $this->getCategories();
        // Staff can edit only; view should hide Add/Delete
        return view('staff.services.manage', compact('services', 'categories'));
    }

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

        return redirect()->route('staff.services.manage')
            ->with('success', 'Service updated successfully!');
    }

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
}
