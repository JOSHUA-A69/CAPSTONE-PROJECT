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
    <body class="font-sans antialiased text-base lg:text-[18px]">
        <!-- Skip to main content link for keyboard users -->
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-indigo-600 focus:text-white focus:rounded-lg focus:shadow-lg">
            Skip to main content
        </a>

        <div class="min-h-screen flex flex-col bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow" role="banner">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main id="main-content" class="flex-1 min-h-[calc(100vh-4rem)] page-content" role="main" tabindex="-1">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>

            <!-- Footer positioned at bottom, visible on scroll -->
            <div class="mt-auto">
                @include('layouts.footer')
            </div>
        </div>

        <!-- Screen reader announcements for dynamic content -->
        <div id="sr-announcements" aria-live="polite" aria-atomic="true" class="sr-only"></div>
        
        <!-- Dark Mode Toggle Script -->
        <script>
            function toggleDarkMode(button) {
                document.documentElement.classList.toggle('dark');
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                updateDarkModeText();
            }
            
            function updateDarkModeText() {
                const isDark = document.documentElement.classList.contains('dark');
                const toggleButton = document.getElementById('darkModeToggle');
                if (toggleButton) {
                    const textSpan = toggleButton.querySelector('.dark-mode-text');
                    if (textSpan) {
                        textSpan.textContent = isDark ? 'Light Mode' : 'Dark Mode';
                    }
                }
            }
            
            // Update text on page load
            document.addEventListener('DOMContentLoaded', updateDarkModeText);
        </script>
    </body>
</html>
