<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin'))

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white rounded-lg shadow-sm border border-t-gray-200 border-r-gray-200 border-b-gray-200 p-6 border-l-4 border-blue-500">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Open Tickets</p>
                        <p class="text-3xl font-black text-gray-900 mt-2">{{ $kpis['total_open'] }}</p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-t-gray-200 border-r-gray-200 border-b-gray-200 p-6 border-l-4 {{ $kpis['unassigned'] > 0 ? 'border-red-500 animate-pulse' : 'border-gray-300' }}">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Unassigned Queue</p>
                        <p class="text-3xl font-black {{ $kpis['unassigned'] > 0 ? 'text-red-600' : 'text-gray-900' }} mt-2">{{ $kpis['unassigned'] }}</p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-t-gray-200 border-r-gray-200 border-b-gray-200 p-6 border-l-4 border-orange-500">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Active SLA Breaches</p>
                        <p class="text-3xl font-black text-orange-600 mt-2">{{ $kpis['sla_breaches'] }}</p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-t-gray-200 border-r-gray-200 border-b-gray-200 p-6 border-l-4 border-yellow-400">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Global CSAT Avg</p>
                        <div class="flex items-center gap-2 mt-2">
                            <p class="text-3xl font-black text-gray-900">{{ $kpis['global_csat'] }}</p>
                            <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider mb-4 border-b pb-2">Tickets by Status</h3>
                        <div class="relative h-64 w-full">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider mb-4 border-b pb-2">Tickets by Priority</h3>
                        <div class="relative h-64 w-full">
                            <canvas id="priorityChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Agent Performance Matrix (This Month)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white text-xs text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                    <th class="p-4 font-bold">Agent Name</th>
                                    <th class="p-4 font-bold text-center">Resolved Vol.</th>
                                    <th class="p-4 font-bold text-center">Active Breaches</th>
                                    <th class="p-4 font-bold text-center">CSAT Average</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($agents as $agent)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-4 font-bold text-gray-900 flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center text-xs">
                                                {{ substr($agent->name, 0, 1) }}
                                            </div>
                                            {{ $agent->name }}
                                        </td>
                                        <td class="p-4 text-center text-gray-700 font-medium">{{ $agent->resolved_this_month }}</td>
                                        <td class="p-4 text-center">
                                            @if($agent->active_sla_breaches > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                                    {{ $agent->active_sla_breaches }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 font-medium">0</span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-center">
                                            @if($agent->csat_average > 0)
                                                <span class="inline-flex items-center gap-1 font-bold {{ $agent->csat_average >= 4.0 ? 'text-green-600' : 'text-yellow-600' }}">
                                                    {{ number_format($agent->csat_average, 1) }}
                                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 italic">No ratings</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const statusCtx = document.getElementById('statusChart').getContext('2d');
                        new Chart(statusCtx, {
                            type: 'doughnut',
                            data: {
                                labels: {!! json_encode(array_keys($ticketsByStatus)) !!},
                                datasets: [{
                                    data: {!! json_encode(array_values($ticketsByStatus)) !!},
                                    backgroundColor: ['#EF4444', '#F59E0B', '#10B981', '#6B7280', '#3B82F6'],
                                    borderWidth: 0
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                        });

                        const priorityCtx = document.getElementById('priorityChart').getContext('2d');
                        new Chart(priorityCtx, {
                            type: 'bar',
                            data: {
                                labels: {!! json_encode(array_keys($ticketsByPriority)) !!},
                                datasets: [{
                                    label: 'Tickets',
                                    data: {!! json_encode(array_values($ticketsByPriority)) !!},
                                    backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                                    borderRadius: 4
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
                        });
                    });
                </script>

            @elseif(Auth::user()->hasRole('agent'))

                <h3 class="font-bold text-gray-700 uppercase tracking-wider text-sm mb-2">My Performance (This Month)</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow-sm border border-b-gray-200 border-l-gray-200 border-r-gray-200 p-6 border-t-4 border-blue-500 text-center">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tickets Resolved</p>
                        <p class="text-4xl font-black text-gray-900 mt-2">{{ $personalKpis['resolved_this_month'] }}</p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-b-gray-200 border-l-gray-200 border-r-gray-200 p-6 border-t-4 border-yellow-400 text-center">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">My CSAT Average</p>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <p class="text-4xl font-black text-gray-900">{{ $personalKpis['csat_average'] }}</p>
                            <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 border-t-4 {{ $personalKpis['active_sla_breaches'] > 0 ? 'border-red-500' : 'border-green-500' }} text-center">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Active SLA Breaches</p>
                        <p class="text-4xl font-black {{ $personalKpis['active_sla_breaches'] > 0 ? 'text-red-600' : 'text-green-600' }} mt-2">{{ $personalKpis['active_sla_breaches'] }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-4 bg-red-700 border-b border-red-800 flex justify-between items-center">
                            <h3 class="font-bold text-white text-sm uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                My Active Queue (Needs Attention)
                            </h3>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($activeTickets as $ticket)
                                <a href="{{ route('tickets.show', $ticket) }}" class="flex flex-col sm:flex-row sm:items-center justify-between p-4 hover:bg-gray-50 transition gap-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-bold text-gray-400">#{{ $ticket->id }}</span>
                                            <span class="font-bold text-gray-900 truncate max-w-xs">{{ $ticket->title }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 text-xs">
                                            <span class="px-2 py-0.5 rounded-full font-bold {{ $ticket->status === 'Pending Customer' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800' }}">{{ $ticket->status }}</span>
                                            <span class="text-gray-500">From: {{ $ticket->user->name }}</span>
                                        </div>
                                    </div>
                                    <div class="text-left sm:text-right">
                                        <span class="block text-xs font-bold text-gray-500 uppercase">SLA Deadline</span>
                                        <span class="text-sm font-bold {{ $ticket->is_breaching_sla ? 'text-red-600 animate-pulse' : 'text-gray-900' }}">
                                            {{ $ticket->sla_deadline->diffForHumans() }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <div class="p-8 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <p class="font-medium">Your queue is completely clear!</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                Recent Feedback
                            </h3>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($recentFeedback as $feedback)
                                <a href="{{ route('tickets.show', $feedback) }}" class="block p-4 hover:bg-gray-50 transition">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-gray-500">Ticket #{{ $feedback->id }}</span>
                                        <span class="flex items-center gap-1 text-sm font-bold {{ $feedback->rating >= 4 ? 'text-green-600' : 'text-yellow-600' }}">
                                            {{ $feedback->rating }} <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                        </span>
                                    </div>
                                    @if($feedback->csat_feedback)
                                        <p class="text-xs text-gray-600 italic line-clamp-2">"{{ $feedback->csat_feedback }}"</p>
                                    @else
                                        <p class="text-xs text-gray-400 italic">No written comment provided.</p>
                                    @endif
                                </a>
                            @empty
                                <div class="p-6 text-center text-xs text-gray-500 italic">
                                    No ratings received yet this month.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            @else

                <div class="bg-white rounded-lg shadow-sm border border-t-gray-200 border-r-gray-200 border-b-gray-200 overflow-hidden flex flex-col md:flex-row justify-between items-center p-8 border-l-4 border-red-600">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900">Hello, {{ explode(' ', $user->name)[0] }}!</h3>
                        <p class="text-gray-500 mt-1 text-sm">How can our IT Support team help you today?</p>
                    </div>
                    <div class="mt-6 md:mt-0 flex gap-4">
                        <a href="{{ route('kb.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 px-6 rounded-lg transition text-sm">Browse Solutions</a>
                        <a href="{{ route('tickets.create') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition shadow-md text-sm">Submit a Ticket</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tickets in Progress</p>
                            <p class="text-3xl font-black text-gray-900 mt-1">{{ $customerKpis['open'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tickets Resolved</p>
                            <p class="text-3xl font-black text-gray-900 mt-1">{{ $customerKpis['closed'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">My Recent Tickets</h3>
                        <a href="{{ route('tickets.index') }}" class="text-xs font-bold text-red-600 hover:text-red-800">View All</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($myTickets as $ticket)
                            <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-bold text-gray-400">#{{ $ticket->id }}</span>
                                        <span class="font-bold text-gray-900 truncate max-w-sm">{{ $ticket->title }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Updated {{ $ticket->updated_at->diffForHumans() }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full border
                                    {{ $ticket->status === 'Unassigned' ? 'bg-gray-100 text-gray-800 border-gray-200' : '' }}
                                    {{ $ticket->status === 'Open' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                                    {{ str_contains($ticket->status, 'Pending') ? 'bg-orange-50 text-orange-700 border-orange-200' : '' }}
                                    {{ in_array($ticket->status, ['Resolved', 'Closed']) ? 'bg-green-50 text-green-700 border-green-200' : '' }}">
                                    {{ $ticket->status }}
                                </span>
                            </a>
                        @empty
                            <div class="p-8 text-center text-gray-500">
                                <p class="text-sm font-medium">You have no ticket history.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            @endif

        </div>
    </div>
</x-app-layout>
