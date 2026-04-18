<?php

namespace App\Http\Controllers;

use App\Models\AuditLog; // <-- Now using the generic Audit Log
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index()
    {
        if (Auth::user()->role->name !== 'admin') {
            abort(403, 'Unauthorized access to global audit logs.');
        }

        $logs = AuditLog::with(['user'])
            ->latest()
            ->paginate(50);

        return view('audit-logs.index', compact('logs'));
    }
}
