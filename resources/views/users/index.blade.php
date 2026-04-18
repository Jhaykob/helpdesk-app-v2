<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center" x-data>
            <h2 class="font-semibold text-xl text-red-700 leading-tight">
                {{ __('User Management') }}
            </h2>
            <button @click="$dispatch('open-modal')" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow transition ease-in-out duration-150 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                Add User
            </button>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen" x-data="{ isModalOpen: false }" @open-modal.window="isModalOpen = true">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <form method="GET" action="{{ route('users.index') }}" class="flex flex-col md:flex-row gap-4 items-end md:items-center">

                    <div class="w-full md:w-1/3 relative">
                        <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name or email..." class="block w-full pl-9 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="w-full md:w-1/4">
                        <label for="role" class="block text-xs font-medium text-gray-700 mb-1">Filter by Role</label>
                        <select name="role" id="role" class="block w-full py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                            <option value="">All Roles</option>
                            @foreach($roles as $roleOption)
                                <option value="{{ $roleOption->id }}" {{ request('role') == $roleOption->id ? 'selected' : '' }}>
                                    {{ ucfirst($roleOption->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:w-1/4">
                        <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Filter by Status</label>
                        <select name="status" id="status" class="block w-full py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                            <option value="">All Statuses</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>

                    <div class="w-full md:w-auto flex gap-2">
                        <button type="submit" class="w-full md:w-auto bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium border border-transparent transition shadow-sm">
                            Apply
                        </button>
                        @if(request()->hasAny(['search', 'role', 'status']) && (request('search') || request('role') || request()->filled('status')))
                            <a href="{{ route('users.index') }}" class="w-full md:w-auto flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition border border-gray-300">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-red-600">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered On</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change Role</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white text-xs {{ $user->role->name === 'admin' ? 'bg-red-600' : ($user->role->name === 'agent' ? 'bg-blue-600' : 'bg-gray-500') }} {{ !$user->is_active ? 'opacity-50' : '' }}">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <span class="{{ !$user->is_active ? 'text-gray-400 line-through' : '' }}">{{ $user->name }}</span>
                                        @if(!$user->is_active)
                                            <span class="ml-2 px-2 py-0.5 text-[10px] font-bold bg-red-100 text-red-800 rounded-full uppercase tracking-wider">Suspended</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $user->role->name === 'admin' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $user->role->name === 'agent' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $user->role->name === 'user' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ ucfirst($user->role->name) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($user->id !== Auth::id())
                                            <form method="POST" action="{{ route('users.toggleStatus', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-xs font-bold py-1.5 px-3 rounded shadow transition
                                                    {{ $user->is_active ? 'bg-orange-100 text-orange-800 hover:bg-orange-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }}">
                                                    {{ $user->is_active ? 'Suspend Access' : 'Reactivate' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Current User</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <form method="POST" action="{{ route('users.updateRole', $user) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                                        {{ ucfirst($role->name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1.5 px-3 rounded text-xs transition">
                                                Save
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        No users found matching your filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>

                </div>
            </div>
        </div>

        <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="isModalOpen" @click.away="isModalOpen = false" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-4 border-red-600">
                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-xl leading-6 font-bold text-gray-900 mb-6 border-b pb-2" id="modal-title">Provision New User</h3>

                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" required>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" required>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="block text-sm font-medium text-gray-700">Temporary Password</label>
                                <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" required minlength="8">
                                <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters long.</p>
                            </div>

                            <div class="mb-4">
                                <label for="role_id" class="block text-sm font-medium text-gray-700">Initial Role</label>
                                <select name="role_id" id="role_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ $role->name === 'user' ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-4 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition">Create User</button>
                            <button type="button" @click="isModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
