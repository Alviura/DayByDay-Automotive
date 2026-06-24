@php
    $indexParams = request()->only(['search', 'module', 'action', 'user_id', 'date_from', 'date_to']);
@endphp

<aside class="mi-card aud-sidebar">
    <div class="aud-sidebar-hero">
        <div class="aud-sidebar-hero-icon">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="min-w-0">
            <p class="aud-sidebar-hero-label">Performed by</p>
            <p class="aud-sidebar-hero-title truncate">{{ $auditLog->user?->name ?? 'System' }}</p>
            <p class="aud-sidebar-hero-sub truncate">{{ $auditLog->user?->email ?? 'No authenticated user' }}</p>
        </div>
    </div>

    <div class="mi-guide-body" style="padding:.35rem 0 .5rem">
        <section class="mi-guide-section mi-guide-section-first" style="padding:.85rem 1.15rem .75rem">
            <ul class="mi-show-meta">
                <li>
                    <span class="mi-show-meta-label"><i class="fas fa-clock"></i> Occurred</span>
                    <span class="mi-show-meta-value">{{ $auditLog->created_at->format('d M Y') }}</span>
                    <span class="mi-show-meta-sub">{{ $auditLog->created_at->format('H:i:s') }} · {{ $auditLog->created_at->diffForHumans() }}</span>
                </li>
                <li>
                    <span class="mi-show-meta-label"><i class="fas fa-hashtag"></i> Audit ID</span>
                    <span class="mi-show-meta-value mono">#{{ $auditLog->id }}</span>
                </li>
                <li>
                    <span class="mi-show-meta-label"><i class="fas fa-fingerprint"></i> Record ID</span>
                    <span class="mi-show-meta-value mono">#{{ $auditLog->auditable_id ?? '—' }}</span>
                </li>
                @if ($auditLog->reference_number)
                    <li>
                        <span class="mi-show-meta-label"><i class="fas fa-barcode"></i> Reference</span>
                        <span class="mi-show-meta-value mono">{{ $auditLog->reference_number }}</span>
                    </li>
                @endif
                <li>
                    <span class="mi-show-meta-label"><i class="fas fa-network-wired"></i> IP address</span>
                    <span class="mi-show-meta-value mono">{{ $auditLog->ip_address ?? '—' }}</span>
                </li>
            </ul>
        </section>

        <section class="mi-guide-section" style="padding:.85rem 1.15rem .75rem">
            <h3 class="mi-guide-section-title"><i class="fas fa-bolt"></i> Quick actions</h3>
            <div class="mi-show-actions">
                @if ($auditableUrl ?? null)
                    <a href="{{ $auditableUrl }}" class="mi-btn-orange w-full justify-center">
                        <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                        Open related record
                    </a>
                @endif
                <a href="{{ route('audit-logs.index', $indexParams) }}" class="mi-btn-ghost w-full justify-center">
                    <i class="fas fa-list text-xs"></i> Back to Audit Log
                </a>
            </div>
        </section>

        @if ($auditLog->user_agent)
            <div class="aud-tech-panel">
                <strong>User agent</strong>
                {{ $auditLog->user_agent }}
            </div>
        @endif
    </div>
</aside>
