<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Message to Admin
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700" style="height: calc(100vh - 180px);">
                <div class="flex h-full">
                    <!-- Left Sidebar - Conversations List -->
                    <div class="w-full md:w-80 lg:w-96 border-r border-gray-200 dark:border-gray-700 flex flex-col bg-white dark:bg-gray-800">
                        <!-- Header -->
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        @if(auth()->user()->role === 'requestor')
                                            Admin Support
                                        @else
                                            Requestor Messages
                                        @endif
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Direct communication</p>
                                </div>
                            </div>
                        </div>

                        <!-- Conversations List -->
                        <div class="flex-1 overflow-y-auto">
                            @forelse($conversations as $conversation)
                                <a href="{{ route('chat.show', $conversation->id) }}"
                                   class="flex items-center px-4 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 {{ request()->route('userId') == $conversation->id ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-600' : 'border-l-4 border-transparent' }}">
                                    <!-- Profile Picture -->
                                    <div class="relative flex-shrink-0">
                                        <img src="{{ asset('storage/' . ($conversation->profile_picture ?? 'profile-pictures/default-avatar.png')) }}"
                                             alt="{{ $conversation->first_name ?? 'User' }}"
                                             class="w-9 h-9 rounded-full object-cover border-2 {{ request()->route('userId') == $conversation->id ? 'border-blue-500' : 'border-gray-200 dark:border-gray-600' }}">
                                        @if($conversation->unread_count > 0)
                                            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-lg">
                                                {{ $conversation->unread_count > 9 ? '9+' : $conversation->unread_count }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- User Info -->
                                    <div class="ml-3 flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                {{ $conversation->first_name ?? 'User' }} {{ $conversation->last_name ?? '' }}
                                            </h4>
                                            @if($conversation->last_message_at)
                                                <span class="text-xs text-gray-500 dark:text-gray-500 ml-2 flex-shrink-0 font-medium">
                                                    {{ \Carbon\Carbon::parse($conversation->last_message_at)->diffForHumans(null, true) }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 truncate font-normal">
                                            {{ $conversation->last_message ?? 'No messages yet' }}
                                        </p>
                                    </div>
                                </a>
                            @empty
                                <div class="flex flex-col items-center justify-center h-full p-8 text-center">
                                    <div class="w-20 h-20 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">No conversations yet</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs">
                                        @if(auth()->user()->role === 'requestor')
                                            Your conversations with admin will appear here. Admins will respond to your reservation requests and questions.
                                        @else
                                            Requestor conversations will appear here when they send messages or make reservation requests.
                                        @endif
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Right Side - Empty State -->
                    <div class="hidden md:flex flex-1 items-center justify-center bg-gray-50 dark:bg-gray-900">
                        <div class="text-center px-6">
                            <div class="w-24 h-24 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                                @if(auth()->user()->role === 'requestor')
                                    Admin Support Chat
                                @else
                                    Requestor Messages
                                @endif
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 max-w-md">
                                @if(auth()->user()->role === 'requestor')
                                    Select a conversation to view your messages with the admin team. Get help with your reservations and questions.
                                @else
                                    Select a conversation to view messages from requestors. Respond to their questions and manage their reservation requests.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
