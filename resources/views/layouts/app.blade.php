<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HelpDesk') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900">

    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden bg-gray-50">

        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-800 bg-opacity-75 z-20 md:hidden"
            @click="sidebarOpen = false" style="display: none;"></div>

        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-30 w-64 bg-red-700 text-white flex flex-col shadow-xl transition-transform duration-300 ease-in-out md:static md:translate-x-0">
            <div class="h-16 flex items-center px-6 font-bold text-2xl tracking-wider border-b border-red-800">
                <svg class="w-8 h-8 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z">
                    </path>
                </svg>
                HelpDesk
            </div>

            <nav class="flex-1 px-4 py-6 space-y-3 overflow-y-auto">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-red-900 font-bold shadow-inner' : 'hover:bg-red-600' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                        </path>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('tickets.index') }}"
                    class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('tickets.*') ? 'bg-red-900 font-bold shadow-inner' : 'hover:bg-red-600' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
                        </path>
                    </svg>
                    Tickets
                </a>

                {{-- @if (Auth::user()->role->name === 'admin')
                        <a href="{{ route('users.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'bg-red-900 font-bold shadow-inner' : 'hover:bg-red-600' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Users
                        </a>
                    @endif --}}

                @if (Auth::user()->role->name === 'admin')
                    <a href="{{ route('users.index') }}"
                        class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'bg-red-900 font-bold shadow-inner' : 'hover:bg-red-600' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        Users
                    </a>

                    <a href="{{ route('audit-logs.index') }}"
                        class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('audit-logs.*') ? 'bg-red-900 font-bold shadow-inner' : 'hover:bg-red-600' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Audit Logs
                    </a>

                    <a href="{{ route('settings.index') }}"
                        class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'bg-red-900 font-bold shadow-inner' : 'hover:bg-red-600' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Settings
                    </a>
                @endif

                @if (Auth::user()->hasRole('admin') || Auth::user()->can('manage_kb_articles'))
                    <a href="{{ route('articles.index') }}"
                        class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('articles.*') ? 'bg-red-900 font-bold shadow-inner' : 'hover:bg-red-600' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                        Manage KB
                    </a>
                @endif

                <a href="{{ route('kb.index') }}"
                    class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('kb.*') ? 'bg-red-900 font-bold shadow-inner' : 'hover:bg-red-600' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                    Knowledge Base
                </a>

                @if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('agent'))
                    <a href="{{ route('macros.index') }}"
                        class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('macros.*') ? 'bg-red-900 font-bold shadow-inner' : 'hover:bg-red-600' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                            </path>
                        </svg>
                        Canned Responses
                    </a>
                @endif

                <a href="{{ route('profile.edit') }}"
                    class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('profile.*') ? 'bg-red-900 font-bold shadow-inner' : 'hover:bg-red-600' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profile
                </a>
            </nav>

            <div class="p-4 border-t border-red-800 bg-red-800 bg-opacity-30">
                <div class="mb-3 px-2 text-sm text-red-200">
                    Logged in as:<br>
                    <span class="font-bold text-white text-base">{{ Auth::user()->name }}</span>
                    <span
                        class="text-xs ml-1 bg-red-600 px-2 py-0.5 rounded-full">{{ ucfirst(Auth::user()->role->name) }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-left flex items-center px-2 py-2 text-sm text-red-100 hover:text-white hover:bg-red-600 rounded-lg transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-screen overflow-hidden">

            <header
                class="flex items-center justify-between bg-white shadow-sm px-4 py-3 md:hidden z-10 border-b border-gray-200">
                <div class="font-bold text-xl text-red-700 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    HelpDesk
                </div>
                <div class="flex items-center gap-2">
                    <div x-data="{ notifyOpen: false }" class="relative">
                        <button @click="notifyOpen = !notifyOpen"
                            class="relative p-2 text-gray-400 hover:text-red-600 transition focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                </path>
                            </svg>
                            @if (Auth::user()->unreadNotifications->count() > 0)
                                <span
                                    class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full">{{ Auth::user()->unreadNotifications->count() }}</span>
                            @endif
                        </button>
                        <div x-show="notifyOpen" @click.away="notifyOpen = false" style="display: none;"
                            class="absolute right-0 mt-2 w-72 bg-white rounded-md shadow-lg overflow-hidden z-50 border border-gray-100">
                            <div
                                class="py-2 px-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-700">Alerts</span>
                                @if (Auth::user()->unreadNotifications->count() > 0)
                                    <form method="POST" action="{{ route('notifications.read') }}">
                                        @csrf
                                        <button type="submit"
                                            class="text-xs text-red-600 hover:text-red-800 font-medium">Mark all
                                            read</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-60 overflow-y-auto">
                                @forelse(Auth::user()->unreadNotifications as $notification)
                                    <a href="{{ $notification->data['url'] }}"
                                        class="block px-4 py-3 border-b border-gray-50 hover:bg-red-50 transition">
                                        <p class="text-sm text-gray-800 font-medium">
                                            {{ $notification->data['message'] }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $notification->created_at->diffForHumans() }}</p>
                                    </a>
                                @empty
                                    <p class="text-sm text-gray-500 px-4 py-3 text-center">No new notifications.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <button @click="sidebarOpen = true"
                        class="text-gray-600 hover:text-red-600 focus:outline-none p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </header>

            @if (isset($header))
                <header class="bg-white shadow-sm z-10 hidden md:flex justify-between items-center px-8 py-5">
                    <div class="flex-1">
                        {{ $header }}
                    </div>

                    <div x-data="{ dropdownOpen: false }" class="relative ml-4">
                        <button @click="dropdownOpen = !dropdownOpen"
                            class="relative p-2 text-gray-400 hover:text-red-600 transition focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                </path>
                            </svg>
                            @if (Auth::user()->unreadNotifications->count() > 0)
                                <span
                                    class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full shadow">{{ Auth::user()->unreadNotifications->count() }}</span>
                            @endif
                        </button>

                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" style="display: none;"
                            class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-20 border border-gray-100">
                            <div
                                class="py-2 px-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-700">Notifications</span>
                                @if (Auth::user()->unreadNotifications->count() > 0)
                                    <form method="POST" action="{{ route('notifications.read') }}">
                                        @csrf
                                        <button type="submit"
                                            class="text-xs text-red-600 hover:text-red-800 font-medium focus:outline-none">Mark
                                            all as read</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse(Auth::user()->unreadNotifications as $notification)
                                    <a href="{{ $notification->data['url'] }}"
                                        class="block px-4 py-3 border-b border-gray-50 hover:bg-red-50 transition">
                                        <p class="text-sm text-gray-800 font-medium">
                                            {{ $notification->data['message'] }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $notification->created_at->diffForHumans() }}</p>
                                    </a>
                                @empty
                                    <p class="text-sm text-gray-500 px-4 py-4 text-center">No new notifications right
                                        now.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </header>
            @endif

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

</body>

</html>
