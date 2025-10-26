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
    </head>
    <body class="font-sans text-gray-900 antialiased text-base lg:text-[18px]">
        <div class="min-h-screen flex flex-col bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
            <div class="flex-1 flex flex-col sm:justify-center items-center pt-8 sm:pt-6 pb-12 px-4">
                <div class="w-full sm:max-w-2xl lg:max-w-3xl mt-6 px-6 sm:px-8 py-8 bg-white dark:bg-gray-800 shadow-xl overflow-hidden sm:rounded-xl border border-gray-200 dark:border-gray-700">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </div>
            </div>

            @include('layouts.footer')
        </div>
    </body>
</html>
