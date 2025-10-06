<x-guest-layout>
    <div class="w-full min-h-screen flex items-center justify-center p-6">
        <div class="max-w-[420px] w-full bg-white dark:bg-[#161615] rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.06)] border border-[#e3e3e0] dark:border-[#3E3E3A]">
            <div class="p-8 lg:p-10">
                <div class="flex flex-col items-center mb-6">
                    <img src="/images/ers-logo.png" alt="eReligiousServices" class="w-16 h-16 rounded-full mb-4 object-contain" />
                    <h1 class="text-3xl font-bold text-[#2ecc71]">Create Account</h1>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-[#1b1b18]">First name</label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required autofocus class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-sm" />
                            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                        </div>
                        <div>
                            <label for="middle_name" class="block text-sm font-medium text-[#1b1b18]">Middle name (optional)</label>
                            <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name') }}" class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-sm" />
                            <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-[#1b1b18]">Last name</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-sm" />
                            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-[#1b1b18]">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-sm" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-[#1b1b18]">Phone (optional)</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}" class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-sm" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-[#1b1b18]">Password</label>
                        <input id="password" name="password" type="password" required class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-sm" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-[#1b1b18]">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-sm" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    @if(config('registration.allow_role_selection'))
                        <div class="mb-4">
                            <label for="role" class="block text-sm font-medium text-[#1b1b18]">User Role</label>
                            <select id="role" name="role" class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-sm">
                                <option value="">Select your role</option>
                                <option value="admin" {{ old('role')=='admin' ? 'selected' : '' }}>Admin</option>
                                <option value="staff" {{ old('role')=='staff' ? 'selected' : '' }}>Staff</option>
                                <option value="adviser" {{ old('role')=='adviser' ? 'selected' : '' }}>Adviser</option>
                                <option value="requestor" {{ old('role')=='requestor' ? 'selected' : '' }}>Requestor</option>
                                <option value="priest" {{ old('role')=='priest' ? 'selected' : '' }}>Priest</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <label for="elevated_code" class="block text-sm font-medium text-[#1b1b18]">Elevated registration code (required for high roles)</label>
                            <input id="elevated_code" name="elevated_code" type="text" value="{{ old('elevated_code') }}" class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-sm" />
                            <x-input-error :messages="$errors->get('elevated_code')" class="mt-2" />
                        </div>
                    @else
                        <!-- Self-registration is only for Requestors. Higher roles must be created by an admin. -->
                        <input type="hidden" name="role" value="requestor" />
                    @endif

                    <div class="mt-6">
                        <button type="submit" class="w-full bg-[#2ecc71] hover:bg-[#28c76a] text-white font-semibold py-3 rounded-full">Register</button>
                    </div>

                    <div class="mt-4 text-center text-sm text-[#706f6c]">
                        Already registered? <a href="{{ route('login') }}" class="text-[#2ecc71] underline">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
