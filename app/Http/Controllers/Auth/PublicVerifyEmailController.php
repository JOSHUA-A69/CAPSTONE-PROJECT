<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Providers\RouteServiceProvider;

class PublicVerifyEmailController extends Controller
{
    /**
     * Handle email verification via signed URL without requiring prior login.
     */
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        // Validate the temporary signed URL first
        if (! URL::hasValidSignature($request)) {
            abort(403, 'Invalid or expired verification link.');
        }

        $user = User::findOrFail($id);

        // Ensure the hash matches the user's email as Laravel expects
        if (! hash_equals($hash, sha1($user->email))) {
            abort(403, 'Invalid verification hash.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // Log the user in and redirect to their role dashboard
        Auth::login($user);
        return redirect()->route(RouteServiceProvider::routeNameForRole($user->role));
    }
}
