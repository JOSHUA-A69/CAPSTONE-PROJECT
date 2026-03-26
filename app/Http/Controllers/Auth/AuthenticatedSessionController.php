<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\LoginVerificationCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Carbon\Carbon;

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

        // Get the authenticated user
        /** @var User $user */
        $user = Auth::user();
        
        // Log out the user temporarily (we'll log them back in after verification)
        Auth::logout();

        // Generate 4-digit code
        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Save code to user
        $user->update([
            'login_code' => $code,
            'login_code_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Send verification email
        Mail::to($user->email)->send(new LoginVerificationCode($user, $code));

        // Store user ID and remember preference in session
        $request->session()->put('login_code_user_id', $user->id);
        $request->session()->put('login_remember', $request->boolean('remember'));

        // Redirect to verification page
        return redirect()->route('login.code.show');
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
