<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->entity_type, fn ($q, $type) => $q->where('entity_type', 'like', "%{$type}"))
            ->when($request->action, fn ($q, $action) => $q->where('action', $action))
            ->latest('id')
            ->paginate(40)->withQueryString();

        $entityTypes = AuditLog::query()->select('entity_type')->distinct()->pluck('entity_type');

        return view('audit-logs.index', compact('logs', 'entityTypes'));
    }
}
