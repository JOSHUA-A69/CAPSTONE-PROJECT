@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Storage;

    $conversationData = $conversations->map(function ($conversation) {
        return [
            'id' => $conversation->id,
            'first_name' => $conversation->first_name ?? 'User',
            'last_name' => $conversation->last_name ?? '',
            'full_name' => trim(($conversation->first_name ?? 'User') . ' ' . ($conversation->last_name ?? '')),
            'profile_picture_url' => (function() use ($conversation) {
                try {
                    if ($conversation->profile_picture) {
                        return Storage::url($conversation->profile_picture);
                    }
                } catch (\Throwable $e) {
                    return asset('images/default-avatar.svg');
                }
                return asset('images/default-avatar.svg');
            })(),
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

    <div class="py-4 sm:py-6">
        <div class="max-w-6xl mx-auto px-2 sm:px-4">
            <div class="h-[calc(100vh-140px)] sm:h-[calc(100vh-170px)] rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#f1e8ff] via-[#fff6df] to-[#f1e8ff] p-2 sm:p-3 md:p-5 shadow-[0_20px_60px_-35px_rgba(76,29,149,0.55)]"
                 x-data="supportMessenger({{ $conversationData->toJson() }}, {{ auth()->id() }})"
                 x-init="init()">
                <div class="flex h-full gap-2 sm:gap-3 lg:gap-6">
                    <!-- Conversations Panel - Hidden on mobile when chat is selected -->
                    <div class="flex flex-col bg-white/80 backdrop-blur rounded-2xl sm:rounded-3xl shadow-lg border border-white/70 overflow-hidden transition-all duration-300"
                         :class="selectedConversation ? 'hidden md:flex md:w-64 lg:w-72' : 'w-full md:w-64 lg:w-72'">
                        <div class="bg-gradient-to-r from-[#6f48ff] to-[#8f63ff] px-4 sm:px-6 py-4 sm:py-5">
                            <div class="flex items-center gap-3">
                                <div class="text-white">
                                    <p class="text-sm font-semibold">@if(auth()->user()->role === 'requestor') Admin Support @else Requestor Inbox @endif</p>

                                </div>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto space-y-2 px-3 sm:px-4 py-3 sm:py-4 custom-scrollbar">
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

                    <!-- Chat Panel - Full width on mobile when selected -->
                    <div class="flex-1 backdrop-blur rounded-2xl sm:rounded-3xl shadow-lg border overflow-hidden flex-col transition-all duration-300"
                         :class="[
                             darkMode ? 'bg-gray-900/90 border-gray-700' : 'bg-white/70 border-white/60',
                             selectedConversation ? 'flex' : 'hidden md:flex'
                         ]">
                        <div class="px-3 sm:px-5 md:px-8 py-3 sm:py-5 border-b transition-colors duration-200"
                             :class="darkMode ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200'">
                            <div class="flex items-center justify-between gap-2 sm:gap-3">
                                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                    <!-- Back button for mobile -->
                                    <button type="button"
                                            @click="selectedConversation = null; stopPolling()"
                                            class="md:hidden flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </button>
                                    <template x-if="selectedConversation">
                                        <img :src="selectedConversation.profile_picture_url"
                                             :alt="selectedConversation.full_name"
                                             class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover border border-gray-200 flex-shrink-0">
                                    </template>
                                    <p class="font-semibold transition-colors duration-200 flex items-center gap-2 truncate text-sm sm:text-base"
                                       :class="darkMode ? 'text-white' : 'text-gray-900'">
                                        <span class="truncate" x-text="selectedConversation ? selectedConversation.full_name : 'Admin Support Chat'"></span>
                                            <span x-show="selectedConversation"
                                                class="hidden sm:inline-flex items-center justify-center px-[3px] h-[14px] rounded-full text-[9px] font-semibold bg-gray-200 text-gray-700 flex-shrink-0"
                                              :class="darkMode ? 'bg-gray-700 text-gray-200' : 'bg-gray-200 text-gray-700'"
                                              x-text="messages.length > 999 ? '999+' : messages.length"></span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <!-- Add FAQ Button (Admin Only) -->
                                    @if(auth()->user()->role === 'admin')
                                    <button type="button"
                                            x-show="selectedConversation"
                                            @click="openFaqModal()"
                                            class="flex-shrink-0 w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-full text-[#7050ff] hover:text-white hover:bg-[#7050ff] transition-all duration-200"
                                            title="Add new FAQ">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                    @endif
                                    <button type="button"
                                            x-show="selectedConversation"
                                            @click="showClearConfirmation = true"
                                            class="flex-shrink-0 w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-full text-gray-600 hover:text-red-500 hover:bg-red-50 transition-all duration-200"
                                            title="Delete conversation">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 overflow-hidden">
                            <div class="h-full overflow-y-auto px-3 sm:px-4 md:px-8 py-4 sm:py-6 space-y-3 sm:space-y-4 custom-scrollbar transition-colors duration-200"
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
                                        <div class="flex items-start gap-2 max-w-[90%] sm:max-w-[80%] md:max-w-[65%]">
                                            <div x-show="message.sender_id !== currentUserId" class="flex-shrink-0 mt-0.5 hidden sm:block">
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
                                                     class="rounded-2xl sm:rounded-3xl rounded-br-md px-3 sm:px-4 py-2 sm:py-2.5 shadow-sm transition-colors duration-200">
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

                                                    <p x-show="message.message" class="text-sm sm:text-[15px] leading-relaxed whitespace-pre-wrap break-words" x-text="message.message"></p>

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

                        <div class="border-t px-2 sm:px-3 md:px-6 py-3 sm:py-4 transition-colors duration-200"
                             :class="darkMode ? 'border-gray-700 bg-gray-800/70' : 'border-white/50 bg-white/70'">

                            @if(auth()->user()->role === 'requestor')
                            <!-- FAQ Quick Select (Collapsible) - Only show when conversation is selected -->
                            <div x-show="selectedConversation" class="max-w-3xl mx-auto mb-2 sm:mb-3">
                                <button type="button"
                                        @click="showFaqPanel = !showFaqPanel"
                                        class="w-full flex items-center justify-between px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-xs sm:text-sm font-medium transition-all duration-200"
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
                                    <!-- Dynamic FAQs from Database -->
                                    <template x-for="(faq, index) in allFaqs" :key="faq.id">
                                        <div class="flex items-center gap-1">
                                            <button type="button"
                                                    @click="sendFaqQuestionById(faq.id); showFaqPanel = false"
                                                    class="flex-1 text-left px-3 py-2.5 rounded-lg text-sm transition-all duration-200 flex items-center gap-2"
                                                    :class="darkMode
                                                        ? 'hover:bg-purple-900/40 text-gray-300 hover:text-purple-300'
                                                        : 'hover:bg-purple-50 text-gray-600 hover:text-purple-700'">
                                                <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                                      :class="darkMode ? 'bg-purple-900/60 text-purple-400' : 'bg-purple-100 text-purple-600'"
                                                      x-text="index + 1"></span>
                                                <span x-text="faq.question" class="line-clamp-2"></span>
                                            </button>
                                            <!-- Delete FAQ Button (Admin Only) -->
                                            @if(auth()->user()->role === 'admin')
                                            <button type="button"
                                                    x-show="typeof faq.id === 'number'"
                                                    @click.stop="deleteFaq(faq.id)"
                                                    class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all duration-200"
                                                    title="Delete FAQ">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                            @endif
                                        </div>
                                    </template>
                                    <!-- No FAQs message -->
                                    <div x-show="allFaqs.length === 0" class="text-center py-4 text-gray-500 text-sm">
                                        No FAQ questions available.
                                    </div>
                                </div>
                            </div>
                            @endif

                            <form @submit.prevent="sendMessage" class="max-w-3xl mx-auto">
                                <div class="flex items-center gap-1.5 sm:gap-2">
                                    <button type="button"
                                            @click="$refs.fileInput.click()"
                                            class="flex-shrink-0 w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl flex items-center justify-center transition-all duration-200"
                                            :class="darkMode ? 'bg-gray-700 text-purple-400 hover:bg-gray-600' : 'bg-[#ede5ff] text-[#6f52ff] hover:bg-[#dfd4ff]'"
                                            title="Attach file">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                              class="flex-1 rounded-xl sm:rounded-2xl border focus:ring-2 text-sm px-3 sm:px-4 py-2.5 sm:py-3 resize-none transition-colors duration-200"
                                              :class="darkMode
                                                  ? 'bg-gray-700 border-gray-600 focus:border-purple-500 focus:ring-purple-500/30 text-white placeholder-gray-400'
                                                  : 'bg-white border-[#dacfff] focus:border-[#a68dff] focus:ring-[#a68dff]/30 text-gray-700 placeholder-gray-400'"
                                              style="min-height: 40px;"></textarea>

                                    <button type="submit"
                                            :disabled="(!newMessage.trim() && !selectedFile) || sending || !selectedConversation"
                                            :class="(newMessage.trim() || selectedFile) && !sending && selectedConversation
                                                ? (darkMode ? 'bg-purple-600 hover:bg-purple-700 text-white' : 'bg-[#6f52ff] hover:bg-[#5c41ef] text-white')
                                                : (darkMode ? 'bg-gray-700 text-gray-500 cursor-not-allowed' : 'bg-[#e2d9ff] text-[#a69ae4] cursor-not-allowed')"
                                            class="flex-shrink-0 w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl flex items-center justify-center transition-all duration-200">
                                        <svg x-show="!sending" class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                                        </svg>
                                        <svg x-show="sending" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div x-show="selectedFile" class="mt-2 sm:mt-3 p-2 sm:p-3 rounded-xl sm:rounded-2xl flex items-center justify-between text-sm transition-colors duration-200"
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

                <!-- FAQ Management Modal (Admin Only) - Enhanced Responsive -->
                @if(auth()->user()->role === 'admin')
                <div x-show="showFaqModal"
                     x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm overflow-y-auto"
                     @click.self="closeFaqModal()"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full sm:max-w-2xl sm:mx-4 mx-3 my-4 sm:my-8 overflow-hidden transform transition-all h-auto sm:max-h-[85vh] flex flex-col"
                         @click.stop
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95">
                        <!-- Modal Header -->
                        <div class="bg-gradient-to-r from-[#6f48ff] to-[#8f63ff] px-4 sm:px-6 py-3 sm:py-4 flex-shrink-0">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2 flex-1 pr-3">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="editingFaq ? 'Edit FAQ' : 'FAQ Management'" class="truncate"></span>
                                </h3>
                                <button type="button" @click="closeFaqModal()" class="text-white/80 hover:text-white transition flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <!-- Tabs with Enhanced Design -->
                            <div class="flex items-center gap-1 sm:gap-2 mt-3" x-show="!editingFaq">
                                <!-- Existing FAQs Tab -->
                                <button type="button"
                                        @click="faqModalTab = 'list'"
                                        class="flex-1 px-3 sm:px-4 py-2 rounded-t-lg text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-1.5"
                                        :class="faqModalTab === 'list'
                                            ? 'bg-white text-[#6f48ff] shadow-lg shadow-white/20'
                                            : 'bg-white/10 text-white/70 hover:bg-white/15 hover:text-white/90'">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h2m0 0h10a2 2 0 002-2V7a2 2 0 00-2-2h-2m0 0V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m0 0H9m0 0v10" />
                                    </svg>
                                    <span class="hidden sm:inline">Existing FAQs</span>
                                    <span class="sm:hidden">FAQs</span>
                                </button>

                                <!-- Add New FAQ Button - Prominent -->
                                <button type="button"
                                        @click="faqModalTab = 'create'"
                                        class="flex-1 px-3 sm:px-4 py-2 rounded-t-lg text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-1.5"
                                        :class="faqModalTab === 'create'
                                            ? 'bg-white text-[#6f48ff] shadow-lg shadow-white/20'
                                            : 'bg-white/10 text-white/70 hover:bg-white/15 hover:text-white/90'">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span class="hidden sm:inline">Add FAQ</span>
                                    <span class="sm:hidden">Add</span>
                                </button>
                            </div>
                        </div>

                        <!-- Modal Body -->
                        <div class="flex-1 overflow-y-auto min-h-[300px] sm:min-h-[350px]">
                            <!-- Error Message -->
                            <div x-show="faqFormError" x-cloak class="mx-3 sm:mx-6 mt-3 sm:mt-4 bg-red-50 border border-red-200 text-red-700 px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl text-xs sm:text-sm">
                                <span x-text="faqFormError"></span>
                            </div>

                            <!-- Existing FAQs List Tab -->
                            <div x-show="faqModalTab === 'list' && !editingFaq" class="p-3 sm:p-5">
                                <div x-show="dynamicFaqs.length === 0" class="text-center py-8 sm:py-10">
                                    <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 sm:mb-4 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 text-xs sm:text-sm">No FAQs created yet.</p>
                                    <button type="button" @click="faqModalTab = 'create'" class="mt-3 text-[#6f48ff] text-xs sm:text-sm font-semibold hover:underline">
                                        Create your first FAQ →
                                    </button>
                                </div>

                                <div x-show="dynamicFaqs.length > 0" class="space-y-2 sm:space-y-3 max-h-[400px] overflow-y-auto custom-scrollbar pr-1">
                                    <template x-for="(faq, index) in dynamicFaqs" :key="faq.id">
                                        <div class="bg-gray-50 rounded-lg sm:rounded-xl border border-gray-200 p-3 sm:p-4 hover:shadow-md transition-shadow duration-200">
                                            <!-- Title Row with Actions -->
                                            <div class="flex items-start justify-between gap-2 mb-2">
                                                <span class="font-semibold text-gray-900 text-xs sm:text-sm line-clamp-1" x-text="faq.title || 'Untitled'"></span>
                                                <div class="flex items-center gap-1 flex-shrink-0">
                                                    <button type="button" @click="startEditFaq(faq)" class="p-1 sm:p-1.5 text-gray-400 hover:text-[#6f48ff] rounded transition-colors flex-shrink-0" title="Edit">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                    <button type="button"
                                                            @click.stop.prevent="confirmDeleteFaq(faq)"
                                                            class="p-1 sm:p-1.5 text-gray-400 hover:text-red-500 rounded transition-colors flex-shrink-0"
                                                            title="Delete">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- Question -->
                                            <p class="text-xs sm:text-sm text-gray-800 mb-1.5" x-text="faq.question" class="line-clamp-2"></p>
                                            <!-- Response Preview -->
                                            <p class="text-xs text-gray-500 line-clamp-1" x-text="faq.response"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Create/Edit FAQ Form Tab -->
                            <div x-show="faqModalTab === 'create' || editingFaq" class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                                <!-- Back button when editing -->
                                <button x-show="editingFaq"
                                        type="button"
                                        @click="cancelEditFaq()"
                                        class="flex items-center gap-1 text-xs sm:text-sm text-gray-500 hover:text-[#6f48ff] transition mb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Back to list
                                </button>

                                <!-- FAQ Title (Optional) -->
                                <div>
                                    <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5 sm:mb-2">
                                        Short Title <span class="text-gray-400 font-normal text-xs">(optional)</span>
                                    </label>
                                    <input type="text"
                                           x-model="faqForm.title"
                                           maxlength="100"
                                           placeholder="e.g., Booking Advance"
                                           class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-[#7050ff]/30 focus:border-[#7050ff] transition text-xs sm:text-sm">
                                </div>

                                <!-- FAQ Question -->
                                <div>
                                    <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5 sm:mb-2">
                                        FAQ Question <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="faqForm.question"
                                              rows="2"
                                              maxlength="500"
                                              placeholder="Enter the question users will see..."
                                              class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-[#7050ff]/30 focus:border-[#7050ff] transition text-xs sm:text-sm resize-none"></textarea>
                                    <p class="text-xs text-gray-400 mt-1" x-text="(faqForm.question?.length || 0) + '/500'"></p>
                                </div>

                                <!-- Auto Response -->
                                <div>
                                    <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5 sm:mb-2">
                                        Auto Response <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="faqForm.response"
                                              rows="3"
                                              maxlength="2000"
                                              placeholder="Enter the automated response..."
                                              class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-[#7050ff]/30 focus:border-[#7050ff] transition text-xs sm:text-sm resize-none"></textarea>
                                    <p class="text-xs text-gray-400 mt-1" x-text="(faqForm.response?.length || 0) + '/2000'"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer - Enhanced Clean Design -->
                        <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gradient-to-t from-gray-50 to-white border-t border-gray-100 flex items-center justify-end gap-2 sm:gap-3 flex-shrink-0 flex-wrap">
                            <button type="button"
                                    x-show="faqModalTab === 'create' || editingFaq"
                                    @click="editingFaq ? updateFaq() : submitFaq()"
                                    :disabled="faqFormLoading || !faqForm.question?.trim() || !faqForm.response?.trim()"
                                    class="px-3 sm:px-5 py-2 sm:py-2.5 rounded-lg sm:rounded-xl bg-gradient-to-r from-[#6f48ff] to-[#8f63ff] text-white font-semibold text-xs sm:text-sm hover:from-[#5f38ef] hover:to-[#7f53ef] disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-md hover:shadow-lg shadow-purple-500/25 flex items-center justify-center gap-1.5 whitespace-nowrap">
                                <span x-show="!faqFormLoading" class="flex items-center gap-1.5">
                                    <svg x-show="!editingFaq" class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <svg x-show="editingFaq" class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span x-text="editingFaq ? 'Update' : 'Create'" class="hidden sm:inline"></span>
                                    <span x-text="editingFaq ? 'Update FAQ' : 'Create FAQ'" class="sm:hidden"></span>
                                </span>
                                <span x-show="faqFormLoading" class="flex items-center justify-center gap-1.5">
                                    <svg class="animate-spin h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="editingFaq ? 'Updating...' : 'Creating...'" class="hidden sm:inline"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Delete FAQ Confirmation Modal - Enhanced Responsive -->
                @if(auth()->user()->role === 'admin')
                <div x-show="showDeleteFaqConfirm"
                     x-cloak
                     class="fixed inset-0 flex items-center justify-center p-3 sm:p-4 bg-black/50 overflow-y-auto"
                     style="z-index: 9999 !important;"
                     @click.self="showDeleteFaqConfirm = false; faqToDelete = null;"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    <!-- Modal -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-sm p-6 sm:p-8 my-4"
                         @click.stop
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95">
                        <!-- Warning Icon -->
                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4 sm:mb-5">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>

                        <!-- Title -->
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 text-center mb-2">Delete FAQ?</h3>

                        <!-- Description -->
                        <p class="text-xs sm:text-sm text-gray-500 text-center mb-5 sm:mb-6">
                            Are you sure you want to delete this FAQ? This action cannot be undone.
                        </p>

                        <!-- FAQ Preview -->
                        <div x-show="faqToDelete" class="mb-5 sm:mb-6 p-3 bg-gray-50 rounded-lg sm:rounded-xl border border-gray-200">
                            <p class="text-xs sm:text-sm text-gray-700 font-medium line-clamp-3" x-text="faqToDelete?.question"></p>
                        </div>

                        <!-- Buttons - Clean Enhanced Design -->
                        <div class="flex gap-2 sm:gap-3 flex-col-reverse sm:flex-row">
                            <button type="button"
                                    @click="showDeleteFaqConfirm = false; faqToDelete = null;"
                                    class="flex-1 px-4 sm:px-5 py-2.5 sm:py-3 border-2 border-gray-200 text-gray-700 font-semibold text-xs sm:text-sm rounded-lg sm:rounded-xl hover:border-gray-300 hover:bg-gray-50 transition-all duration-200 flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Cancel
                            </button>
                            <button type="button"
                                    @click="executeDeleteFaq()"
                                    :disabled="faqFormLoading"
                                    class="flex-1 px-4 sm:px-5 py-2.5 sm:py-3 bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold text-xs sm:text-sm rounded-lg sm:rounded-xl hover:from-red-600 hover:to-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center gap-1.5 shadow-md hover:shadow-lg shadow-red-500/25">
                                <svg x-show="!faqFormLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <svg x-show="faqFormLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="faqFormLoading ? 'Deleting...' : 'Delete'"></span>
                            </button>
                        </div>
                    </div>
                </div>
                @endif
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
                // FAQ Management (Admin only)
                showFaqModal: false,
                faqFormLoading: false,
                faqFormError: '',
                faqForm: {
                    id: null,
                    title: '',
                    question: '',
                    response: ''
                },
                dynamicFaqs: [],
                faqsLoaded: false,
                faqModalTab: 'list',
                editingFaq: null,
                showDeleteFaqConfirm: false,
                faqToDelete: null,

                init() {
                    // Load dark mode preference from localStorage
                    this.darkMode = localStorage.getItem('chatDarkMode') === 'true';
                    window.addEventListener('beforeunload', () => this.cleanup());

                    // Load dynamic FAQs from database
                    this.loadFaqs();

                    // Auto-select first conversation for requestors (they usually only chat with admin)
                    @if(auth()->user()->role === 'requestor')
                    if (this.conversations.length > 0) {
                        this.$nextTick(() => {
                            this.selectConversation(this.conversations[0]);
                        });
                    }
                    @endif
                },

                async loadFaqs() {
                    try {
                        const response = await fetch('/faqs');
                        const data = await response.json();
                        if (data.success && data.faqs) {
                            this.dynamicFaqs = data.faqs;
                            this.faqsLoaded = true;
                        }
                    } catch (error) {
                        console.warn('Failed to load FAQs', error);
                    }
                },

                openFaqModal() {
                    this.faqForm = { id: null, title: '', question: '', response: '' };
                    this.faqFormError = '';
                    this.editingFaq = null;
                    this.faqModalTab = 'list';
                    this.showFaqModal = true;
                },

                closeFaqModal() {
                    this.showFaqModal = false;
                    this.faqFormError = '';
                    this.editingFaq = null;
                    this.faqToDelete = null;
                    this.showDeleteFaqConfirm = false;
                },

                startEditFaq(faq) {
                    this.editingFaq = faq;
                    this.faqForm = {
                        id: faq.id,
                        title: faq.title || '',
                        question: faq.question,
                        response: faq.response
                    };
                    this.faqFormError = '';
                },

                cancelEditFaq() {
                    this.editingFaq = null;
                    this.faqForm = { id: null, title: '', question: '', response: '' };
                    this.faqFormError = '';
                    this.faqModalTab = 'list';
                },

                async deleteFaqWithConfirm(faq) {
                    // Show native confirmation dialog
                    const confirmed = confirm(`Delete FAQ?\n\nAre you sure you want to delete this FAQ?\n\n"${faq.question}"\n\nThis action cannot be undone.`);

                    if (!confirmed) return;

                    // Proceed with deletion
                    this.faqFormLoading = true;
                    this.faqFormError = '';

                    try {
                        const response = await fetch(`/admin/faqs/${faq.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Remove from list
                            this.dynamicFaqs = this.dynamicFaqs.filter(f => f.id !== faq.id);
                            // Show success (optional - could add a toast notification)
                        } else {
                            alert('Failed to delete FAQ: ' + (data.error || 'Unknown error'));
                            this.faqFormError = data.error || 'Failed to delete FAQ.';
                        }
                    } catch (error) {
                        console.error('Failed to delete FAQ', error);
                        alert('An error occurred while deleting the FAQ. Please try again.');
                        this.faqFormError = 'An error occurred. Please try again.';
                    } finally {
                        this.faqFormLoading = false;
                    }
                },

                confirmDeleteFaq(faq) {
                    this.faqToDelete = faq;
                    this.showDeleteFaqConfirm = true;
                },

                async executeDeleteFaq() {
                    if (!this.faqToDelete) return;

                    this.faqFormLoading = true;
                    this.faqFormError = '';
                    const faqId = this.faqToDelete.id;

                    try {
                        const response = await fetch(`/admin/faqs/${faqId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.dynamicFaqs = this.dynamicFaqs.filter(f => f.id !== faqId);
                            this.showDeleteFaqConfirm = false;
                            this.faqToDelete = null;
                        } else {
                            this.faqFormError = data.error || 'Failed to delete FAQ.';
                            this.showDeleteFaqConfirm = false;
                            this.faqToDelete = null;
                        }
                    } catch (error) {
                        console.error('Failed to delete FAQ', error);
                        this.faqFormError = 'An error occurred. Please try again.';
                        this.showDeleteFaqConfirm = false;
                        this.faqToDelete = null;
                    } finally {
                        this.faqFormLoading = false;
                    }
                },

                async updateFaq() {
                    if (!this.editingFaq || !this.faqForm.question?.trim() || !this.faqForm.response?.trim()) {
                        this.faqFormError = 'Question and response are required.';
                        return;
                    }

                    this.faqFormLoading = true;
                    this.faqFormError = '';

                    try {
                        const response = await fetch(`/admin/faqs/${this.editingFaq.id}`, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                title: this.faqForm.title?.trim() || '',
                                question: this.faqForm.question.trim(),
                                response: this.faqForm.response.trim(),
                            }),
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Update in the list
                            const index = this.dynamicFaqs.findIndex(f => f.id === this.editingFaq.id);
                            if (index !== -1) {
                                this.dynamicFaqs[index] = data.faq;
                            }
                            this.cancelEditFaq();
                        } else {
                            this.faqFormError = data.error || 'Failed to update FAQ.';
                        }
                    } catch (error) {
                        console.error('Failed to update FAQ', error);
                        this.faqFormError = 'An error occurred. Please try again.';
                    } finally {
                        this.faqFormLoading = false;
                    }
                },

                async submitFaq() {
                    if (!this.faqForm.question?.trim() || !this.faqForm.response?.trim()) {
                        this.faqFormError = 'Question and response are required.';
                        return;
                    }

                    this.faqFormLoading = true;
                    this.faqFormError = '';

                    try {
                        const formData = new FormData();
                        formData.append('title', this.faqForm.title?.trim() || '');
                        formData.append('question', this.faqForm.question.trim());
                        formData.append('response', this.faqForm.response.trim());

                        const response = await fetch('/admin/faqs', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: formData,
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Add to dynamic FAQs
                            this.dynamicFaqs.push(data.faq);
                            // Reset form and go back to list
                            this.faqForm = { id: null, title: '', question: '', response: '' };
                            this.faqModalTab = 'list';
                        } else {
                            this.faqFormError = data.error || 'Failed to create FAQ.';
                        }
                    } catch (error) {
                        console.error('Failed to submit FAQ', error);
                        this.faqFormError = 'An error occurred. Please try again.';
                    } finally {
                        this.faqFormLoading = false;
                    }
                },

                async deleteFaq(faqId) {
                    if (!confirm('Are you sure you want to delete this FAQ?')) return;

                    try {
                        const response = await fetch(`/admin/faqs/${faqId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.dynamicFaqs = this.dynamicFaqs.filter(f => f.id !== faqId);
                        }
                    } catch (error) {
                        console.error('Failed to delete FAQ', error);
                    }
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
                    this.pollHandle = setInterval(() => this.pollNewMessages(), 30000);
                },

                async loadMessages() {
                    if (!this.selectedConversation) return;

                    try {
                        const response = await fetch(`/chat/messages/${this.selectedConversation.id}?t=${Date.now()}`, { cache: 'no-store' });
                        const data = await response.json();
                        // Sort by ID to ensure correct chronological order
                        this.messages = (data.messages || []).sort((a, b) => a.id - b.id);
                    } catch (error) {
                        console.error('Failed to load messages', error);
                        this.error = 'Unable to load messages right now.';
                    }
                },

                async pollNewMessages() {
                    if (!this.selectedConversation) return;
                    // Skip polling while sending (prevents race conditions with FAQ)
                    if (this.sending) return;

                    try {
                        const response = await fetch(`/chat/messages/${this.selectedConversation.id}?t=${Date.now()}`, { cache: 'no-store' });
                        const data = await response.json();

                        if (!Array.isArray(data.messages)) {
                            return;
                        }

                        // Get existing message IDs to avoid duplicates
                        const existingIds = new Set(this.messages.map(m => m.id));
                        const incoming = data.messages.filter(message => !existingIds.has(message.id));

                        if (incoming.length) {
                            this.messages.push(...incoming);
                            // Sort by ID to ensure correct chronological order
                            this.messages.sort((a, b) => a.id - b.id);
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

                // FAQ Auto-Reply System - Default fallback FAQs
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

                // Get combined FAQs (dynamic from DB + fallback defaults)
                get allFaqs() {
                    // If we have dynamic FAQs from DB, use those; otherwise use defaults
                    if (this.dynamicFaqs.length > 0) {
                        return this.dynamicFaqs;
                    }
                    // Convert default faqResponses to array format
                    return Object.entries(this.faqResponses).map(([key, value]) => ({
                        id: key,
                        title: key.replace('_', ' '),
                        question: value.question,
                        response: value.answer
                    }));
                },

                async sendFaqQuestionById(faqId) {
                    if (!this.selectedConversation || this.sending) return;

                    // Find FAQ by ID (could be dynamic or default key)
                    let faq = this.dynamicFaqs.find(f => f.id === faqId);
                    if (!faq && this.faqResponses[faqId]) {
                        faq = {
                            question: this.faqResponses[faqId].question,
                            response: this.faqResponses[faqId].answer
                        };
                    }
                    if (!faq) return;

                    this.sending = true;
                    this.error = '';

                    try {
                        const faqFormData = new FormData();
                        faqFormData.append('receiver_id', this.selectedConversation.id);
                        faqFormData.append('question', faq.question);
                        faqFormData.append('answer', faq.response);

                        const response = await fetch('{{ route('chat.send-faq') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: faqFormData,
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Add both messages immediately for faster response
                            this.messages.push(data.question);
                            this.messages.push(data.auto_reply);
                            this.scrollToBottom();

                            // Update conversation preview with auto-reply
                            this.selectedConversation.last_message = '🤖 Automated FAQ Response';
                            this.selectedConversation.last_message_at = data.auto_reply.created_at;
                        } else {
                            this.error = data.error || 'Failed to send FAQ question.';
                        }
                    } catch (error) {
                        console.error('Failed to send FAQ question', error);
                        this.error = 'Failed to send message. Please try again.';
                    } finally {
                        this.sending = false;
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
                            // Add both messages immediately for faster response
                            this.messages.push(data.question);
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
                            method: 'POST',
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
