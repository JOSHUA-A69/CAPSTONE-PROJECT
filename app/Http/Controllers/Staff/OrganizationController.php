<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':staff']);
    }

    public function index()
    {
        $organizations = Organization::orderBy('org_name')->paginate(20);
        return view('staff.organizations.index', compact('organizations'));
    }

    public function create()
    {
        $advisers = User::where('role', 'adviser')->orderBy('first_name')->get();
        return view('staff.organizations.create', compact('advisers'));
    }

    public function store(OrganizationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Handle custom organization name
        if (isset($data['org_name']) && $data['org_name'] === 'Other' && !empty($data['custom_org_name'])) {
            $data['org_name'] = $data['custom_org_name'];
        }
        unset($data['custom_org_name']);

        Organization::create($data);
        return Redirect::route('staff.organizations.index')->with('status', 'organization-created');
    }

    public function edit($org_id)
    {
        $organization = Organization::findOrFail($org_id);
        $advisers = User::where('role', 'adviser')->orderBy('first_name')->get();
        return view('staff.organizations.edit', compact('organization', 'advisers'));
    }

    public function update(OrganizationRequest $request, $org_id): RedirectResponse
    {
        $organization = Organization::findOrFail($org_id);
        $data = $request->validated();

        // Handle custom organization name
        if (isset($data['org_name']) && $data['org_name'] === 'Other' && !empty($data['custom_org_name'])) {
            $data['org_name'] = $data['custom_org_name'];
        }
        unset($data['custom_org_name']);

        $organization->update($data);
        return Redirect::route('staff.organizations.index')->with('status', 'organization-updated');
    }

    public function destroy(Request $request, $org_id): RedirectResponse
    {
        $organization = Organization::findOrFail($org_id);
        $organization->delete();
        return Redirect::back()->with('status', 'organization-deleted');
    }
}
