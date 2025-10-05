<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountActivated;
use Illuminate\Support\Facades\Redirect;

class UserApprovalController extends Controller
{
    /**
     * Approve a pending user and activate the account.
     */
    public function approve(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $user->status = 'active';
        $user->save();

        // notify user
        Mail::to($user->email)->send(new AccountActivated($user));

        return Redirect::back()->with('status', 'user-activated');
    }
}
