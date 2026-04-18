<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            {{ __('Global Audit Logs') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-col md:flex-row gap-4 items-end md:items-center">

                    <div class="w-full md:w-1/2 relative">
                        <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search Logs</label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search by user, action, or ID..." class="block w-full pl-9 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="w-full md:w-1/4">
                        <label for="target_type" class="block text-xs font-medium text-gray-700 mb-1">Entity Type</label>
                        <select name="target_type" id="target_type" class="block w-full py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                            <option value="">All Entities</option>
                            <option value="Ticket" {{ request('target_type') == 'Ticket' ? 'selected' : '' }}>Tickets</option>
                            <option value="User" {{ request('target_type') == 'User' ? 'selected' : '' }}>Users</option>
                        </select>
                    </div>

                    <div class="w-full md:w-auto flex gap-2">
                        <button type="submit" class="w-full md:w-auto bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-md text-sm font-medium transition shadow-sm">Filter</button>
                        @if(request()->hasAny(['search', 'target_type']) && (request('search') || request('target_type')))
                            <a href="{{ route('audit-logs.index') }}" class="w-full md:w-auto flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition border border-gray-300">Clear</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-gray-800">
                <div class="p-6 text-gray-900 overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Timestamp</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Target Focus</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Details</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($logs as $log)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $log->user ? $log->user->name : 'System/Deleted User' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($log->target_type === 'Ticket')
                                            <a href="{{ route('tickets.show', $log->target_id) }}" class="text-blue-600 hover:underline">Ticket #{{ $log->target_id }}</a>
                                        @else
                                            <span class="text-gray-600">{{ $log->target_type }} #{{ $log->target_id }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($log->old_value && $log->new_value)
                                            Changed from <span class="font-bold text-gray-700">{{ $log->old_value }}</span> to <span class="font-bold text-gray-900">{{ $log->new_value }}</span>
                                        @elseif($log->new_value)
                                            <span class="font-bold text-gray-900">{{ $log->new_value }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        No logs found matching your search.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
