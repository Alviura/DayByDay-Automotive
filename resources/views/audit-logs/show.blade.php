@php
    $changes = $auditLog->changedFields();
    $payload = $auditLog->payloadValues();
    $auditableUrl = $auditLog->auditableUrl();
    $changeCount = count($changes);
    $payloadCount = count($payload);
    $indexParams = request()->only(['search', 'module', 'action', 'user_id', 'date_from', 'date_to']);
@endphp

<x-app-layout :title="'Audit · '.($auditLog->reference_number ?? '#'.$auditLog->id)">

    @push('styles')
        <x-module.page-index-styles />
        @include('audit-logs.partials.page-styles')
    @endpush

    <div class="mi-page aud-page space-y-5">

        {{-- Hero --}}
        <div class="aud-show-top">
            <div class="aud-show-identity">
                <div class="aud-hero-icon {{ $auditLog->actionHeroClass() }}">
                    <i class="fas {{ $auditLog->actionIcon() }}"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">
                            {{ $auditLog->actionLabel() }}
                            <span class="text-gray-400 font-semibold">·</span>
                            {{ $auditLog->moduleLabel() }}
                        </h1>
                        <span class="{{ $auditLog->actionBadgeClass() }}">
                            <i class="fas {{ $auditLog->actionIcon() }}"></i>
                            {{ $auditLog->actionLabel() }}
                        </span>
                        <span class="{{ $auditLog->moduleBadgeClass() }}">
                            <i class="fas {{ $auditLog->moduleIcon() }}"></i>
                            {{ $auditLog->moduleLabel() }}
                        </span>
                    </div>
                    <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                        <span class="aud-show-ref">#{{ $auditLog->id }}</span>
                        @if ($auditLog->reference_number)
                            <span class="aud-show-ref">{{ $auditLog->reference_number }}</span>
                        @endif
                        <span class="mi-cat-badge">{{ $auditLog->created_at->format('d M Y, H:i') }}</span>
                        <span>{{ $auditLog->created_at->diffForHumans() }}</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-600">
                        <i class="fas fa-user text-[.65rem] text-gray-400"></i>
                        {{ $auditLog->user?->name ?? 'System' }}
                        @if ($auditLog->user?->email)
                            <span class="text-gray-400">· {{ $auditLog->user->email }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('audit-logs.index', $indexParams) }}" class="mi-btn-ghost">
                    <i class="fas fa-arrow-left text-xs"></i> Audit Log
                </a>
                @if ($auditableUrl)
                    <a href="{{ $auditableUrl }}" class="mi-btn-orange">
                        <i class="fas fa-arrow-up-right-from-square text-xs"></i> Open Record
                    </a>
                @endif
            </div>
        </div>

        {{-- KPIs --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Audit ID</p>
                    <p class="mi-kpi-value" style="font-size:1.25rem">#{{ $auditLog->id }}</p>
                    <p class="aud-kpi-sub">Immutable log entry</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-fingerprint"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Fields Changed</p>
                    <p class="mi-kpi-value">{{ $changeCount ?: $payloadCount }}</p>
                    <p class="aud-kpi-sub">{{ $changeCount ? 'Before / after diff' : ($payloadCount ? 'Snapshot fields' : 'No detail stored') }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-code-compare"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Module</p>
                    <p class="mi-kpi-value text-status" style="font-size:1rem">{{ $auditLog->moduleLabel() }}</p>
                    <p class="aud-kpi-sub">{{ $auditLog->module }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas {{ $auditLog->moduleIcon() }}"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Source IP</p>
                    <p class="mi-kpi-value orange" style="font-size:1rem">{{ $auditLog->ip_address ?? '—' }}</p>
                    <p class="aud-kpi-sub">{{ $auditLog->created_at->format('H:i:s T') }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-globe"></i></div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="aud-show-summary">
            <i class="fas fa-circle-info"></i>
            <p><strong>{{ $auditLog->eventHeadline() }}</strong> — logged at {{ $auditLog->created_at->format('l, d F Y \a\t H:i:s') }}.</p>
        </div>

        {{-- Content --}}
        <div class="aud-show-grid">
            <div class="space-y-5">
                @if ($changeCount > 0)
                    <div class="mi-card">
                        <div class="mi-card-head aud-section-head">
                            <div>
                                <p class="aud-section-title">
                                    <i class="fas fa-code-compare text-blue-500"></i>
                                    Field Changes
                                </p>
                                <p class="aud-section-sub">{{ $changeCount }} {{ str('attribute')->plural($changeCount) }} modified in this update</p>
                            </div>
                            <span class="mi-cat-badge">{{ $changeCount }} changed</span>
                        </div>
                        <div class="mi-table-wrap">
                            <table class="mi-table aud-diff-table">
                                <thead>
                                    <tr>
                                        <th class="w-[22%]">Field</th>
                                        <th>Before</th>
                                        <th>After</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($changes as $change)
                                        @php
                                            $old = $auditLog->formatValue($change['old']);
                                            $new = $auditLog->formatValue($change['new']);
                                        @endphp
                                        <tr>
                                            <td class="aud-cell-field">{{ $auditLog->fieldLabel($change['field']) }}</td>
                                            <td class="aud-cell-old">
                                                @if ($old === '—')
                                                    <span class="aud-diff-empty">empty</span>
                                                @else
                                                    {{ $old }}
                                                @endif
                                            </td>
                                            <td class="aud-cell-new">
                                                @if ($new === '—')
                                                    <span class="aud-diff-empty">empty</span>
                                                @else
                                                    {{ $new }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif ($payloadCount > 0)
                    <div class="mi-card">
                        <div class="mi-card-head aud-section-head">
                            <div>
                                <p class="aud-section-title">
                                    <i class="fas {{ $auditLog->action === 'deleted' ? 'fa-trash-can text-rose-500' : 'fa-file-circle-plus text-emerald-500' }}"></i>
                                    {{ $auditLog->action === 'deleted' ? 'Deleted Record Snapshot' : 'Created Record Values' }}
                                </p>
                                <p class="aud-section-sub">{{ $payloadCount }} {{ str('field')->plural($payloadCount) }} captured at time of event</p>
                            </div>
                        </div>
                        <div class="aud-payload-grid">
                            @foreach ($payload as $field => $value)
                                <div class="aud-payload-item">
                                    <p class="aud-payload-label">{{ $auditLog->fieldLabel($field) }}</p>
                                    <p class="aud-payload-value">{{ $auditLog->formatValue($value) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="mi-card aud-show-empty">
                        <div class="aud-empty-icon"><i class="fas fa-file-circle-question"></i></div>
                        <p class="font-semibold text-gray-700">No field-level detail recorded</p>
                        <p class="text-sm text-gray-400 mt-1 max-w-md mx-auto">
                            This event was logged without stored before/after values. The action and actor are still captured for traceability.
                        </p>
                    </div>
                @endif
            </div>

            @include('audit-logs.partials.show-sidebar', [
                'auditLog' => $auditLog,
                'auditableUrl' => $auditableUrl,
            ])
        </div>
    </div>
</x-app-layout>
