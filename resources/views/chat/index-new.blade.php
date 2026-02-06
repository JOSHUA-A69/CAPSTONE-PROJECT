@php
    use Carbon\Carbon;

    $conversationData = $conversations->map(function ($conversation) {
        return [
            'id' => $conversation->id,
            'first_name' => $conversation->first_name ?? 'User',
            'last_name' => $conversation->last_name ?? '',
            'full_name' => trim(($conversation->first_name ?? 'User') . ' ' . ($conversation->last_name ?? '')),
            'profile_picture_url' => $conversation->profile_picture
                ? asset('storage/' . $conversation->profile_picture)
                : asset('images/default-avatar.svg'),
            'unread_count' => $conversation->unread_count ?? 0,
            'message_count' => $conversation->message_count ?? 0,
            'last_message' => $conversation->last_message ?? '',
            'last_message_at' => $conversation->last_message_at
                ? Carbon::parse($conversation->last_message_at)->toIso8601String()
                : null,
            'role' => $conversation->role ?? null,
        ];
    })->values();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ auth()->user()->role === 'requestor' ? 'Admin Messages' : 'Requestor Messages' }} - {{ config('app.name') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Custom scrollbar */
        .chat-scrollbar::-webkit-scrollbar { width: 6px; }
        .chat-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .chat-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 3px; }
        .chat-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.3); }
        
        /* Hide scrollbar on mobile */
        @media (max-width: 768px) {
            .chat-scrollbar::-webkit-scrollbar { display: none; }
            .chat-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        }
        
        /* Smooth transitions */
        .slide-enter { transform: translateX(-100%); }
        .slide-enter-active { transform: translateX(0); transition: transform 0.3s ease; }
        .slide-leave-active { transform: translateX(-100%); transition: transform 0.3s ease; }
        
        /* Message bubble animations */
        @keyframes messageIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message-bubble { animation: messageIn 0.2s ease-out; }
        
        /* Input focus glow */
        .input-glow:focus { box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2); }
        
        /* Safe area for mobile */
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0); }
        .safe-top { padding-top: env(safe-area-inset-top, 0); }
    </style>
