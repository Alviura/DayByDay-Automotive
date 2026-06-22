{{-- Shared styles for goods receipt pages --}}
<style>
    .grn-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
    }
    @media (max-width: 1100px) { .grn-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px)  { .grn-kpi-grid { grid-template-columns: 1fr; } }

    .grn-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .grn-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .grn-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .grn-badge-green  { background: #dcfce7; color: #15803d; } .grn-badge-green::before  { background: #22c55e; }
    .grn-badge-amber  { background: #fef3c7; color: #b45309; } .grn-badge-amber::before  { background: #f59e0b; }
    .grn-badge-emerald { background: #d1fae5; color: #047857; } .grn-badge-emerald::before { background: #10b981; }
    .grn-badge-slate  { background: #f1f5f9; color: #475569; } .grn-badge-slate::before  { background: #94a3b8; }

    /* ── Show sidebar ── */
    .grn-sidebar { display: flex; flex-direction: column; }

    .grn-sidebar-hero {
        display: flex; align-items: center; gap: .85rem;
        padding: 1.1rem 1.15rem;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-bottom: 1px solid #a7f3d0;
    }
    .grn-sidebar-hero-icon {
        width: 2.75rem; height: 2.75rem; border-radius: 12px; flex-shrink: 0;
        background: #fff; border: 1px solid #6ee7b7;
        color: #059669; font-size: 1.1rem;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 8px rgba(5, 150, 105, .12);
    }
    .grn-sidebar-hero-label {
        font-size: .62rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #047857; opacity: .85;
    }
    .grn-sidebar-hero-title {
        font-size: .95rem; font-weight: 700; color: #064e3b;
        line-height: 1.3; margin-top: .1rem;
    }
    .grn-sidebar-hero-sub {
        font-size: .72rem; color: #059669; margin-top: .15rem;
    }

    .grn-sidebar-body { padding: .35rem 0 .5rem; }

    .grn-sidebar-block { padding: .85rem 1.15rem .75rem; }
    .grn-sidebar-block + .grn-sidebar-block,
    .grn-sidebar-note + .grn-sidebar-block {
        border-top: 1px solid #f3f4f6;
    }

    .grn-sidebar-block-title {
        display: flex; align-items: center; gap: .4rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; color: #9ca3af;
        margin-bottom: .65rem;
    }
    .grn-sidebar-block-title i { font-size: .65rem; }

    .grn-detail-list { display: flex; flex-direction: column; gap: .5rem; margin: 0; }
    .grn-detail-row {
        display: grid; grid-template-columns: 4.5rem 1fr;
        gap: .65rem; align-items: baseline;
        font-size: .8rem; line-height: 1.35;
    }
    .grn-detail-row dt { color: #9ca3af; font-weight: 500; margin: 0; }
    .grn-detail-row dd { color: #374151; margin: 0; text-align: left; word-break: break-word; }

    .grn-sidebar-note {
        margin: 0 1.15rem .85rem;
        padding: .75rem .85rem;
        background: #f9fafb; border: 1px solid #f3f4f6;
        border-radius: 10px;
    }
    .grn-sidebar-note-label {
        font-size: .65rem; font-weight: 700; color: #9ca3af;
        text-transform: uppercase; letter-spacing: .04em;
        display: flex; align-items: center; gap: .35rem; margin-bottom: .35rem;
    }
    .grn-sidebar-note-text { font-size: .78rem; color: #4b5563; line-height: 1.45; margin: 0; }

    .grn-doc-stack { display: flex; flex-direction: column; gap: .5rem; }
    .grn-doc-tile {
        display: flex; align-items: center; gap: .65rem;
        padding: .7rem .75rem; border-radius: 10px;
        border: 1px solid #f0f0f0; background: #fafafa;
        text-decoration: none; color: inherit;
        transition: all .15s ease;
    }
    .grn-doc-tile:hover { transform: translateY(-1px); box-shadow: 0 3px 10px rgba(0,0,0,.06); }
    .grn-doc-tile-blue:hover  { border-color: #bfdbfe; background: #eff6ff; }
    .grn-doc-tile-orange:hover { border-color: #fed7aa; background: #fff7ed; }

    .grn-doc-tile-icon {
        width: 2rem; height: 2rem; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: .8rem;
    }
    .grn-doc-tile-blue .grn-doc-tile-icon  { background: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }
    .grn-doc-tile-orange .grn-doc-tile-icon { background: #ffedd5; color: #ea580c; border: 1px solid #fed7aa; }

    .grn-doc-tile-label {
        font-size: .58rem; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; color: #9ca3af; line-height: 1;
    }
    .grn-doc-tile-title {
        font-size: .82rem; font-weight: 700; color: #111827;
        margin-top: .2rem; line-height: 1.25;
    }
    .grn-doc-tile-sub {
        font-size: .68rem; color: #9ca3af; margin-top: .12rem;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .grn-doc-tile-arrow { font-size: .6rem; color: #d1d5db; flex-shrink: 0; margin-left: auto; }
    .grn-doc-tile:hover .grn-doc-tile-arrow { color: #9ca3af; }

    .grn-row-icon {
        width: 2.1rem; height: 2.1rem; border-radius: 8px; flex-shrink: 0;
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1px solid #a7f3d0; color: #059669;
        display: flex; align-items: center; justify-content: center; font-size: .75rem;
    }
    .grn-cell-main { display: flex; align-items: flex-start; gap: .65rem; }
    .grn-qty-good { font-weight: 700; color: #059669; }
    .grn-qty-damaged { font-weight: 600; color: #d97706; }

    .grn-empty-icon {
        width: 3.5rem; height: 3.5rem; border-radius: 50%;
        background: #f3f4f6; color: #d1d5db;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin: 0 auto 1rem;
    }

    .grn-section-title {
        display: flex; align-items: center; gap: .5rem;
        font-size: .875rem; font-weight: 700; color: #111827;
    }
    .grn-section-title i { color: #9ca3af; font-size: .8rem; }
    .grn-section-sub { font-size: .75rem; color: #9ca3af; margin-top: .15rem; }

    .grn-link-card {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .85rem 1rem; border: 1px solid #f0f0f0; border-radius: 10px;
        text-decoration: none; color: inherit; transition: all .15s;
    }
    .grn-link-card:hover { border-color: #a7f3d0; background: #ecfdf5; }
    .grn-link-card-icon {
        width: 2.25rem; height: 2.25rem; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; font-size: .85rem;
    }

    .grn-phase-banner {
        display: flex; align-items: flex-start; gap: .75rem;
        padding: .85rem 1.1rem; border-radius: 10px;
        font-size: .82rem; color: #374151; line-height: 1.5;
    }
    .grn-phase-banner i { margin-top: .15rem; flex-shrink: 0; }
    .grn-phase-banner-emerald {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1px solid #a7f3d0; color: #065f46;
    }
    .grn-phase-banner-emerald i { color: #059669; }

    .grn-po-summary {
        border: 1px dashed #bfdbfe;
        background: linear-gradient(135deg, #eff6ff, #f0f9ff);
        border-radius: 10px; padding: .85rem 1rem;
    }
    .grn-po-summary-label {
        font-size: .62rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #9ca3af; margin-bottom: .35rem;
    }

    .grn-input-qty { max-width: 7rem; }
    .grn-input-cost { max-width: 8rem; }

    .grn-damage-flag {
        display: inline-flex; align-items: center; gap: .25rem;
        font-size: .65rem; font-weight: 700; color: #b45309;
        background: #fef3c7; border-radius: 9999px; padding: .15rem .5rem;
    }

    /* ── Void panel ── */
    .grn-void-record {
        display: flex; align-items: flex-start; gap: 1rem;
        padding: 1rem 1.15rem;
        border-radius: 12px;
        border: 1px solid #fecaca;
        background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
    }
    .grn-void-record-icon {
        width: 2.5rem; height: 2.5rem; border-radius: 10px; flex-shrink: 0;
        background: #fff; border: 1px solid #fca5a5;
        color: #dc2626; font-size: 1rem;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 8px rgba(220, 38, 38, .1);
    }
    .grn-void-record-title {
        font-size: .875rem; font-weight: 700; color: #991b1b; line-height: 1.35;
    }
    .grn-void-record-meta { font-weight: 500; color: #b91c1c; }
    .grn-void-record-reason {
        margin-top: .5rem; font-size: .82rem; color: #7f1d1d; line-height: 1.5;
    }
    .grn-void-record-note {
        margin-top: .5rem; font-size: .72rem; color: #be123c; opacity: .85;
    }

    .grn-void-panel {
        border-radius: 14px;
        border: 1px solid #fecaca;
        background: #fff;
        box-shadow: 0 8px 30px rgba(220, 38, 38, .08), 0 2px 8px rgba(0, 0, 0, .04);
        overflow: hidden;
    }
    .grn-void-panel-head {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
        padding: 1rem 1.15rem;
        background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
        border-bottom: 1px solid #fecaca;
    }
    .grn-void-panel-icon {
        width: 2.5rem; height: 2.5rem; border-radius: 10px; flex-shrink: 0;
        background: #fff; border: 1px solid #fca5a5;
        color: #dc2626; font-size: 1rem;
        display: flex; align-items: center; justify-content: center;
    }
    .grn-void-panel-title {
        font-size: .95rem; font-weight: 700; color: #991b1b; line-height: 1.3;
    }
    .grn-void-panel-sub {
        font-size: .75rem; color: #be123c; margin-top: .2rem;
    }
    .grn-void-panel-close {
        width: 2rem; height: 2rem; border-radius: 8px; flex-shrink: 0;
        border: 1px solid #fecaca; background: #fff; color: #9ca3af;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all .15s ease;
    }
    .grn-void-panel-close:hover {
        color: #dc2626; border-color: #fca5a5; background: #fff1f2;
    }

    .grn-void-panel-body {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr);
        gap: 0;
    }
    @media (max-width: 900px) {
        .grn-void-panel-body { grid-template-columns: 1fr; }
    }

    .grn-void-impact {
        padding: 1.15rem;
        background: #fafafa;
        border-right: 1px solid #f3f4f6;
    }
    @media (max-width: 900px) {
        .grn-void-impact { border-right: none; border-bottom: 1px solid #f3f4f6; }
    }
    .grn-void-impact-title {
        display: flex; align-items: center; gap: .4rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; color: #9ca3af;
        margin-bottom: .85rem;
    }
    .grn-void-impact-title i { font-size: .65rem; }
    .grn-void-impact-list {
        list-style: none; margin: 0; padding: 0;
        display: flex; flex-direction: column; gap: .75rem;
    }
    .grn-void-impact-list li {
        position: relative;
        padding-left: 1.1rem;
        font-size: .8rem; color: #4b5563; line-height: 1.45;
    }
    .grn-void-impact-list li::before {
        content: '';
        position: absolute; left: 0; top: .45rem;
        width: 6px; height: 6px; border-radius: 50%;
        background: #f87171;
    }
    .grn-void-impact-list strong { color: #374151; font-weight: 600; }
    .grn-void-impact-stat {
        display: inline-block; font-weight: 700; color: #dc2626;
    }

    .grn-void-form {
        padding: 1.15rem;
        display: flex; flex-direction: column; gap: 1rem;
    }
    .grn-void-textarea { min-height: 7rem; resize: vertical; }
    .grn-void-field-hint {
        margin-top: .35rem; font-size: .68rem; color: #9ca3af;
    }
    .grn-void-form-actions {
        display: flex; flex-wrap: wrap; align-items: center;
        justify-content: flex-end; gap: .5rem;
        padding-top: .25rem;
    }

    .grn-btn-danger {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .5rem 1rem; border-radius: 8px;
        font-size: .8rem; font-weight: 600; color: #fff;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border: 1px solid #b91c1c;
        box-shadow: 0 2px 6px rgba(220, 38, 38, .25);
        transition: all .15s ease;
        cursor: pointer;
    }
    .grn-btn-danger:hover {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        box-shadow: 0 4px 12px rgba(220, 38, 38, .3);
    }
</style>
