<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes" />
        <title>{{ config('app.name', 'eReligiousServices') }}</title>

    <!-- Favicon / site logo -->
    <link rel="icon" href="/images/ers-logo.png" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            <script>window.ANIMATIONS_MODE='full';</script>
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            /* Performance optimizations */
            * {
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            html {
                scroll-behavior: smooth;
                overflow-x: hidden;
            }

            body {
                overflow-x: hidden;
                -webkit-overflow-scrolling: touch;
            }

            /* GPU acceleration for better performance */
            .gpu-accelerate {
                transform: translateZ(0);
                backface-visibility: hidden;
                perspective: 1000px;
            }

            .hero-gradient {
                background: linear-gradient(135deg, #10b981 0%, #047857 100%);
                transform: translateZ(0);
            }

            .card-hover {
                transition: box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                           border-color 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                           transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                will-change: transform;
                transform: translateZ(0);
            }

            .card-hover:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                transform: translateY(-2px) translateZ(0);
            }

            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }

            .float-animation {
                animation: float 6s ease-in-out infinite;
            }

            /* Enhanced Hero Animations - GPU accelerated */
            @keyframes heroFadeIn {
                from {
                    opacity: 0;
                    transform: translate3d(0, 40px, 0);
                }
                to {
                    opacity: 1;
                    transform: translate3d(0, 0, 0);
                }
            }

            @keyframes heroSlideLeft {
                from {
                    opacity: 0;
                    transform: translate3d(-60px, 0, 0);
                }
                to {
                    opacity: 1;
                    transform: translate3d(0, 0, 0);
                }
            }

            @keyframes heroSlideRight {
                from {
                    opacity: 0;
                    transform: translate3d(60px, 0, 0);
                }
                to {
                    opacity: 1;
                    transform: translate3d(0, 0, 0);
                }
            }

            @keyframes heroScale {
                from {
                    opacity: 0;
                    transform: scale3d(0.8, 0.8, 1);
                }
                to {
                    opacity: 1;
                    transform: scale3d(1, 1, 1);
                }
            }

            .hero-animate-1 {
                animation: heroFadeIn 1s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
                will-change: transform, opacity;
            }
            .hero-animate-2 {
                animation: heroFadeIn 1s cubic-bezier(0.16, 1, 0.3, 1) 0.4s both;
                will-change: transform, opacity;
            }
            .hero-animate-3 {
                animation: heroFadeIn 1s cubic-bezier(0.16, 1, 0.3, 1) 0.6s both;
                will-change: transform, opacity;
            }
            .hero-animate-4 {
                animation: heroFadeIn 1s cubic-bezier(0.16, 1, 0.3, 1) 0.8s both;
                will-change: transform, opacity;
            }
            .hero-animate-5 {
                animation: heroFadeIn 1s cubic-bezier(0.16, 1, 0.3, 1) 1s both;
                will-change: transform, opacity;
            }

            /* Animated Background Gradient */
            @keyframes gradientMove {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }

            .animated-gradient-bg {
                background: linear-gradient(-45deg, #10b981, #059669, #047857, #065f46);
                background-size: 400% 400%;
                animation: gradientMove 15s ease infinite;
            }

            /* Smooth Scroll Reveal - GPU accelerated */
            .reveal-on-scroll {
                opacity: 0;
                transform: translate3d(0, 30px, 0);
                transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1),
                           transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                will-change: transform, opacity;
            }

            .reveal-on-scroll.revealed {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }

            /* Fade in on page load */
            @keyframes fadeInPage {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }

            body {
                animation: fadeInPage 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            }

            /* Staggered card animations */
            .stagger-card:nth-child(1) { animation-delay: 0.1s; }
            .stagger-card:nth-child(2) { animation-delay: 0.2s; }
            .stagger-card:nth-child(3) { animation-delay: 0.3s; }

            /* Glowing effect on buttons */
            .glow-on-hover {
                position: relative;
                overflow: hidden;
            }

            .glow-on-hover::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                background: rgba(255,255,255,0.2);
                border-radius: 50%;
                transform: translate(-50%, -50%);
                transition: width 0.6s, height 0.6s;
            }

            .glow-on-hover:hover::before {
                width: 300px;
                height: 300px;
            }

            /* Morphing background blobs */
            .morph-blob-1 {
                animation: morphBlob1 8s ease-in-out infinite;
            }

            .morph-blob-2 {
                animation: morphBlob2 10s ease-in-out infinite;
            }

            @keyframes morphBlob1 {
                0%, 100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; transform: rotate(0deg); }
                50% { border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%; transform: rotate(180deg); }
            }

            @keyframes morphBlob2 {
                0%, 100% { border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; transform: rotate(0deg) scale(1); }
                50% { border-radius: 70% 30% 50% 50% / 30% 30% 70% 70%; transform: rotate(-180deg) scale(1.1); }
            }

            /* Enhanced button styles - GPU accelerated */
            .btn-enhanced {
                position: relative;
                overflow: hidden;
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                transform: translateZ(0);
                will-change: transform;
            }

            .btn-enhanced::after {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
                transform: translate3d(-100%, 0, 0);
                transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .btn-enhanced:hover::after {
                transform: translate3d(100%, 0, 0);
            }

            .btn-enhanced:hover {
                transform: translateY(-2px) translateZ(0);
            }

            /* Header navigation styling to match footer theme */
            .site-header {
                background: linear-gradient(135deg, #10b981 0%, #059669 50%, #10b981 100%);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                transform: translateZ(0);
                will-change: transform;
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }

            /* Dark mode header styling */
            @media (prefers-color-scheme: dark) {
                .site-header {
                    background: linear-gradient(135deg, #10b981 0%, #059669 50%, #10b981 100%);
                }
            }
            .site-header .nav-link {
                position: relative;
                font-weight: 600;
                letter-spacing: .5px;
                color: #374151;
                transition: color 0.3s cubic-bezier(0.16, 1, 0.3, 1),
                           transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                will-change: transform;
                transform: translateZ(0);
            }

            @media (prefers-color-scheme: dark) {
                .site-header .nav-link {
                    color: #d1d5db;
                }
            }

            .site-header .nav-link:after {
                content: '';
                position: absolute;
                left: 0; bottom: -4px;
                width: 0; height: 2px;
                background: #10b981;
                border-radius: 2px;
                transition: width .3s ease;
            }
            .site-header .nav-link:hover:after,
            .site-header .nav-link:focus:after {
                width: 100%;
            }
            .site-header .nav-link:hover,
            .site-header .nav-link:focus {
                color: #10b981;
                transform: translate3d(0, -2px, 0);
            }
            @media (max-width: 768px) {
                .site-header nav {
                    display: none; /* keep header clean on small screens */
                }
            }

            /* Parallax-like scroll effect */
            .parallax-bg {
                transform: translateZ(0);
                will-change: transform;
            }

            /* Number counter animation */
            .count-up {
                display: inline-block;
            }

            /* Responsive hero background image — professional cover */
            .hero-bg-responsive {
                object-fit: cover;
                object-position: center 35%;
                filter: brightness(0.95) saturate(1.05);
                transition: transform 12s cubic-bezier(0.16, 1, 0.3, 1),
                           filter 1s cubic-bezier(0.16, 1, 0.3, 1);
                will-change: transform;
                transform: translateZ(0);
            }

            /* Subtle slow-zoom on load for a cinematic feel */
            @keyframes heroZoom {
                from { transform: scale3d(1, 1, 1); }
                to   { transform: scale3d(1.06, 1.06, 1); }
            }
            .hero-bg-zoom {
                animation: heroZoom 20s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                will-change: transform;
            }

            /* Mobile: Focus on the church building center */
            @media (max-width: 640px) {
                .hero-bg-responsive {
                    object-position: center 40%;
                }
            }

            /* Tablet */
            @media (min-width: 641px) and (max-width: 1024px) {
                .hero-bg-responsive {
                    object-position: center 35%;
                }
            }

            /* Desktop and larger */
            @media (min-width: 1025px) {
                .hero-bg-responsive {
                    object-position: center 30%;
                }
            }

            /* Vignette overlay for depth */
            .hero-vignette {
                background: radial-gradient(ellipse at center, transparent 50%, rgba(0,0,0,0.15) 100%);
            }
        </style>
    </head>
    <body class="antialiased bg-gray-50 dark:bg-gray-900 text-[#1b1b18] full-animations">
        <!-- Header -->
    <header class="site-header text-white border-b border-emerald-400/30 shadow-lg" style="background: #4ade80 !important;"
"
">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-green-600 to-emerald-600 rounded-full blur-md opacity-40 group-hover:opacity-60 transition-opacity"></div>
                            <img src="/images/ers-logo.png" alt="eReligiousServices logo" class="relative w-14 h-14 object-contain transform group-hover:scale-110 transition-transform duration-300 drop-shadow" />
                        </div>
                        <div class="hidden sm:block">
                            <div class="font-bold text-xl text-white tracking-wide">eReligiousServices</div>
                            <div class="text-xs text-green-100">Center for Religious Education and Mission</div>
                        </div>
                    </a>

                    <!-- Primary Navigation removed as requested -->

                    <div class="flex items-center gap-2 sm:gap-3">
                        @guest
                            <!-- Sign In Button (opens modal) -->
                            <button onclick="document.getElementById('signInModal').classList.remove('hidden'); document.body.style.overflow='hidden';"
                               class="group relative inline-flex items-center justify-center gap-1 sm:gap-1.5 px-2.5 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-bold text-emerald-700 bg-white border-2 border-emerald-300 rounded-lg sm:rounded-2xl hover:border-emerald-500 hover:text-emerald-600 transition-all duration-300 shadow-sm hover:shadow-md cursor-pointer">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                <span class="tracking-wide">SIGN IN</span>
                            </button>

                            <!-- Register Button -->
                            <a href="{{ route('register') }}"
                               class="group relative inline-flex items-center justify-center gap-1 sm:gap-1.5 px-2.5 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg sm:rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 overflow-hidden">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span class="tracking-wide relative z-10">REGISTER</span>
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center justify-center gap-1 sm:gap-1.5 px-2.5 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg sm:rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <main class="relative overflow-hidden min-h-screen">
            <!-- Responsive Background Image Container -->
            <div class="absolute inset-0 z-0 overflow-hidden">
                <!-- Background Image with responsive cover & cinematic zoom -->
                <img src="{{ asset('images/church-bg-1.jpg') }}"
                     alt=""
                     class="absolute inset-0 w-full h-full hero-bg-responsive hero-bg-zoom"
                     onerror="this.src='{{ asset('images/hero-placeholder.jpg') }}'"
                     loading="eager" />

                <!-- Vignette for depth & focus -->
                <div class="absolute inset-0 hero-vignette"></div>

                <!-- Soft colour-tint overlay for branding consistency -->
                <div class="absolute inset-0 bg-gradient-to-b from-emerald-900/10 via-transparent to-emerald-900/15"></div>

                <!-- Readability overlay — lighter at centre, darker at edges -->
                <div class="absolute inset-0 bg-white/70 dark:bg-gray-900/80 backdrop-blur-[2px]"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 sm:py-20">
                <!-- Hero Content -->
                <div class="text-center mb-8 sm:mb-16">
                    <div class="hero-animate-1 inline-flex items-center gap-2 px-3 sm:px-4 py-1.5 sm:py-2 bg-green-100 dark:bg-green-900/30 rounded-full text-xs sm:text-sm font-semibold text-green-700 dark:text-green-300 mb-4 sm:mb-8 backdrop-blur-sm border border-green-200 dark:border-green-700">
                        <span class="relative flex h-2 w-2 sm:h-3 sm:w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 sm:h-3 sm:w-3 bg-green-500"></span>
                        </span>
                        Welcome to HNU Center for Religious Education and Mission
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold tracking-tight mb-4 sm:mb-8">
                        <span class="hero-animate-2 block text-gray-900 dark:text-white">Faith.</span>
                        <span class="hero-animate-3 block text-emerald-600 dark:text-emerald-400">Community.</span>
                        <span class="hero-animate-4 block text-gray-900 dark:text-white">Service.</span>
                    </h1>

                    <p class="hero-animate-4 mt-4 sm:mt-8 text-base sm:text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed px-2">
                        Your gateway to spiritual growth and community engagement at <span class="font-semibold text-green-600 dark:text-green-400"><br>Holy Name University</span>
                    </p>

                    <p class="hero-animate-5 mt-2 sm:mt-4 text-sm sm:text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto px-2">
                        Experience seamless booking for liturgical services, retreats, and religious events. Join our vibrant faith community today.
                    </p>

                    <!-- CTA Buttons - Enhanced -->
                    <div class="hero-animate-5 mt-6 sm:mt-12 flex gap-3 sm:gap-6 flex-wrap justify-center px-2">
                        <a href="#home-calendar"
                           class="btn-enhanced glow-on-hover group relative inline-flex items-center gap-2 sm:gap-3 px-5 sm:px-8 py-3 sm:py-4 text-sm sm:text-lg font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl sm:rounded-2xl transition-all duration-300 shadow-2xl hover:shadow-emerald-500/40 hover:scale-105 transform">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 transition-transform group-hover:scale-110 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>View Calendar</span>
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>

                        <a href="#services"
                           class="btn-enhanced group inline-flex items-center gap-2 sm:gap-3 px-5 sm:px-8 py-3 sm:py-4 text-sm sm:text-lg font-bold text-emerald-700 dark:text-emerald-300 bg-white dark:bg-gray-800 border-2 border-emerald-300 dark:border-emerald-700 rounded-xl sm:rounded-2xl hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:border-emerald-500 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 transition-transform group-hover:rotate-12 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Explore Services</span>
                        </a>
                    </div>
                </div>

                <!-- Stats Section - Enhanced with scroll reveal and improved responsive layout -->
                <div class="mt-12 sm:mt-20 lg:mt-24 px-2 sm:px-0">
                    <!-- Desktop: Equal-sized horizontal cards with gap, Mobile: Vertical Stack -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 max-w-5xl mx-auto">
                        <!-- Card 1: Student Organizations -->
                        <div class="reveal-on-scroll stagger-card card-hover relative bg-white dark:bg-gray-800 rounded-2xl p-6 sm:p-8 shadow-xl border border-gray-200 dark:border-gray-700 group min-h-[160px] sm:min-h-[180px] flex flex-col justify-center">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-400 rounded-bl-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                            <div class="relative text-center">
                                <div class="text-4xl sm:text-5xl font-extrabold text-emerald-600 dark:text-emerald-400 mb-2 sm:mb-3 count-up">6+</div>
                                <div class="text-gray-700 dark:text-gray-300 font-semibold text-base sm:text-lg">Student Organizations</div>
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">Active faith communities</div>
                            </div>
                        </div>

                        <!-- Card 2: Daily Mass -->
                        <div class="reveal-on-scroll stagger-card card-hover relative bg-white dark:bg-gray-800 rounded-2xl p-6 sm:p-8 shadow-xl border border-gray-200 dark:border-gray-700 group min-h-[160px] sm:min-h-[180px] flex flex-col justify-center">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-400 to-teal-400 rounded-bl-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                            <div class="relative text-center">
                                <div class="text-4xl sm:text-5xl font-extrabold text-emerald-600 dark:text-emerald-400 mb-2 sm:mb-3">Daily</div>
                                <div class="text-gray-700 dark:text-gray-300 font-semibold text-base sm:text-lg">Noon Mass</div>
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">Join us for worship</div>
                            </div>
                        </div>

                        <!-- Card 3: Online Booking -->
                        <div class="reveal-on-scroll stagger-card card-hover relative bg-white dark:bg-gray-800 rounded-2xl p-6 sm:p-8 shadow-xl border border-gray-200 dark:border-gray-700 group min-h-[160px] sm:min-h-[180px] flex flex-col justify-center">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-400 to-cyan-400 rounded-bl-full opacity-10 group-hover:opacity-20 transition-opacity"></div>
                            <div class="relative text-center">
                                <div class="text-4xl sm:text-5xl font-extrabold text-cyan-600 dark:text-cyan-400 mb-2 sm:mb-3">24/7</div>
                                <div class="text-gray-700 dark:text-gray-300 font-semibold text-base sm:text-lg">Online Booking</div>
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">Reserve services anytime</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Featured Services Section - Enhanced with better responsive layout -->
                <div id="services" class="mt-8 sm:mt-12 lg:mt-16 px-2 sm:px-0">
                    <div class="text-center mb-6 sm:mb-10 reveal-on-scroll">
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-white mb-2 sm:mb-3">Our Services</h2>
                        <p class="text-sm sm:text-base lg:text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">Discover the various ways we serve our community</p>
                    </div>

                    <!-- Desktop: Equal-sized horizontal cards with gap, Mobile: Vertical Stack -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 max-w-5xl mx-auto">
                        <!-- Mass Reservations -->
                        <div class="reveal-on-scroll stagger-card card-hover group bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-7 shadow-xl border border-gray-200 dark:border-gray-700 hover:border-green-500 dark:hover:border-green-400 relative overflow-hidden min-h-[200px] sm:min-h-[240px] flex flex-col">
                            <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative flex flex-col items-center sm:items-center text-center flex-1">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg group-hover:shadow-green-500/25">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-2 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">Mass Reservations</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">Book your spot for daily mass, special celebrations, and liturgical events</p>
                            </div>
                        </div>

                        <!-- Retreats -->
                        <div class="reveal-on-scroll stagger-card card-hover group bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-7 shadow-xl border border-gray-200 dark:border-gray-700 hover:border-teal-500 dark:hover:border-teal-400 relative overflow-hidden min-h-[200px] sm:min-h-[240px] flex flex-col">
                            <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-teal-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative flex flex-col items-center sm:items-center text-center flex-1">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-green-500 to-teal-500 rounded-xl flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg group-hover:shadow-teal-500/25">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                </div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">Spiritual Retreats</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">Join immersive retreat experiences for spiritual renewal and growth</p>
                            </div>
                        </div>

                        <!-- Community Events -->
                        <div class="reveal-on-scroll stagger-card card-hover group bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-7 shadow-xl border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 relative overflow-hidden min-h-[200px] sm:min-h-[240px] flex flex-col">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-cyan-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative flex flex-col items-center sm:items-center text-center flex-1">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg group-hover:shadow-blue-500/25">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Community Events</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">Participate in faith-based activities and community gatherings</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Reservations Calendar Section -->
                @include('partials.home-calendar')
            </div>
        </main>

        @include('layouts.footer')

        <!-- Scroll Reveal Animation Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Intersection Observer for scroll animations
                const observerOptions = {
                    root: null,
                    rootMargin: '0px',
                    threshold: 0.1
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry, index) => {
                        if (entry.isIntersecting) {
                            // Add staggered delay based on element position
                            const delay = entry.target.classList.contains('stagger-card')
                                ? index * 100
                                : 0;

                            setTimeout(() => {
                                entry.target.classList.add('revealed');
                            }, delay);

                            // Optionally unobserve after animation
                            observer.unobserve(entry.target);
                        }
                    });
                }, observerOptions);

                // Observe all elements with reveal-on-scroll class
                document.querySelectorAll('.reveal-on-scroll').forEach(el => {
                    observer.observe(el);
                });

                // Smooth scroll for anchor links
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    });
                });

                // Enhanced parallax effect with smoother performance
                let ticking = false;
                let lastScrollY = 0;

                const handleScroll = () => {
                    const scrolled = window.pageYOffset;

                    // Only update if scroll position changed significantly (throttle)
                    if (Math.abs(scrolled - lastScrollY) > 1) {
                        lastScrollY = scrolled;

                        const blobs = document.querySelectorAll('.morph-blob-1, .morph-blob-2');
                        blobs.forEach((blob, i) => {
                            const speed = i === 0 ? 0.05 : 0.03;
                            blob.style.transform = `translate3d(0, ${scrolled * speed}px, 0)`;
                        });
                    }

                    ticking = false;
                };

                window.addEventListener('scroll', () => {
                    if (!ticking) {
                        window.requestAnimationFrame(handleScroll);
                        ticking = true;
                    }
                }, { passive: true });

                // Smooth scroll polyfill for better cross-browser support
                document.documentElement.style.scrollBehavior = 'smooth';
            });
        </script>

        {{-- ==================== GLASSY SIGN-IN MODAL ==================== --}}
        <div id="signInModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4">
            {{-- Backdrop --}}
            <div onclick="closeSignInModal()" class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

            {{-- Glassy Card --}}
            <div class="relative w-full max-w-md animate-[modalSlideUp_0.35s_ease-out]
                        bg-white/80 dark:bg-gray-900/80
                        backdrop-blur-xl backdrop-saturate-150
                        border border-white/30 dark:border-gray-700/40
                        rounded-3xl shadow-2xl shadow-emerald-900/10
                        overflow-hidden">

                {{-- Decorative top accent bar --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-emerald-400 via-green-500 to-teal-500"></div>

                {{-- Close button --}}
                <button onclick="closeSignInModal()"
                        class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors z-10"
                        aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="px-6 sm:px-8 pt-6 pb-6">
                    {{-- Header --}}
                    <div class="flex items-center justify-center gap-3 mb-2">
                        <img src="/images/ers-logo.png" alt="eReligiousServices" class="w-10 h-10 object-contain drop-shadow" />
                        <span class="text-emerald-700 dark:text-emerald-400 font-semibold text-sm tracking-tight">eReligiousServices</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-3 mb-1 tracking-tight text-center">Welcome Back</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 text-center">
                        Sign in to access your account
                    </p>

                    {{-- Session status --}}
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    {{-- Form --}}
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <label for="modal_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email Address</label>
                            <input id="modal_email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                   placeholder="you@example.com"
                                   class="w-full px-4 py-2.5 text-sm bg-white/60 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-600/60 rounded-xl
                                          focus:ring-2 focus:ring-emerald-500 focus:border-transparent
                                          backdrop-blur-sm transition-all duration-200 placeholder-gray-400" />
                            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs" />
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="modal_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                            <div class="relative">
                                <input id="modal_password" name="password" type="password" required autocomplete="current-password"
                                       placeholder="••••••••"
                                       class="w-full px-4 py-2.5 pr-12 text-sm bg-white/60 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-600/60 rounded-xl
                                              focus:ring-2 focus:ring-emerald-500 focus:border-transparent
                                              backdrop-blur-sm transition-all duration-200 placeholder-gray-400" />
                                <button type="button"
                                        onclick="toggleModalPassword()"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-emerald-600 transition-colors focus:outline-none z-10"
                                        aria-label="Toggle password visibility">
                                    <svg id="modalEyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg id="modalEyeOffIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs" />
                        </div>

                        <input type="hidden" name="role" value="" />

                        {{-- Remember & Forgot --}}
                        <div class="flex items-center justify-between gap-2">
                            <label for="modal_remember" class="inline-flex items-center cursor-pointer group">
                                <input id="modal_remember" type="checkbox" name="remember"
                                       class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0 transition-colors" />
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition-colors select-none">Remember me</span>
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                   class="text-sm text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 font-medium transition-colors whitespace-nowrap">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        {{-- Submit --}}
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 py-2.5 text-sm font-semibold text-white
                                       bg-gradient-to-r from-emerald-500 via-green-500 to-teal-500 hover:from-emerald-600 hover:via-green-600 hover:to-teal-600
                                       rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50
                                       transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Sign In
                        </button>
                    </form>

                    {{-- Register link --}}
                    <p class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 font-semibold transition-colors underline decoration-emerald-600/30 hover:decoration-emerald-600 underline-offset-2">
                            Create Account
                        </a>
                    </p>
                </div>
            </div>
        </div>

        {{-- Modal animation keyframes & helpers --}}
        <style>
            @keyframes modalSlideUp {
                from { opacity: 0; transform: translateY(30px) scale(0.97); }
                to   { opacity: 1; transform: translateY(0) scale(1); }
            }
        </style>
        <script>
            function closeSignInModal() {
                document.getElementById('signInModal').classList.add('hidden');
                document.body.style.overflow = '';
            }
            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeSignInModal();
            });
            function toggleModalPassword() {
                const input = document.getElementById('modal_password');
                const eyeIcon = document.getElementById('modalEyeIcon');
                const eyeOffIcon = document.getElementById('modalEyeOffIcon');
                if (input.type === 'password') {
                    input.type = 'text';
                    eyeIcon.classList.add('hidden');
                    eyeOffIcon.classList.remove('hidden');
                } else {
                    input.type = 'password';
                    eyeIcon.classList.remove('hidden');
                    eyeOffIcon.classList.add('hidden');
                }
            }
            // Auto-open modal if there are validation errors (form was submitted)
            @if($errors->any())
                document.getElementById('signInModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            @endif
        </script>
    </body>
</html>
