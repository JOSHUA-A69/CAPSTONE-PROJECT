<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Providers\RouteServiceProvider;
use App\Models\UserRole;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Ensure the normalized user_role_id is set (defaults to 'requestor')
        if (empty($user->user_role_id)) {
            $roleName = $user->role ?? 'requestor';
            $userRole = UserRole::where('role_name', $roleName)->first();
            if ($userRole) {
                $user->user_role_id = $userRole->user_role_id;
                $user->save();
            }
        }

        // Redirect to role-specific landing (fallback to generic dashboard)
        $rolePath = RouteServiceProvider::redirectTo($user->role ?? ($user->userRole->role_name ?? null));
        if ($rolePath) {
            return redirect($rolePath);
        }

        return redirect(route('dashboard', absolute: false));
    }
}
