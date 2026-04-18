<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center" x-data>
            <h2 class="font-semibold text-xl text-red-700 leading-tight">
                {{ __('Tickets') }}
            </h2>
            <button @click="$dispatch('open-modal')" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow transition ease-in-out duration-150 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Create Ticket
            </button>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen"
         x-data="{
             isModalOpen: false,
             searchQuery: '',
             suggestions: [],
             isSearching: false,
             searchKB() {
                 if (this.searchQuery.length < 3) {
                     this.suggestions = [];
                     return;
                 }
                 this.isSearching = true;
                 fetch(`/kb/search?q=${encodeURIComponent(this.searchQuery)}`)
                     .then(res => res.json())
                     .then(data => {
                         this.suggestions = data;
                         this.isSearching = false;
                     });
             }
         }"
         @open-modal.window="isModalOpen = true">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-red-600">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority / SLA</th>
                                @if(Auth::user()->role->name === 'admin' || Auth::user()->role->name === 'agent')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creator</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($tickets as $ticket)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ $ticket->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ticket->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $ticket->status === 'Unassigned' ? 'bg-gray-100 text-gray-800 border border-gray-200' : '' }}
                                            {{ $ticket->status === 'Open' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ str_contains($ticket->status, 'Pending') ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $ticket->status === 'Resolved' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $ticket->status === 'Closed' ? 'bg-gray-200 text-gray-800' : '' }}">
                                            {{ $ticket->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex flex-col items-start gap-1">
                                            <span class="px-2 py-1 text-xs rounded border
                                                {{ $ticket->priority === 'High' ? 'border-red-500 text-red-600 bg-red-50' : 'border-gray-300 text-gray-700 bg-gray-50' }}">
                                                {{ $ticket->priority }}
                                            </span>

                                            @if($ticket->is_breaching_sla)
                                                <span class="flex items-center text-[10px] font-bold text-red-600 uppercase tracking-wider animate-pulse">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    SLA Breached
                                                </span>
                                            @else
                                                <span class="text-[10px] text-gray-400" title="Deadline">
                                                    Due: {{ $ticket->sla_deadline->format('M d, g:i A') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    @if(Auth::user()->role->name === 'admin' || Auth::user()->role->name === 'agent')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ticket->user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($ticket->assignedTo)
                                                <span class="text-blue-600 font-medium">{{ $ticket->assignedTo->name }}</span>
                                            @else
                                                <span class="text-gray-400 italic">Unassigned</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center gap-4">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="text-red-600 hover:text-red-900 font-bold underline">View Details</a>

                                        @if(Auth::user()->can('claim', $ticket))
                                            <form method="POST" action="{{ route('tickets.claim', $ticket) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-1.5 px-3 rounded shadow transition">
                                                    Claim
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No tickets found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="isModalOpen" @click.away="isModalOpen = false" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border-t-4 border-red-600">
                    <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-xl leading-6 font-bold text-gray-900 mb-6 border-b pb-2" id="modal-title">Create New Ticket</h3>

                            <div class="mb-4">
                                <label for="title" class="block text-sm font-medium text-gray-700">Ticket Title (Brief summary of your issue)</label>
                                <input type="text" name="title" id="title" x-model="searchQuery" x-on:input.debounce.500ms="searchKB" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="e.g., I cannot connect to the printer" required>
                            </div>

                            <div x-show="suggestions.length > 0" x-transition style="display: none;" class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4 shadow-inner relative">
                                <div x-show="isSearching" class="absolute inset-0 bg-blue-50 bg-opacity-50 flex items-center justify-center rounded-lg">
                                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>

                                <h4 class="text-sm font-bold text-blue-900 mb-2 flex items-center">
                                    <svg class="w-5 h-5 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Suggested Solutions (Might fix your issue instantly!)
                                </h4>
                                <ul class="space-y-3">
                                    <template x-for="article in suggestions" :key="article.id">
                                        <li class="bg-white p-3 rounded border border-blue-100 shadow-sm">
                                            <strong class="text-sm text-blue-800 block mb-1" x-text="article.title"></strong>
                                            <p class="text-xs text-gray-600 line-clamp-2" x-text="article.content"></p>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700">Detailed Description</label>
                                <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" required></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                                <select name="priority" id="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                </select>
                            </div>

                            <div class="mb-2" x-data="{ fileName: null, filePreview: null }">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Attach Screenshot / File (Optional)</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md transition-colors" :class="fileName ? 'border-red-500 bg-red-50' : 'border-gray-300 hover:border-red-500'">
                                    <div x-show="!fileName" class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="attachment" class="relative cursor-pointer rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                                <span>Upload a file</span>
                                                <input id="attachment" name="attachment" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" @change="const file = $event.target.files[0]; if (file) { fileName = file.name; if (file.type.startsWith('image/')) { filePreview = URL.createObjectURL(file); } else { filePreview = null; } }">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, PDF up to 2MB</p>
                                    </div>
                                    <div x-show="fileName" style="display: none;" class="text-center w-full">
                                        <template x-if="filePreview">
                                            <img :src="filePreview" class="mx-auto h-32 object-contain mb-3 rounded shadow-sm border border-gray-200">
                                        </template>
                                        <template x-if="!filePreview">
                                            <svg class="mx-auto h-12 w-12 text-red-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        </template>
                                        <p class="text-sm font-semibold text-gray-900 truncate px-4" x-text="fileName"></p>
                                        <button type="button" @click="fileName = null; filePreview = null; document.getElementById('attachment').value = '';" class="mt-2 text-xs font-bold text-red-600 hover:text-red-800 underline">Remove File</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-4 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition">Submit Ticket</button>
                            <button type="button" @click="isModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
