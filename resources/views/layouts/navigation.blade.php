<nav x-data="{ open: false }" class="bg-[#2ecc71] dark:bg-dark-bg border-b border-[#27c165] text-white relative z-50" role="navigation" aria-label="Main navigation">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" aria-label="Go to dashboard home">
                        <x-application-logo class="block h-9 w-auto fill-current text-white" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:ms-10 sm:flex items-center" role="menubar">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" role="menuitem">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(auth()->check())
                        @php
                            $calendarRoute = match(auth()->user()->role) {
                                'priest' => 'priest.reservations.calendar',
                                'adviser' => 'adviser.reservations.calendar',
                                'requestor' => 'requestor.reservations.calendar',
                                'staff' => 'staff.reservations.calendar',
                                'admin' => 'admin.calendar.index',
                                default => null,
                            };
                        @endphp
                        @if($calendarRoute)
                            <x-nav-link :href="route($calendarRoute)" :active="request()->routeIs(str_replace('.','.*',$calendarRoute))" role="menuitem">
                                {{ __('View Calendar') }}
                            </x-nav-link>
                        @endif

                        @if(auth()->user()->role === 'requestor')
                            <x-nav-link :href="route('requestor.organization-bookings.create')" :active="request()->routeIs('requestor.organization-bookings.*')" role="menuitem">
                                {{ __('Organization Request') }}
                            </x-nav-link>
                        @endif
                    @endif

                    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'requestor']))
                        <x-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')"
                                    role="menuitem"
                                    x-data="{ unreadCount: 0, _seq: 0 }"
                                    x-init="
                                        const updateUnread = () => {
                                            const seq = ++_seq;
                                            fetch(`{{ route('chat.unread.count') }}?t=${Date.now()}` , { cache: 'no-store' })
                                                .then(res => res.json())
                                                .then(data => {
                                                    if (seq !== _seq) return; // ignore stale responses
                                                    const n = Number(data?.count ?? 0);
                                                    unreadCount = isNaN(n) ? 0 : n;
                                                })
                                                .catch(() => {});
                                        };
                                        updateUnread();
                                        setInterval(updateUnread, 15000);
                                        window.addEventListener('chat:unread-updated', (e) => {
                                            if (e?.detail && typeof e.detail.count !== 'undefined') {
                                                const n = Number(e.detail.count);
                                                unreadCount = isNaN(n) ? 0 : n;
                                            } else {
                                                updateUnread();
                                            }
                                        });
                                        window.addEventListener('focus', updateUnread);
                                    ">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <span class="inline-flex items-center">
                                    <span>{{ __('Messages') }}</span>
                                    <span x-show="Number(unreadCount) > 0"
                                        x-cloak
                                        x-text="Number(unreadCount) > 9 ? '9+' : unreadCount"
                                        class="ml-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none bg-red-600 text-white rounded-full"
                                        role="status"
                                        aria-label="Unread messages"
                                        style="min-width: 1.5rem;"></span>
                                </span>
                            </span>
                        </x-nav-link>
                    @endif

                    @if(auth()->check() && auth()->user()->role === 'staff')
                        <x-nav-link :href="route('staff.organizations.index')" :active="request()->routeIs('staff.organizations*')" role="menuitem">
                            {{ __('Organizations') }}
                        </x-nav-link>
                        <x-nav-link :href="route('staff.cancellations.index')" :active="request()->routeIs('staff.cancellations*')" role="menuitem">
                            {{ __('Cancellations') }}
                        </x-nav-link>
                    @endif
                    @if(auth()->check() && in_array(auth()->user()->role, ['staff','adviser']))
                        <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" role="menuitem">
                            {{ __('Generate Report') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 relative z-40">
                <!-- Notification Bell (All authenticated users) -->
                @if(auth()->check())
                <div class="relative mr-3 z-50" x-data="{
                    open: false,
                    count: 0,
                    loadNotifications() {
                        let url = '';
                        @if(auth()->user()->role === 'priest')
                        url = '{{ route('priest.notifications.recent') }}';
                        @elseif(auth()->user()->role === 'adviser')
                        url = '{{ route('adviser.notifications.recent') }}';
                        @elseif(auth()->user()->role === 'requestor')
                        url = '{{ route('requestor.notifications.recent') }}';
                        @elseif(auth()->user()->role === 'staff')
                        url = '{{ route('staff.notifications.recent') }}';
                        @else
                        url = '{{ route('admin.notifications.recent') }}';
                        @endif
                        document.getElementById('notification-list').innerHTML = '<div class=\'px-6 py-4 text-sm text-gray-500 dark:text-gray-400\'>Loading...</div>';
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                const listEl = document.getElementById('notification-list');
                                listEl.innerHTML = data.html;
                                if(window.attachInlineMarkRead) { window.attachInlineMarkRead(listEl, '{{ auth()->user()->role }}'); }
                            })
                            .catch(() => {
                                document.getElementById('notification-list').innerHTML = '<div class=\'px-6 py-4 text-red-600\'>Error loading notifications. Please refresh.</div>';
                            });
                        // Also update badge count instantly
                        this.updateCount();
                    },
                    updateCount() {
                        @if(auth()->user()->role === 'priest')
                        fetch('{{ route('priest.notifications.count') }}')
                        @elseif(auth()->user()->role === 'adviser')
                        fetch('{{ route('adviser.notifications.count') }}')
                        @elseif(auth()->user()->role === 'requestor')
                        fetch('{{ route('requestor.notifications.count') }}')
                        @elseif(auth()->user()->role === 'staff')
                        fetch('{{ route('staff.notifications.count') }}')
                        @else
                        fetch('{{ route('admin.notifications.count') }}')
                        @endif
                            .then(response => response.json())
                            .then(data => this.count = data.count);
                    }
                }" x-init="
                    updateCount();
                    setInterval(() => updateCount(), 30000);
                    // Also update when window regains focus (returning from notification page)
                    window.addEventListener('focus', () => updateCount());
                    window.addEventListener('notification-update', () => updateCount());
                ">
            <button @click="open = !open; if (open) loadNotifications()" 
                    title="View notifications"
                    class="relative flex items-center justify-center h-10 w-10 text-white hover:text-white focus:text-white bg-white/10 hover:bg-white/20 focus:bg-white/20 rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white/60 group">
                        <svg class="h-6 w-6 transition-transform group-hover:scale-110 duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <!-- Notification Count Badge - Enhanced Design -->
                        <span x-show="count > 0" 
                              x-cloak
                              x-transition
                              class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-gradient-to-r from-red-500 to-red-600 rounded-full border-2 border-white dark:border-gray-800 shadow-lg ring-2 ring-white/30 dark:ring-gray-700/30 animate-pulse"
                              x-text="count > 99 ? '99+' : count"
                              style="min-width: 1.5rem;">
                        </span>
                    </button>

                    <!-- Notification Dropdown - Enhanced Design -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 transform scale-95 -translate-y-2"
                         @click.away="open = false" 
                         class="absolute top-full right-0 mt-2 w-[400px] max-w-[calc(100vw-24px)] bg-white dark:bg-gray-800 rounded-xl shadow-2xl ring-1 ring-black ring-opacity-5 overflow-hidden z-[9999]" 
                         style="display: none;">
                        <div>
                            <!-- Header - Enhanced -->
                            <div class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-750 border-b-2 border-blue-200 dark:border-blue-800">
                                <div class="flex items-center gap-3">
                                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Notifications</h3>
                                </div>
                                <p x-show="count > 0" class="text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/40 px-2.5 py-1 rounded-full" x-text="count === 1 ? '1 unread' : count + ' unread'"></p>
                            </div>

                            <!-- Notification List - Enhanced -->
                            <div id="notification-list" class="max-h-[480px] overflow-y-auto overflow-x-hidden scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-transparent">
                                <!-- Notifications will be loaded here via AJAX -->
                                <template x-if="true">
                                    <div>
                                        @php
                                            // Render the recent-list component with empty notifications for initial load
                                            echo view('components.notifications.recent-list', [
                                                'notifications' => collect([]),
                                                'role' => auth()->user()->role ?? 'requestor',
                                            ]);
                                        @endphp
                                    </div>
                                </template>
                            </div>

                            <!-- Footer with View All Link - Enhanced -->
                            <div class="px-6 py-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-750 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <!-- Removed Mark all read button -->
                                @if(auth()->user()->role === 'priest')
                                <a href="{{ route('priest.notifications.index') }}" class="flex items-center justify-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors group">
                                    <span>View All Notifications</span>
                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                                @elseif(auth()->user()->role === 'adviser')
                                <a href="{{ route('adviser.notifications.index') }}" class="flex items-center justify-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors group">
                                    <span>View All Notifications</span>
                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                                @elseif(auth()->user()->role === 'requestor')
                                <a href="{{ route('requestor.notifications.index') }}" class="flex items-center justify-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors group">
                                    <span>View All Notifications</span>
                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                                @elseif(auth()->user()->role === 'staff')
                                <a href="{{ route('staff.notifications.index') }}" class="flex items-center justify-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors group">
                                    <span>View All Notifications</span>
                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                                @else
                                <a href="{{ route('admin.notifications.index') }}" class="flex items-center justify-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors group">
                                    <span>View All Notifications</span>
                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <x-dropdown align="right" width="64">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center h-10 px-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white hover:text-white/90 focus:text-white focus:outline-none transition ease-in-out duration-150 hover:bg-white/10">
                            <!-- Profile Picture -->
                            <img
                                src="{{ Auth::user()->profile_picture_url }}"
                                alt="{{ Auth::user()->full_name }}"
                                class="w-8 h-8 rounded-full object-cover mr-2 border-2 border-gray-200 dark:border-gray-600"
                            >
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- User Info Header -->
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ Auth::user()->full_name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ Auth::user()->email }}
                            </p>
                        </div>

                        <!-- Dark Mode Toggle -->
                        <button id="darkModeToggle" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 flex items-center gap-2" onclick="toggleDarkMode(this)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                            <span class="dark-mode-text">Dark Mode</span>
                        </button>

                        <!-- Profile Link -->
                        <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Archived History Link (Only for Priest role) -->
                        @if(Auth::user()->role === 'priest')
                        <x-dropdown-link :href="route('priest.history.archived')" class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                            {{ __('Archived History') }}
                        </x-dropdown-link>
                        @endif

                        <!-- Archived Notifications Link (For Admin, Staff, Adviser, Priest) -->
                        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'staff')
                        <x-dropdown-link :href="route('admin.notifications.archived')" class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                            {{ __('Archived Notifications') }}
                        </x-dropdown-link>
                        @elseif(Auth::user()->role === 'adviser')
                        <x-dropdown-link :href="route('adviser.notifications.archived')" class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                            {{ __('Archived Notifications') }}
                        </x-dropdown-link>
                        @elseif(Auth::user()->role === 'priest')
                        <x-dropdown-link :href="route('priest.notifications.archived')" class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                            {{ __('Archived Notifications') }}
                        </x-dropdown-link>
                        @endif

                        <!-- Divider -->
                        <div class="border-t border-gray-200 dark:border-gray-600"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    class="flex items-center gap-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if(auth()->check())
                @php
                    $calendarRoute = match(auth()->user()->role) {
                        'priest' => 'priest.reservations.calendar',
                        'adviser' => 'adviser.reservations.calendar',
                        'requestor' => 'requestor.reservations.calendar',
                        'staff' => 'staff.reservations.calendar',
                        'admin' => 'admin.calendar.index',
                        default => null,
                    };
                @endphp
                @if($calendarRoute)
                    <x-responsive-nav-link :href="route($calendarRoute)" :active="request()->routeIs(str_replace('.','.*',$calendarRoute))">
                        {{ __('View Calendar') }}
                    </x-responsive-nav-link>
                @endif
            @endif
            @if(auth()->check() && in_array(auth()->user()->role, ['staff','adviser']))
                <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                    {{ __('Generate Report') }}
                </x-responsive-nav-link>
            @endif

            @if(auth()->check() && auth()->user()->role === 'staff')
                <x-responsive-nav-link :href="route('staff.organizations.index')" :active="request()->routeIs('staff.organizations*')">
                    {{ __('Manage Organizations') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.cancellations.index')" :active="request()->routeIs('staff.cancellations*')">
                    {{ __('Cancellations') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    window.attachInlineMarkRead = function(container, role){
        try {
            var wrappers = container.querySelectorAll('div.group.bg-blue-50, div.group.bg-blue-900/20');
            wrappers.forEach(function(wrapper){
                if(wrapper.dataset.markAugmented) return;
                wrapper.dataset.markAugmented = '1';
                var anchor = wrapper.querySelector('a[href]');
                if(!anchor) return;
                var href = anchor.getAttribute('href');
                var m = href.match(/\/(\d+)(?:$|\?|#)/);
                if(!m) return;
                var id = m[1];
                        var roleMarkBase = {};
                        roleMarkBase.priest = "{{ url('/priest/notifications') }}";
                        roleMarkBase.adviser = "{{ url('/adviser/notifications') }}";
                        roleMarkBase.requestor = "{{ url('/requestor/notifications') }}";
                        roleMarkBase.staff = "{{ url('/admin/notifications') }}";
                        roleMarkBase.admin = "{{ url('/admin/notifications') }}";
                var base = roleMarkBase[role] || roleMarkBase['admin'];
                var markUrl = base + '/' + id + '/mark-read';
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'absolute top-4 right-12 opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-green-600 dark:hover:text-green-400';
                btn.setAttribute('aria-label','Mark notification as read');
                btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                btn.addEventListener('click', function(e){
                    e.preventDefault(); e.stopPropagation();
                    var tokenEl = document.querySelector('meta[name=csrf-token]');
                    var token = tokenEl ? tokenEl.getAttribute('content') : '';
                    fetch(markUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': token }})
                        .then(function(r){ return r.json(); })
                        .then(function(){
                            wrapper.classList.remove('bg-blue-50','dark:bg-blue-900/20');
                            wrapper.classList.add('bg-white','dark:bg-gray-800');
                            var evt = new Event('notification-update');
                            window.dispatchEvent(evt);
                            btn.remove();
                        })
                        .catch(function(){});
                });
                wrapper.appendChild(btn);
            });
        } catch(err) { /* no-op */ }
    };
</script>
