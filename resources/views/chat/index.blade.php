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

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ auth()->user()->role === 'requestor' ? 'Admin Messages' : 'Requestor Messages' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-2 sm:px-4">
            <div class="h-[calc(100vh-170px)] rounded-3xl bg-gradient-to-br from-[#f1e8ff] via-[#fff6df] to-[#f1e8ff] p-3 sm:p-5 shadow-[0_20px_60px_-35px_rgba(76,29,149,0.55)]"
                 x-data="supportMessenger({{ $conversationData->toJson() }}, {{ auth()->id() }})"
                 x-init="init()">
                <div class="flex h-full gap-3 lg:gap-6">
                    <!-- Conversations Panel -->
                    <div class="w-64 md:w-72 flex flex-col bg-white/80 backdrop-blur rounded-3xl shadow-lg border border-white/70 overflow-hidden">
                        <div class="bg-gradient-to-r from-[#6f48ff] to-[#8f63ff] px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="text-white">
                                    <p class="text-sm font-semibold">@if(auth()->user()->role === 'requestor') Admin Support @else Requestor Inbox @endif</p>
                                    
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto space-y-2 px-4 py-4 custom-scrollbar">
                            <template x-if="filteredConversations.length === 0">
                                <div class="text-center text-sm text-gray-500 pt-6">
                                    No conversations found
                                </div>
                            </template>

                            <template x-for="conversation in filteredConversations" :key="conversation.id">
                                <button type="button"
                                        @click="selectConversation(conversation)"
                                        :class="isSelected(conversation) ? 'bg-gradient-to-r from-[#7050ff] to-[#8d63ff] text-white shadow-xl' : 'bg-white/70 text-gray-700 border border-white/60 hover:bg-white'"
                                        class="w-full text-left rounded-2xl px-4 py-3 transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="relative">
                                            <img :src="conversation.profile_picture_url"
                                                 :alt="conversation.full_name"
                                                 class="w-10 h-10 rounded-full object-cover border-2"
                                                 :class="isSelected(conversation) ? 'border-white/80' : 'border-[#f3e9ff]'">
                                              <span x-show="conversation.unread_count > 0 && !isSelected(conversation)"
                                                  class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[14px] h-[14px] px-[3px] rounded-full text-[9px] font-semibold bg-red-600 text-white shadow ring-1 ring-white/70"
                                                  x-text="conversation.unread_count > 9 ? '9+' : conversation.unread_count"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="text-sm font-semibold truncate flex items-center gap-2">
                                                    <span x-text="conversation.full_name"></span>
                                                        <span x-show="conversation.message_count > 0" class="inline-flex items-center justify-center px-[3px] h-[14px] rounded-full text-[9px] font-semibold bg-gray-200 text-gray-700"
                                                          :class="isSelected(conversation) ? 'bg-white/25 text-white' : 'bg-gray-200 text-gray-700'"
                                                          x-text="conversation.message_count > 999 ? '999+' : conversation.message_count"></span>
                                                </p>
                                                <span class="text-xs" :class="isSelected(conversation) ? 'text-white/80' : 'text-gray-400'" x-text="formatListTime(conversation.last_message_at)"></span>
                                            </div>
                                            <p class="text-xs truncate mt-1" :class="isSelected(conversation) ? 'text-white/80' : 'text-gray-500'"
                                               x-text="conversation.last_message || 'No messages yet'"></p>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Chat Panel -->
                    <div class="flex-1 backdrop-blur rounded-3xl shadow-lg border overflow-hidden flex flex-col transition-colors duration-200"
                         :class="darkMode ? 'bg-gray-900/90 border-gray-700' : 'bg-white/70 border-white/60'">
                        <div class="px-5 sm:px-8 py-5 border-b transition-colors duration-200"
                             :class="darkMode ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200'">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <template x-if="selectedConversation">
                                        <img :src="selectedConversation.profile_picture_url"
                                             :alt="selectedConversation.full_name"
                                             class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                    </template>
                                    <p class="font-semibold transition-colors duration-200 flex items-center gap-2"
                                       :class="darkMode ? 'text-white' : 'text-gray-900'">
                                        <span x-text="selectedConversation ? selectedConversation.full_name : 'Admin Support Chat'"></span>
                                            <span x-show="selectedConversation"
                                                class="inline-flex items-center justify-center px-[3px] h-[14px] rounded-full text-[9px] font-semibold bg-gray-200 text-gray-700"
                                              :class="darkMode ? 'bg-gray-700 text-gray-200' : 'bg-gray-200 text-gray-700'"
                                              x-text="messages.length > 999 ? '999+' : messages.length"></span>
                                    </p>
                                </div>
                                <button type="button"
                                        x-show="selectedConversation"
                                        @click="showClearConfirmation = true"
                                        class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-full text-gray-600 hover:text-red-500 hover:bg-red-50 transition-all duration-200"
                                        title="Delete conversation">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex-1 overflow-hidden">
                            <div class="h-full overflow-y-auto px-4 sm:px-8 py-6 space-y-4 custom-scrollbar transition-colors duration-200"
                                 :class="darkMode ? 'bg-gray-900' : 'bg-transparent'"
                                 x-ref="messagesContainer">
                                <div x-show="!selectedConversation && !loadingMessages" class="flex h-full items-center justify-center text-center">
                                    <div>
                                        <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center mb-5 shadow-lg transition-colors duration-200"
                                             :class="darkMode ? 'bg-gradient-to-br from-purple-600 to-indigo-700 text-white' : 'bg-gradient-to-br from-[#7050ff] to-[#9679ff] text-white'">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-semibold mb-2 transition-colors duration-200"
                                            :class="darkMode ? 'text-white' : 'text-gray-700'">Admin Support Chat</h3>
                                        <p class="text-sm transition-colors duration-200"
                                           :class="darkMode ? 'text-gray-400' : 'text-gray-500'">Select a conversation from the left to view messages and continue the discussion.</p>
                                    </div>
                                </div>

                                <div x-show="loadingMessages" class="flex h-full items-center justify-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-12 h-12 border-4 rounded-full animate-spin transition-colors duration-200"
                                             :class="darkMode ? 'border-purple-900 border-t-purple-400' : 'border-purple-200 border-t-purple-600'"></div>
                                        <p class="text-sm transition-colors duration-200"
                                           :class="darkMode ? 'text-gray-400' : 'text-gray-500'">Loading conversation…</p>
                                    </div>
                                </div>

                                <div x-show="selectedConversation && !loadingMessages && messages.length === 0" class="flex h-full items-center justify-center text-center">
                                    <div class="max-w-md px-6">
                                        <div class="mb-6">
                                            <svg class="w-48 h-48 mx-auto" viewBox="0 0 200 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <!-- Mailbox base -->
                                                <rect x="60" y="80" width="80" height="60" rx="8" fill="#3B82F6" opacity="0.9"/>
                                                <rect x="90" y="80" width="80" height="60" rx="8" fill="#60A5FA"/>
                                                
                                                <!-- Mailbox flag -->
                                                <rect x="155" y="65" width="6" height="35" fill="#3B82F6"/>
                                                <rect x="155" y="65" width="25" height="15" rx="2" fill="#EF4444"/>
                                                
                                                <!-- Mailbox door line -->
                                                <line x1="100" y1="95" x2="160" y2="95" stroke="white" stroke-width="2" opacity="0.5"/>
                                                
                                                <!-- Bird nest -->
                                                <ellipse cx="100" cy="120" rx="35" ry="20" fill="#1E3A8A" opacity="0.8"/>
                                                <ellipse cx="100" cy="118" rx="32" ry="18" fill="#1E40AF"/>
                                                
                                                <!-- Baby birds -->
                                                <circle cx="90" cy="110" r="8" fill="white"/>
                                                <circle cx="110" cy="110" r="8" fill="white"/>
                                                <circle cx="88" cy="107" r="2" fill="#1F2937"/>
                                                <circle cx="112" cy="107" r="2" fill="#1F2937"/>
                                                
                                                <!-- Beaks -->
                                                <path d="M90 112 L88 115 L92 115 Z" fill="#F59E0B"/>
                                                <path d="M110 112 L108 115 L112 115 Z" fill="#F59E0B"/>
                                                
                                                <!-- Decorative sparkles -->
                                                <path d="M40 50 L42 54 L40 58 L38 54 Z" fill="#60A5FA" opacity="0.6"/>
                                                <path d="M170 40 L172 44 L170 48 L168 44 Z" fill="#60A5FA" opacity="0.6"/>
                                                <path d="M160 120 L162 124 L160 128 L158 124 Z" fill="#60A5FA" opacity="0.6"/>
                                                <path d="M50 100 L52 104 L50 108 L48 104 Z" fill="#60A5FA" opacity="0.6"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-2xl font-bold mb-3 transition-colors duration-200"
                                            :class="darkMode ? 'text-white' : 'text-gray-800'">No messages yet</h3>
                                        <p class="text-base transition-colors duration-200"
                                           :class="darkMode ? 'text-gray-400' : 'text-gray-600'"
                                           x-text="'{{ auth()->user()->role }}' === 'admin' ? 'Send a message to begin a conversation' : 'Send a message or select a quick question below'"></p>
                                        
                                        @if(auth()->user()->role === 'requestor')
                                        <!-- FAQ Quick Questions for Requestors -->
                                        <div class="mt-6 space-y-2">
                                            <p class="text-xs font-semibold uppercase tracking-wide mb-3"
                                               :class="darkMode ? 'text-gray-500' : 'text-gray-400'">Frequently Asked Questions</p>
                                            <button type="button" 
                                                    @click="sendFaqQuestion('advance_booking')"
                                                    class="w-full text-left px-4 py-3 rounded-xl border-2 transition-all duration-200 hover:scale-[1.02]"
                                                    :class="darkMode 
                                                        ? 'bg-gray-800 border-purple-500/30 hover:border-purple-400 text-gray-200' 
                                                        : 'bg-white border-purple-200 hover:border-purple-400 text-gray-700 hover:bg-purple-50'">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </span>
                                                    <span class="text-sm font-medium">How far in advance should I request a Mass or event?</span>
                                                </div>
                                            </button>
                                            <button type="button" 
                                                    @click="sendFaqQuestion('edit_cancel')"
                                                    class="w-full text-left px-4 py-3 rounded-xl border-2 transition-all duration-200 hover:scale-[1.02]"
                                                    :class="darkMode 
                                                        ? 'bg-gray-800 border-blue-500/30 hover:border-blue-400 text-gray-200' 
                                                        : 'bg-white border-blue-200 hover:border-blue-400 text-gray-700 hover:bg-blue-50'">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </span>
                                                    <span class="text-sm font-medium">Can I edit or cancel my reservation after submitting it?</span>
                                                </div>
                                            </button>
                                            <button type="button" 
                                                    @click="sendFaqQuestion('pending_contact')"
                                                    class="w-full text-left px-4 py-3 rounded-xl border-2 transition-all duration-200 hover:scale-[1.02]"
                                                    :class="darkMode 
                                                        ? 'bg-gray-800 border-green-500/30 hover:border-green-400 text-gray-200' 
                                                        : 'bg-white border-green-200 hover:border-green-400 text-gray-700 hover:bg-green-50'">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                        </svg>
                                                    </span>
                                                    <span class="text-sm font-medium">Who do I contact if my reservation is still pending?</span>
                                                </div>
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <template x-for="message in messages" :key="message.id">
                                    <div :class="message.sender_id === currentUserId ? 'flex justify-end' : 'flex justify-start'">
                                        <div class="flex items-start gap-2 max-w-[80%] md:max-w-[65%]">
                                            <div x-show="message.sender_id !== currentUserId" class="flex-shrink-0 mt-0.5">
                                                <img :src="message.sender.profile_picture"
                                                     :alt="message.sender.name"
                                                     class="w-7 h-7 rounded-full object-cover">
                                            </div>
                                            <div class="space-y-1">
                                                <div :class="[
                                                        message.is_image && !message.message
                                                            ? 'bg-transparent p-0'
                                                            : message.sender_id === currentUserId
                                                                ? (darkMode ? 'bg-purple-600 text-white' : 'bg-[#6f52ff] text-white')
                                                                : (darkMode ? 'bg-gray-700 text-gray-100' : 'bg-[#f0e8ff] text-[#3c2c6b]')
                                                    ]"
                                                     class="rounded-3xl rounded-br-md px-4 py-2.5 shadow-sm transition-colors duration-200">
                                                    <template x-if="message.attachment_url">
                                                        <div :class="message.is_image ? '' : 'mb-2'">
                                                            <div x-show="message.is_image" class="rounded-2xl overflow-hidden">
                                                                <a :href="message.attachment_url" target="_blank" class="block hover:opacity-90 transition-opacity">
                                                                    <img :src="message.attachment_url"
                                                                         :alt="message.attachment_name"
                                                                         class="max-w-full h-auto max-h-80 object-cover">
                                                                </a>
                                                            </div>
                                                            <a x-show="!message.is_image"
                                                               :href="message.attachment_url"
                                                               download
                                                               target="_blank"
                                                               class="flex items-center gap-3 px-3 py-2 rounded-2xl transition-colors duration-200"
                                                               :class="message.sender_id === currentUserId 
                                                                   ? 'bg-white/20 text-white' 
                                                                   : (darkMode ? 'bg-gray-600 text-purple-300' : 'bg-white text-[#6b4dff]')">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                                </svg>
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="text-sm font-medium truncate" x-text="message.attachment_name"></p>
                                                                    <p class="text-xs opacity-80" x-text="formatFileSize(message.attachment_size)"></p>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </template>

                                                    <p x-show="message.message" class="text-[15px] leading-relaxed whitespace-pre-wrap break-words" x-text="message.message"></p>
                                                    
                                                    <!-- Auto-Reply Badge -->
                                                    <div x-show="message.is_auto_reply" 
                                                         class="mt-2 pt-2 border-t flex items-center gap-1.5 text-xs"
                                                         :class="darkMode ? 'border-gray-600 text-gray-400' : 'border-purple-200 text-purple-600'">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <span class="font-medium">Automated FAQ Response</span>
                                                    </div>
                                                </div>
                                                <p class="text-xs transition-colors duration-200" 
                                                   :class="[
                                                       message.sender_id === currentUserId ? 'text-right' : 'text-left',
                                                       darkMode ? 'text-gray-500' : 'text-gray-400'
                                                   ]"
                                                   x-text="formatMessageTime(message.created_at)"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="border-t px-3 sm:px-6 py-4 transition-colors duration-200"
                             :class="darkMode ? 'border-gray-700 bg-gray-800/70' : 'border-white/50 bg-white/70'">
                            
                            @if(auth()->user()->role === 'requestor')
                            <!-- FAQ Quick Select (Collapsible) - Only show when conversation is selected -->
                            <div x-show="selectedConversation" class="max-w-3xl mx-auto mb-3">
                                <button type="button" 
                                        @click="showFaqPanel = !showFaqPanel"
                                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200"
                                        :class="darkMode 
                                            ? 'bg-gradient-to-r from-purple-900/50 to-indigo-900/50 text-purple-300 hover:from-purple-900/70 hover:to-indigo-900/70 border border-purple-700/50' 
                                            : 'bg-gradient-to-r from-purple-50 to-indigo-50 text-purple-700 hover:from-purple-100 hover:to-indigo-100 border border-purple-200'">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Quick FAQ Questions
                                    </span>
                                    <svg class="w-4 h-4 transition-transform duration-200" 
                                         :class="showFaqPanel ? 'rotate-180' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                
                                <div x-show="showFaqPanel" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-2"
                                     class="mt-2 p-3 rounded-xl border space-y-2"
                                     :class="darkMode 
                                         ? 'bg-gray-800/80 border-gray-700' 
                                         : 'bg-white/80 border-purple-100'">
                                    <button type="button" 
                                            @click="sendFaqQuestion('advance_booking'); showFaqPanel = false"
                                            class="w-full text-left px-3 py-2.5 rounded-lg text-sm transition-all duration-200 flex items-center gap-2"
                                            :class="darkMode 
                                                ? 'hover:bg-purple-900/40 text-gray-300 hover:text-purple-300' 
                                                : 'hover:bg-purple-50 text-gray-600 hover:text-purple-700'">
                                        <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="darkMode ? 'bg-purple-900/60 text-purple-400' : 'bg-purple-100 text-purple-600'">1</span>
                                        How far in advance should I request a Mass or event?
                                    </button>
                                    <button type="button" 
                                            @click="sendFaqQuestion('edit_cancel'); showFaqPanel = false"
                                            class="w-full text-left px-3 py-2.5 rounded-lg text-sm transition-all duration-200 flex items-center gap-2"
                                            :class="darkMode 
                                                ? 'hover:bg-blue-900/40 text-gray-300 hover:text-blue-300' 
                                                : 'hover:bg-blue-50 text-gray-600 hover:text-blue-700'">
                                        <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="darkMode ? 'bg-blue-900/60 text-blue-400' : 'bg-blue-100 text-blue-600'">2</span>
                                        Can I edit or cancel my reservation after submitting it?
                                    </button>
                                    <button type="button" 
                                            @click="sendFaqQuestion('pending_contact'); showFaqPanel = false"
                                            class="w-full text-left px-3 py-2.5 rounded-lg text-sm transition-all duration-200 flex items-center gap-2"
                                            :class="darkMode 
                                                ? 'hover:bg-green-900/40 text-gray-300 hover:text-green-300' 
                                                : 'hover:bg-green-50 text-gray-600 hover:text-green-700'">
                                        <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="darkMode ? 'bg-green-900/60 text-green-400' : 'bg-green-100 text-green-600'">3</span>
                                        Who do I contact if my reservation is still pending?
                                    </button>
                                </div>
                            </div>
                            @endif

                            <form @submit.prevent="sendMessage" class="max-w-3xl mx-auto">
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="$refs.fileInput.click()"
                                            class="flex-shrink-0 w-11 h-11 rounded-2xl flex items-center justify-center transition-all duration-200"
                                            :class="darkMode ? 'bg-gray-700 text-purple-400 hover:bg-gray-600' : 'bg-[#ede5ff] text-[#6f52ff] hover:bg-[#dfd4ff]'"
                                            title="Attach file">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>

                                    <input type="file"
                                           x-ref="fileInput"
                                           @change="handleFileUpload($event)"
                                           accept="image/*,application/pdf,.doc,.docx"
                                           class="hidden">

                                    <textarea x-model="newMessage"
                                              @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                                              @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 140) + 'px'"
                                              placeholder="Type a message…"
                                              rows="1"
                                              class="flex-1 rounded-2xl border focus:ring-2 text-sm px-4 py-3 resize-none transition-colors duration-200"
                                              :class="darkMode 
                                                  ? 'bg-gray-700 border-gray-600 focus:border-purple-500 focus:ring-purple-500/30 text-white placeholder-gray-400' 
                                                  : 'bg-white border-[#dacfff] focus:border-[#a68dff] focus:ring-[#a68dff]/30 text-gray-700 placeholder-gray-400'"
                                              style="min-height: 44px;"></textarea>

                                    <button type="submit"
                                            :disabled="(!newMessage.trim() && !selectedFile) || sending || !selectedConversation"
                                            :class="(newMessage.trim() || selectedFile) && !sending && selectedConversation 
                                                ? (darkMode ? 'bg-purple-600 hover:bg-purple-700 text-white' : 'bg-[#6f52ff] hover:bg-[#5c41ef] text-white')
                                                : (darkMode ? 'bg-gray-700 text-gray-500 cursor-not-allowed' : 'bg-[#e2d9ff] text-[#a69ae4] cursor-not-allowed')"
                                            class="flex-shrink-0 w-11 h-11 rounded-2xl flex items-center justify-center transition-all duration-200">
                                        <svg x-show="!sending" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                                        </svg>
                                        <svg x-show="sending" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div x-show="selectedFile" class="mt-3 p-3 rounded-2xl flex items-center justify-between text-sm transition-colors duration-200"
                                     :class="darkMode ? 'bg-gray-700 text-gray-200' : 'bg-[#f1e8ff] text-[#4a3bb7]'">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        <div>
                                            <p class="font-medium" x-text="selectedFileName"></p>
                                            <p class="text-xs transition-colors duration-200"
                                               :class="darkMode ? 'text-gray-400' : 'text-[#7a6cd1]'"
                                               x-text="selectedFileSize"></p>
                                        </div>
                                    </div>
                                    <button type="button"
                                            @click="clearFile()"
                                            class="p-1.5 rounded-full transition-colors duration-200"
                                            :class="darkMode ? 'hover:bg-gray-600' : 'hover:bg-white/60'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                <p x-show="error" class="mt-2 text-sm transition-colors duration-200"
                                   :class="darkMode ? 'text-red-400 bg-red-900/20 border border-red-800' : 'text-red-500'"
                                   x-text="error"></p>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Dialog -->
                <div x-show="showClearConfirmation"
                     x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                     @click.self="showClearConfirmation = false">
                    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 p-6 transform transition-all"
                         @click.stop>
                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mx-auto mb-4">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Delete All Messages?</h3>
                        <p class="text-sm text-gray-600 text-center mb-6">
                            Are you sure you want to delete all messages in this conversation? This action cannot be undone.
                        </p>
                        <div class="flex gap-3">
                            <button type="button"
                                    @click="showClearConfirmation = false"
                                    class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">
                                Cancel
                            </button>
                            <button type="button"
                                    @click="confirmClearConversation()"
                                    :disabled="clearing"
                                    class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                <span x-show="!clearing">Confirm</span>
                                <span x-show="clearing" class="flex items-center justify-center">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar {
            scrollbar-width: thin;
        }

        /* Light mode scrollbar */
        .custom-scrollbar:not(.dark-scrollbar) {
            scrollbar-color: rgba(111, 82, 255, 0.35) transparent;
        }

        .custom-scrollbar:not(.dark-scrollbar)::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar:not(.dark-scrollbar)::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar:not(.dark-scrollbar)::-webkit-scrollbar-thumb {
            background: rgba(111, 82, 255, 0.35);
            border-radius: 9999px;
        }

        .custom-scrollbar:not(.dark-scrollbar)::-webkit-scrollbar-thumb:hover {
            background: rgba(111, 82, 255, 0.6);
        }

        /* Dark mode scrollbar */
        [x-bind\:class*="darkMode"] .custom-scrollbar {
            scrollbar-color: rgba(147, 51, 234, 0.5) transparent;
        }

        [x-bind\:class*="darkMode"] .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        [x-bind\:class*="darkMode"] .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        [x-bind\:class*="darkMode"] .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(147, 51, 234, 0.5);
            border-radius: 9999px;
        }

        [x-bind\:class*="darkMode"] .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(147, 51, 234, 0.7);
        }
    </style>

    <script>
        function supportMessenger(conversations, currentUserId) {
            return {
                conversations,
                currentUserId,
                selectedConversation: null,
                messages: [],
                loadingMessages: false,
                sending: false,
                newMessage: '',
                selectedFile: null,
                selectedFileName: '',
                selectedFileSize: '',
                error: '',
                pollHandle: null,
                searchTerm: '',
                showClearConfirmation: false,
                clearing: false,
                darkMode: false,
                showFaqPanel: false,

                init() {
                    // Load dark mode preference from localStorage
                    this.darkMode = localStorage.getItem('chatDarkMode') === 'true';
                    window.addEventListener('beforeunload', () => this.cleanup());
                    
                    // Auto-select first conversation for requestors (they usually only chat with admin)
                    @if(auth()->user()->role === 'requestor')
                    if (this.conversations.length > 0) {
                        this.$nextTick(() => {
                            this.selectConversation(this.conversations[0]);
                        });
                    }
                    @endif
                },

                toggleDarkMode() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('chatDarkMode', this.darkMode);
                },

                get filteredConversations() {
                    if (!this.searchTerm.trim()) {
                        return this.conversations;
                    }

                    const term = this.searchTerm.trim().toLowerCase();
                    return this.conversations.filter(conversation => (
                        conversation.full_name.toLowerCase().includes(term)
                        || (conversation.last_message && conversation.last_message.toLowerCase().includes(term))
                    ));
                },

                isSelected(conversation) {
                    return this.selectedConversation && this.selectedConversation.id === conversation.id;
                },

                async selectConversation(conversation) {
                    if (this.selectedConversation && this.selectedConversation.id === conversation.id) {
                        return;
                    }

                    this.selectedConversation = conversation;
                    this.messages = [];
                    this.newMessage = '';
                    this.error = '';
                    this.clearFile();

                    this.loadingMessages = true;
                    await this.loadMessages();
                    this.loadingMessages = false;

                    this.markAsRead();
                    conversation.unread_count = 0;

                    this.scrollToBottom();

                    if (this.pollHandle) {
                        clearInterval(this.pollHandle);
                    }
                    this.pollHandle = setInterval(() => this.pollNewMessages(), 4000);
                },

                async loadMessages() {
                    if (!this.selectedConversation) return;

                    try {
                        const response = await fetch(`/chat/messages/${this.selectedConversation.id}?t=${Date.now()}`, { cache: 'no-store' });
                        const data = await response.json();
                        this.messages = data.messages || [];
                    } catch (error) {
                        console.error('Failed to load messages', error);
                        this.error = 'Unable to load messages right now.';
                    }
                },

                async pollNewMessages() {
                    if (!this.selectedConversation) return;

                    try {
                        const response = await fetch(`/chat/messages/${this.selectedConversation.id}?t=${Date.now()}`, { cache: 'no-store' });
                        const data = await response.json();

                        if (!Array.isArray(data.messages)) {
                            return;
                        }

                        const lastId = this.messages.length ? this.messages[this.messages.length - 1].id : 0;
                        const incoming = data.messages.filter(message => message.id > lastId);

                        if (incoming.length) {
                            this.messages.push(...incoming);
                            this.scrollToBottom();
                            this.markAsRead();
                        }
                    } catch (error) {
                        console.warn('Polling failed', error);
                    }
                },

                async markAsRead() {
                    if (!this.selectedConversation) return;
                    try {
                        await fetch(`/chat/mark-read/${this.selectedConversation.id}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });
                        // Immediately update global unread badge
                        try {
                            const res = await fetch(`/chat/unread/count?t=${Date.now()}`, { cache: 'no-store' });
                            const data = await res.json();
                            window.dispatchEvent(new CustomEvent('chat:unread-updated', { detail: { count: data.count } }));
                        } catch (_) {}
                    } catch (error) {
                        console.warn('Failed to mark messages as read', error);
                    }
                },

                async sendMessage() {
                    if (!this.selectedConversation || this.sending || (!this.newMessage.trim() && !this.selectedFile)) {
                        return;
                    }

                    this.sending = true;
                    this.error = '';

                    try {
                        const formData = new FormData();
                        formData.append('receiver_id', this.selectedConversation.id);
                        formData.append('message', this.newMessage);

                        if (this.selectedFile) {
                            formData.append('file', this.selectedFile);
                        }

                        const response = await fetch('/chat/send', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: formData,
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.messages.push(data.message);
                            this.newMessage = '';
                            this.clearFile();
                            this.scrollToBottom();

                            // Update list preview
                            this.selectedConversation.last_message = data.message.message || data.message.attachment_name;
                            this.selectedConversation.last_message_at = data.message.created_at;

                            const index = this.conversations.findIndex(conv => conv.id === this.selectedConversation.id);
                            if (index !== -1) {
                                this.conversations.splice(index, 1);
                                this.conversations.unshift(this.selectedConversation);
                            }
                        } else {
                            this.error = data.error || 'Failed to send message. Please try again.';
                        }
                    } catch (error) {
                        console.error('Failed to send message', error);
                        this.error = 'Failed to send message. Please try again.';
                    } finally {
                        this.sending = false;
                    }
                },

                // FAQ Auto-Reply System
                faqResponses: {
                    advance_booking: {
                        question: "How far in advance should I request a Mass or event?",
                        answer: "For the best availability, please send your reservation 2–4 weeks before your desired date. Requests made at least 7 days in advance allow smoother review by the adviser, administrator, and priest, but you may still contact Support through your dashboard for urgent intentions."
                    },
                    edit_cancel: {
                        question: "Can I edit or cancel my reservation after submitting it?",
                        answer: "Yes. You can update or cancel your reservation by going to My Reservations, opening the specific booking, and submitting a change or cancellation request. For events within 7 days, kindly coordinate directly with our staff so we can assist you properly."
                    },
                    pending_contact: {
                        question: "Who do I contact if my reservation is still pending?",
                        answer: "If your reservation is still pending, please message us through the Admin Support Chat in your dashboard. Our CREaM team usually responds within 72 hours and can provide an update, answer questions, and guide you on the next steps."
                    }
                },

                async sendFaqQuestion(faqKey) {
                    if (!this.selectedConversation || this.sending) return;
                    
                    const faq = this.faqResponses[faqKey];
                    if (!faq) return;

                    this.sending = true;
                    this.error = '';

                    try {
                        // Send both question and automated reply to database
                        const faqFormData = new FormData();
                        faqFormData.append('receiver_id', this.selectedConversation.id);
                        faqFormData.append('question', faq.question);
                        faqFormData.append('answer', faq.answer);

                        const response = await fetch('{{ route('chat.send-faq') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: faqFormData,
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Add question message
                            this.messages.push(data.question);
                            this.scrollToBottom();

                            // Update conversation preview with question
                            this.selectedConversation.last_message = data.question.message;
                            this.selectedConversation.last_message_at = data.question.created_at;

                            // Small delay before showing auto-reply (feels more natural)
                            await new Promise(resolve => setTimeout(resolve, 800));

                            // Add automated reply message
                            this.messages.push(data.auto_reply);
                            this.scrollToBottom();

                            // Update conversation preview with auto-reply
                            this.selectedConversation.last_message = '🤖 Automated FAQ Response';
                            this.selectedConversation.last_message_at = data.auto_reply.created_at;
                        } else {
                            this.error = data.error || 'Failed to send FAQ question. Please try again.';
                        }
                    } catch (error) {
                        console.error('Failed to send FAQ question', error);
                        this.error = 'Failed to send message. Please try again.';
                    } finally {
                        this.sending = false;
                    }
                },

                async confirmClearConversation() {
                    if (!this.selectedConversation || this.clearing) return;

                    this.clearing = true;
                    this.error = '';

                    try {
                        const response = await fetch(`/chat/clear/${this.selectedConversation.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();

                        if (data.success) {
                            // Clear messages locally
                            this.messages = [];
                            
                            // Update conversation in list
                            const convIndex = this.conversations.findIndex(c => c.id === this.selectedConversation.id);
                            if (convIndex !== -1) {
                                this.conversations[convIndex].last_message = '';
                                this.conversations[convIndex].last_message_at = null;
                                this.conversations[convIndex].unread_count = 0;
                            }

                            this.showClearConfirmation = false;
                            
                            // Show success feedback briefly
                            this.$nextTick(() => {
                                this.scrollToBottom();
                            });
                        } else {
                            this.error = data.error || 'Failed to clear conversation.';
                            this.showClearConfirmation = false;
                        }
                    } catch (error) {
                        console.error('Failed to clear conversation', error);
                        this.error = 'Failed to clear conversation. Please try again.';
                        this.showClearConfirmation = false;
                    } finally {
                        this.clearing = false;
                    }
                },

                handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const maxSize = 10 * 1024 * 1024; // 10MB
                    if (file.size > maxSize) {
                        this.error = 'File size must be less than 10MB.';
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
                    const fileInput = this.$refs.fileInput;
                    if (fileInput) {
                        fileInput.value = '';
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
                    if (Number.isNaN(date.getTime())) return '';

                    const now = new Date();
                    const isToday = date.toDateString() === now.toDateString();
                    const options = { hour: 'numeric', minute: 'numeric' };

                    if (isToday) {
                        return date.toLocaleTimeString([], options);
                    }

                    const diff = (now - date) / (1000 * 60 * 60 * 24);
                    if (diff < 7) {
                        return date.toLocaleDateString([], { weekday: 'short' });
                    }

                    return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
                },

                formatMessageTime(timestamp) {
                    const date = new Date(timestamp);
                    if (Number.isNaN(date.getTime())) return '';
                    return date.toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric' });
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.messagesContainer;
                        if (container) {
                            container.scrollTo({
                                top: container.scrollHeight,
                                behavior: 'smooth',
                            });
                        }
                    });
                },

                cleanup() {
                    if (this.pollHandle) {
                        clearInterval(this.pollHandle);
                        this.pollHandle = null;
                    }
                },
            };
        }
    </script>
</x-app-layout>
