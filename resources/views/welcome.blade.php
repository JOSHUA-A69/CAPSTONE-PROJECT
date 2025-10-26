<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ config('app.name', 'eReligiousServices') }}</title>

    <!-- Favicon / site logo -->
    <link rel="icon" href="/images/ers-logo.png" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="antialiased bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18]">
        <header class="border-b bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <img src="/images/ers-logo.png" alt="eReligiousServices logo" class="w-12 h-12 object-contain" />
                        <div class="hidden sm:block">
                            <div class="font-semibold text-lg">eReligiousServices</div>
                            <div class="text-xs text-gray-500">Center for Religious Education and Mission</div>
                        </div>
                    </a>

                    <div class="flex items-center gap-3">
                        @guest
                            <!-- Sign In Button -->
                            <a href="{{ route('login') }}"
                               class="group relative inline-flex items-center justify-center gap-2 px-5 py-3 text-base font-extrabold text-gray-900 bg-white border-3 border-gray-900 rounded-lg hover:bg-gray-900 hover:text-white transition-all duration-200 shadow-md hover:shadow-xl">
                                <svg class="w-5 h-5 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                <span class="text-base font-extrabold tracking-wide">SIGN IN</span>
                            </a>

                            <!-- Register Button -->
                            <a href="{{ route('register') }}"
                               class="group relative inline-flex items-center justify-center gap-2 px-5 py-3 text-base font-extrabold text-white bg-[#2ecc71] border-3 border-[#2ecc71] rounded-lg hover:bg-[#27ae60] hover:border-[#27ae60] transition-all duration-200 shadow-md hover:shadow-xl hover:scale-105">
                                <svg class="w-5 h-5 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span class="text-base font-extrabold tracking-wide">REGISTER</span>
                                <span class="absolute -top-1.5 -right-1.5 flex h-5 w-5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-300 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-5 w-5 bg-yellow-300 shadow-lg"></span>
                                </span>
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center justify-center gap-2 px-5 py-3 text-base font-extrabold text-gray-900 bg-white border-3 border-gray-900 rounded-lg hover:bg-gray-900 hover:text-white transition-all duration-200 shadow-md hover:shadow-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="text-base font-extrabold tracking-wide">DASHBOARD</span>
                            </a>
                        @endguest
                    </div>
                </div>
            </div>
        </header>

        <main class="mt-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="py-12 text-center">
                    <h1 class="er-hero-title text-hero-4xl mx-auto">
                        <span class="text-[#1b1b18]">Faith.</span>
                        <span class="text-[var(--er-green)]"> Community.</span>
                        <span class="text-[#1b1b18]"> Service.</span>
                    </h1>

                    <p class="mt-6 text-[#706f6c] dark:text-[#A1A09A] max-w-prose mx-auto text-lg">
                        Welcome to eReligiousServices your gateway to spiritual growth and community engagement at Holy Name University's Center for Religious Education and Mission.
                    </p>

                    <p class="mt-4 text-[#706f6c] dark:text-[#A1A09A] max-w-prose mx-auto">
                        Experience seamless booking for liturgical services, retreats, and religious events. Join our vibrant faith community and discover opportunities for spiritual formation and service.
                    </p>

                    <div class="mt-8 flex gap-4 flex-wrap justify-center">
                        <a href="#" class="group er-cta-primary inline-flex items-center gap-2 px-6 py-3 transition-all duration-300 hover:scale-105 transform">
                            <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            View Calendar
                        </a>

                        <a href="#" class="group er-cta-ghost inline-flex items-center gap-2 px-6 py-3 transition-all duration-300 hover:scale-105 transform">
                            <svg class="w-5 h-5 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Organizations
                        </a>
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

        @include('layouts.footer')
    </body>
</html>
