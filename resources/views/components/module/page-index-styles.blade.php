{{-- Shared styles for module index pages (Package Management layout) --}}
<style>
    .mi-page { --mi-orange: #ff6b35; --mi-orange-hover: #e85a28; }

    .mi-kpi-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
    }
    @media (max-width: 900px) {
        .mi-kpi-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 520px) {
        .mi-kpi-row { grid-template-columns: 1fr; }
    }

    /* ── KPI cards ── */
    .mi-kpi {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #f0f0f0;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 0;
        border-left-width: 4px;
        border-left-style: solid;
    }
    .mi-kpi-label { font-size: .65rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mi-kpi-value  { margin-top: .25rem; font-size: 1.65rem; font-weight: 800; color: #111827; line-height: 1; }
    .mi-kpi-value.orange { font-size: 1.35rem; color: var(--mi-orange); }
    .mi-kpi-icon {
        width: 2.5rem; height: 2.5rem; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    .mi-kpi-purple  { border-left-color: #8b5cf6; }
    .mi-kpi-purple .mi-kpi-icon  { background: #f3e8ff; color: #8b5cf6; }
    .mi-kpi-green   { border-left-color: #22c55e; }
    .mi-kpi-green .mi-kpi-icon   { background: #dcfce7; color: #22c55e; }
    .mi-kpi-amber   { border-left-color: #f59e0b; }
    .mi-kpi-amber .mi-kpi-icon   { background: #fef3c7; color: #f59e0b; }
    .mi-kpi-orange  { border-left-color: #ff6b35; }
    .mi-kpi-orange .mi-kpi-icon  { background: #fff0eb; color: #ff6b35; }

    /* ── Cards ── */
    .mi-card {
        --mi-card-px: 1.5rem;
        background: #fff;
        border-radius: 12px;
        border: 1px solid #f0f0f0;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
        overflow: hidden;
    }
    .mi-card-head {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem;
        padding: 1rem var(--mi-card-px);
        border-bottom: 1px solid #f3f4f6;
    }
    .mi-card-foot {
        padding: 1rem var(--mi-card-px);
        border-top: 1px solid #f3f4f6;
    }

    /* ── Buttons ── */
    .mi-btn-orange {
        display: inline-flex; align-items: center; gap: .45rem;
        background: var(--mi-orange); color: #fff;
        border-radius: 10px; padding: .6rem 1.25rem;
        font-size: .875rem; font-weight: 600;
        border: none; cursor: pointer; text-decoration: none;
        transition: background .15s; white-space: nowrap;
    }
    .mi-btn-orange:hover { background: var(--mi-orange-hover); color: #fff; }
    .mi-btn-ghost {
        display: inline-flex; align-items: center; gap: .45rem;
        background: #fff; color: #4b5563;
        border: 1px solid #e5e7eb; border-radius: 10px;
        padding: .55rem 1.15rem; font-size: .875rem; font-weight: 500;
        cursor: pointer; text-decoration: none; transition: background .15s;
    }
    .mi-btn-ghost:hover { background: #f9fafb; color: #374151; }
    .mi-btn-toggle {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #fff; color: #6b7280;
        border: 1px solid #e5e7eb; border-radius: 8px;
        padding: .35rem .75rem; font-size: .78rem; font-weight: 500; cursor: pointer;
    }

    /* ── Form fields ── */
    .mi-field-label {
        display: flex; align-items: center; gap: .35rem;
        font-size: .72rem; font-weight: 600; color: #6b7280;
        margin-bottom: .4rem; text-transform: capitalize;
    }
    .mi-field-label i { color: #9ca3af; font-size: .65rem; }
    .mi-input, .mi-select {
        width: 100%; border: 1px solid #e5e7eb; border-radius: 10px;
        padding: .62rem .9rem; font-size: .84rem; color: #374151;
        background: #fff; appearance: none;
    }
    .mi-input:focus, .mi-select:focus {
        outline: none; border-color: var(--mi-orange);
        box-shadow: 0 0 0 3px rgba(255,107,53,.12);
    }
    .mi-input-wrap { position: relative; }
    .mi-input-wrap .mi-input { padding-left: 2.2rem; }
    .mi-input-wrap i {
        position: absolute; left: .8rem; top: 50%; transform: translateY(-50%);
        color: #9ca3af; font-size: .75rem; pointer-events: none;
    }

    /* ── Filter grid ── */
    .mi-filter-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 1.1rem 1.25rem;
        padding: 1.35rem var(--mi-card-px) 1.15rem;
        align-items: end;
    }
    @media (max-width: 1100px) {
        .mi-filter-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .mi-card { --mi-card-px: 1rem; }
        .mi-filter-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
            padding: 1.15rem var(--mi-card-px) 1rem;
        }
    }
    .mi-filter-actions {
        display: flex; flex-wrap: wrap; align-items: center; gap: .75rem;
        padding: 1rem var(--mi-card-px) 1.25rem;
        border-top: 1px solid #f3f4f6;
        background: #fafafa;
    }

    /* ── View toggle ── */
    .mi-view-toggle { display: inline-flex; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
    .mi-view-toggle button {
        padding: .45rem .7rem; background: #fff; color: #9ca3af;
        border: none; cursor: pointer; font-size: .85rem; transition: all .15s;
    }
    .mi-view-toggle button.active { background: var(--mi-orange); color: #fff; }

    /* ── Table ── */
    .mi-table-wrap {
        padding: 0 var(--mi-card-px) .25rem;
        overflow-x: auto;
    }
    .mi-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .84rem; }
    .mi-table thead th {
        padding: .85rem 1.15rem;
        text-align: left;
        font-size: .65rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #9ca3af;
        background: #fafafa;
        border-bottom: 1px solid #ececec;
        white-space: nowrap;
    }
    .mi-table thead th:first-child { border-radius: 8px 0 0 0; padding-left: 1rem; }
    .mi-table thead th:last-child  { border-radius: 0 8px 0 0; padding-right: 1rem; }
    .mi-table tbody td {
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle; color: #374151;
    }
    .mi-table tbody td:first-child { padding-left: 1rem; }
    .mi-table tbody td:last-child  { padding-right: 1rem; }
    .mi-table tbody tr:last-child td { border-bottom: none; padding-bottom: 1.15rem; }
    .mi-table tbody tr:hover td { background: #fafafa; }
    .mi-grid-wrap {
        padding: 1.25rem var(--mi-card-px) 1.35rem;
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
    }
    @media (min-width: 640px)  { .mi-grid-wrap { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1024px) { .mi-grid-wrap { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .mi-grid-item {
        border: 1px solid #f0f0f0; border-radius: 12px;
        padding: 1.15rem 1.25rem;
        transition: box-shadow .15s;
        display: flex; flex-direction: column; gap: .55rem;
    }
    .mi-grid-item:hover { box-shadow: 0 4px 14px rgba(0,0,0,.06); }
    .mi-grid-item-head {
        display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem;
    }
    .mi-grid-item-name { margin-top: .35rem; }
    .mi-grid-item-meta {
        display: flex; flex-direction: column; gap: .35rem;
    }
    .mi-grid-item-actions {
        display: flex; align-items: center; justify-content: flex-end; gap: .35rem;
        margin-top: .25rem;
    }

    /* ── Badges & cells ── */
    .mi-pkg-name  { font-weight: 600; color: #111827; font-size: .875rem; }
    .mi-pkg-sub   { font-size: .72rem; color: #9ca3af; margin-top: .1rem; }
    .mi-thumb {
        width: 2.5rem; height: 2.5rem; border-radius: 50%;
        object-fit: cover; display: flex; align-items: center; justify-content: center;
        font-size: .85rem; color: #fff; flex-shrink: 0;
    }
    .mi-cat-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        background: #ede9fe; color: #7c3aed;
        border-radius: 9999px; padding: .25rem .65rem;
        font-size: .72rem; font-weight: 600; white-space: nowrap;
    }
    .mi-dest { display: flex; align-items: center; gap: .35rem; color: #374151; font-size: .82rem; }
    .mi-dest i { color: #22c55e; font-size: .7rem; }
    .mi-price { font-weight: 700; color: var(--mi-orange); font-size: .875rem; white-space: nowrap; }
    .mi-duration { display: flex; align-items: center; gap: .3rem; color: #374151; font-size: .82rem; white-space: nowrap; }
    .mi-duration i { color: var(--mi-orange); font-size: .72rem; }
    .mi-rating { display: flex; align-items: center; gap: .2rem; }
    .mi-rating i { color: #d1d5db; font-size: .65rem; }
    .mi-rating span { font-size: .78rem; color: #6b7280; margin-left: .15rem; }
    .mi-bookings { display: flex; align-items: center; gap: .35rem; color: #374151; font-size: .82rem; }
    .mi-bookings i { color: #3b82f6; font-size: .75rem; }
    .mi-status-active {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #ecfdf5; color: #059669;
        border-radius: 9999px; padding: .25rem .7rem;
        font-size: .72rem; font-weight: 600;
    }
    .mi-status-active::before {
        content: ''; width: 6px; height: 6px; border-radius: 50%; background: #22c55e;
    }
    .mi-status-inactive {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #f3f4f6; color: #6b7280;
        border-radius: 9999px; padding: .25rem .7rem;
        font-size: .72rem; font-weight: 600;
    }
    .mi-status-inactive::before {
        content: ''; width: 6px; height: 6px; border-radius: 50%; background: #9ca3af;
    }
    .mi-action {
        display: inline-flex; align-items: center; justify-content: center;
        width: 2rem; height: 2rem; border-radius: 8px;
        border: 1px solid #e5e7eb; background: #fff; color: #6b7280;
        cursor: pointer; text-decoration: none; transition: all .15s; font-size: .78rem;
    }
    .mi-action:hover { background: #f9fafb; }
    .mi-action.view:hover  { color: #2563eb; border-color: #bfdbfe; }
    .mi-action.edit:hover  { color: #6b7280; }
    .mi-action.del:hover   { color: #dc2626; border-color: #fecaca; background: #fef2f2; }

    /* ── Page header icon ── */
    .mi-page-icon {
        width: 2.75rem; height: 2.75rem; border-radius: 10px;
        background: #22c55e; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem; flex-shrink: 0;
    }

    /* ── Create / edit forms ── */
    .mi-form-split {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .mi-form-split { grid-template-columns: 1fr; }
    }
    .mi-form-main { min-width: 0; }
    .mi-form-body {
        padding: 1.35rem var(--mi-card-px) 1.15rem;
    }
    .mi-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem 1.5rem;
    }
    @media (max-width: 640px) {
        .mi-form-grid { grid-template-columns: 1fr; }
    }
    .mi-form-grid .mi-span-full { grid-column: 1 / -1; }
    .mi-field-hint {
        margin-top: .35rem;
        font-size: .72rem;
        color: #9ca3af;
        line-height: 1.4;
    }
    .mi-form-actions {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: .65rem;
        padding: 1rem var(--mi-card-px);
        background: #fafafa;
        border-top: 1px solid #f3f4f6;
    }
    .mi-form-actions .mi-btn-orange { border: none; cursor: pointer; }
    .mi-toggle-row {
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        margin-top: 1.25rem;
        padding: 1rem 1.15rem;
        background: #f9fafb;
        border: 1px solid #f3f4f6;
        border-radius: 10px;
    }
    .mi-toggle-copy { min-width: 0; }
    .mi-toggle-title { font-size: .84rem; font-weight: 600; color: #374151; }
    .mi-toggle-desc  { margin-top: .15rem; font-size: .72rem; color: #9ca3af; line-height: 1.4; }
    .mi-toggle-check {
        width: 1.1rem; height: 1.1rem; flex-shrink: 0;
        border-radius: 4px; border: 1px solid #d1d5db;
        color: var(--mi-orange); accent-color: var(--mi-orange);
        cursor: pointer;
    }
    .mi-toggle-check:focus { outline: none; box-shadow: 0 0 0 3px rgba(255,107,53,.15); }

    /* ── Form guide panel ── */
    .mi-guide {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #f0f0f0;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
        overflow: hidden;
        position: sticky;
        top: 1rem;
    }
    @media (max-width: 1024px) {
        .mi-guide { position: static; }
    }
    .mi-guide-head {
        display: flex; align-items: flex-start; gap: .75rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid #f3f4f6;
        background: linear-gradient(135deg, #fff7ed 0%, #fff 55%);
    }
    .mi-guide-icon {
        width: 2.25rem; height: 2.25rem; border-radius: 9px; flex-shrink: 0;
        background: #ffedd5; color: var(--mi-orange);
        display: flex; align-items: center; justify-content: center;
        font-size: .95rem;
    }
    .mi-guide-title { font-size: .9rem; font-weight: 700; color: #111827; line-height: 1.3; }
    .mi-guide-subtitle { margin-top: .15rem; font-size: .72rem; color: #9ca3af; }
    .mi-guide-body { padding: 1rem 1.25rem 1.25rem; }
    .mi-guide-section + .mi-guide-section { margin-top: 1.15rem; padding-top: 1.15rem; border-top: 1px solid #f3f4f6; }
    .mi-guide-section-first { margin-top: 0 !important; padding-top: 0 !important; border-top: none !important; }
    .mi-guide-section-title {
        display: flex; align-items: center; gap: .4rem;
        font-size: .78rem; font-weight: 700; color: #374151;
        margin-bottom: .55rem;
    }
    .mi-guide-section-title i { color: var(--mi-orange); font-size: .72rem; }
    .mi-guide-text { font-size: .78rem; color: #6b7280; line-height: 1.55; margin: 0; }
    .mi-guide-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .65rem; }
    .mi-guide-list li { font-size: .76rem; line-height: 1.45; }
    .mi-guide-list strong { display: block; color: #374151; font-weight: 600; font-size: .78rem; }
    .mi-guide-list span { color: #9ca3af; }
    .mi-guide-tips { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .5rem; }
    .mi-guide-tips li {
        display: flex; align-items: flex-start; gap: .45rem;
        font-size: .76rem; color: #6b7280; line-height: 1.45;
    }
    .mi-guide-tips li > i { color: #22c55e; font-size: .65rem; margin-top: .2rem; flex-shrink: 0; }
    .mi-guide-tips code {
        font-size: .7rem; background: #f3f4f6; color: #374151;
        padding: .1rem .35rem; border-radius: 4px; font-family: ui-monospace, monospace;
    }
    .mi-guide-note {
        display: flex; align-items: flex-start; gap: .55rem;
        margin-top: 1.15rem; padding: .75rem .85rem;
        border-radius: 9px; font-size: .76rem; line-height: 1.45;
    }
    .mi-guide-note i { margin-top: .1rem; flex-shrink: 0; font-size: .78rem; }
    .mi-guide-note p { margin: 0; }
    .mi-guide-note-blue  { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
    .mi-guide-note-blue i { color: #3b82f6; }
    .mi-guide-note-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .mi-guide-note-amber i { color: #f59e0b; }

    /* ── Show / detail pages ── */
    .mi-kpi-value.text-status { font-size: 1.15rem; }
    .mi-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.35rem 1.5rem;
        padding: 1.35rem var(--mi-card-px) 1.25rem;
    }
    @media (max-width: 640px) {
        .mi-detail-grid { grid-template-columns: 1fr; }
    }
    .mi-detail-grid .mi-span-full { grid-column: 1 / -1; }
    .mi-detail-label {
        display: flex; align-items: center; gap: .35rem;
        font-size: .72rem; font-weight: 600; color: #9ca3af;
        text-transform: uppercase; letter-spacing: .04em;
    }
    .mi-detail-label i { font-size: .65rem; }
    .mi-detail-value {
        margin-top: .4rem;
        font-size: .9rem; font-weight: 500; color: #111827; line-height: 1.45;
    }
    .mi-detail-empty { color: #d1d5db; font-style: italic; font-weight: 400; }
    .mi-show-empty {
        padding: 2.5rem var(--mi-card-px);
        text-align: center; color: #9ca3af;
    }
    .mi-show-empty i { font-size: 1.75rem; color: #e5e7eb; margin-bottom: .65rem; }
    .mi-show-empty p { margin: 0; font-size: .84rem; }
    .mi-show-meta { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .85rem; }
    .mi-show-meta li {
        display: flex; flex-direction: column; gap: .1rem;
        padding: .65rem .75rem; background: #f9fafb;
        border: 1px solid #f3f4f6; border-radius: 9px;
    }
    .mi-show-meta-label {
        display: flex; align-items: center; gap: .35rem;
        font-size: .7rem; font-weight: 600; color: #9ca3af; text-transform: uppercase;
    }
    .mi-show-meta-label i { font-size: .62rem; }
    .mi-show-meta-value { font-size: .84rem; font-weight: 600; color: #374151; }
    .mi-show-meta-value.mono { font-family: ui-monospace, monospace; color: #6b7280; }
    .mi-show-meta-sub { font-size: .72rem; color: #9ca3af; }
    .mi-show-actions { display: flex; flex-direction: column; gap: .5rem; }
    .mi-show-actions .mi-btn-orange,
    .mi-show-actions .mi-btn-ghost { width: 100%; justify-content: center; }
    .mi-btn-danger {
        display: inline-flex; align-items: center; gap: .45rem;
        background: #fff; color: #dc2626;
        border: 1px solid #fecaca; border-radius: 10px;
        padding: .55rem 1.15rem; font-size: .875rem; font-weight: 500;
        cursor: pointer; text-decoration: none; transition: all .15s; width: 100%;
        justify-content: center;
    }
    .mi-btn-danger:hover { background: #fef2f2; border-color: #fca5a5; }
</style>
