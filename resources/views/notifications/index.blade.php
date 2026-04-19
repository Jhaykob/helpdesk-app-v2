<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Notification History') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 uppercase tracking-wider text-sm">All Notifications</h3>
                    @if(Auth::user()->unreadNotifications->count() > 0)
                        <form action="{{ route('notifications.markAllRead') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs bg-gray-800 hover:bg-black text-white font-bold py-1.5 px-3 rounded shadow-sm transition">
                                Mark All as Read
                            </button>
                        </form>
                    @endif
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse($notifications as $notification)
                        <div class="p-4 {{ is_null($notification->read_at) ? 'bg-blue-50' : 'bg-white' }} hover:bg-gray-50 transition flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    @if(is_null($notification->read_at))
                                        <span class="w-2 h-2 rounded-full bg-blue-600 shadow-sm" title="Unread"></span>
                                    @endif
                                    <span class="text-sm font-bold {{ is_null($notification->read_at) ? 'text-gray-900' : 'text-gray-500' }}">
                                        {{ $notification->data['title'] ?? 'System Alert' }}
                                    </span>
                                    <span class="text-xs text-gray-400">&bull; {{ $notification->created_at->format('M d, Y g:i A') }}</span>
                                </div>
                                <p class="text-sm {{ is_null($notification->read_at) ? 'text-gray-800' : 'text-gray-500' }}">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="{{ route('notifications.read', $notification->id) }}" class="text-xs font-bold text-red-600 hover:text-red-800 uppercase tracking-wider border border-red-200 bg-red-50 hover:bg-red-100 py-1.5 px-3 rounded transition">
                                    View Ticket
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-gray-500 flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span class="font-medium">No notification history found.</span>
                        </div>
                    @endforelse
                </div>

                @if($notifications->hasPages())
                    <div class="p-4 border-t border-gray-200 bg-gray-50">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
