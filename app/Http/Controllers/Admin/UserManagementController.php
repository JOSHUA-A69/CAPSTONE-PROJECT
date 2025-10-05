<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\UserRole;

class UserManagementController extends Controller
{
    /**
     * Delete a user by id. Only accessible to admins via middleware.
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Prevent admins from deleting their own account via this route.
        if ($request->user()->id === $user->id) {
            return Redirect::back()->with('error', 'Administrators cannot delete their own account from this panel.');
        }

        $user->delete();

        return Redirect::back()->with('status', 'user-deleted');
    }

    /**
     * Display a listing of users for admin management.
     */
    public function index(Request $request)
    {
        $users = User::orderBy('created_at', 'desc')->paginate(25);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $userRoles = UserRole::orderBy('role_name')->get();
        return view('admin.users.create', compact('userRoles'));
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
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status'] = $data['status'] ?? 'active';

        User::create($data);

        return Redirect::route('admin.users.index')->with('status', 'user-created');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        $userRoles = UserRole::orderBy('role_name')->get();
        return view('admin.users.edit', compact('user', 'userRoles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

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

        $user->update($data);

        return Redirect::route('admin.users.index')->with('status', 'user-updated');
    }
}
