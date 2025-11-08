<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ config('app.name', 'eReligiousServices') }}</title>

    <!-- Favicon / site logo -->
    <link rel="icon" href="/images/ers-logo.png" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            <script>window.ANIMATIONS_MODE='full';</script>
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        
        <style>
            .hero-gradient {
                background: linear-gradient(135deg, #10b981 0%, #047857 100%);
            }
            
            .card-hover {
                transition: all 0.3s ease;
            }
            
            .card-hover:hover {
                transform: translateY(-8px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            }
            
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            
            .float-animation {
                animation: float 6s ease-in-out infinite;
            }
            
            /* Removed gradient-text; using solid colors instead of text transparency */
            /* Header navigation styling to match footer theme */
            .site-header {
                /* Green brand bar matching footer */
                background-color: #2ecc71;
            }
            .site-header .nav-link {
                position: relative;
                font-weight: 600;
                letter-spacing: .5px;
                transition: color .25s ease, transform .25s ease;
            }
            .site-header .nav-link:after {
                content: '';
                position: absolute;
                left: 0; bottom: -4px;
                width: 0; height: 2px;
                background: rgba(255,255,255,.85);
                border-radius: 2px;
                transition: width .3s ease;
            }
            .site-header .nav-link:hover:after,
            .site-header .nav-link:focus:after {
                width: 100%;
            }
            .site-header .nav-link:hover,
            .site-header .nav-link:focus {
                transform: translateY(-2px);
            }
            @media (max-width: 768px) {
                .site-header nav {
                    display: none; /* keep header clean on small screens */
                }
            }
        </style>
    </head>
    <body class="antialiased bg-gradient-to-br from-gray-50 via-white to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 text-[#1b1b18] full-animations">
        <!-- Header -->
    <header class="site-header text-white dark:text-white/90 border-b border-[#27c165] shadow-lg">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-green-600 to-emerald-600 rounded-full blur-md opacity-40 group-hover:opacity-60 transition-opacity"></div>
                            <img src="/images/ers-logo.png" alt="eReligiousServices logo" class="relative w-14 h-14 object-contain transform group-hover:scale-110 transition-transform duration-300 drop-shadow" />
                        </div>
                        <div class="hidden sm:block">
                            <div class="font-bold text-xl text-white tracking-wide">eReligiousServices</div>
                            <div class="text-xs text-white/90">Center for Religious Education and Mission</div>
                        </div>
                    </a>

                    <!-- Primary Navigation removed as requested -->

                    <div class="flex items-center gap-3">
                        @guest
                            <!-- Sign In Button -->
                                     <a href="{{ route('login') }}"
                                         class="group relative inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold text-emerald-700 bg-white border-2 border-emerald-300 rounded-xl hover:border-emerald-500 hover:text-emerald-600 transition-all duration-300 shadow-sm hover:shadow-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                <span class="tracking-wide">SIGN IN</span>
                            </a>

                            <!-- Register Button -->
                                     <a href="{{ route('register') }}"
                                         class="group relative inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 overflow-hidden">
                                <svg class="w-5 h-5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span class="tracking-wide relative z-10">REGISTER</span>
                                <span class="absolute -top-1 -right-1 flex h-6 w-6">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-300 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-6 w-6 bg-yellow-300 shadow-lg items-center justify-center text-xs font-bold text-green-900">!</span>
                                </span>
                            </a>
                        @else
                                     <a href="{{ route('dashboard') }}"
                                         class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="tracking-wide">DASHBOARD</span>
                            </a>
                        @endguest
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="relative overflow-hidden">
            <!-- Background Decoration -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute top-20 right-0 w-96 h-96 bg-green-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-emerald-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse" style="animation-delay: 2s;"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
                <!-- Hero Content -->
                <div class="text-center mb-16">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 dark:bg-green-900/30 rounded-full text-sm font-semibold text-green-700 dark:text-green-300 mb-8">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        Welcome to HNU Center for Religious Education
                    </div>
                    
                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight mb-8">
                        <span class="block text-gray-900 dark:text-white">Faith.</span>
                        <span class="block text-emerald-600 dark:text-emerald-400">Community.</span>
                        <span class="block text-gray-900 dark:text-white">Service.</span>
                    </h1>

                    <p class="mt-8 text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
                        Your gateway to spiritual growth and community engagement at <span class="font-semibold text-green-600 dark:text-green-400">Holy Name University</span>
                    </p>

                    <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                        Experience seamless booking for liturgical services, retreats, and religious events. Join our vibrant faith community today.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="mt-12 flex gap-6 flex-wrap justify-center">
                                <a href="#home-calendar" 
                                    class="group relative inline-flex items-center gap-3 px-8 py-4 text-lg font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-2xl transition-all duration-300 shadow-2xl hover:shadow-emerald-500/50 hover:scale-105 transform">
                            <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>View Calendar</span>
                            <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>

                                <a href="#services" 
                                    class="group inline-flex items-center gap-3 px-8 py-4 text-lg font-bold text-emerald-700 dark:text-emerald-300 bg-white dark:bg-gray-800 border-2 border-emerald-300 dark:border-emerald-700 rounded-2xl hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:border-emerald-500 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform">
                            <svg class="w-6 h-6 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Explore Services</span>
                        </a>
                    </div>
                </div>

                <!-- Stats Section -->
                <div class="mt-24 grid grid-cols-1 sm:grid-cols-3 gap-8">
                    <div class="card-hover relative bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl border border-gray-200 dark:border-gray-700">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-400 rounded-bl-full opacity-10"></div>
                        <div class="relative">
                            <div class="text-5xl font-extrabold text-emerald-600 dark:text-emerald-400 mb-3">6+</div>
                            <div class="text-gray-600 dark:text-gray-300 font-semibold">Student Organizations</div>
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">Active faith communities</div>
                        </div>
                    </div>

                    <div class="card-hover relative bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl border border-gray-200 dark:border-gray-700">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-400 to-teal-400 rounded-bl-full opacity-10"></div>
                        <div class="relative">
                            <div class="text-5xl font-extrabold text-emerald-600 dark:text-emerald-400 mb-3">Daily</div>
                            <div class="text-gray-600 dark:text-gray-300 font-semibold">Noon Mass</div>
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">Join us for worship</div>
                        </div>
                    </div>

                    <div class="card-hover relative bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl border border-gray-200 dark:border-gray-700">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-400 to-cyan-400 rounded-bl-full opacity-10"></div>
                        <div class="relative">
                            <div class="text-5xl font-extrabold text-cyan-600 dark:text-cyan-400 mb-3">24/7</div>
                            <div class="text-gray-600 dark:text-gray-300 font-semibold">Online Booking</div>
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">Reserve services anytime</div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Reservations Calendar Section (just before Explore Services) -->
                @include('partials.home-calendar')

                <!-- Featured Services Section -->
                <div id="services" class="mt-8">
                    <div class="text-center mb-8">
                        <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-4">Our Services</h2>
                        <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">Discover the various ways we serve our community</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <!-- Mass Reservations -->
                        <div class="card-hover group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl border border-gray-200 dark:border-gray-700 hover:border-green-500 dark:hover:border-green-400">
                            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Mass Reservations</h3>
                            <p class="text-gray-600 dark:text-gray-300">Book your spot for daily mass, special celebrations, and liturgical events</p>
                        </div>

                        <!-- Retreats -->
                        <div class="card-hover group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl border border-gray-200 dark:border-gray-700 hover:border-green-500 dark:hover:border-green-400">
                            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-teal-500 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Spiritual Retreats</h3>
                            <p class="text-gray-600 dark:text-gray-300">Join immersive retreat experiences for spiritual renewal and growth</p>
                        </div>

                        <!-- Community Events -->
                        <div class="card-hover group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Community Events</h3>
                            <p class="text-gray-600 dark:text-gray-300">Participate in faith-based activities and community gatherings</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        @include('layouts.footer')
    </body>
</html>
