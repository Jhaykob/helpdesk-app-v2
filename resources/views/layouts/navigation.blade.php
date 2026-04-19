<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">
                        {{ __('Tickets') }}
                    </x-nav-link>

                    @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin'))
                        <x-nav-link :href="route('users.index')" :active="request()->request->routeIs('users.*')">
                            {{ __('Users') }}
                        </x-nav-link>

                        <x-nav-link :href="route('audit-logs.index')" :active="request()->routeIs('audit-logs.*')">
                            {{ __('Audit Logs') }}
                        </x-nav-link>

                        <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                            {{ __('Settings') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">

                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('agent') || Auth::user()->hasRole('super-admin'))
                <div class="relative" x-data="{ notificationOpen: false }" @click.outside="notificationOpen = false">
                    <button @click="notificationOpen = !notificationOpen" class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>

                        @if(Auth::user()->unreadNotifications->count() > 0)
                            <span class="absolute top-1 right-1 flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-600 rounded-full border-2 border-white">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </button>

                    <div x-show="notificationOpen" x-transition style="display: none;" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg border border-gray-100 z-50 overflow-hidden">
                        <div class="p-3 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <span class="text-sm font-bold text-gray-700">Notifications</span>
                            @if(Auth::user()->unreadNotifications->count() > 0)
                                <form action="{{ route('notifications.markAllRead') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Mark all read</button>
                                </form>
                            @endif
                        </div>

                        <div class="max-h-96 overflow-y-auto">
                            @forelse(Auth::user()->unreadNotifications as $notification)
                                <a href="{{ route('notifications.read', $notification->id) }}" class="block p-4 border-b border-gray-50 hover:bg-gray-50 transition {{ $loop->last ? 'border-0' : '' }}">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="text-xs font-bold text-gray-900">{{ $notification->data['title'] ?? 'Alert' }}</span>
                                        <span class="text-[10px] text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 truncate">{{ $notification->data['message'] ?? '' }}</p>
                                </a>
                            @empty
                                <div class="p-4 text-center text-sm text-gray-500 flex flex-col items-center">
                                    <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    You're all caught up!
                                </div>
                            @endforelse
                        </div>

                        <div class="p-2 border-t border-gray-100 bg-gray-50 text-center">
                            <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-red-600 hover:text-red-800 uppercase tracking-wider block py-1">View All History</a>
                        </div>
                    </div>
                </div>
                @endif

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
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

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">
                {{ __('Tickets') }}
            </x-responsive-nav-link>

            @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin'))
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    {{ __('Users') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('audit-logs.index')" :active="request()->routeIs('audit-logs.*')">
                    {{ __('Audit Logs') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                    {{ __('Settings') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

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
