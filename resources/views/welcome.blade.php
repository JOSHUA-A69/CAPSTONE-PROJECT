<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ config('app.name', 'eReligiousServices') }}</title>

        <!-- Favicon / site logo -->
        <link rel="icon" href="/images/ers-logo.svg" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="antialiased bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18]">
        <header class="border-b bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <img src="/images/ers-logo.svg" alt="eReligiousServices logo" class="w-12 h-12 object-contain" />
                        <div class="hidden sm:block">
                            <div class="font-semibold text-lg">eReligiousServices</div>
                            <div class="text-xs text-gray-500">Center for Religious Education and Mission</div>
                        </div>
                    </a>

                    <div class="flex items-center gap-3">
                        @guest
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm border rounded-md">Sign In</a>
                            <a href="{{ route('register') }}" class="px-4 py-2 text-sm bg-[var(--er-green)] text-white rounded-md">Register</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm border rounded-md">Dashboard</a>
                        @endguest
                    </div>
                </div>
            </div>
        </header>

        <main class="mt-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <div class="py-12">
                        <h1 class="er-hero-title text-hero-4xl">
                            <span class="text-[#1b1b18]">Faith.</span>
                            <span class="text-[var(--er-green)]"> Community.</span>
                            <span class="text-[#1b1b18]"> Service.</span>
                        </h1>

                        <p class="mt-6 text-[#706f6c] dark:text-[#A1A09A] max-w-prose text-lg">
                            Welcome to eReligiousServices — your gateway to spiritual growth and community engagement at Holy Name University's Center for Religious Education and Mission.
                        </p>

                        <p class="mt-4 text-[#706f6c] dark:text-[#A1A09A] max-w-prose">
                            Experience seamless booking for liturgical services, retreats, and religious events. Join our vibrant faith community and discover opportunities for spiritual formation and service.
                        </p>

                        <div class="mt-8 flex gap-4 flex-wrap">
                            <a href="#" class="er-cta-primary"> 
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                View Calendar
                            </a>

                            <a href="#" class="er-cta-ghost">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M12 11v6M8 15h8"/></svg>
                                Organizations
                            </a>
                        </div>
                    </div>

                    <div class="py-12 flex items-center justify-center">
                        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow p-8">
                            <div class="h-64 bg-[url('/images/hero-placeholder.svg')] bg-cover bg-center rounded-lg flex items-center justify-center">
                                <div class="text-center bg-white/60 dark:bg-black/40 p-4 rounded">
                                    <div class="text-2xl font-semibold text-[var(--er-green)]">eReligiousServices</div>
                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">Experience seamless booking and community engagement</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="er-stat-card">
                        <div class="text-2xl font-extrabold text-[var(--er-green)]">6+</div>
                        <div class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">Student Organizations</div>
                    </div>

                    <div class="er-stat-card">
                        <div class="text-2xl font-extrabold text-[var(--er-green)]">Daily</div>
                        <div class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">Noon Mass</div>
                    </div>

                    <div class="er-stat-card">
                        <div class="text-2xl font-extrabold text-[var(--er-green)]">24/7</div>
                        <div class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">Online Booking</div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="mt-16 border-t bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 text-sm text-gray-500">
                © {{ date('Y') }} eReligiousServices — Center for Religious Education and Mission
            </div>
        </footer>
    </body>
</html>
