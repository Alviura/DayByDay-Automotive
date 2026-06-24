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
        $actionFilter = $request->string('action')->toString();

        $logs = AuditLog::query()
            ->with(['user:id,name,email'])
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($request->module, fn ($q) => $q->where('module', $request->module))
            ->when($actionFilter, fn ($q) => $q->where('action', $actionFilter))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'this_week' => AuditLog::where('created_at', '>=', now()->startOfWeek())->count(),
            'creates' => AuditLog::where('action', 'created')->count(),
            'updates' => AuditLog::where('action', 'updated')->count(),
            'deletes' => AuditLog::where('action', 'deleted')->count(),
        ];

        $pipeline = [
            ['key' => '', 'label' => 'All events', 'icon' => 'fa-list', 'count' => $stats['total']],
            ['key' => 'created', 'label' => 'Created', 'icon' => 'fa-plus', 'count' => $stats['creates']],
            ['key' => 'updated', 'label' => 'Updated', 'icon' => 'fa-pen', 'count' => $stats['updates']],
            ['key' => 'deleted', 'label' => 'Deleted', 'icon' => 'fa-trash', 'count' => $stats['deletes']],
        ];

        $modules = collect(AuditLog::modules())
            ->merge(
                AuditLog::query()
                    ->whereNotNull('module')
                    ->distinct()
                    ->orderBy('module')
                    ->pluck('module')
                    ->mapWithKeys(fn (string $key) => [$key => AuditLog::modules()[$key] ?? str($key)->replace(['-', '_'], ' ')->title()->toString()])
            )
            ->sortKeys();

        $users = User::active()->orderBy('name')->get(['id', 'name']);

        $hasFilters = $request->hasAny(['search', 'module', 'action', 'user_id', 'date_from', 'date_to']);

        return view('audit-logs.index', compact(
            'logs',
            'stats',
            'modules',
            'users',
            'pipeline',
            'actionFilter',
            'hasFilters',
        ));
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load(['user', 'auditable']);

        return view('audit-logs.show', compact('auditLog'));
    }
}
