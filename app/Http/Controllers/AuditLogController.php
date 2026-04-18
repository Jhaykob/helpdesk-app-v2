<?php

namespace App\Http\Controllers;

use App\Models\AuditLog; // <-- Now using the generic Audit Log
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->role->name !== 'admin') {
            abort(403, 'Unauthorized access to global audit logs.');
        }

        $search = $request->input('search');
        $targetType = $request->input('target_type');

        $logs = AuditLog::with(['user'])
            ->when($search, function ($q, $search) {
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('action', 'like', "%{$search}%")
                        ->orWhere('target_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQ) use ($search) {
                            $userQ->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($targetType, function ($q, $targetType) {
                $q->where('target_type', $targetType);
            })
            ->latest()
            ->paginate(10) // Show a bit more per page since logs are dense
            ->withQueryString();

        return view('audit-logs.index', compact('logs'));
    }
}
