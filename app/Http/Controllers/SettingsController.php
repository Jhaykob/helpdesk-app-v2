<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        // For now, only Admins can access settings. We will upgrade this to Dynamic Permissions later.
        if (Auth::user()->role->name !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        // Pluck creates a clean associative array: ['company_name' => 'IT Help Desk', ...]
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        if (Auth::user()->role->name !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $data = $request->except(['_token', '_method']);

        // Handle checkboxes explicitly (HTML doesn't send them if unchecked)
        $checkboxes = ['allow_user_close', 'send_email_notifications'];
        foreach ($checkboxes as $checkbox) {
            if (!$request->has($checkbox)) {
                $data[$checkbox] = '0';
            }
        }

        // Loop through everything submitted and create/update the key-value pairs
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Log this action globally
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated System Settings',
            'target_type' => 'System',
            'target_id' => 0,
        ]);

        return back()->with('success', 'System settings updated successfully.');
    }
}
