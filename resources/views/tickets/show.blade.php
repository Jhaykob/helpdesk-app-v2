<x-app-layout>
    @php
        $isClosed = $ticket->status === 'Closed';
        $isAdmin = Auth::user()->role->name === 'admin';
        $isReadOnly = $isClosed && !$isAdmin;
    @endphp

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <span class="text-gray-400">#{{ $ticket->id }}</span>
                {{ $ticket->title }}
                @if($isReadOnly)
                    <span class="ml-2 px-2 py-1 bg-gray-200 text-gray-600 text-xs font-bold rounded uppercase tracking-wider flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Archived / Read-Only
                    </span>
                @endif
            </h2>
            <a href="{{ route('tickets.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                &larr; Back to Tickets
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col lg:flex-row gap-6">

            <div class="w-full lg:w-2/3 space-y-6">

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                        <ul class="list-disc pl-5 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-red-600">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center gap-3 mb-4 border-b pb-4">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center font-bold text-red-700">
                                {{ substr($ticket->user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-bold text-md text-gray-900">{{ $ticket->user->name }}</p>
                                <p class="text-xs text-gray-500">Submitted on {{ $ticket->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        <div class="prose max-w-none text-gray-800 whitespace-pre-wrap">{{ $ticket->description }}</div>

                        @if($ticket->attachment_path)
                            <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm text-gray-700 font-medium">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    Attached File
                                </div>
                                <a href="{{ Storage::url($ticket->attachment_path) }}" target="_blank" class="text-sm font-bold text-red-600 hover:text-red-800 underline">
                                    View / Download
                               </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    @if(in_array($ticket->status, ['Resolved', 'Closed']) && !$ticket->rating && Auth::id() === $ticket->user_id)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 shadow-sm" x-data="{ rating: 0, hoverRating: 0 }">
                            <h3 class="text-lg font-bold text-yellow-800 mb-2">How did we do?</h3>
                            <p class="text-sm text-yellow-700 mb-4">Your ticket is marked as <strong>{{ $ticket->status }}</strong>. Please rate your support experience.</p>

                            <form method="POST" action="{{ route('tickets.rate', $ticket) }}">
                                @csrf
                                <input type="hidden" name="rating" x-model="rating" required>

                                <div class="flex items-center gap-2 mb-4 cursor-pointer" @mouseleave="hoverRating = 0">
                                    <template x-for="star in 5" :key="star">
                                        <svg
                                            @mouseover="hoverRating = star"
                                            @click="rating = star"
                                            class="w-10 h-10 transition-colors duration-150"
                                            :class="(hoverRating >= star || rating >= star) ? 'text-yellow-400' : 'text-gray-300'"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    </template>
                                    <span x-show="rating > 0" x-text="rating + ' Star' + (rating > 1 ? 's' : '')" class="ml-3 font-bold text-yellow-800"></span>
                                </div>

                                <div class="mb-4" x-show="rating > 0" x-transition>
                                    <label for="csat_feedback" class="block text-sm font-medium text-yellow-800 mb-1">Additional Feedback (Optional)</label>
                                    <textarea name="csat_feedback" id="csat_feedback" rows="3" class="w-full rounded-md border-yellow-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-sm" placeholder="Tell us what went well or how we can improve..."></textarea>
                                </div>

                                <button type="submit" x-show="rating > 0" x-transition class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded shadow transition">
                                    Submit Feedback
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($ticket->rating)
                        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">Customer Satisfaction Score</h3>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-6 h-6 {{ $i <= $ticket->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="font-bold text-gray-900">{{ $ticket->rating }} out of 5 Stars</span>
                            </div>
                            @if($ticket->csat_feedback)
                                <div class="mt-3 p-3 bg-gray-50 rounded text-sm text-gray-700 italic border border-gray-100">
                                    "{{ $ticket->csat_feedback }}"
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-6 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-bold text-lg text-gray-800">Communication History</h3>
                    </div>

                    <div class="p-6 space-y-6">
                        @forelse($ticket->comments as $comment)
                            @if($comment->is_internal && Auth::user()->role->name === 'user')
                                @continue
                            @endif

                            <div class="flex gap-4 {{ $comment->user_id === Auth::id() ? 'flex-row-reverse' : '' }}">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-bold text-white text-xs {{ $comment->user->role->name === 'admin' ? 'bg-red-600' : ($comment->user->role->name === 'agent' ? 'bg-blue-600' : 'bg-gray-500') }}">
                                    {{ substr($comment->user->name, 0, 1) }}
                                </div>
                                <div class="flex max-w-2xl flex-col {{ $comment->user_id === Auth::id() ? 'items-end' : 'items-start' }}">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-bold text-gray-900">{{ $comment->user->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>

                                    @php
                                        $bgClass = $comment->is_internal
                                            ? 'bg-yellow-50 border border-yellow-200 text-yellow-900'
                                            : ($comment->user_id === Auth::id() ? 'bg-red-50 border border-red-100 text-gray-800 rounded-tr-none' : 'bg-gray-100 border border-gray-200 text-gray-800 rounded-tl-none');
                                    @endphp

                                    <div class="p-3 rounded-lg text-sm {{ $bgClass }}">
                                        @if($comment->is_internal)
                                            <div class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-yellow-600 mb-2 border-b border-yellow-200 pb-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                Internal Note
                                            </div>
                                        @endif

                                        {!! nl2br(e($comment->content)) !!}

                                        @if($comment->attachment_path)
                                            <div class="mt-3 pt-3 border-t {{ $comment->is_internal ? 'border-yellow-200' : ($comment->user_id === Auth::id() ? 'border-red-200' : 'border-gray-300') }}">
                                                <a href="{{ Storage::url($comment->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold {{ $comment->is_internal ? 'text-yellow-700 hover:text-yellow-900' : 'text-blue-600 hover:text-blue-800' }} underline">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    View Attachment
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 italic text-center py-4">No comments yet. Be the first to reply.</p>
                        @endforelse
                    </div>

                    @if($isReadOnly)
                        <div class="p-6 bg-gray-100 border-t border-gray-200 text-center">
                            <p class="text-sm text-gray-500">This ticket is closed. New replies have been disabled.</p>
                        </div>
                    @else
                        <div class="p-6 bg-gray-50 border-t border-gray-200">
                            <form method="POST" action="{{ route('comments.store', $ticket) }}" enctype="multipart/form-data">
                                @csrf

                                @if(Auth::user()->role->name === 'admin' || Auth::user()->role->name === 'agent')
                                    <div class="mb-3 flex items-center bg-yellow-50 border border-yellow-200 p-2 rounded-md w-max">
                                        <input type="checkbox" name="is_internal" id="is_internal" value="1" class="rounded border-gray-300 text-yellow-600 shadow-sm focus:ring-yellow-500 cursor-pointer">
                                        <label for="is_internal" class="ml-2 text-xs font-bold text-yellow-800 cursor-pointer flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                            Mark as Internal Note
                                        </label>
                                    </div>
                                @endif

                                <div>
                                    <label for="content" class="sr-only">Add a comment</label>
                                    <textarea id="content" name="content" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm" placeholder="Type your reply here..." required></textarea>
                                </div>

                                <div class="mt-3 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                    <div class="w-full sm:w-auto">
                                        <input type="file" name="attachment" id="attachment" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300 transition cursor-pointer" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                        <p class="mt-1 text-[10px] text-gray-400">Max size: 2MB (JPG, PNG, PDF, DOCX)</p>
                                    </div>

                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition w-full sm:w-auto justify-center">
                                        Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-1/3 space-y-6">

                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 p-6">
                    <h3 class="font-bold text-gray-900 border-b pb-2 mb-4">Ticket Details</h3>

                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Status</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $ticket->status === 'Unassigned' ? 'bg-gray-100 text-gray-800 border border-gray-200' : '' }}
                                {{ $ticket->status === 'Open' ? 'bg-red-100 text-red-800' : '' }}
                                {{ str_contains($ticket->status, 'Pending') ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $ticket->status === 'Resolved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $ticket->status === 'Closed' ? 'bg-gray-200 text-gray-800' : '' }}">
                                {{ $ticket->status }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Priority</span>
                            <span class="px-2 py-1 text-xs rounded border font-semibold
                                {{ $ticket->priority === 'High' ? 'border-red-500 text-red-600 bg-red-50' : 'border-gray-300 text-gray-700 bg-gray-50' }}">
                                {{ $ticket->priority }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center border-t pt-4">
                            <span class="text-gray-500">Assigned To</span>
                            <span class="font-medium {{ $ticket->assignedTo ? 'text-gray-900' : 'text-gray-400 italic' }}">
                                {{ $ticket->assignedTo ? $ticket->assignedTo->name : 'Unassigned' }}
                            </span>
                        </div>

                        <div class="flex justify-between items-start border-t pt-4">
                            <span class="text-gray-500">SLA Deadline</span>
                            <div class="text-right">
                                <span class="block font-medium text-gray-900">{{ $ticket->sla_deadline->format('M d, Y') }}</span>
                                <span class="block text-xs {{ $ticket->is_breaching_sla ? 'text-red-600 font-bold animate-pulse' : 'text-gray-500' }}">
                                    {{ $ticket->sla_deadline->format('g:i A') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if(Auth::user()->role->name === 'admin' || Auth::user()->role->name === 'agent')
                    <div class="bg-gray-50 shadow-sm sm:rounded-lg border border-gray-200 p-6 relative"
                         x-data="{
                            showToast: false,
                            toastMessage: '',
                            isUpdating: false,
                            oldValues: {
                                status: '{{ $ticket->status }}',
                                priority: '{{ $ticket->priority }}',
                                assigned_to: '{{ $ticket->assigned_to }}'
                            },
                            confirmAndUpdate(field, event) {
                                // Get the human-readable text of what they selected
                                let selectedText = event.target.options[event.target.selectedIndex].text;
                                let friendlyField = field.replace('_', ' ').toUpperCase();

                                // 1. Fire the native confirmation prompt
                                if (!confirm(`Are you sure you want to change the ${friendlyField} to '${selectedText}'?`)) {
                                    // 2. If they hit cancel, revert the dropdown back to what it was
                                    event.target.value = this.oldValues[field];
                                    return;
                                }

                                // 3. If they hit OK, update our 'old values' tracker and run the AJAX fetch
                                this.oldValues[field] = event.target.value;
                                this.updateTicket(field, event.target.value);
                            },
                            updateTicket(field, value) {
                                this.isUpdating = true;

                                let formData = new FormData();
                                formData.append('_method', 'PATCH');
                                formData.append('_token', '{{ csrf_token() }}');
                                formData.append(field, value);

                                fetch('{{ route('tickets.update', $ticket) }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    },
                                    body: formData
                                })
                                .then(response => {
                                    if (!response.ok) throw new Error('Update failed or forbidden.');
                                    return response.json();
                                })
                                .then(data => {
                                    this.toastMessage = data.message || 'Saved successfully!';
                                    this.showToast = true;

                                    // Hide toast after 2 seconds, then refresh timeline
                                    setTimeout(() => {
                                        this.showToast = false;
                                        setTimeout(() => window.location.reload(), 300);
                                    }, 2000);
                                })
                                .catch(error => {
                                    alert('Error: You do not have permission, or the system failed to save.');
                                    // Revert the value if the backend rejected it
                                    window.location.reload();
                                })
                                .finally(() => {
                                    this.isUpdating = false;
                                });
                            }
                         }">

                        <div x-show="showToast"
                             x-transition
                             class="fixed bottom-6 right-6 z-50 bg-gray-900 text-white px-5 py-3 rounded-lg shadow-2xl flex items-center gap-3 border-l-4 border-green-500"
                             style="display: none;">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="toastMessage" class="font-medium text-sm"></span>
                        </div>

                        <h3 class="font-bold text-gray-900 border-b pb-2 mb-4 text-sm uppercase tracking-wider flex justify-between items-center">
                            Update Ticket
                            <svg x-show="isUpdating" class="animate-spin h-4 w-4 text-red-600" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </h3>

                        @if($isReadOnly)
                            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 text-xs text-red-700 shadow-sm rounded-r-md">
                                <p class="font-bold flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Ticket Closed
                                </p>
                                <p class="mt-1">This ticket is locked. Only an Administrator can make further modifications.</p>
                            </div>
                        @endif

                        <form onsubmit="event.preventDefault();">
                            <div class="space-y-4">
                                <div>
                                    <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Change Status</label>
                                    <select id="status" @change="confirmAndUpdate('status', $event)" :disabled="isUpdating || {{ $isReadOnly ? 'true' : 'false' }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm disabled:bg-gray-200 disabled:text-gray-500 disabled:cursor-not-allowed transition">
                                        <option value="Unassigned" {{ $ticket->status == 'Unassigned' ? 'selected' : '' }}>Unassigned</option>
                                        <option value="Open" {{ $ticket->status == 'Open' ? 'selected' : '' }}>Open</option>
                                        <option value="Pending Customer" {{ $ticket->status == 'Pending Customer' ? 'selected' : '' }}>Pending Customer</option>
                                        <option value="Pending Technician" {{ $ticket->status == 'Pending Technician' ? 'selected' : '' }}>Pending Technician</option>
                                        <option value="Resolved" {{ $ticket->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                        <option value="Closed" {{ $ticket->status == 'Closed' ? 'selected' : '' }}>Closed</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="priority" class="block text-xs font-medium text-gray-700 mb-1">Change Priority</label>
                                    <select id="priority" @change="confirmAndUpdate('priority', $event)" :disabled="isUpdating || {{ $isReadOnly ? 'true' : 'false' }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm disabled:bg-gray-200 disabled:text-gray-500 disabled:cursor-not-allowed transition">
                                        <option value="Low" {{ $ticket->priority == 'Low' ? 'selected' : '' }}>Low</option>
                                        <option value="Medium" {{ $ticket->priority == 'Medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="High" {{ $ticket->priority == 'High' ? 'selected' : '' }}>High</option>
                                    </select>
                                </div>

                                @if(isset($agents))
                                <div>
                                    <label for="assigned_to" class="block text-xs font-medium text-gray-700 mb-1">Assign Agent</label>
                                    <select id="assigned_to" @change="confirmAndUpdate('assigned_to', $event)" :disabled="isUpdating || {{ $isReadOnly ? 'true' : 'false' }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm disabled:bg-gray-200 disabled:text-gray-500 disabled:cursor-not-allowed transition">
                                        <option value="">-- Unassigned --</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}" {{ $ticket->assigned_to == $agent->id ? 'selected' : '' }}>
                                                {{ $agent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                        </form>
                    </div>
                @endif

                @if(isset($activities) && $activities->count() > 0)
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 p-6">
                    <h3 class="font-bold text-gray-900 border-b pb-4 mb-4 text-sm uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Activity Timeline
                    </h3>

                    <div class="relative border-l border-gray-200 ml-3 space-y-6">
                        @foreach($activities as $activity)
                            <div class="mb-4 ml-6 relative">
                                <span class="absolute flex items-center justify-center w-6 h-6 bg-red-100 rounded-full -left-9 ring-4 ring-white">
                                    <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </span>
                                <h4 class="mb-1 text-sm font-semibold text-gray-900">{{ $activity->action }}</h4>
                                @if($activity->new_value && $activity->old_value)
                                    <p class="text-xs text-gray-500 mb-1">Changed from <strong>{{ $activity->old_value }}</strong> to <strong>{{ $activity->new_value }}</strong></p>
                                @endif
                                <div class="flex items-center text-xs text-gray-400 gap-2 mt-1">
                                    <span>By {{ $activity->user ? $activity->user->name : 'System' }}</span>
                                    <span>&bull;</span>
                                    <time>{{ $activity->created_at->format('M d, H:i') }}</time>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
