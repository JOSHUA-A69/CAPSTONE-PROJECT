<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Providers\RouteServiceProvider;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

    // Role-based redirect: prefer the role landing route but allow an
    // 'intended' redirect only if it points into the same role area.
    $routeName = RouteServiceProvider::routeNameForRole(Auth::user()->role);
    $rolePath = RouteServiceProvider::redirectTo(Auth::user()->role);

        // Get intended URL from the session (if any)
        $intended = $request->session()->pull('url.intended');

        if ($intended) {
            // Normalize intended path (strip host)
            $intendedPath = parse_url($intended, PHP_URL_PATH) ?: '/';

            // Allow the intended redirect only when it belongs to the same
            // role-area (e.g. /admin/* for admin) to avoid sending users
            // back to unrelated pages.
            if (str_starts_with($intendedPath, $rolePath)) {
                return redirect($intended);
            }
        }

        // Default: send user to their role landing route
        return redirect()->route($routeName);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
