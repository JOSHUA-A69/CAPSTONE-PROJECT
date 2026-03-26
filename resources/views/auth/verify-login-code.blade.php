<x-guest-layout>
    <div class="p-4 lg:p-6">
        <!-- Header -->
        <div class="flex flex-col items-center mb-8">
            <img src="/images/ers-logo.png" alt="eReligiousServices" class="w-20 h-20 mb-4 object-contain" />
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Verify Your Identity</h1>
            <p class="mt-2 text-sm text-muted text-center max-w-sm">
                We've sent a 4-digit verification code to<br>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $email }}</span>
            </p>
        </div>

        <!-- Status Messages -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Verification Form -->
        <form method="POST" action="{{ route('login.code.verify') }}" class="space-y-6" id="verification-form">
            @csrf

            <!-- Code Input -->
            <div>
                <x-input-label for="code" :value="__('Enter Verification Code')" class="text-center mb-4" />
                
                <div class="flex justify-center gap-3" id="code-inputs">
                    <input type="text" 
                           maxlength="1" 
                           class="code-input w-14 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:bg-gray-800 dark:border-gray-600 dark:text-white transition-all duration-200"
                           data-index="0"
                           inputmode="numeric"
                           pattern="[0-9]"
                           autocomplete="off" />
                    <input type="text" 
                           maxlength="1" 
                           class="code-input w-14 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:bg-gray-800 dark:border-gray-600 dark:text-white transition-all duration-200"
                           data-index="1"
                           inputmode="numeric"
                           pattern="[0-9]"
                           autocomplete="off" />
                    <input type="text" 
                           maxlength="1" 
                           class="code-input w-14 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:bg-gray-800 dark:border-gray-600 dark:text-white transition-all duration-200"
                           data-index="2"
                           inputmode="numeric"
                           pattern="[0-9]"
                           autocomplete="off" />
                    <input type="text" 
                           maxlength="1" 
                           class="code-input w-14 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:bg-gray-800 dark:border-gray-600 dark:text-white transition-all duration-200"
                           data-index="3"
                           inputmode="numeric"
                           pattern="[0-9]"
                           autocomplete="off" />
                </div>

                <!-- Hidden field for form submission -->
                <input type="hidden" name="code" id="code-hidden" value="" />
                
                <x-input-error :messages="$errors->get('code')" class="mt-3 text-center" />
            </div>

            <!-- Timer & Info -->
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Code expires in <span id="timer" class="font-semibold text-indigo-600 dark:text-indigo-400">10:00</span></span>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <x-primary-button class="w-full justify-center btn-lg" id="verify-btn" disabled>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('Verify & Sign In') }}
                </x-primary-button>
            </div>
        </form>

        <!-- Resend Code -->
        <div class="mt-6 text-center">
            <p class="text-sm text-muted mb-2">Didn't receive the code?</p>
            <form method="POST" action="{{ route('login.code.resend') }}" class="inline">
                @csrf
                <button type="submit" 
                        class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded">
                    Resend Code
                </button>
            </form>
        </div>

        <!-- Back to Login -->
        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" 
               class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Sign In
            </a>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.code-input');
            const hiddenInput = document.getElementById('code-hidden');
            const verifyBtn = document.getElementById('verify-btn');
            const form = document.getElementById('verification-form');

            // Focus first input
            inputs[0].focus();

            // Handle input
            inputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    // Only allow numbers
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    if (this.value.length === 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                    
                    updateHiddenInput();
                });

                input.addEventListener('keydown', function(e) {
                    // Handle backspace
                    if (e.key === 'Backspace' && this.value === '' && index > 0) {
                        inputs[index - 1].focus();
                        inputs[index - 1].value = '';
                        updateHiddenInput();
                    }
                    
                    // Handle arrow keys
                    if (e.key === 'ArrowLeft' && index > 0) {
                        inputs[index - 1].focus();
                    }
                    if (e.key === 'ArrowRight' && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                });

                // Handle paste
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 4);
                    
                    pastedData.split('').forEach((char, i) => {
                        if (inputs[i]) {
                            inputs[i].value = char;
                        }
                    });
                    
                    if (pastedData.length > 0) {
                        inputs[Math.min(pastedData.length - 1, inputs.length - 1)].focus();
                    }
                    
                    updateHiddenInput();
                });

                // Select all on focus
                input.addEventListener('focus', function() {
                    this.select();
                });
            });

            function updateHiddenInput() {
                const code = Array.from(inputs).map(i => i.value).join('');
                hiddenInput.value = code;
                
                // Enable/disable button
                if (code.length === 4) {
                    verifyBtn.disabled = false;
                    verifyBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    verifyBtn.disabled = true;
                    verifyBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }

            // Timer countdown (10 minutes)
            let timeLeft = 10 * 60;
            const timerElement = document.getElementById('timer');
            
            const countdown = setInterval(function() {
                timeLeft--;
                
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                
                timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 60) {
                    timerElement.classList.add('text-red-500');
                    timerElement.classList.remove('text-indigo-600', 'dark:text-indigo-400');
                }
                
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    timerElement.textContent = 'Expired';
                }
            }, 1000);
        });
    </script>
    @endpush
</x-guest-layout>
