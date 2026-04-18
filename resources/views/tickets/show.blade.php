<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ticket <span class="text-red-600">#{{ $ticket->id }}</span>
            </h2>
            <a href="{{ route('tickets.index') }}" class="text-gray-600 hover:text-red-600 font-bold transition">
                &larr; Back to List
            </a>
        </div>
    </x-slot>

    <div x-data="{ showDeleteModal: false }" class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">

            <div class="w-full md:w-2/3 space-y-6">

                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-red-600">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $ticket->title }}</h3>

                        <div class="flex items-center gap-4 text-sm text-gray-500 mb-6 pb-4 border-b border-gray-100">
                            <p><strong>Created by:</strong> {{ $ticket->user->name }}</p>
                            <p><strong>Date:</strong> {{ $ticket->created_at->format('M d, Y h:i A') }}</p>
                        </div>

                        <div class="prose max-w-none text-gray-700 mb-8 whitespace-pre-wrap">{{ $ticket->description }}</div>

                        @if($ticket->attachments->count() > 0)
                            <div class="mt-6 pt-6 border-t border-gray-100">
                                <h4 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    Attachments
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($ticket->attachments as $attachment)
                                        <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg hover:border-red-400 hover:bg-red-50 transition">
                                            <svg class="w-8 h-8 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <span class="text-sm font-medium text-gray-700 truncate w-full">{{ $attachment->file_name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center">
                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        <h4 class="text-lg font-semibold text-gray-800">Discussion</h4>
                    </div>

                    <div class="p-6">
                        <div class="space-y-6 mb-8">
                            @forelse ($ticket->comments as $comment)

                                @if($comment->is_internal && Auth::user()->role->name === 'user')
                                    @continue
                                @endif

                                @php
                                    $bgClass = '';
                                    if ($comment->is_internal) {
                                        $bgClass = 'bg-yellow-50 border border-yellow-200 p-4 rounded-lg shadow-sm';
                                    } elseif ($comment->user->role->name === 'admin') {
                                        $bgClass = 'bg-red-50 border border-red-100 p-4 rounded-lg';
                                    } elseif ($comment->user->role->name === 'agent') {
                                        $bgClass = 'bg-blue-50 border border-blue-100 p-4 rounded-lg';
                                    }
                                @endphp

                                <div class="flex gap-4 {{ $bgClass }}">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white
                                            {{ $comment->user->role->name === 'admin' ? 'bg-red-600' : ($comment->user->role->name === 'agent' ? 'bg-blue-600' : 'bg-gray-600') }}">
                                            {{ substr($comment->user->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-1">
                                            <h5 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                                {{ $comment->user->name }}

                                                @if($comment->user->role->name === 'admin')
                                                    <span class="px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded-full">Admin</span>
                                                @elseif($comment->user->role->name === 'agent')
                                                    <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">Agent</span>
                                                @endif

                                                @if($comment->is_internal)
                                                    <span class="flex items-center text-xs font-bold text-yellow-700 bg-yellow-200 px-2 py-0.5 rounded">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                        Internal Note
                                                    </span>
                                                @endif
                                            </h5>
                                            <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700 whitespace-pre-wrap {{ $comment->is_internal ? 'italic' : '' }}">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-sm text-center italic">No comments yet. Start the conversation!</p>
                            @endforelse
                        </div>

                        <div class="border-t border-gray-100 pt-6">
                            <form method="POST" action="{{ route('comments.store', $ticket) }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="content" class="sr-only">Your Reply</label>
                                    <textarea name="content" id="content" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm" placeholder="Type your reply here..." required></textarea>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        @if(Auth::user()->role->name === 'admin' || Auth::user()->role->name === 'agent')
                                            <div class="flex items-center">
                                                <input type="checkbox" name="is_internal" id="is_internal" value="1" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                                <label for="is_internal" class="ml-2 text-sm text-gray-600 font-medium flex items-center cursor-pointer select-none">
                                                    <svg class="w-4 h-4 mr-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                                    Mark as Internal Note (Hidden from Customer)
                                                </label>
                                            </div>
                                        @endif
                                    </div>

                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow transition text-sm flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                        Post Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 overflow-hidden mt-6">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center">
                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h4 class="text-lg font-semibold text-gray-800">Activity History</h4>
                    </div>

                    <div class="p-6">
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @foreach($ticket->activities as $activity)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif

                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                        {{ $activity->action === 'created the ticket' ? 'bg-green-500' : '' }}
                                                        {{ $activity->action === 'claimed the ticket' ? 'bg-blue-500' : '' }}
                                                        {{ $activity->action === 'changed status' ? 'bg-gray-500' : '' }}">

                                                        @if($activity->action === 'created the ticket')
                                                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                        @elseif($activity->action === 'claimed the ticket')
                                                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                        @else
                                                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            <span class="font-medium text-gray-900">{{ $activity->user ? $activity->user->name : 'System' }}</span>
                                                            {{ $activity->action }}

                                                            @if($activity->old_value && $activity->new_value)
                                                                from <span class="font-semibold text-gray-700">{{ $activity->old_value }}</span>
                                                                to <span class="font-semibold text-gray-900">{{ $activity->new_value }}</span>
                                                            @elseif($activity->new_value)
                                                                to <span class="font-semibold text-gray-900">{{ $activity->new_value }}</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-xs whitespace-nowrap text-gray-500">
                                                        <time datetime="{{ $activity->created_at }}">{{ $activity->created_at->format('M d, g:i A') }}</time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

            </div>

            <div class="w-full md:w-1/3 space-y-6">

                <div class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-100">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Ticket Info</h4>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Status</p>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full mt-1
                                {{ $ticket->status === 'Unassigned' ? 'bg-gray-100 text-gray-800 border border-gray-200' : '' }}
                                {{ $ticket->status === 'Open' ? 'bg-red-100 text-red-800' : '' }}
                                {{ str_contains($ticket->status, 'Pending') ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $ticket->status === 'Resolved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $ticket->status === 'Closed' ? 'bg-gray-200 text-gray-800' : '' }}">
                                {{ $ticket->status }}
                            </span>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 font-medium">Priority</p>
                            <span class="px-2 py-1 text-sm rounded border inline-block mt-1
                                {{ $ticket->priority === 'High' ? 'border-red-500 text-red-600 bg-red-50' : 'border-gray-300 text-gray-700 bg-gray-50' }}">
                                {{ $ticket->priority }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($ticket->assignedTo)
                    <div class="bg-blue-50 shadow-sm sm:rounded-lg p-6 border border-blue-100">
                        <h4 class="text-sm font-semibold text-blue-900 mb-3 uppercase tracking-wider">Assigned Technician</h4>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white bg-blue-600 shadow-sm">
                                {{ substr($ticket->assignedTo->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $ticket->assignedTo->name }}</p>
                                <p class="text-xs text-blue-700 font-medium">{{ $ticket->assignedTo->email }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(Auth::user()->can('claim', $ticket))
                    <div class="bg-blue-50 shadow-sm sm:rounded-lg p-6 border border-blue-200 text-center">
                        <h4 class="text-lg font-bold text-blue-900 mb-2">Unassigned Ticket</h4>
                        <p class="text-sm text-blue-700 mb-4">This ticket is waiting for a technician.</p>
                        <form method="POST" action="{{ route('tickets.claim', $ticket) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded shadow-md text-sm transition">
                                Claim This Ticket
                            </button>
                        </form>
                    </div>
                @elseif(Auth::user()->can('update', $ticket))
                    <div class="bg-red-50 shadow-sm sm:rounded-lg p-6 border border-red-100">
                        <h4 class="text-lg font-semibold text-red-900 mb-4 border-b border-red-200 pb-2">Technician Controls</h4>

                        <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="mb-6">
                            @csrf
                            @method('PUT')
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Update Status</label>
                            <div class="flex gap-2">
                                <select name="status" id="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="Open" {{ $ticket->status == 'Open' ? 'selected' : '' }}>Open</option>
                                    <option value="Pending Customer" {{ $ticket->status == 'Pending Customer' ? 'selected' : '' }}>Pending Customer</option>
                                    <option value="Pending Technician" {{ $ticket->status == 'Pending Technician' ? 'selected' : '' }}>Pending Technician</option>
                                    <option value="Resolved" {{ $ticket->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                    @if(Auth::user()->role->name === 'admin')
                                        <option value="Closed" {{ $ticket->status == 'Closed' ? 'selected' : '' }}>Closed</option>
                                    @endif
                                </select>
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow text-sm transition">
                                    Update
                                </button>
                            </div>
                        </form>

                        @if(Auth::user()->can('delete', $ticket))
                            <button type="button" @click="showDeleteModal = true" class="w-full bg-white border border-red-300 text-red-700 hover:bg-red-100 font-bold py-2 px-4 rounded shadow-sm text-sm transition flex justify-center items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Delete Ticket
                            </button>
                        @endif
                    </div>
                @endif

            </div>
        </div>

        <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showDeleteModal" @click.away="showDeleteModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-4 border-red-600">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Delete Ticket</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Are you sure you want to completely delete this ticket? All of its data and attachments will be permanently removed. This action cannot be undone.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <form method="POST" action="{{ route('tickets.destroy', $ticket) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Yes, Delete</button>
                        </form>
                        <button type="button" @click="showDeleteModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
