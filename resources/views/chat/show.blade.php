<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between bg-white dark:bg-gray-800 -mx-4 -my-2 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-4">
                <a href="{{ route('chat.index') }}"
                   class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div class="h-8 w-px bg-gray-300 dark:bg-gray-600"></div>
                <div class="relative">
                    <img src="{{ $otherUser->profile_picture_url }}"
                         alt="{{ $otherUser->first_name }}"
                         class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600">
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
                </div>
                <div>
                    <h2 class="font-semibold text-base text-gray-900 dark:text-white flex items-center gap-2">
                        {{ $otherUser->first_name }} {{ $otherUser->last_name }}
                        <span class="inline-flex items-center justify-center px-[3px] h-[14px] rounded-full text-[9px] font-semibold bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                            {{ min($messages->count(), 999) }}@if($messages->count() > 999)+@endif
                        </span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ ucfirst($otherUser->role) }} • Active
                    </p>
                </div>
            </div>
        </div>
    </x-slot>


    <div class="bg-white dark:bg-gray-900" style="height: calc(100vh - 73px);">
        <div class="h-full max-w-4xl mx-auto">
            <!-- Clean Chat Container -->
            <div class="relative bg-white dark:bg-gray-800 flex flex-col h-full"
                 x-data="chatApp({{ $otherUser->id }}, {{ auth()->id() }})"
                 x-init="init()">


                <!-- Messages Container -->
                 <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6 bg-gray-50 dark:bg-gray-800"
                     x-ref="messagesContainer"
                     @scroll="handleScroll">


                    <!-- Enhanced Loading State -->
                    <div x-show="loading" class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-blue-600 mb-4"></div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Loading messages...</p>
                        </div>
                    </div>


                    <!-- Clean Message Bubbles (Meta AI Style) -->
                    <template x-for="message in messages" :key="message.id">
                        <div>
                            <!-- Centered Timestamp -->
                            <div class="flex justify-center mb-3">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium px-3 py-1 bg-gray-50 dark:bg-gray-700 rounded-full"
                                      x-text="formatTime(message.created_at)"></span>
                            </div>

                            <!-- Message Row -->
                            <div :class="message.sender_id === currentUserId ? 'flex justify-end' : 'flex justify-start'" class="mb-1">
                                <div class="flex items-start gap-2 max-w-[75%] md:max-w-[65%]">
                                    <!-- Profile Picture (other user) -->
                                    <div x-show="message.sender_id !== currentUserId" class="flex-shrink-0 mt-0.5">
                                        <img :src="message.sender.profile_picture_url"
                                             :alt="message.sender.name"
                                             class="w-7 h-7 rounded-full object-cover">
                                    </div>


                                    <!-- Message Content -->
                                    <div :class="message.sender_id === currentUserId ? 'order-2' : 'order-1'">
                                        <!-- Message Bubble -->
                                        <div :class="[
                                                message.is_image && !message.message
                                                    ? 'bg-transparent p-0'
                                                    : message.sender_id === currentUserId
                                                        ? 'bg-blue-600 text-white rounded-3xl rounded-br-md px-4 py-2.5'
                                                        : 'bg-indigo-50 text-indigo-900 dark:bg-gray-700 dark:text-gray-100 rounded-3xl rounded-bl-md px-4 py-2.5 border border-indigo-100'
                                            ]">


                                        <!-- Attachment (if exists) -->
                                        <template x-if="message.attachment_url">
                                            <div :class="message.is_image ? '' : 'mb-2'">
                                                <!-- Image Attachment -->
                                                <div x-show="message.is_image" class="rounded-2xl overflow-hidden">
                                                    <a :href="message.attachment_url" target="_blank" class="block hover:opacity-90 transition-opacity">
                                                        <img :src="message.attachment_url"
                                                             :alt="message.attachment_name"
                                                             class="max-w-full h-auto max-h-80 object-cover">
                                                    </a>
                                                </div>


                                                <!-- File Attachment (non-image) -->
                                                <a x-show="!message.is_image"
                                                   :href="message.attachment_url"
                                                   download
                                                   target="_blank"
                                                   class="flex items-center gap-2 p-2 rounded-lg"
                                                   :class="message.sender_id === currentUserId
                                                       ? 'bg-blue-700 hover:bg-blue-800'
                                                       : 'bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500'">
                                                    <svg class="w-6 h-6" :class="message.sender_id === currentUserId ? 'text-white' : 'text-blue-600 dark:text-blue-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                                     <p x-show="message.message" class="text-[15px] leading-relaxed whitespace-pre-wrap break-words"
                                           x-text="message.message"></p>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </template>


                    <!-- Clean Empty State -->
                    <div x-show="!loading && messages.length === 0" class="flex items-center justify-center h-full">
                        <div class="text-center px-4">
                            <div class="w-20 h-20 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No messages yet</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                                Start your conversation below
                            </p>
                        </div>
                    </div>


                    <!-- Typing Indicator -->
                    <div x-show="isTyping" class="flex justify-start mb-4">
                        <div class="flex items-start gap-2">
                            <img src="{{ $otherUser->profile_picture_url }}"
                                 alt="{{ $otherUser->first_name }}"
                                 class="w-7 h-7 rounded-full object-cover mt-0.5">
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-3xl rounded-bl-md px-4 py-3">
                                <div class="flex space-x-1.5">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Clean Input Area (Meta AI Style) -->
                <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                    <form @submit.prevent="sendMessage">
                        <!-- Message Input with Attachment and Send -->
                        <div class="flex items-center gap-2 max-w-4xl mx-auto">
                            <!-- Attachment Button -->
                            <button type="button"
                                    @click="$refs.fileInput.click()"
                                    class="flex-shrink-0 p-2.5 text-gray-400 hover:text-blue-600 dark:text-gray-500 dark:hover:text-blue-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    title="Attach file">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>


                            <!-- Hidden File Input -->
                            <input type="file"
                                   x-ref="fileInput"
                                   @change="handleFileUpload($event)"
                                   accept="image/*,application/pdf,.doc,.docx"
                                   class="hidden">


                            <!-- Message Textarea -->
                            <textarea x-model="newMessage"
                                      @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                                      @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                      placeholder="Type a message..."
                                      rows="1"
                                      class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 resize-none text-[15px]"
                                      style="min-height: 40px; max-height: 120px;"></textarea>


                            <!-- Send Button -->
                            <button type="submit"
                                    :disabled="(!newMessage.trim() && !selectedFile) || sending"
                                    :class="(newMessage.trim() || selectedFile) && !sending
                                        ? 'bg-blue-600 hover:bg-blue-700 text-white'
                                        : 'bg-gray-200 dark:bg-gray-600 text-gray-400 cursor-not-allowed'"
                                    class="flex-shrink-0 p-2.5 rounded-full transition-colors"
                                    title="Send message">
                                <svg x-show="!sending" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                                </svg>
                                <svg x-show="sending" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>


                        <!-- File Preview -->
                        <div x-show="selectedFile" class="mt-2 px-2 max-w-4xl mx-auto">
                            <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="selectedFileName"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="selectedFileSize"></p>
                                    </div>
                                </div>
                                <button type="button"
                                        @click="clearFile()"
                                        class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-full transition-colors">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </form>


                    <!-- Error Message -->
                    <div x-show="error"
                         x-text="error"
                         class="mt-3 text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-4 py-2 rounded-lg border border-red-200 dark:border-red-800"></div>
                </div>
            </div>
        </div>
    </div>


    <style>
        /* Minimal Scrollbar (Meta AI Style) */
        [x-ref="messagesContainer"] {
            scrollbar-width: thin;
            scrollbar-color: #e5e7eb transparent;
        }


        [x-ref="messagesContainer"]::-webkit-scrollbar {
            width: 4px;
        }


        [x-ref="messagesContainer"]::-webkit-scrollbar-track {
            background: transparent;
        }


        [x-ref="messagesContainer"]::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 10px;
        }


        [x-ref="messagesContainer"]::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }


        .dark [x-ref="messagesContainer"]::-webkit-scrollbar-thumb {
            background: #374151;
        }


        .dark [x-ref="messagesContainer"]::-webkit-scrollbar-thumb:hover {
            background: #4b5563;
        }


        /* Smooth Input Focus */
        textarea:focus {
            outline: none;
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
                            const response = await fetch(`/chat/messages/${this.otherUserId}?t=${Date.now()}`, { cache: 'no-store' });
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
                        // Refresh global unread badge immediately
                        try {
                                const res = await fetch(`/chat/unread/count?t=${Date.now()}`, { cache: 'no-store' });
                            const data = await res.json();
                            window.dispatchEvent(new CustomEvent('chat:unread-updated', { detail: { count: data.count } }));
                        } catch (_) {}
                    } catch (err) {
                        console.error('Failed to mark messages as read:', err);
                    }
                },


                setupBroadcasting() {
                    // Use SSE for real-time chat updates (no more polling!)
                    if (window.sseClient) {
                        // Connect to chat-specific SSE stream
                        window.sseClient.connectChat(this.otherUserId);

                        // Listen for new messages from SSE
                        window.sseClient.on('chat:new_messages', async (data) => {
                            if (data && data.messages && data.messages.length > 0) {
                                const lastId = this.messages.length ? this.messages[this.messages.length - 1].id : 0;
                                const incoming = data.messages.filter(m => m.id > lastId);

                                if (incoming.length) {
                                    // Add new messages
                                    for (const message of incoming) {
                                        this.messages.push(message);
                                    }
                                    this.$nextTick(() => this.scrollToBottom());
                                    await this.markAsRead();
                                }
                            }
                        });

                        // Listen for unread count updates
                        window.sseClient.on('chat:unread_update', (data) => {
                            if (data && typeof data.count !== 'undefined') {
                                window.dispatchEvent(new CustomEvent('chat:unread-updated', { detail: { count: data.count } }));
                            }
                        });

                        console.log('[Chat] SSE connected for conversation with user', this.otherUserId);
                    } else {
                        // Fallback to polling if SSE not available
                        console.log('[Chat] SSE not available, using polling fallback');
                        setInterval(() => {
                            this.pollNewMessages();
                        }, 3000);
                    }
                },


                async pollNewMessages() {
                    try {
                        const response = await fetch(`/chat/messages/${this.otherUserId}?t=${Date.now()}`, { cache: 'no-store' });
                        const data = await response.json();


                        // Determine the last known message ID/time
                        const lastId = this.messages.length ? this.messages[this.messages.length - 1].id : 0;
                        const incoming = data.messages.filter(m => m.id > lastId);


                        if (incoming.length) {
                            this.messages.push(...incoming);
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
