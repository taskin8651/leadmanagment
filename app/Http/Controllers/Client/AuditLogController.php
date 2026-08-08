<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::where('client_id', Auth::user()->client_id)->with('user')->latest('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs = $query->paginate(30)->withQueryString();
        $actions = AuditLog::where('client_id', Auth::user()->client_id)->distinct()->orderBy('action')->pluck('action');

        return view('client.audit-logs.index', compact('logs', 'actions'));
    }
}
