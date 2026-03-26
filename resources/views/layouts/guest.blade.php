<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'eReligiousServices') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" href="/images/ers-logo.png" />

    <!-- Animation Mode: none for auth pages (login/register) -->
    <script>
        window.ANIMATIONS_MODE = 'none';
    </script>
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dark Mode Initialization Script -->
    <script>
        // Initialize dark mode from localStorage before page renders
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased text-base lg:text-[18px] no-animations">
        <div class="min-h-screen flex flex-col bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
            <div class="flex-1 flex flex-col sm:justify-center items-center pt-4 sm:pt-6 pb-6 sm:pb-10 px-3 sm:px-4">
                <div class="w-full guest-content-box bg-white dark:bg-gray-900 shadow-2xl overflow-hidden rounded-3xl border border-gray-100 dark:border-gray-800 backdrop-blur-sm">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </div>
            </div>

            @include('layouts.footer')
        </div>

        <style>
            /* Default: login and other small auth pages */
            .guest-content-box {
                max-width: 24rem; /* max-w-sm on mobile */
            }
            /* Tablet+ for small pages */
            @media (min-width: 640px) {
                .guest-content-box {
                    max-width: 24rem; /* max-w-sm */
                }
            }
            /* When register page is loaded, widen container */
            .guest-register-page .guest-content-box {
                max-width: 100%; /* full width on mobile */
            }
            @media (min-width: 640px) {
                .guest-register-page .guest-content-box {
                    max-width: 42rem; /* sm:max-w-2xl */
                }
            }
            @media (min-width: 1024px) {
                .guest-register-page .guest-content-box {
                    max-width: 64rem; /* lg:max-w-4xl */
                }
            }
            @media (min-width: 1280px) {
                .guest-register-page .guest-content-box {
                    max-width: 80rem; /* xl:max-w-6xl */
                }
            }
        </style>

        @stack('scripts')
    </body>
</html>
