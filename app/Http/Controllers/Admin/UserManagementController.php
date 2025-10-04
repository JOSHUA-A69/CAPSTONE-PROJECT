<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

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
}