</head>
<body class="h-full bg-gray-100 dark:bg-gray-900 font-sans antialiased overflow-hidden">
    <div class="h-full flex flex-col"
         x-data="messengerApp({{ $conversationData->toJson() }}, {{ auth()->id() }})"
         x-init="init()">
        
        <!-- Mobile: Full screen layout -->
        <div class="flex h-full relative">
            
            <!-- Conversations Sidebar -->
            <div class="conversation-sidebar absolute md:relative inset-y-0 left-0 z-30 w-full md:w-80 lg:w-96 flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-transform duration-300 ease-in-out"
                 :class="{ '-translate-x-full md:translate-x-0': showChat && selectedConversation, 'translate-x-0': !showChat || !selectedConversation }">
                
                <!-- Sidebar Header -->
                <div class="flex-shrink-0 px-4 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 safe-top">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('dashboard') }}" class="p-2 -ml-2 rounded-full hover:bg-white/10 transition-colors">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                            </a>
                            <div>
                                <h1 class="text-lg font-bold text-white">
                                    {{ auth()->user()->role === 'requestor' ? 'Admin Messages' : 'Requestor Messages' }}
                                </h1>
                                <p class="text-xs text-indigo-200" x-text="conversations.length + ' conversation' + (conversations.length !== 1 ? 's' : '')"></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Search (optional) -->
                <div class="flex-shrink-0 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" 
                               x-model="searchQuery"
                               placeholder="Search conversations..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-full text-sm focus:ring-2 focus:ring-indigo-500 dark:text-white placeholder-gray-500">
                    </div>
                </div>
                
                <!-- Conversations List -->
                <div class="flex-1 overflow-y-auto chat-scrollbar">
                    <template x-if="filteredConversations.length === 0">
                        <div class="flex flex-col items-center justify-center h-full px-6 text-center">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">No conversations yet</p>
                        </div>
                    </template>
                    
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="conversation in filteredConversations" :key="conversation.id">
                            <button @click="selectConversation(conversation); showChat = true"
                                    class="w-full px-4 py-3 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-left"
                                    :class="{ 'bg-indigo-50 dark:bg-indigo-900/30': isSelected(conversation) }">
                                <!-- Avatar -->
                                <div class="relative flex-shrink-0">
                                    <img :src="conversation.profile_picture_url" 
                                         :alt="conversation.full_name"
                                         class="w-12 h-12 rounded-full object-cover border-2"
                                         :class="isSelected(conversation) ? 'border-indigo-500' : 'border-transparent'">
                                    <!-- Online indicator (optional) -->
                                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full" x-show="false"></span>
                                    <!-- Unread badge -->
                                    <span x-show="conversation.unread_count > 0"
                                          class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
                                          x-text="conversation.unread_count > 99 ? '99+' : conversation.unread_count"></span>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-semibold text-gray-900 dark:text-white truncate" x-text="conversation.full_name"></p>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0" x-text="formatListTime(conversation.last_message_at)"></span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate mt-0.5" 
                                       :class="{ 'font-semibold text-gray-700 dark:text-gray-300': conversation.unread_count > 0 }"
                                       x-text="conversation.last_message || 'No messages yet'"></p>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            
            <!-- Chat Area -->
            <div class="flex-1 flex flex-col bg-gray-50 dark:bg-gray-900 min-w-0">
                
                <!-- Chat Header -->
                <div class="flex-shrink-0 px-4 py-3 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3 safe-top">
                    <!-- Back button (mobile only) -->
                    <button @click="showChat = false" 
                            class="md:hidden p-2 -ml-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    
                    <template x-if="selectedConversation">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <img :src="selectedConversation.profile_picture_url" 
                                 :alt="selectedConversation.full_name"
                                 class="w-10 h-10 rounded-full object-cover">
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-white truncate" x-text="selectedConversation.full_name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span x-text="messages.length"></span> messages
                                </p>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="!selectedConversation">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white">Select a conversation</p>
                        </div>
                    </template>
                    
                    <!-- Header actions -->
                    <div class="flex items-center gap-1">
                        @if(auth()->user()->role === 'admin')
                        <button x-show="selectedConversation"
                                @click="openFaqModal()"
                                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-colors"
                                title="Add FAQ">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                        @endif
                        <button x-show="selectedConversation"
                                @click="showClearConfirmation = true"
                                class="p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900/30 text-gray-600 dark:text-gray-300 hover:text-red-500 transition-colors"
                                title="Delete conversation">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto chat-scrollbar px-4 py-4" x-ref="messagesContainer">
                    
                    <!-- Empty state: No conversation selected -->
                    <div x-show="!selectedConversation && !loadingMessages" class="h-full flex items-center justify-center">
                        <div class="text-center px-6 max-w-sm">
                            <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Your Messages</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Select a conversation from the sidebar to start chatting</p>
                        </div>
                    </div>
                    
                    <!-- Loading state -->
                    <div x-show="loadingMessages" class="h-full flex items-center justify-center">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Loading messages...</p>
                        </div>
                    </div>
                    
                    <!-- Empty conversation state -->
                    <div x-show="selectedConversation && !loadingMessages && messages.length === 0" class="h-full flex items-center justify-center">
                        <div class="text-center px-6 max-w-md">
                            <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-12 h-12 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">No messages yet</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Send a message to start the conversation</p>
                            
                            @if(auth()->user()->role === 'requestor')
                            <!-- Quick FAQ buttons -->
                            <div class="space-y-2">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Quick Questions</p>
                                <button @click="sendFaqQuestion('advance_booking')"
                                        class="w-full text-left px-4 py-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-sm transition-all text-sm text-gray-700 dark:text-gray-300">
                                    📅 How far in advance should I request?
                                </button>
                                <button @click="sendFaqQuestion('edit_cancel')"
                                        class="w-full text-left px-4 py-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-sm transition-all text-sm text-gray-700 dark:text-gray-300">
                                    ✏️ Can I edit or cancel my reservation?
                                </button>
                                <button @click="sendFaqQuestion('pending_contact')"
                                        class="w-full text-left px-4 py-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-sm transition-all text-sm text-gray-700 dark:text-gray-300">
                                    📞 Who do I contact for pending requests?
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Messages -->
                    <div x-show="selectedConversation && !loadingMessages && messages.length > 0" class="space-y-3">
                        <template x-for="(message, index) in messages" :key="message.id">
                            <div class="message-bubble" :class="message.sender_id === currentUserId ? 'flex justify-end' : 'flex justify-start'">
                                <div class="flex items-end gap-2 max-w-[85%] sm:max-w-[70%]"
                                     :class="message.sender_id === currentUserId ? 'flex-row-reverse' : ''">
                                    
                                    <!-- Avatar (only for received messages) -->
                                    <img x-show="message.sender_id !== currentUserId"
                                         :src="message.sender.profile_picture"
                                         :alt="message.sender.name"
                                         class="w-7 h-7 rounded-full object-cover flex-shrink-0">
                                    
                                    <!-- Message content -->
                                    <div>
                                        <!-- Attachment -->
                                        <template x-if="message.attachment_url">
                                            <div class="mb-1">
                                                <!-- Image attachment -->
                                                <a x-show="message.is_image"
                                                   :href="message.attachment_url" 
                                                   target="_blank"
                                                   class="block rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                                    <img :src="message.attachment_url" 
                                                         :alt="message.attachment_name"
                                                         class="max-w-full max-h-64 object-cover">
                                                </a>
                                                <!-- File attachment -->
                                                <a x-show="!message.is_image"
                                                   :href="message.attachment_url"
                                                   download
                                                   class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-colors"
                                                   :class="message.sender_id === currentUserId 
                                                       ? 'bg-indigo-500 text-white hover:bg-indigo-600' 
                                                       : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600'">
                                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-medium truncate" x-text="message.attachment_name"></p>
                                                        <p class="text-xs opacity-75" x-text="formatFileSize(message.attachment_size)"></p>
                                                    </div>
                                                </a>
                                            </div>
                                        </template>
                                        
                                        <!-- Text bubble -->
                                        <div x-show="message.message"
                                             class="px-4 py-2.5 rounded-2xl shadow-sm"
                                             :class="message.sender_id === currentUserId 
                                                 ? 'bg-indigo-600 text-white rounded-br-md' 
                                                 : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-bl-md border border-gray-100 dark:border-gray-600'">
                                            <p class="text-[15px] leading-relaxed whitespace-pre-wrap break-words" x-text="message.message"></p>
                                            
                                            <!-- Auto-reply badge -->
                                            <div x-show="message.is_auto_reply" 
                                                 class="mt-2 pt-2 border-t flex items-center gap-1.5 text-xs"
                                                 :class="message.sender_id === currentUserId ? 'border-indigo-400/50 text-indigo-200' : 'border-gray-200 dark:border-gray-600 text-gray-500'">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                <span>Automated Response</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Timestamp -->
                                        <p class="text-[10px] text-gray-400 mt-1 px-1"
                                           :class="message.sender_id === currentUserId ? 'text-right' : 'text-left'"
                                           x-text="formatMessageTime(message.created_at)"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                <!-- Input Area -->
                <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 safe-bottom"
                     x-show="selectedConversation">
                    
                    @if(auth()->user()->role === 'requestor')
                    <!-- FAQ Quick Panel (collapsible) -->
                    <div x-show="showFaqPanel" 
                         x-transition
                         class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(faq, index) in allFaqs.slice(0, 4)" :key="faq.id">
                                <button @click="sendFaqQuestionById(faq.id); showFaqPanel = false"
                                        class="px-3 py-1.5 bg-white dark:bg-gray-700 rounded-full text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 hover:border-indigo-300 hover:text-indigo-600 transition-colors truncate max-w-[200px]"
                                        x-text="faq.question"></button>
                            </template>
                        </div>
                    </div>
                    @endif
                    
                    <!-- File preview -->
                    <div x-show="selectedFile" 
                         class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3 p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl">
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-white truncate" x-text="selectedFileName"></p>
                                <p class="text-xs text-gray-500" x-text="selectedFileSize"></p>
                            </div>
                            <button @click="clearFile()" class="p-1.5 hover:bg-indigo-100 dark:hover:bg-indigo-800 rounded-full transition-colors">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Error message -->
                    <div x-show="error" class="px-4 py-2 bg-red-50 dark:bg-red-900/30">
                        <p class="text-sm text-red-600 dark:text-red-400" x-text="error"></p>
                    </div>
                    
                    <!-- Input bar -->
                    <form @submit.prevent="sendMessage" class="px-3 py-3 flex items-end gap-2">
                        <!-- Attachment button -->
                        <button type="button"
                                @click="$refs.fileInput.click()"
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                        <input type="file" x-ref="fileInput" @change="handleFileUpload($event)" accept="image/*,application/pdf,.doc,.docx" class="hidden">
                        
                        @if(auth()->user()->role === 'requestor')
                        <!-- FAQ toggle button -->
                        <button type="button"
                                @click="showFaqPanel = !showFaqPanel"
                                class="flex-shrink-0 w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition-colors"
                                :class="showFaqPanel ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600' : 'text-gray-500'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                        @endif
                        
                        <!-- Text input -->
                        <div class="flex-1 relative">
                            <textarea x-model="newMessage"
                                      @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                                      @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'"
                                      placeholder="Type a message..."
                                      rows="1"
                                      class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-3xl text-sm focus:ring-2 focus:ring-indigo-500 dark:text-white placeholder-gray-500 resize-none input-glow"
                                      style="min-height: 44px; max-height: 120px;"></textarea>
                        </div>
                        
                        <!-- Send button -->
                        <button type="submit"
                                :disabled="(!newMessage.trim() && !selectedFile) || sending"
                                class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center transition-all"
                                :class="(newMessage.trim() || selectedFile) && !sending 
                                    ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-500/30' 
                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-400 cursor-not-allowed'">
                            <svg x-show="!sending" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                            <svg x-show="sending" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </button>
                    </form>
                </div>
                
                <!-- Placeholder when no conversation selected (mobile input hidden) -->
                <div x-show="!selectedConversation" class="flex-shrink-0 md:hidden"></div>
            </div>
        </div>
        
        <!-- Delete Confirmation Modal -->
        <div x-show="showClearConfirmation"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="showClearConfirmation = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-sm w-full p-6"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="w-14 h-14 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white text-center mb-2">Delete Conversation?</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">All messages will be permanently deleted. This cannot be undone.</p>
                <div class="flex gap-3">
                    <button @click="showClearConfirmation = false"
                            class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button @click="confirmClearConversation()"
                            :disabled="clearing"
                            class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 disabled:opacity-50 transition-colors">
                        <span x-show="!clearing">Delete</span>
                        <svg x-show="clearing" class="w-5 h-5 mx-auto animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- FAQ Modal (Admin) -->
        @if(auth()->user()->role === 'admin')
        <div x-show="showFaqModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             x-transition
             @click.self="closeFaqModal()">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] flex flex-col overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white" x-text="editingFaq ? 'Edit FAQ' : 'Add New FAQ'"></h3>
                    <button @click="closeFaqModal()" class="text-white/80 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Question</label>
                            <input type="text" x-model="faqForm.question" 
                                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="Enter the FAQ question">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Answer</label>
                            <textarea x-model="faqForm.answer" rows="4"
                                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white resize-none"
                                      placeholder="Enter the FAQ answer"></textarea>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex gap-3">
                    <button @click="closeFaqModal()" class="flex-1 px-4 py-2.5 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                        Cancel
                    </button>
                    <button @click="saveFaq()" :disabled="savingFaq || !faqForm.question || !faqForm.answer"
                            class="flex-1 px-4 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 disabled:opacity-50 transition-colors">
                        <span x-show="!savingFaq" x-text="editingFaq ? 'Update' : 'Save'"></span>
                        <svg x-show="savingFaq" class="w-5 h-5 mx-auto animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <script>
        function messengerApp(initialConversations, currentUserId) {
            return {
                conversations: initialConversations,
                currentUserId: currentUserId,
                selectedConversation: null,
                messages: [],
                newMessage: '',
                searchQuery: '',
                showChat: false,
                loadingMessages: false,
                sending: false,
                error: '',
                pollHandle: null,
                showClearConfirmation: false,
                clearing: false,
                showFaqPanel: false,
                showFaqModal: false,
                editingFaq: null,
                savingFaq: false,
                faqForm: { question: '', answer: '' },
                allFaqs: [],
                selectedFile: null,
                selectedFileName: '',
                selectedFileSize: '',
                
                get filteredConversations() {
                    if (!this.searchQuery.trim()) return this.conversations;
                    const query = this.searchQuery.toLowerCase();
                    return this.conversations.filter(c => 
                        c.full_name.toLowerCase().includes(query) ||
                        (c.last_message && c.last_message.toLowerCase().includes(query))
                    );
                },
                
                init() {
                    this.loadFaqs();
                    
                    // Auto-select on desktop if there's a conversation
                    if (window.innerWidth >= 768 && this.conversations.length > 0) {
                        // Don't auto-select, let user choose
                    }
                    
                    // Start polling
                    this.pollHandle = setInterval(() => this.pollConversations(), 5000);
                    
                    // Cleanup on page unload
                    window.addEventListener('beforeunload', () => this.cleanup());
                },
                
                isSelected(conversation) {
                    return this.selectedConversation && this.selectedConversation.id === conversation.id;
                },
                
                async selectConversation(conversation) {
                    if (this.isSelected(conversation)) return;
                    
                    this.selectedConversation = conversation;
                    this.messages = [];
                    this.loadingMessages = true;
                    this.error = '';
                    this.showFaqPanel = false;
                    
                    try {
                        const response = await fetch(`/chat/messages/${conversation.id}`);
                        const data = await response.json();
                        
                        if (data.success) {
                            this.messages = data.messages;
                            
                            // Mark as read
                            const convIndex = this.conversations.findIndex(c => c.id === conversation.id);
                            if (convIndex !== -1) {
                                this.conversations[convIndex].unread_count = 0;
                            }
                            
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    } catch (error) {
                        console.error('Failed to load messages', error);
                        this.error = 'Failed to load messages';
                    } finally {
                        this.loadingMessages = false;
                    }
                },
                
                async sendMessage() {
                    if ((!this.newMessage.trim() && !this.selectedFile) || this.sending || !this.selectedConversation) return;
                    
                    this.sending = true;
                    this.error = '';
                    
                    const formData = new FormData();
                    formData.append('receiver_id', this.selectedConversation.id);
                    if (this.newMessage.trim()) {
                        formData.append('message', this.newMessage.trim());
                    }
                    if (this.selectedFile) {
                        formData.append('attachment', this.selectedFile);
                    }
                    
                    const messageText = this.newMessage;
                    this.newMessage = '';
                    
                    try {
                        const response = await fetch('/chat/send', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.messages.push(data.message);
                            this.clearFile();
                            
                            // Update conversation
                            const convIndex = this.conversations.findIndex(c => c.id === this.selectedConversation.id);
                            if (convIndex !== -1) {
                                this.conversations[convIndex].last_message = data.message.message || '📎 Attachment';
                                this.conversations[convIndex].last_message_at = data.message.created_at;
                            }
                            
                            this.scrollToBottom();
                        } else {
                            this.error = data.error || 'Failed to send message';
                            this.newMessage = messageText;
                        }
                    } catch (error) {
                        console.error('Failed to send message', error);
                        this.error = 'Failed to send message. Please try again.';
                        this.newMessage = messageText;
                    } finally {
                        this.sending = false;
                    }
                },
                
                async pollConversations() {
                    if (!this.selectedConversation) return;
                    
                    try {
                        const response = await fetch(`/chat/messages/${this.selectedConversation.id}`);
                        const data = await response.json();
                        
                        if (data.success && data.messages.length > this.messages.length) {
                            const newMessages = data.messages.slice(this.messages.length);
                            this.messages.push(...newMessages);
                            this.scrollToBottom();
                        }
                    } catch (error) {
                        // Silent fail for polling
                    }
                },
                
                async loadFaqs() {
                    try {
                        const response = await fetch('/chat/faqs');
                        const data = await response.json();
                        if (data.success) {
                            this.allFaqs = data.faqs;
                        }
                    } catch (error) {
                        console.error('Failed to load FAQs', error);
                    }
                },
                
                async sendFaqQuestion(type) {
                    if (!this.selectedConversation || this.sending) return;
                    
                    this.sending = true;
                    this.error = '';
                    
                    try {
                        const response = await fetch('/chat/send-faq', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                receiver_id: this.selectedConversation.id,
                                faq_type: type,
                            }),
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.messages.push(data.question_message);
                            this.scrollToBottom();
                            
                            await new Promise(r => setTimeout(r, 800));
                            
                            if (data.auto_reply) {
                                this.messages.push(data.auto_reply);
                                this.scrollToBottom();
                            }
                        }
                    } catch (error) {
                        console.error('Failed to send FAQ question', error);
                    } finally {
                        this.sending = false;
                    }
                },
                
                async sendFaqQuestionById(faqId) {
                    if (!this.selectedConversation || this.sending) return;
                    
                    this.sending = true;
                    
                    try {
                        const response = await fetch('/chat/send-faq', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                receiver_id: this.selectedConversation.id,
                                faq_id: faqId,
                            }),
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.messages.push(data.question_message);
                            this.scrollToBottom();
                            
                            await new Promise(r => setTimeout(r, 800));
                            
                            if (data.auto_reply) {
                                this.messages.push(data.auto_reply);
                                this.scrollToBottom();
                            }
                        }
                    } catch (error) {
                        console.error('Failed to send FAQ', error);
                    } finally {
                        this.sending = false;
                    }
                },
                
                async confirmClearConversation() {
                    if (!this.selectedConversation || this.clearing) return;
                    
                    this.clearing = true;
                    
                    try {
                        const response = await fetch(`/chat/clear/${this.selectedConversation.id}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.messages = [];
                            const convIndex = this.conversations.findIndex(c => c.id === this.selectedConversation.id);
                            if (convIndex !== -1) {
                                this.conversations[convIndex].last_message = '';
                                this.conversations[convIndex].last_message_at = null;
                            }
                            this.showClearConfirmation = false;
                        }
                    } catch (error) {
                        console.error('Failed to clear conversation', error);
                    } finally {
                        this.clearing = false;
                    }
                },
                
                openFaqModal() {
                    this.editingFaq = null;
                    this.faqForm = { question: '', answer: '' };
                    this.showFaqModal = true;
                },
                
                closeFaqModal() {
                    this.showFaqModal = false;
                    this.editingFaq = null;
                    this.faqForm = { question: '', answer: '' };
                },
                
                async saveFaq() {
                    if (!this.faqForm.question || !this.faqForm.answer || this.savingFaq) return;
                    
                    this.savingFaq = true;
                    
                    try {
                        const url = this.editingFaq ? `/chat/faqs/${this.editingFaq.id}` : '/chat/faqs';
                        const method = this.editingFaq ? 'PUT' : 'POST';
                        
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(this.faqForm),
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            await this.loadFaqs();
                            this.closeFaqModal();
                        }
                    } catch (error) {
                        console.error('Failed to save FAQ', error);
                    } finally {
                        this.savingFaq = false;
                    }
                },
                
                handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    
                    if (file.size > 10 * 1024 * 1024) {
                        this.error = 'File size must be less than 10MB';
                        return;
                    }
                    
                    this.selectedFile = file;
                    this.selectedFileName = file.name;
                    this.selectedFileSize = this.formatFileSize(file.size);
                    this.error = '';
                },
                
                clearFile() {
                    this.selectedFile = null;
                    this.selectedFileName = '';
                    this.selectedFileSize = '';
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },
                
                formatFileSize(bytes) {
                    if (!bytes) return '';
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(1024));
                    return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${sizes[i]}`;
                },
                
                formatListTime(timestamp) {
                    if (!timestamp) return '';
                    const date = new Date(timestamp);
                    if (isNaN(date.getTime())) return '';
                    
                    const now = new Date();
                    const isToday = date.toDateString() === now.toDateString();
                    
                    if (isToday) {
                        return date.toLocaleTimeString([], { hour: 'numeric', minute: 'numeric' });
                    }
                    
                    const diff = (now - date) / (1000 * 60 * 60 * 24);
                    if (diff < 7) {
                        return date.toLocaleDateString([], { weekday: 'short' });
                    }
                    
                    return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
                },
                
                formatMessageTime(timestamp) {
                    const date = new Date(timestamp);
                    if (isNaN(date.getTime())) return '';
                    return date.toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric' });
                },
                
                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.messagesContainer;
                        if (container) {
                            container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
                        }
                    });
                },
                
                cleanup() {
                    if (this.pollHandle) {
                        clearInterval(this.pollHandle);
                    }
                },
            };
        }
    </script>
</body>
</html>
