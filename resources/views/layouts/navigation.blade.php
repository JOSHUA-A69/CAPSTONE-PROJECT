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
                    @endif

                    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'requestor']))
                        <x-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')"
                                    role="menuitem"
                                    x-data="{ unreadCount: 0 }"
                                    x-init="
                                        fetch('{{ route('chat.unread.count') }}')
                                            .then(res => res.json())
                                            .then(data => unreadCount = data.count);
                                        setInterval(() => {
                                            fetch('{{ route('chat.unread.count') }}')
                                                .then(res => res.json())
                                                .then(data => unreadCount = data.count);
                                        }, 30000);
                                    ">
                            <span class="relative inline-flex items-center">
                                💬 {{ __('Messages') }}
                                <span x-show="unreadCount > 0"
                                      x-text="unreadCount > 9 ? '9+' : unreadCount"
                                      class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full"
                                      role="status"
                                      aria-label="Unread messages"></span>
                            </span>
                        </x-nav-link>
                    @endif

                    @if(auth()->check() && auth()->user()->role === 'admin')
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users*')" role="menuitem">
                            {{ __('User Accounts') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.cancellations.index')" :active="request()->routeIs('admin.cancellations*')" role="menuitem">
                            {{ __('Cancellations') }}
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
                                document.getElementById('notification-list').innerHTML = data.html;
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
                class="relative flex items-center justify-center h-10 w-10 text-white/85 hover:text-white focus:text-white hover:bg-emerald-700/40 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white/60">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <!-- Notification Count Badge - Professional Clean Design -->
                        <span x-show="count > 0" 
                              x-cloak
                              class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-semibold leading-none text-white bg-red-600 rounded-full border-2 border-white dark:border-gray-800 shadow-sm transform translate-x-1/2 -translate-y-1/2"
                              x-text="count > 99 ? '99+' : count"
                              style="min-width: 1.25rem;">
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
                         class="absolute top-full right-0 mt-2 w-[500px] max-w-[calc(100vw-24px)] bg-white dark:bg-gray-800 rounded-xl shadow-2xl ring-1 ring-black ring-opacity-5 overflow-hidden z-[9999]" 
                         style="display: none;">
                        <div>
                            <!-- Header - Enhanced -->
                            <div class="px-6 py-4 flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-750 border-b-2 border-blue-200 dark:border-blue-800">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 flex items-center justify-center shadow-md">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Notifications</h3>
                                        <p x-show="count > 0" class="text-xs text-gray-600 dark:text-gray-400" x-text="count === 1 ? '1 unread message' : count + ' unread messages'"></p>
                                    </div>
                                    <span x-show="count > 0" 
                                          class="ml-2 flex items-center justify-center min-w-[28px] h-7 px-2.5 text-sm font-bold text-white bg-gradient-to-br from-red-500 to-red-600 rounded-full shadow-lg border-2 border-white dark:border-gray-800 animate-pulse" 
                                          x-text="count"></span>
                                </div>
                                <button @click="open = false" class="flex-shrink-0 p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Notification List - Enhanced -->
                            <div id="notification-list" class="max-h-[480px] overflow-y-auto overflow-x-hidden scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-transparent">
                                <!-- Notifications will be loaded here -->
                                <div class="px-6 py-8 text-center">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Loading notifications...</p>
                                </div>
                            </div>

                            <!-- Footer with View All Link - Enhanced -->
                            <div class="px-6 py-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-750 border-t border-gray-200 dark:border-gray-700">
                                @if(auth()->user()->role === 'priest')
                                <a href="{{ route('priest.notifications.index') }}" class="flex items-center justify-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors group">
                                    <span>View All Notifications</span>
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                                @elseif(auth()->user()->role === 'adviser')
                                <a href="{{ route('adviser.notifications.index') }}" class="flex items-center justify-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors group">
                                    <span>View All Notifications</span>
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                                @elseif(auth()->user()->role === 'requestor')
                                <a href="{{ route('requestor.notifications.index') }}" class="flex items-center justify-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors group">
                                    <span>View All Notifications</span>
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                                @elseif(auth()->user()->role === 'staff')
                                <a href="{{ route('staff.notifications.index') }}" class="flex items-center justify-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors group">
                                    <span>View All Notifications</span>
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                                @else
                                <a href="{{ route('admin.notifications.index') }}" class="flex items-center justify-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors group">
                                    <span>View All Notifications</span>
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            @if(auth()->check() && auth()->user()->role === 'admin')
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users*')">
                    {{ __('Manage Users') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.cancellations.index')" :active="request()->routeIs('admin.cancellations*')">
                    {{ __('Cancellations') }}
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
