<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $action = $request->input('action');
        $userId = $request->input('user_id');
        $search = $request->input('search');

        $query = AuditLog::with('user');

        if ($action) {
            $query->where('action', $action);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($search) {
            $query->where('description', 'like', "%{$search}%");
        }

        $logs = $query->latest()->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('audit_logs.index', compact('logs', 'users', 'actions', 'action', 'userId', 'search'));
    }
}
