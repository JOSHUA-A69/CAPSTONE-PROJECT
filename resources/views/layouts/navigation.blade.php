<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700" role="navigation" aria-label="Main navigation">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" aria-label="Go to dashboard home">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex" role="menubar">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" role="menuitem">
                        {{ __('Dashboard') }}
                    </x-nav-link>

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
                    @endif

                    @if(auth()->check() && auth()->user()->role === 'staff')
                        <x-nav-link :href="route('staff.organizations.index')" :active="request()->routeIs('staff.organizations*')" role="menuitem">
                            {{ __('Organizations') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Notification Bell (All authenticated users) -->
                @if(auth()->check())
                <div class="relative mr-3" x-data="{
                    open: false,
                    count: 0,
                    loadNotifications() {
                        @if(auth()->user()->role === 'priest')
                        fetch('{{ route('priest.notifications.recent') }}')
                        @elseif(auth()->user()->role === 'adviser')
                        fetch('{{ route('adviser.notifications.recent') }}')
                        @elseif(auth()->user()->role === 'requestor')
                        fetch('{{ route('requestor.notifications.recent') }}')
                        @else
                        fetch('{{ route('admin.notifications.recent') }}')
                        @endif
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById('notification-list').innerHTML = data.html;
                            });
                    },
                    updateCount() {
                        @if(auth()->user()->role === 'priest')
                        fetch('{{ route('priest.notifications.count') }}')
                        @elseif(auth()->user()->role === 'adviser')
                        fetch('{{ route('adviser.notifications.count') }}')
                        @elseif(auth()->user()->role === 'requestor')
                        fetch('{{ route('requestor.notifications.count') }}')
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
                    <button @click="open = !open; if (open) loadNotifications()" class="relative p-2 text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span x-show="count > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full" x-text="count"></span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-3 w-[500px] max-w-[calc(100vw-24px)] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden overflow-x-hidden z-50" style="display: none;">
                        <div>
                            <!-- Header -->
                            <div class="px-6 py-4 flex items-center justify-between bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Notifications</h3>
                                <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Notification List -->
                            <div id="notification-list" class="max-h-[560px] overflow-y-auto overflow-x-hidden bg-gray-50 dark:bg-gray-900">
                                <!-- Notifications will be loaded here -->
                                <div class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">Loading...</div>
                            </div>

                            <!-- Footer with View All Link -->
                            <div class="px-6 py-3 bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                                @if(auth()->user()->role === 'priest')
                                <a href="{{ route('priest.notifications.index') }}" class="block text-center text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    View All Notifications →
                                </a>
                                @elseif(auth()->user()->role === 'adviser')
                                <a href="{{ route('adviser.notifications.index') }}" class="block text-center text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    View All Notifications →
                                </a>
                                @elseif(auth()->user()->role === 'requestor')
                                <a href="{{ route('requestor.notifications.index') }}" class="block text-center text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    View All Notifications →
                                </a>
                                @else
                                <a href="{{ route('admin.notifications.index') }}" class="block text-center text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    View All Notifications →
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
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
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
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
            @if(auth()->check() && auth()->user()->role === 'admin')
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users*')">
                    {{ __('Manage Users') }}
                </x-responsive-nav-link>
            @endif

            @if(auth()->check() && auth()->user()->role === 'staff')
                <x-responsive-nav-link :href="route('staff.organizations.index')" :active="request()->routeIs('staff.organizations*')">
                    {{ __('Manage Organizations') }}
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
