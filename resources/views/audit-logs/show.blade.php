<x-app-layout title="Audit Entry #{{ $auditLog->id }}">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex justify-between gap-4">
            <div>
                <h1 class="text-[1.35rem] font-bold">Audit Entry #{{ $auditLog->id }}</h1>
                <p class="text-sm text-gray-500">{{ $auditLog->created_at->format('d M Y H:i:s') }} · {{ $auditLog->user?->name ?? 'System' }}</p>
            </div>
            <a href="{{ route('audit-logs.index') }}" class="mi-btn-ghost">Back</a>
        </div>

        <div class="mi-card p-5">
            <dl class="mi-detail-grid">
                <div class="mi-detail-item"><dt class="mi-detail-label">Action</dt><dd>{{ $auditLog->actionLabel() }}</dd></div>
                <div class="mi-detail-item"><dt class="mi-detail-label">Module</dt><dd>{{ $auditLog->moduleLabel() }}</dd></div>
                <div class="mi-detail-item"><dt class="mi-detail-label">Reference</dt><dd>{{ $auditLog->reference_number ?? '—' }}</dd></div>
                <div class="mi-detail-item"><dt class="mi-detail-label">IP Address</dt><dd>{{ $auditLog->ip_address ?? '—' }}</dd></div>
            </dl>
        </div>

        @if (count($auditLog->changedFields()) > 0)
            <div class="mi-card">
                <div class="mi-card-head"><span class="text-sm font-semibold">Field Changes</span></div>
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>Field</th><th>Before</th><th>After</th></tr></thead>
                        <tbody>
                            @foreach ($auditLog->changedFields() as $change)
                                <tr>
                                    <td class="font-medium">{{ str_replace('_', ' ', $change['field']) }}</td>
                                    <td class="text-sm text-gray-500">{{ is_array($change['old']) ? json_encode($change['old']) : ($change['old'] ?? '—') }}</td>
                                    <td class="text-sm">{{ is_array($change['new']) ? json_encode($change['new']) : ($change['new'] ?? '—') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif ($auditLog->action === 'created' && $auditLog->new_values)
            <div class="mi-card p-5">
                <p class="text-sm font-semibold mb-3">Created Values</p>
                <pre class="text-xs bg-gray-50 p-4 rounded-lg overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @elseif ($auditLog->action === 'deleted' && $auditLog->old_values)
            <div class="mi-card p-5">
                <p class="text-sm font-semibold mb-3">Deleted Record</p>
                <pre class="text-xs bg-gray-50 p-4 rounded-lg overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>
</x-app-layout>
