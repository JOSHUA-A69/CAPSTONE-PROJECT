<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':staff']);
    }

    public function index()
    {
        // Only exclude soft-deleted if column exists
        if (Schema::hasColumn('organizations', 'deleted_at')) {
            $organizations = Organization::whereNull('deleted_at')
                ->orderBy('org_name')
                ->paginate(20);
        } else {
            $organizations = Organization::orderBy('org_name')
                ->paginate(20);
        }

        return view('staff.organizations.index', compact('organizations'));
    }

    public function archives()
    {
        // Check if soft deletes column exists
        if (!Schema::hasColumn('organizations', 'deleted_at')) {
            return redirect()->route('staff.organizations.index')
                ->with('info', 'Archive feature requires database migration. Please run: php artisan migrate');
        }

        $archivedOrganizations = Organization::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(20);
        return view('staff.organizations.archives', compact('archivedOrganizations'));
    }

    public function create()
    {
        $advisers = User::where('role', 'adviser')->orderBy('first_name')->get();
        return view('staff.organizations.create', compact('advisers'));
    }

    public function store(OrganizationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['custom_org_name']); // Clean up just in case it's still sent

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
        unset($data['custom_org_name']); // Clean up just in case it's still sent

        $organization->update($data);
        return Redirect::route('staff.organizations.index')->with('status', 'organization-updated');
    }

    public function destroy(Request $request, $org_id): RedirectResponse
    {
        $organization = Organization::findOrFail($org_id);
        $organization->delete(); // Soft delete
        return Redirect::back()->with('status', 'organization-archived');
    }

    public function restore($org_id): RedirectResponse
    {
        $organization = Organization::onlyTrashed()->findOrFail($org_id);
        $organization->restore();
        return Redirect::route('staff.organizations.archives')->with('status', 'organization-restored');
    }

    public function forceDestroy($org_id): RedirectResponse
    {
        $organization = Organization::onlyTrashed()->findOrFail($org_id);
        $organization->forceDelete(); // Permanent delete
        return Redirect::back()->with('status', 'organization-permanently-deleted');
    }
}
