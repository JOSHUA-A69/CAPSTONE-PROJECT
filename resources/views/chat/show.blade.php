<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between bg-gradient-to-r from-slate-50 to-blue-50 dark:from-gray-900 dark:to-gray-800 -mx-4 -my-2 px-6 py-4 shadow-sm">
            <div class="flex items-center space-x-4">
                <a href="{{ route('chat.index') }}"
                   class="group p-2.5 text-slate-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-white dark:hover:bg-gray-700 rounded-xl transition-all duration-300 shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div class="h-12 w-px bg-gradient-to-b from-transparent via-slate-300 to-transparent dark:via-gray-600"></div>
                <div class="relative">
                    <img src="{{ $otherUser->profile_picture_url }}"
                         alt="{{ $otherUser->first_name }}"
                         class="w-9 h-9 rounded-full object-cover ring-2 ring-blue-500 ring-offset-2 ring-offset-slate-50 dark:ring-offset-gray-900 shadow-lg">
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-white dark:border-gray-900 rounded-full shadow-sm"></span>
                </div>
                <div>
                    <h2 class="font-bold text-lg text-slate-800 dark:text-white tracking-tight">
                        {{ $otherUser->first_name }} {{ $otherUser->last_name }}
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-gray-400 font-medium mt-0.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span>
                            {{ ucfirst($otherUser->role) }} • Active now
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-gray-100 dark:bg-gray-950 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Modern Chat Container with Solid Background -->
            <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700 flex flex-col"
                 style="height: calc(100vh - 200px);"
                 x-data="chatApp({{ $otherUser->id }}, {{ auth()->id() }})"
                 x-init="init()">

                <!-- Decorative gradient overlay -->
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>

                <!-- Messages Container with Solid Background -->
                <div class="flex-1 overflow-y-auto p-6 sm:p-8 space-y-6 bg-gray-50 dark:bg-gray-900"
                     x-ref="messagesContainer"
                     @scroll="handleScroll">

                    <!-- Enhanced Loading State -->
                    <div x-show="loading" class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <div class="relative">
                                <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 animate-spin mx-auto mb-4 flex items-center justify-center">
                                    <div class="w-16 h-16 rounded-full bg-white dark:bg-gray-900"></div>
                                </div>
                                <svg class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-700 dark:text-gray-300">Loading conversation...</p>
                            <p class="text-xs text-slate-500 dark:text-gray-500 mt-1">Please wait</p>
                        </div>
                    </div>

                    <!-- Premium Message Bubbles -->
                    <template x-for="message in messages" :key="message.id">
                        <div :class="message.sender_id === currentUserId ? 'flex justify-end' : 'flex justify-start'" class="animate-slideIn">
                            <div class="flex items-end space-x-3 max-w-[80%] md:max-w-[65%]">
                                <!-- Profile Picture (other user) - Enhanced -->
                                <div x-show="message.sender_id !== currentUserId" class="flex-shrink-0 mb-6 relative group">
                                    <img :src="message.sender.profile_picture"
                                         :alt="message.sender.name"
                                         class="w-8 h-8 rounded-full object-cover ring-2 ring-white dark:ring-gray-800 shadow-lg group-hover:ring-blue-400 transition-all duration-300">
                                </div>

                                <!-- Message Content with Modern Design -->
                                <div :class="message.sender_id === currentUserId ? 'order-2' : 'order-1'" class="flex-1">
                                    <!-- Message Bubble with Premium Styling -->
                                    <div :class="[
                                            message.is_image && !message.message
                                                ? 'bg-transparent shadow-none p-0'
                                                : message.sender_id === currentUserId
                                                    ? 'bg-blue-600 text-white rounded-3xl rounded-br-md shadow-xl hover:shadow-2xl px-6 py-4'
                                                    : 'bg-white dark:bg-gray-700 text-black dark:text-gray-100 rounded-3xl rounded-bl-md shadow-lg hover:shadow-xl border border-gray-200 dark:border-gray-600 px-6 py-4'
                                        ]"
                                         class="relative group transition-all duration-300">

                                        <!-- Attachment (if exists) -->
                                        <template x-if="message.attachment_url">
                                            <div :class="message.is_image ? '' : 'mb-3'">
                                                <!-- Image Attachment -->
                                                <div x-show="message.is_image" class="rounded-lg overflow-hidden shadow-md">
                                                    <a :href="message.attachment_url" target="_blank" class="block">
                                                        <img :src="message.attachment_url"
                                                             :alt="message.attachment_name"
                                                             class="max-w-full h-auto max-h-80 object-cover hover:opacity-95 transition-opacity">
                                                    </a>
                                                </div>

                                                <!-- File Attachment (non-image) -->
                                                <a x-show="!message.is_image"
                                                   :href="message.attachment_url"
                                                   download
                                                   target="_blank"
                                                   class="flex items-center gap-3 p-3 rounded-lg transition-colors"
                                                   :class="message.sender_id === currentUserId
                                                       ? 'bg-blue-700 hover:bg-blue-800'
                                                       : 'bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500'">
                                                    <svg class="w-8 h-8" :class="message.sender_id === currentUserId ? 'text-white' : 'text-blue-600 dark:text-blue-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium truncate"
                                                           :class="message.sender_id === currentUserId ? 'text-white' : 'text-gray-900 dark:text-white'"
                                                           x-text="message.attachment_name"></p>
                                                        <p class="text-xs"
                                                           :class="message.sender_id === currentUserId ? 'text-blue-100' : 'text-gray-500 dark:text-gray-400'"
                                                           x-text="formatFileSize(message.attachment_size)"></p>
                                                    </div>
                                                    <svg class="w-5 h-5" :class="message.sender_id === currentUserId ? 'text-white' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </template>

                                        <!-- Message text -->
                                        <p x-show="message.message" class="text-[15px] leading-relaxed whitespace-pre-wrap break-words font-medium"
                                           :class="message.sender_id === currentUserId ? '!text-white' : '!text-black dark:!text-gray-100'"
                                           x-text="message.message"></p>

                                        <!-- Subtle decoration for sender -->
                                        <div x-show="message.sender_id === currentUserId"
                                             class="absolute -bottom-1 -right-1 w-4 h-4 bg-blue-700 rounded-full opacity-20"></div>
                                    </div>

                                    <!-- Timestamp & Status with Better Design -->
                                    <div :class="message.sender_id === currentUserId ? 'text-right' : 'text-left'"
                                         class="mt-2 px-4 flex items-center gap-1.5"
                                         :class="message.sender_id === currentUserId ? 'justify-end' : 'justify-start'">
                                        <span class="text-[11px] text-slate-500 dark:text-gray-500 font-semibold tracking-wide"
                                              x-text="formatTime(message.created_at)"></span>
                                        <template x-if="message.sender_id === currentUserId">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path>
                                                </svg>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Profile Picture (current user) - Enhanced -->
                                <div x-show="message.sender_id === currentUserId" class="flex-shrink-0 mb-6 order-3 relative group">
                                    <img :src="'{{ auth()->user()->profile_picture_url }}'"
                                         alt="{{ auth()->user()->first_name }}"
                                         class="w-8 h-8 rounded-full object-cover ring-2 ring-blue-500 ring-offset-2 ring-offset-white dark:ring-offset-gray-800 shadow-xl group-hover:ring-indigo-500 transition-all duration-300">
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Premium Empty State -->
                    <div x-show="!loading && messages.length === 0" class="flex items-center justify-center h-full">
                        <div class="text-center max-w-md">
                            <div class="relative mb-6">
                                <div class="w-28 h-28 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 rounded-3xl flex items-center justify-center mx-auto shadow-2xl transform rotate-3 hover:rotate-6 transition-transform duration-500">
                                    <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <!-- Decorative elements -->
                                <div class="absolute top-0 right-8 w-3 h-3 bg-blue-400 rounded-full animate-ping"></div>
                                <div class="absolute bottom-2 left-8 w-2 h-2 bg-purple-400 rounded-full animate-pulse"></div>
                            </div>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white mb-3 tracking-tight">No messages yet</h3>
                            <p class="text-sm text-slate-600 dark:text-gray-400 leading-relaxed max-w-sm mx-auto">
                                Start your conversation by sending a message below.
                                <span class="block mt-2 text-blue-600 dark:text-blue-400 font-medium">Let's break the ice! 💬</span>
                            </p>
                        </div>
                    </div>

                    <!-- Premium Typing Indicator -->
                    <div x-show="isTyping" class="flex justify-start animate-slideIn">
                        <div class="flex items-end space-x-3">
                            <div class="relative">
                                <img src="{{ $otherUser->profile_picture_url }}"
                                     alt="{{ $otherUser->first_name }}"
                                     class="w-8 h-8 rounded-full object-cover ring-2 ring-white dark:ring-gray-800 shadow-lg">
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-blue-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
                            </div>
                            <div class="bg-white dark:bg-gray-700 rounded-3xl rounded-bl-md shadow-xl border border-slate-100 dark:border-gray-600 px-7 py-5">
                                <div class="flex space-x-2">
                                    <div class="w-2.5 h-2.5 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                    <div class="w-2.5 h-2.5 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                    <div class="w-2.5 h-2.5 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Premium Input Area -->
                <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-5 sm:p-6">
                    <form @submit.prevent="sendMessage">
                        <!-- Premium Message Input with Upload and Send Buttons - Aligned Horizontally -->
                        <div class="flex items-end gap-3">
                            <!-- Upload Button - Left Side -->
                            <button type="button"
                                    @click="$refs.fileInput.click()"
                                    class="flex-shrink-0 p-4 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-full transition-all duration-200 shadow-md hover:shadow-lg hover:scale-105 flex items-center justify-center"
                                    style="width: 56px; height: 56px; margin-bottom: 0;">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                            </button>

                            <!-- Hidden File Input -->
                            <input type="file"
                                   x-ref="fileInput"
                                   @change="handleFileUpload($event)"
                                   accept="image/*,application/pdf,.doc,.docx"
                                   class="hidden">

                            <textarea x-model="newMessage"
                                      @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                                      @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                      placeholder="Type your message..."
                                      rows="1"
                                      class="flex-1 px-5 py-4 border-2 border-blue-500 dark:border-gray-600 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 resize-none transition-all duration-300 text-[15px] font-normal shadow-sm focus:shadow-lg"
                                      style="min-height: 56px; max-height: 150px;"></textarea>

                            <!-- Send Button - Right Side -->
                            <button type="submit"
                                    :disabled="(!newMessage.trim() && !selectedFile) || sending"
                                    :class="(newMessage.trim() || selectedFile) && !sending
                                        ? 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800'
                                        : 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed'"
                                    class="flex-shrink-0 p-4 text-white rounded-full transition-all duration-200 shadow-xl hover:shadow-2xl hover:scale-105 flex items-center justify-center"
                                    style="width: 56px; height: 56px; margin-bottom: 0;">
                                <svg x-show="!sending" class="w-6 h-6 transform rotate-45" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                                </svg>
                                <svg x-show="sending" class="animate-spin w-6 h-6" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- File Preview Area -->
                        <div x-show="selectedFile" class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="selectedFileName"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="selectedFileSize"></p>
                                </div>
                            </div>
                            <button type="button"
                                    @click="clearFile()"
                                    class="p-1 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </form>

                    <!-- Premium Error Message -->
                    <div x-show="error"
                         x-text="error"
                         class="mt-4 text-sm text-red-600 dark:text-red-400 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 px-5 py-3 rounded-xl border border-red-200 dark:border-red-800 shadow-sm flex items-center">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Premium Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }

        .animate-slideIn {
            animation: slideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }

        /* Premium Custom Scrollbar */
        [x-ref="messagesContainer"] {
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 transparent;
        }

        [x-ref="messagesContainer"]::-webkit-scrollbar {
            width: 8px;
        }

        [x-ref="messagesContainer"]::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 10px;
        }

        [x-ref="messagesContainer"]::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #94a3b8 0%, #64748b 100%);
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .dark [x-ref="messagesContainer"]::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #475569 0%, #334155 100%);
        }

        [x-ref="messagesContainer"]::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #64748b 0%, #475569 100%);
        }

        .dark [x-ref="messagesContainer"]::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #334155 0%, #1e293b 100%);
        }

        /* Glassmorphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .dark .glass-effect {
            background: rgba(31, 41, 55, 0.8);
        }

        /* Smooth transitions for all interactive elements */
        button, a, input, textarea {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Premium focus styles */
        textarea:focus, input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Loading shimmer effect */
        .shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }

        .dark .shimmer {
            background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
            background-size: 1000px 100%;
        }
    </style>

    <script>
        function chatApp(otherUserId, currentUserId) {
            return {
                messages: [],
                newMessage: '',
                loading: true,
                sending: false,
                error: '',
                isTyping: false,
                otherUserId: otherUserId,
                currentUserId: currentUserId,
                selectedFile: null,
                selectedFileName: '',
                selectedFileSize: '',

                async init() {
                    await this.loadMessages();
                    this.scrollToBottom();
                    this.setupBroadcasting();

                    // Mark messages as read
                    await this.markAsRead();
                },

                async loadMessages() {
                    try {
                        this.loading = true;
                        const response = await fetch(`/chat/messages/${this.otherUserId}`);
                        const data = await response.json();
                        this.messages = data.messages;
                    } catch (err) {
                        this.error = 'Failed to load messages';
                        console.error(err);
                    } finally {
                        this.loading = false;
                    }
                },

                async sendMessage() {
                    if ((!this.newMessage.trim() && !this.selectedFile) || this.sending) return;

                    try {
                        this.sending = true;
                        this.error = '';

                        const formData = new FormData();
                        formData.append('receiver_id', this.otherUserId);
                        formData.append('message', this.newMessage);

                        if (this.selectedFile) {
                            formData.append('file', this.selectedFile);
                        }

                        const response = await fetch('/chat/send', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.messages.push(data.message);
                            this.newMessage = '';
                            this.clearFile();
                            this.$nextTick(() => {
                                this.scrollToBottom();
                                // Reset textarea height
                                const textarea = this.$el.querySelector('textarea');
                                if (textarea) {
                                    textarea.style.height = 'auto';
                                }
                            });
                        } else {
                            this.error = data.error || 'Failed to send message';
                        }
                    } catch (err) {
                        this.error = 'Failed to send message. Please try again.';
                        console.error(err);
                    } finally {
                        this.sending = false;
                    }
                },

                handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Check file size (max 10MB)
                    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
                    if (file.size > maxSize) {
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
                    // Reset file input
                    const fileInput = this.$refs.fileInput;
                    if (fileInput) {
                        fileInput.value = '';
                    }
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
                },

                async markAsRead() {
                    try {
                        await fetch(`/chat/mark-read/${this.otherUserId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                    } catch (err) {
                        console.error('Failed to mark messages as read:', err);
                    }
                },

                setupBroadcasting() {
                    // Polling every 3 seconds for new messages
                    setInterval(() => {
                        this.pollNewMessages();
                    }, 3000);
                },

                async pollNewMessages() {
                    try {
                        const response = await fetch(`/chat/messages/${this.otherUserId}`);
                        const data = await response.json();

                        if (data.messages.length > this.messages.length) {
                            const newMessages = data.messages.slice(this.messages.length);
                            this.messages.push(...newMessages);
                            this.$nextTick(() => this.scrollToBottom());
                            await this.markAsRead();
                        }
                    } catch (err) {
                        console.error('Polling failed:', err);
                    }
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.messagesContainer;
                        if (container) {
                            container.scrollTo({
                                top: container.scrollHeight,
                                behavior: 'smooth'
                            });
                        }
                    });
                },

                handleScroll() {
                    // Can implement "load more" functionality here
                },

                formatTime(timestamp) {
                    const date = new Date(timestamp);
                    const now = new Date();
                    const diffMs = now - date;
                    const diffMins = Math.floor(diffMs / 60000);
                    const diffHours = Math.floor(diffMs / 3600000);
                    const diffDays = Math.floor(diffMs / 86400000);

                    if (diffMins < 1) return 'Just now';
                    if (diffMins < 60) return `${diffMins}m`;
                    if (diffHours < 24) return `${diffHours}h`;
                    if (diffDays < 7) return `${diffDays}d`;

                    return date.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' +
                           date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
            }
        }
    </script>
</x-app-layout>
