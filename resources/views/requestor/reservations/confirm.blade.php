@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-8 text-center">
        <svg class="w-12 h-12 mx-auto mb-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">No Confirmation Needed</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            The requestor confirmation step has been removed. Your reservation will proceed after staff contact and admin assignment.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-[var(--er-green)] text-white font-semibold hover:opacity-95">Go to Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-[var(--er-green)] text-white font-semibold hover:opacity-95">Sign in</a>
            @endauth
            <a href="/" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Home</a>
        </div>
    </div>
</div>
@endsection
