<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Management') }}
            </h2>
            <button x-data="" x-on:click="$dispatch('open-modal', 'add-user-modal')" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow flex items-center gap-2 transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New User
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
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <form method="GET" action="{{ route('users.index') }}" class="flex w-full max-w-md">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..." class="w-full rounded-l-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-r-md hover:bg-gray-700 transition font-semibold text-sm">Search</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Assigned Role</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Overrides</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($users as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-red-100 flex items-center justify-center font-bold text-red-700 text-xs">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $roleName = $user->roles->first() ? $user->roles->first()->name : 'unassigned';
                                        @endphp
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $roleName === 'super-admin' ? 'bg-yellow-100 text-yellow-800' : ($roleName === 'admin' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ strtoupper(str_replace('-', ' ', $roleName)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($roleName === 'super-admin')
                                            <span class="text-xs text-gray-400 italic">Master Key Active</span>
                                        @elseif($user->permissions->count() > 0)
                                            <span class="text-xs font-bold text-blue-600 flex items-center gap-1 cursor-help" title="{{ $user->permissions->pluck('name')->implode(', ') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ $user->permissions->count() }} Custom Overrides
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->is_active)
                                            <span class="flex items-center gap-1 text-sm text-green-600 font-medium"><span class="w-2 h-2 rounded-full bg-green-500"></span> Active</span>
                                        @else
                                            <span class="flex items-center gap-1 text-sm text-gray-500 font-medium"><span class="w-2 h-2 rounded-full bg-gray-400"></span> Suspended</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button x-data=""
                                                x-on:click="$dispatch('open-modal', 'edit-user-modal'); $dispatch('set-edit-user', { id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}', role: '{{ $roleName }}', is_active: {{ $user->is_active ? 'true' : 'false' }}, permissions: {{ json_encode($user->permissions->pluck('name')) }} })"
                                                class="text-indigo-600 hover:text-indigo-900 font-bold mr-3">Edit</button>

                                        @if($user->id !== Auth::id() && !$user->hasRole('super-admin'))
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you absolutely sure you want to delete this user? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Delete</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    <x-modal name="add-user-modal" focusable>
        <form method="POST" action="{{ route('users.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900 mb-4">Create New User</h2>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Assign Role</label>
                        <select name="role" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ strtoupper(str_replace('-', ' ', $role->name)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Temporary Password</label>
                        <input type="password" name="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200 mt-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Direct Permission Overrides (Optional)</label>
                    <p class="text-xs text-gray-500 mb-3">Check these boxes to grant this specific user extra capabilities beyond their assigned role.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-3 bg-gray-50 border border-gray-200 rounded-md">
                        @foreach($permissions as $permission)
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_add_{{ $permission->id }}" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded cursor-pointer">
                                </div>
                                <div class="ml-3 text-xs">
                                    <label for="perm_add_{{ $permission->id }}" class="font-medium text-gray-700 cursor-pointer capitalize">{{ str_replace('_', ' ', $permission->name) }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" x-on:click="$dispatch('close')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">Cancel</button>
                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none">Create User</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit-user-modal" focusable>
        <div x-data="{ user: { id: '', name: '', email: '', role: '', is_active: false, permissions: [] } }"
             x-on:set-edit-user.window="user = $event.detail">

            <form :action="`/users/${user.id}`" method="POST" class="p-6">
                @csrf
                @method('PATCH')

                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit User Account</h2>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" x-model="user.name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" x-model="user.email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Change Role</label>
                            <select name="role" x-model="user.role" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm" :disabled="user.role === 'super-admin'">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ strtoupper(str_replace('-', ' ', $role->name)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Update Password <span class="text-xs text-gray-400 font-normal">(Optional)</span></label>
                            <input type="password" name="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-200 mt-4" x-show="user.role !== 'super-admin'">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Direct Permission Overrides</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-3 bg-gray-50 border border-gray-200 rounded-md">
                            @foreach($permissions as $permission)
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" x-model="user.permissions" id="edit_perm_{{ $permission->id }}" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded cursor-pointer">
                                    </div>
                                    <div class="ml-3 text-xs">
                                        <label for="edit_perm_{{ $permission->id }}" class="font-medium text-gray-700 cursor-pointer capitalize">{{ str_replace('_', ' ', $permission->name) }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2 border-t mt-4 flex items-center">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" x-model="user.is_active" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded cursor-pointer">
                        <label for="edit_is_active" class="ml-2 block text-sm text-gray-900 font-medium cursor-pointer">Account is Active</label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" x-on:click="$dispatch('close')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-800 hover:bg-gray-900">Save Changes</button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
