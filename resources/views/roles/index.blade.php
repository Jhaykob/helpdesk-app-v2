<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Roles & Security') }}
            </h2>
            <button x-data="" x-on:click="$dispatch('open-modal', 'add-role-modal')" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow flex items-center gap-2 transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create Custom Role
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

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role Name</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Assigned Permissions</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($roles as $role)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            @if($role->name === 'super-admin')
                                                <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                                            @endif
                                            <div class="text-sm font-bold text-gray-900 uppercase tracking-wider">{{ str_replace('-', ' ', $role->name) }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($role->name === 'super-admin')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded-md bg-gray-800 text-white">ALL PERMISSIONS (MASTER KEY)</span>
                                        @elseif($role->permissions->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($role->permissions as $permission)
                                                    <span class="px-2 py-1 inline-flex text-[10px] leading-4 font-semibold rounded bg-gray-100 text-gray-700 border border-gray-200">
                                                        {{ str_replace('_', ' ', $permission->name) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">No specific permissions assigned.</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($role->name !== 'super-admin')
                                            <button x-data=""
                                                    x-on:click="$dispatch('open-modal', 'edit-role-modal'); $dispatch('set-edit-role', { id: {{ $role->id }}, name: '{{ addslashes($role->name) }}', permissions: {{ json_encode($role->permissions->pluck('name')) }} })"
                                                    class="text-indigo-600 hover:text-indigo-900 font-bold mr-3">Edit Policy</button>

                                            @if(!in_array($role->name, ['admin', 'agent', 'user']))
                                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this custom role? Users assigned to this role will lose these permissions.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Delete</button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="add-role-modal" focusable>
        <form method="POST" action="{{ route('roles.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900 mb-4">Create Custom Role</h2>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Role Name</label>
                    <input type="text" name="name" required placeholder="e.g. Tier 2 Support" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3 border-b pb-2">Assign Permissions</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-2 bg-gray-50 border border-gray-200 rounded-md">
                        @foreach($permissions as $permission)
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded cursor-pointer">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="perm_{{ $permission->id }}" class="font-medium text-gray-700 cursor-pointer capitalize">{{ str_replace('_', ' ', $permission->name) }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                <button type="button" x-on:click="$dispatch('close')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">Create Role</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit-role-modal" focusable>
        <div x-data="{ role: { id: '', name: '', permissions: [] } }" x-on:set-edit-role.window="role = $event.detail">
            <form :action="`/roles/${role.id}`" method="POST" class="p-6">
                @csrf
                @method('PATCH')

                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    Editing Policy for: <span class="text-red-600 uppercase" x-text="role.name.replace('-', ' ')"></span>
                </h2>
                <p class="text-xs text-gray-500 mb-6 pb-2 border-b">Note: Core role names cannot be changed to prevent system errors. You may only update their permissions here.</p>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Assigned Permissions</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-2 bg-gray-50 border border-gray-200 rounded-md">
                        @foreach($permissions as $permission)
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" x-model="role.permissions" id="edit_perm_{{ $permission->id }}" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded cursor-pointer">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="edit_perm_{{ $permission->id }}" class="font-medium text-gray-700 cursor-pointer capitalize">{{ str_replace('_', ' ', $permission->name) }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                    <button type="button" x-on:click="$dispatch('close')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-900 hover:bg-black">Save Policy</button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
