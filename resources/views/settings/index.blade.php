<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Settings') }}
        </h2>
        <p class="text-sm text-gray-500 mt-1">Configure your help desk system</p>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('settings.update') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">General Settings</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                                    <input type="text" name="company_name" value="{{ $settings['company_name'] ?? 'IT Help Desk' }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                                    <input type="email" name="support_email" value="{{ $settings['support_email'] ?? 'support@company.com' }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Time Zone</label>
                                    <select name="timezone" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                        <option value="UTC" {{ ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                        <option value="America/New_York" {{ ($settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                                        <option value="Europe/London" {{ ($settings['timezone'] ?? '') == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                                        </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ticket Settings</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Auto-assign Tickets</label>
                                    <select name="auto_assign" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                        <option value="disabled" {{ ($settings['auto_assign'] ?? 'disabled') == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                        <option value="round_robin" {{ ($settings['auto_assign'] ?? '') == 'round_robin' ? 'selected' : '' }}>Round Robin (To active agents)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Priority</label>
                                    <select name="default_priority" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                        <option value="Low" {{ ($settings['default_priority'] ?? 'Low') == 'Low' ? 'selected' : '' }}>Low</option>
                                        <option value="Medium" {{ ($settings['default_priority'] ?? '') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="High" {{ ($settings['default_priority'] ?? '') == 'High' ? 'selected' : '' }}>High</option>
                                    </select>
                                </div>

                                <div class="pt-2 space-y-3">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="allow_user_close" value="1" {{ ($settings['allow_user_close'] ?? '1') == '1' ? 'checked' : '' }} class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded cursor-pointer">
                                        <label class="ml-2 block text-sm text-gray-900 font-medium cursor-pointer">Allow users to close their own tickets</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="send_email_notifications" value="1" {{ ($settings['send_email_notifications'] ?? '1') == '1' ? 'checked' : '' }} class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded cursor-pointer">
                                        <label class="ml-2 block text-sm text-gray-900 font-medium cursor-pointer">Send email notifications</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">SLA Settings <span class="text-xs font-normal text-gray-500">(Resolution Deadlines)</span></h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Urgent Priority Response Time (hours)</label>
                                    <input type="number" name="sla_urgent_hours" value="{{ $settings['sla_urgent_hours'] ?? '1' }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">High Priority Response Time (hours)</label>
                                    <input type="number" name="sla_high_hours" value="{{ $settings['sla_high_hours'] ?? '4' }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Medium Priority Response Time (hours)</label>
                                    <input type="number" name="sla_medium_hours" value="{{ $settings['sla_medium_hours'] ?? '24' }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Low Priority Response Time (hours)</label>
                                    <input type="number" name="sla_low_hours" value="{{ $settings['sla_low_hours'] ?? '72' }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Email Templates</h3>
                            <p class="text-xs text-gray-500 mb-4">Available variables: <code class="bg-gray-100 px-1 py-0.5 rounded text-red-600">{ticket_id}</code>, <code class="bg-gray-100 px-1 py-0.5 rounded text-red-600">{user_name}</code>, <code class="bg-gray-100 px-1 py-0.5 rounded text-red-600">{status}</code></p>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ticket Created Template</label>
                                    <textarea name="email_template_created" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">{{ $settings['email_template_created'] ?? 'Hello {user_name}, your ticket #{ticket_id} has been successfully created.' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ticket Resolved Template</label>
                                    <textarea name="email_template_resolved" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">{{ $settings['email_template_resolved'] ?? 'Hello {user_name}, your ticket #{ticket_id} has been marked as resolved.' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded shadow transition ease-in-out duration-150 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Save Changes
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
