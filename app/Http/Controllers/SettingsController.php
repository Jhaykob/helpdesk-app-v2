<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class SettingsController extends Controller
{
    public function index()
    {
        // Fetch all settings from Cache with safe defaults
        $settings = [
            'company_name' => Cache::get('company_name', 'IT Help Desk'),
            'support_email' => Cache::get('support_email', 'support@company.com'),
            'timezone' => Cache::get('timezone', 'UTC'),
            'auto_assign' => Cache::get('auto_assign', 'disabled'),
            'default_priority' => Cache::get('default_priority', 'Low'),
            'allow_user_close' => Cache::get('allow_user_close', '1'),
            'send_email_notifications' => Cache::get('send_email_notifications', '1'),
            'sla_urgent_hours' => Cache::get('sla_urgent_hours', 1),
            'sla_high_hours' => Cache::get('sla_high_hours', 4),
            'sla_medium_hours' => Cache::get('sla_medium_hours', 24),
            'sla_low_hours' => Cache::get('sla_low_hours', 72),
            'email_template_created' => Cache::get('email_template_created', 'Hello {user_name}, your ticket #{ticket_id} has been successfully created.'),
            'email_template_resolved' => Cache::get('email_template_resolved', 'Hello {user_name}, your ticket #{ticket_id} has been marked as resolved.'),
            'auto_close_days' => Cache::get('auto_close_days', 3),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // 1. Validate all incoming settings
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'support_email' => 'required|email|max:255',
            'timezone' => 'required|string',
            'auto_assign' => 'required|string|in:disabled,round_robin',
            'default_priority' => 'required|string|in:Low,Medium,High,Urgent',
            'sla_urgent_hours' => 'required|integer|min:1',
            'sla_high_hours' => 'required|integer|min:1',
            'sla_medium_hours' => 'required|integer|min:1',
            'sla_low_hours' => 'required|integer|min:1',
            'email_template_created' => 'required|string|max:1000',
            'email_template_resolved' => 'required|string|max:1000',
            'auto_close_days' => 'required|integer|min:1|max:30',
        ]);

        // 2. Handle Checkboxes (If they aren't in the request, they were unchecked)
        $validated['allow_user_close'] = $request->has('allow_user_close') ? '1' : '0';
        $validated['send_email_notifications'] = $request->has('send_email_notifications') ? '1' : '0';

        // 3. Save everything permanently to the Cache
        foreach ($validated as $key => $value) {
            Cache::forever($key, $value);
        }

        // 4. Log the system change using the IDE-friendly Auth Facade
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated System Settings',
            'target_type' => 'System',
            'target_id' => 0,
            'new_value' => 'Global configurations modified via Admin UI.',
        ]);

        return back()->with('success', 'System settings saved successfully.');
    }
}
