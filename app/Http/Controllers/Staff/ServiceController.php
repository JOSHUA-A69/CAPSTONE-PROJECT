<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':staff']);
    }

    public function index()
    {
        $query = Service::query();

        $search = request('q');
        $category = request('category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('service_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('service_category', $category);
        }

        $services = $query->orderBy('service_name')->paginate(20)->appends(request()->only('q', 'category'));

        $categories = [
            'Liturgical Celebrations',
            'Retreats and Recollections',
            'Prayer Services',
            'Outreach Activities',
            'Daily Noon Mass',
            'Catechetical Activities',
        ];

        return view('staff.services.index', compact('services', 'categories', 'search', 'category'));
    }

    public function create()
    {
        $categories = [
            'Liturgical Celebrations',
            'Retreats and Recollections',
            'Prayer Services',
            'Outreach Activities',
            'Daily Noon Mass',
            'Catechetical Activities',
        ];
        return view('staff.services.create', compact('categories'));
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        Service::create($request->validated());
        return Redirect::route('staff.services.index')->with('status', 'service-created');
    }

    public function edit($service_id)
    {
        $service = Service::findOrFail($service_id);
        $categories = [
            'Liturgical Celebrations',
            'Retreats and Recollections',
            'Prayer Services',
            'Outreach Activities',
            'Daily Noon Mass',
            'Catechetical Activities',
        ];
        return view('staff.services.edit', compact('service', 'categories'));
    }

    public function update(ServiceRequest $request, $service_id): RedirectResponse
    {
        $service = Service::findOrFail($service_id);
        $service->update($request->validated());
        return Redirect::route('staff.services.index')->with('status', 'service-updated');
    }

    public function destroy($service_id): RedirectResponse
    {
        $service = Service::findOrFail($service_id);
        $service->delete();
        return Redirect::back()->with('status', 'service-deleted');
    }
}
