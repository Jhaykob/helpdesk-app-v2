<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Canned Responses') }}
            </h2>
            <button x-data="" x-on:click="$dispatch('open-modal', 'add-macro-modal')" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow flex items-center gap-2 transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Response
            </button>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($macros as $macro)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col h-full relative group">

                        <div class="flex justify-between items-start mb-3">
                            <h3 class="font-bold text-gray-900 text-lg pr-4">{{ $macro->title }}</h3>
                            @if($macro->is_global)
                                <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider whitespace-nowrap">Global</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider whitespace-nowrap">Personal</span>
                            @endif
                        </div>

                        <p class="text-sm text-gray-600 flex-grow font-mono bg-gray-50 p-3 rounded border border-gray-100 mb-4 whitespace-pre-wrap">{{ $macro->content }}</p>

                        <div class="flex justify-between items-center mt-auto pt-4 border-t border-gray-100">
                            <span class="text-xs text-gray-400">By {{ $macro->user ? $macro->user->name : 'System' }}</span>

                            @php
                                $canEdit = false;
                                if ($macro->is_global && (Auth::user()->hasRole('admin') || Auth::user()->can('manage_global_macros'))) {
                                    $canEdit = true;
                                } elseif (!$macro->is_global && $macro->user_id === Auth::id()) {
                                    $canEdit = true;
                                }
                            @endphp

                            @if($canEdit)
                                <div class="flex gap-3">
                                    <button x-data=""
                                            x-on:click="$dispatch('open-modal', 'edit-macro-modal'); $dispatch('set-edit-macro', { id: {{ $macro->id }}, title: '{{ addslashes($macro->title) }}', content: `{{ addslashes($macro->content) }}`, is_global: {{ $macro->is_global ? 'true' : 'false' }} })"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm font-bold">Edit</button>

                                    <form action="{{ route('macros.destroy', $macro) }}" method="POST" onsubmit="return confirm('Delete this canned response?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-bold">Delete</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-12 text-center rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No canned responses yet</h3>
                        <p class="text-gray-500">Create templates for common answers to speed up your workflow.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $macros->links() }}
            </div>
        </div>
    </div>

    <x-modal name="add-macro-modal" focusable>
        <form method="POST" action="{{ route('macros.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900 mb-4">New Canned Response</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title (Shortcut Name)</label>
                    <input type="text" name="title" required placeholder="e.g. Printer Reset Instructions" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Response Content</label>
                    <textarea name="content" rows="6" required placeholder="Type the pre-written message here..." class="block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"></textarea>
                </div>

                @if(Auth::user()->hasRole('admin') || Auth::user()->can('manage_global_macros'))
                    <div class="pt-2 border-t mt-4 flex items-center">
                        <input type="checkbox" name="is_global" id="is_global" value="1" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded cursor-pointer">
                        <label for="is_global" class="ml-2 block text-sm text-gray-900 font-medium cursor-pointer">Make Global (Visible to all Agents)</label>
                    </div>
                @endif
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">Save Response</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit-macro-modal" focusable>
        <div x-data="{ macro: { id: '', title: '', content: '', is_global: false } }"
             x-on:set-edit-macro.window="macro = $event.detail">

            <form :action="`/macros/${macro.id}`" method="POST" class="p-6">
                @csrf
                @method('PATCH')

                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Canned Response</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" x-model="macro.title" required class="block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Response Content</label>
                        <textarea name="content" x-model="macro.content" rows="6" required class="block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"></textarea>
                    </div>

                    @if(Auth::user()->hasRole('admin') || Auth::user()->can('manage_global_macros'))
                        <div class="pt-2 border-t mt-4 flex items-center">
                            <input type="checkbox" name="is_global" id="edit_is_global" value="1" x-model="macro.is_global" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded cursor-pointer">
                            <label for="edit_is_global" class="ml-2 block text-sm text-gray-900 font-medium cursor-pointer">Make Global</label>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" x-on:click="$dispatch('close')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-900 hover:bg-black">Update Response</button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
