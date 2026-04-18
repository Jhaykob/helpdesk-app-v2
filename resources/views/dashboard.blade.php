<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Overview Dashboard') }}
        </h2>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <div class="bg-white rounded-xl shadow-sm border-t-4 border-blue-500 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Total Tickets</h3>
                <div class="p-2 bg-blue-50 rounded-lg"><svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
            </div>
            <div class="text-3xl font-extrabold text-gray-900">{{ $totalTickets }}</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border-t-4 border-red-500 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Open</h3>
                <div class="p-2 bg-red-50 rounded-lg"><svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
            </div>
            <div class="text-3xl font-extrabold text-gray-900">{{ $openTickets }}</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border-t-4 border-yellow-400 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">In Progress</h3>
                <div class="p-2 bg-yellow-50 rounded-lg"><svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            </div>
            <div class="text-3xl font-extrabold text-gray-900">{{ $inProgressTickets }}</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border-t-4 border-green-500 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Resolved</h3>
                <div class="p-2 bg-green-50 rounded-lg"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            </div>
            <div class="text-3xl font-extrabold text-gray-900">{{ $resolvedTickets }}</div>
        </div>

    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
        <h3 class="text-xl font-bold text-gray-800 mb-2">Welcome back, {{ Auth::user()->name }}!</h3>
        <p class="text-gray-500 mb-6">Need to request assistance or report a problem?</p>
        <a href="{{ route('tickets.index') }}" class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow transition">
            Go to My Tickets
        </a>
    </div>
</x-app-layout>
