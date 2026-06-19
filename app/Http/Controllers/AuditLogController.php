<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:audit.view');
    }

    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with(['user:id,name,email'])
            ->when($request->search, function ($q) use ($request) {
                $q->where('reference_number', 'like', "%{$request->search}%")
                    ->orWhere('action', 'like', "%{$request->search}%");
            })
            ->when($request->module, fn ($q) => $q->where('module', $request->module))
            ->when($request->action, fn ($q) => $q->where('action', $request->action))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'creates' => AuditLog::where('action', 'created')->count(),
            'updates' => AuditLog::where('action', 'updated')->count(),
        ];

        $modules = AuditLog::modules();
        $users = User::active()->orderBy('name')->get(['id', 'name']);

        return view('audit-logs.index', compact('logs', 'stats', 'modules', 'users'));
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load(['user', 'auditable']);

        return view('audit-logs.show', compact('auditLog'));
    }
}
