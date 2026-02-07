<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use App\Models\UserRole;
use App\Models\Organization;

class UserManagementController extends Controller
{
    /**
     * Archive (soft delete) a user by id. Only accessible to admins via middleware.
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $user = User::withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class)
            ->findOrFail($id);

        // Prevent admins from archiving their own account via this route.
        if ($request->user()->id === $user->id) {
            return Redirect::back()->with('error', 'Administrators cannot archive their own account from this panel.');
        }

        // Check if soft deletes column exists
        if (Schema::hasColumn('users', 'deleted_at')) {
            $user->delete(); // Soft delete
            // Redirect to archives page to show the newly archived user
            return Redirect::route('admin.users.archives')->with('status', 'user-archived');
        } else {
            return Redirect::back()->with('info', 'Archive feature requires database migration. Please run: php artisan migrate');
        }
    }

    /**
     * Display a listing of users for admin management.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');

        // Only show non-archived users if soft deletes column exists
        if (Schema::hasColumn('users', 'deleted_at')) {
            $query = User::query();
        } else {
            // Use withoutGlobalScope to bypass SoftDeletes when column doesn't exist
            $query = User::withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class);
        }

        // Apply search filter if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply role filter if provided
        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(25);

        // Preserve search and role query in pagination links
        $users->appends(['search' => $search, 'role' => $role]);

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    /**
     * Display archived users.
     */
    public function archives()
    {
        // Check if soft deletes column exists
        if (!Schema::hasColumn('users', 'deleted_at')) {
            return redirect()->route('admin.users.index')
                ->with('info', 'Archive feature requires database migration. Please run: php artisan migrate');
        }

        $archivedUsers = User::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(25);
        return view('admin.users.archives', compact('archivedUsers'));
    }

    /**
     * Restore an archived user.
     */
    public function restore($id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        return Redirect::route('admin.users.archives')->with('status', 'user-restored');
    }

    /**
     * Permanently delete a user.
     */
    public function forceDestroy($id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete(); // Permanent delete
        return Redirect::back()->with('status', 'user-permanently-deleted');
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $userRoles = UserRole::orderBy('role_name')->get();
        $organizations = Organization::orderBy('org_name')->get();
        return view('admin.users.create', compact('userRoles', 'organizations'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', Rule::in(['admin','staff','adviser','priest','requestor'])],
            'status' => ['nullable', Rule::in(['pending','active','suspended'])],
            'user_role_id' => ['nullable', 'integer', Rule::exists('user_roles', 'user_role_id')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'organization_ids' => ['nullable', 'array'],
            'organization_ids.*' => ['integer', Rule::exists('organizations', 'org_id')],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status'] = $data['status'] ?? 'active';

        // Extract organization_ids before creating user
        $organizationIds = $data['organization_ids'] ?? [];
        unset($data['organization_ids']);

        $user = User::create($data);

        // If the role is adviser and organizations were selected, assign them
        if ($data['role'] === 'adviser' && !empty($organizationIds)) {
            Organization::whereIn('org_id', $organizationIds)->update(['adviser_id' => $user->id]);
        }

        // Create role-specific success message
        $roleLabel = ucfirst($data['role']);
        $successMessage = "{$roleLabel} account for {$user->full_name} has been successfully created!";

        return Redirect::route('admin.users.index')
            ->with('status', 'user-created')
            ->with('success', $successMessage)
            ->with('user_role', $roleLabel);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class)
            ->findOrFail($id);

        $userRoles = UserRole::orderBy('role_name')->get();
        $organizations = Organization::orderBy('org_name')->get();

        // Get the IDs of organizations this user is adviser for
        $userOrganizationIds = $user->organizations->pluck('org_id')->toArray();

        return view('admin.users.edit', compact('user', 'userRoles', 'organizations', 'userOrganizationIds'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $user = User::withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class)
            ->findOrFail($id);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', Rule::in(['admin','staff','adviser','priest','requestor'])],
            'status' => ['nullable', Rule::in(['pending','active','suspended'])],
            'user_role_id' => ['nullable', 'integer', Rule::exists('user_roles', 'user_role_id')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'organization_ids' => ['nullable', 'array'],
            'organization_ids.*' => ['integer', Rule::exists('organizations', 'org_id')],
        ]);

        // Prevent an admin from demoting themselves out of the admin role here
        if ($request->user()->id === $user->id && isset($data['role']) && $data['role'] !== 'admin') {
            return Redirect::back()->with('error', 'You cannot change your own administrator role.');
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Extract organization_ids before updating user
        $organizationIds = $data['organization_ids'] ?? [];
        unset($data['organization_ids']);

        $user->update($data);

        // Handle organization assignments for advisers
        if ($data['role'] === 'adviser') {
            // First, remove this adviser from all organizations they were previously assigned to
            Organization::where('adviser_id', $user->id)->update(['adviser_id' => null]);

            // Then assign to selected organizations
            if (!empty($organizationIds)) {
                Organization::whereIn('org_id', $organizationIds)->update(['adviser_id' => $user->id]);
            }
        } else {
            // If role changed from adviser to something else, remove from all organizations
            Organization::where('adviser_id', $user->id)->update(['adviser_id' => null]);
        }

        // Redirect back to index page with success message
        return Redirect::route('admin.users.index')->with('status', 'user-updated');
    }
}
