<x-app-layout title="Audit Log">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex items-start gap-3">
            <div class="mi-page-icon"><i class="fas fa-shield-halved"></i></div>
            <div>
                <h1 class="text-[1.35rem] font-bold text-gray-900">Audit Log</h1>
                <p class="text-sm text-gray-500">Traceability of critical create, update, and delete actions.</p>
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Total Entries</p><p class="mi-kpi-value">{{ $stats['total'] }}</p></div></div>
            <div class="mi-kpi mi-kpi-amber"><div><p class="mi-kpi-label">Today</p><p class="mi-kpi-value">{{ $stats['today'] }}</p></div></div>
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">Creates</p><p class="mi-kpi-value">{{ $stats['creates'] }}</p></div></div>
            <div class="mi-kpi mi-kpi-orange"><div><p class="mi-kpi-label">Updates</p><p class="mi-kpi-value orange">{{ $stats['updates'] }}</p></div></div>
        </div>

        <form method="GET" class="mi-card p-4">
            <div class="mi-form-grid items-end">
                <div>
                    <label class="mi-field-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="mi-input block w-full" placeholder="Reference or action…">
                </div>
                <div>
                    <label class="mi-field-label">Module</label>
                    <select name="module" class="mi-select">
                        <option value="">All modules</option>
                        @foreach ($modules as $key => $label)
                            <option value="{{ $key }}" @selected(request('module') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mi-field-label">Action</label>
                    <select name="action" class="mi-select">
                        <option value="">All actions</option>
                        @foreach (['created', 'updated', 'deleted'] as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst($action) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mi-field-label">User</label>
                    <select name="user_id" class="mi-select">
                        <option value="">All users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mi-field-label">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="mi-input block w-full">
                </div>
                <div>
                    <label class="mi-field-label">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="mi-input block w-full">
                </div>
                <div><button type="submit" class="mi-btn-orange"><i class="fas fa-filter text-xs"></i> Filter</button></div>
            </div>
        </form>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Reference</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="text-sm text-gray-500">{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $log->user?->name ?? 'System' }}</td>
                                <td><span class="mi-status-pending">{{ $log->actionLabel() }}</span></td>
                                <td>{{ $log->moduleLabel() }}</td>
                                <td>{{ $log->reference_number ?? '—' }}</td>
                                <td><a href="{{ route('audit-logs.show', $log) }}" class="mi-action view"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-12 text-gray-400">No audit entries yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())<div class="mi-card-foot">{{ $logs->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
