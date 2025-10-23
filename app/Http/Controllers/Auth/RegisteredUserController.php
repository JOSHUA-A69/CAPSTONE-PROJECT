<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Providers\RouteServiceProvider;
use App\Models\UserRole;
use Illuminate\Support\Facades\Route;

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
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,staff,adviser,requestor,priest'],
        ];
        // If role selection is disabled, force role to requestor regardless of input
        if (!config('registration.allow_role_selection')) {
            $request->merge(['role' => 'requestor']);
        }

        // If the chosen role is elevated, require a valid elevated code
        $elevatedRoles = config('registration.elevated_roles', []);
        $elevatedCodes = config('registration.elevated_codes', []);
        if (in_array($request->input('role'), $elevatedRoles, true)) {
            $rules['elevated_code'] = ['required', function ($attribute, $value, $fail) use ($elevatedCodes) {
                if (empty($elevatedCodes) || !in_array(trim((string)$value), $elevatedCodes, true)) {
                    $fail('The provided elevated registration code is invalid.');
                }
            }];
        }

        $validated = $request->validate($rules);

        // Accept the chosen role from the form. Accounts will be created with
        // a 'pending' status and must verify email to become active.
        $chosenRole = $validated['role'] ?? 'requestor';

        $user = User::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $chosenRole,
            'status' => 'pending',
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        // Explicitly send the email verification notification so UI signups
        // always receive a verification email immediately (avoids relying
        // solely on event listener mappings or queue workers).
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::error('Failed to send verification email on registration: '.$e->getMessage());
        }

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

        // After registration, send the user to their role landing route if it exists,
        // or fall back to the role path or application HOME. This mirrors login behavior
        // and avoids failures when a named route is missing.
        $routeName = RouteServiceProvider::routeNameForRole($user->role);
        $rolePath = RouteServiceProvider::redirectTo($user->role);

        if (Route::has($routeName)) {
            return redirect()->route($routeName);
        }

        $fallback = $rolePath ?: RouteServiceProvider::HOME;
        return redirect($fallback);
    }
}
