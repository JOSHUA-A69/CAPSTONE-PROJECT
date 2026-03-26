<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginVerificationCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;

class LoginCodeController extends Controller
{
    /**
     * Display the login code verification form.
     */
    public function show(Request $request): View|RedirectResponse
    {
        // User must have a pending verification in session
        if (!$request->session()->has('login_code_user_id')) {
            return redirect()->route('login');
        }

        $userId = $request->session()->get('login_code_user_id');
        $user = User::find($userId);

        if (!$user) {
            $request->session()->forget('login_code_user_id');
            return redirect()->route('login');
        }

        return view('auth.verify-login-code', [
            'email' => $this->maskEmail($user->email),
        ]);
    }

    /**
     * Verify the login code entered by the user.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:4'],
        ]);

        if (!$request->session()->has('login_code_user_id')) {
            return redirect()->route('login')
                ->with('status', 'Session expired. Please login again.');
        }

        $userId = $request->session()->get('login_code_user_id');
        $user = User::find($userId);

        $verifyKey = $this->verifyThrottleKey($request, $userId);
        if (RateLimiter::tooManyAttempts($verifyKey, 5)) {
            $seconds = RateLimiter::availableIn($verifyKey);
            return back()->withErrors(['code' => 'Too many verification attempts. Try again in ' . ceil($seconds / 60) . ' minute(s).']);
        }

        if (!$user) {
            $request->session()->forget('login_code_user_id');
            return redirect()->route('login');
        }

        // Check if code is expired
        if ($user->login_code_expires_at && Carbon::parse($user->login_code_expires_at)->isPast()) {
            return back()->withErrors(['code' => 'The verification code has expired. Please request a new one.']);
        }

        // Verify the code
        if ($user->login_code !== $request->code) {
            RateLimiter::hit($verifyKey, 600);
            return back()->withErrors(['code' => 'The verification code you entered is not valid.']);
        }

        RateLimiter::clear($verifyKey);

        // Clear the login code
        $user->update([
            'login_code' => null,
            'login_code_expires_at' => null,
        ]);

        // Clear session
        $request->session()->forget('login_code_user_id');

        // Log the user in
        Auth::login($user, $request->session()->get('login_remember', false));
        $request->session()->forget('login_remember');
        $request->session()->regenerate();

        // Redirect to appropriate dashboard
        $routeName = RouteServiceProvider::routeNameForRole($user->role);
        return redirect()->route($routeName);
    }

    /**
     * Resend the verification code.
     */
    public function resend(Request $request): RedirectResponse
    {
        if (!$request->session()->has('login_code_user_id')) {
            return redirect()->route('login');
        }

        $userId = $request->session()->get('login_code_user_id');
        $user = User::find($userId);

        $resendKey = $this->resendThrottleKey($request, $userId);
        if (RateLimiter::tooManyAttempts($resendKey, 3)) {
            $seconds = RateLimiter::availableIn($resendKey);
            return back()->withErrors(['code' => 'Too many resend requests. Try again in ' . ceil($seconds / 60) . ' minute(s).']);
        }

        if (!$user) {
            $request->session()->forget('login_code_user_id');
            return redirect()->route('login');
        }

        // Generate new code
        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        RateLimiter::hit($resendKey, 600);
        
        $user->update([
            'login_code' => $code,
            'login_code_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Send email
        Mail::to($user->email)->send(new LoginVerificationCode($user, $code));

        return back()->with('status', 'A new verification code has been sent to your email.');
    }

    /**
     * Mask email for display (e.g., j***@example.com)
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        
        if (strlen($name) <= 2) {
            $masked = $name[0] . '***';
        } else {
            $masked = $name[0] . str_repeat('*', strlen($name) - 2) . substr($name, -1);
        }
        
        return $masked . '@' . $domain;
    }

    private function verifyThrottleKey(Request $request, int|string $userId): string
    {
        return 'login-code:verify:' . $userId . '|' . $request->ip();
    }

    private function resendThrottleKey(Request $request, int|string $userId): string
    {
        return 'login-code:resend:' . $userId . '|' . $request->ip();
    }
}
