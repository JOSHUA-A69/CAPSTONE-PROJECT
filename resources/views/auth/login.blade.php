<x-guest-layout>
    <div class="w-full min-h-screen flex items-center justify-center p-6">
        <div class="max-w-[420px] w-full bg-white dark:bg-[#161615] rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.06)] border border-[#e3e3e0] dark:border-[#3E3E3A]">
            <div class="p-8 lg:p-10">
                <div class="flex flex-col items-center mb-6">
                    <img src="/favicon.ico" alt="eReligiousServices" class="w-16 h-16 rounded-full mb-4" />
                    <h1 class="text-3xl font-bold text-[#2ecc71]">Sign In</h1>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-[#1b1b18]">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-sm" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-[#1b1b18]">Password</label>
                        <input id="password" name="password" type="password" required class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-sm" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <label for="role" class="block text-sm font-medium text-[#1b1b18]">User Type</label>
                        <select id="role" name="role" class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-sm" required>
                            <option value="">Select your user type</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="adviser" {{ old('role') === 'adviser' ? 'selected' : '' }}>Adviser</option>
                            <option value="requestor" {{ old('role') === 'requestor' ? 'selected' : '' }}>Requestor</option>
                            <option value="priest" {{ old('role') === 'priest' ? 'selected' : '' }}>Priest</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600" />
                            <span class="ms-2 text-sm text-[#706f6c]">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-[#2ecc71] underline">Forgot password?</a>
                        @endif
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="w-full bg-[#2ecc71] hover:bg-[#28c76a] text-white font-semibold py-3 rounded-full">Sign In</button>
                    </div>

                    <div class="mt-4 text-center text-sm text-[#706f6c]">
                        Don't have an account? <a href="{{ route('register') }}" class="text-[#2ecc71] underline">Create Account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
