<x-guest-layout>
    <div class="p-2 lg:p-4">
        <div class="flex flex-col items-center mb-6">
            <img src="/images/ers-logo.png" alt="eReligiousServices" class="w-20 h-20 mb-4 object-contain" />
            <h1 class="text-3xl font-bold text-[#2ecc71]">Sign In</h1>
        </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-[#1b1b18]">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-base" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-[#1b1b18]">Password</label>
                        <input id="password" name="password" type="password" required class="mt-2 w-full rounded-md border border-[#e3e3e0] px-4 py-3 text-base" />
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

    @push('scripts')
    <script>
        // Refresh CSRF token every 30 minutes to prevent 419 errors
        setInterval(function() {
            fetch('/refresh-csrf')
                .then(response => response.json())
                .then(data => {
                    document.querySelector('input[name="_token"]').value = data.token;
                })
                .catch(error => console.log('CSRF refresh failed:', error));
        }, 30 * 60 * 1000); // 30 minutes

        // Handle form submission and catch 419 errors
        document.querySelector('form').addEventListener('submit', function(e) {
            const form = this;

            // If form submission results in 419, refresh and retry
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                if (response.status === 419) {
                    // Token expired, refresh and show message
                    e.preventDefault();
                    fetch('/refresh-csrf')
                        .then(res => res.json())
                        .then(data => {
                            document.querySelector('input[name="_token"]').value = data.token;
                            alert('Session expired. Please click Sign In again.');
                        });
                }
            }).catch(error => {
                // Let the normal form submission happen
            });
        });
    </script>
    @endpush
</x-guest-layout>
