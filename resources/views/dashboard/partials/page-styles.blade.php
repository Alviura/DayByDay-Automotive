<style>
    .db-page {
        max-width: 100%;
        min-width: 0;
        overflow-x: clip;
    }

    /* ── Hero card ── */
    .db-hero-card {
        position: relative;
        border-radius: 1rem;
        border: 1px solid #fed7aa;
        background: linear-gradient(135deg, #fff 0%, #fffbf7 45%, #fff7ed 100%);
        box-shadow: 0 4px 24px rgba(255, 107, 53, .08);
        overflow: hidden;
    }
    .db-hero-card-glow {
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 60% 80% at 100% 0%, rgba(255,107,53,.12) 0%, transparent 55%),
            radial-gradient(ellipse 40% 60% at 0% 100%, rgba(139,92,246,.06) 0%, transparent 50%);
        pointer-events: none;
    }
    .db-hero-card-inner {
        position: relative;
        display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between;
        gap: 1.25rem; padding: 1.35rem 1.5rem;
    }
    .db-hero-main { display: flex; align-items: flex-start; gap: 1rem; min-width: 0; flex: 1; }
    .db-hero-icon {
        width: 3rem; height: 3rem; border-radius: .85rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #ff6b35, #ea580c);
        color: #fff; font-size: 1.15rem;
        box-shadow: 0 4px 14px rgba(255,107,53,.35);
    }
    .db-hero-copy { min-width: 0; }
    .db-hero-title-row { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
    .db-hero-title {
        font-size: clamp(1.15rem, 2.5vw, 1.5rem);
        font-weight: 800; color: #111827; line-height: 1.25; margin: 0;
    }
    .db-hero-subtitle { margin-top: .35rem; font-size: .875rem; color: #6b7280; line-height: 1.45; }
    .db-role-badge {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .22rem .6rem; border-radius: 999px; font-size: .65rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .05em;
        background: #fff; color: #ea580c; border: 1px solid #fed7aa;
        white-space: nowrap;
    }
    .db-hero-aside { display: flex; flex-direction: column; align-items: flex-end; gap: .75rem; flex-shrink: 0; }
    .db-hero-date {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .78rem; font-weight: 600; color: #6b7280; white-space: nowrap;
    }
    .db-hero-highlights { display: flex; flex-wrap: wrap; gap: .45rem; justify-content: flex-end; }
    .db-hero-pill {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .35rem .65rem; border-radius: 999px;
        background: rgba(255,255,255,.85); border: 1px solid #f3f4f6;
        font-size: .72rem; color: #374151; backdrop-filter: blur(4px);
    }
    .db-hero-pill i { color: #ff6b35; font-size: .65rem; }
    .db-hero-pill-label { color: #9ca3af; font-weight: 600; }
    .db-hero-pill-value { font-weight: 800; color: #111827; }

    /* ── Sections ── */
    .db-section { min-width: 0; }
    .db-section-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: .75rem; gap: .5rem;
    }
    .db-section-title {
        font-size: .7rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: .07em; color: #9ca3af;
        display: flex; align-items: center; gap: .4rem;
    }
    .db-section-title i { color: #ff6b35; font-size: .65rem; }
    .db-section--finance .db-section-title i { color: #8b5cf6; }

    /* ── Rich KPI command strip ── */
    .db-command-strip { display: flex; flex-direction: column; gap: .75rem; min-width: 0; }
    .db-rich-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
        min-width: 0;
    }
    @media (max-width: 1100px) { .db-rich-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px)  { .db-rich-kpi-grid { grid-template-columns: minmax(0, 1fr); } }

    .db-rich-kpi {
        display: block; text-decoration: none; color: inherit;
        background: #fff; border-radius: .85rem; border: 1px solid #f0f0f0;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
        padding: 1rem 1.05rem 1.05rem;
        min-width: 0;
        border-left: 4px solid #e5e7eb;
        transition: transform .15s, box-shadow .15s;
    }
    a.db-rich-kpi:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(0,0,0,.07); }
    .db-rich-kpi-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; }
    .db-rich-kpi-label {
        font-size: .62rem; font-weight: 800; letter-spacing: .07em;
        text-transform: uppercase; color: #9ca3af;
    }
    .db-rich-kpi-icon {
        width: 2rem; height: 2rem; border-radius: .55rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: .8rem;
    }
    .db-rich-kpi-value {
        margin-top: .45rem; font-size: clamp(1.2rem, 2.2vw, 1.75rem);
        font-weight: 800; line-height: 1.1;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .db-rich-kpi-prefix { font-size: .72em; font-weight: 700; opacity: .75; margin-right: .1rem; }
    .db-rich-kpi-badges { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .55rem; }
    .db-rich-badge {
        display: inline-flex; padding: .15rem .45rem; border-radius: 999px;
        font-size: .62rem; font-weight: 700;
    }
    .db-rich-badge--gray   { background: #f3f4f6; color: #6b7280; }
    .db-rich-badge--green  { background: #dcfce7; color: #15803d; }
    .db-rich-badge--blue   { background: #dbeafe; color: #1d4ed8; }
    .db-rich-badge--purple { background: #f3e8ff; color: #7c3aed; }
    .db-rich-badge--amber  { background: #fef3c7; color: #b45309; }
    .db-rich-badge--red    { background: #fee2e2; color: #dc2626; }
    .db-rich-badge--teal   { background: #ccfbf1; color: #0f766e; }
    .db-rich-kpi-foot {
        margin-top: .55rem; font-size: .7rem; font-weight: 600;
        display: flex; align-items: center; gap: .35rem;
    }
    .db-rich-kpi-foot--up      { color: #16a34a; }
    .db-rich-kpi-foot--down    { color: #dc2626; }
    .db-rich-kpi-foot--neutral { color: #9ca3af; }
    .db-rich-kpi-foot--alert   { color: #ea580c; }

    .db-rich-kpi--blue   { border-left-color: #3b82f6; }
    .db-rich-kpi--blue .db-rich-kpi-icon   { background: #dbeafe; color: #2563eb; }
    .db-rich-kpi--blue .db-rich-kpi-value  { color: #1d4ed8; }
    .db-rich-kpi--green  { border-left-color: #22c55e; }
    .db-rich-kpi--green .db-rich-kpi-icon  { background: #dcfce7; color: #16a34a; }
    .db-rich-kpi--green .db-rich-kpi-value { color: #15803d; }
    .db-rich-kpi--red    { border-left-color: #ef4444; }
    .db-rich-kpi--red .db-rich-kpi-icon    { background: #fee2e2; color: #dc2626; }
    .db-rich-kpi--red .db-rich-kpi-value   { color: #dc2626; }
    .db-rich-kpi--teal   { border-left-color: #14b8a6; }
    .db-rich-kpi--teal .db-rich-kpi-icon   { background: #ccfbf1; color: #0d9488; }
    .db-rich-kpi--teal .db-rich-kpi-value  { color: #0f766e; }
    .db-rich-kpi--purple { border-left-color: #8b5cf6; }
    .db-rich-kpi--purple .db-rich-kpi-icon { background: #f3e8ff; color: #7c3aed; }
    .db-rich-kpi--purple .db-rich-kpi-value{ color: #6d28d9; }
    .db-rich-kpi--amber  { border-left-color: #f59e0b; }
    .db-rich-kpi--amber .db-rich-kpi-icon  { background: #fef3c7; color: #d97706; }
    .db-rich-kpi--amber .db-rich-kpi-value { color: #b45309; }
    .db-rich-kpi--orange { border-left-color: #ff6b35; }
    .db-rich-kpi--orange .db-rich-kpi-icon { background: #fff0eb; color: #ea580c; }
    .db-rich-kpi--orange .db-rich-kpi-value{ color: #ea580c; }

    .db-summary-bar {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0;
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: .85rem;
        padding: .9rem 1rem;
        box-shadow: 0 4px 18px rgba(15,23,42,.25);
        min-width: 0;
        overflow: hidden;
    }
    @media (max-width: 900px) { .db-summary-bar { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem 0; } }
    @media (max-width: 520px)  { .db-summary-bar { grid-template-columns: minmax(0, 1fr); } }
    .db-summary-item {
        display: block; text-decoration: none; color: inherit;
        padding: .25rem .75rem;
        border-right: 1px solid rgba(255,255,255,.08);
        min-width: 0;
    }
    .db-summary-item:last-child { border-right: none; }
    @media (max-width: 900px) {
        .db-summary-item:nth-child(2n) { border-right: none; }
    }
    @media (max-width: 520px) {
        .db-summary-item { border-right: none; border-bottom: 1px solid rgba(255,255,255,.08); padding-bottom: .65rem; }
        .db-summary-item:last-child { border-bottom: none; }
    }
    a.db-summary-item:hover .db-summary-value { color: #fdba74; }
    .db-summary-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; }
    .db-summary-value {
        margin-top: .2rem; font-size: 1rem; font-weight: 800; color: #f8fafc;
        display: flex; align-items: center; gap: .4rem;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .db-summary-value--green  { color: #4ade80; }
    .db-summary-value--amber  { color: #fbbf24; }
    .db-summary-value--orange { color: #fb923c; }
    .db-summary-value--purple { color: #c4b5fd; }
    .db-summary-value--blue   { color: #60a5fa; }
    .db-summary-value--teal   { color: #2dd4bf; }
    .db-summary-sub { margin-top: .15rem; font-size: .68rem; color: #94a3b8; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .db-summary-dot { width: .45rem; height: .45rem; border-radius: 50%; flex-shrink: 0; }
    .db-summary-dot--ok   { background: #4ade80; box-shadow: 0 0 6px rgba(74,222,128,.6); }
    .db-summary-dot--warn { background: #fbbf24; box-shadow: 0 0 6px rgba(251,191,36,.5); }

    /* ── KPI grid (legacy) ── */
    .db-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
        min-width: 0;
    }
    @media (max-width: 1100px) { .db-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px)  { .db-kpi-grid { grid-template-columns: minmax(0, 1fr); } }
    .db-kpi-grid--finance .mi-kpi { border-left-color: #8b5cf6; }
    .db-kpi-grid--finance .mi-kpi-icon { background: #f3e8ff; color: #8b5cf6; }
    .db-kpi-link { display: block; text-decoration: none; color: inherit; min-width: 0; transition: transform .15s; }
    .db-kpi-link:hover { transform: translateY(-1px); }
    .db-kpi-link .mi-kpi { cursor: pointer; }
    .db-kpi-grid .mi-kpi > div:first-child { min-width: 0; flex: 1; }
    .db-kpi-grid .mi-kpi-value {
        font-size: clamp(.95rem, 2vw, 1.45rem) !important;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .db-kpi-grid .mi-kpi-value.orange { font-size: clamp(.9rem, 1.8vw, 1.25rem) !important; }

    /* ── Role shell (full-width KPIs, then content + sidebar) ── */
    .db-role-shell { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }

    /* ── Layout ── */
    .db-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 16.5rem);
        gap: 1rem;
        align-items: start;
        min-width: 0;
    }
    @media (max-width: 1100px) { .db-layout { grid-template-columns: minmax(0, 1fr); } }
    .db-main, .db-side { min-width: 0; display: flex; flex-direction: column; gap: 1rem; }
    .db-layout--with-side .db-side .db-quick-grid { grid-template-columns: minmax(0, 1fr); }

    /* ── Quick actions ── */
    .db-quick-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .6rem;
        min-width: 0;
    }
    @media (max-width: 640px) { .db-quick-grid { grid-template-columns: minmax(0, 1fr); } }
    .db-quick {
        display: flex; align-items: flex-start; gap: .65rem;
        padding: .75rem .85rem; border-radius: .75rem; border: 1px solid #e5e7eb;
        background: #fff; text-decoration: none; min-width: 0;
        transition: border-color .15s, box-shadow .15s;
    }
    .db-quick:hover { border-color: #fdba74; box-shadow: 0 4px 14px rgba(255,107,53,.08); }
    .db-quick--primary { border-color: #fed7aa; background: linear-gradient(135deg, #fffbeb, #fff7ed); }
    .db-quick-icon {
        width: 2rem; height: 2rem; border-radius: .55rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: #fff7ed; color: #ea580c; font-size: .8rem;
    }
    .db-quick--primary .db-quick-icon { background: #ff6b35; color: #fff; }
    .db-quick-title { font-size: .8rem; font-weight: 600; color: #111827; line-height: 1.3; }
    .db-quick-desc { font-size: .68rem; color: #6b7280; margin-top: .12rem; line-height: 1.35; }

    /* ── Pipeline chips ── */
    .db-pipeline { display: flex; flex-wrap: wrap; gap: .45rem; }
    .db-pipe-chip {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .4rem .7rem; border-radius: 999px; border: 1px solid #e5e7eb;
        background: #fafafa; font-size: .72rem; font-weight: 600; color: #374151;
        text-decoration: none; transition: border-color .15s, background .15s;
        max-width: 100%;
    }
    .db-pipe-chip:hover { border-color: #fdba74; background: #fff7ed; color: #ea580c; }
    .db-pipe-chip .count {
        min-width: 1.2rem; height: 1.2rem; border-radius: 999px;
        background: #ff6b35; color: #fff; font-size: .62rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center; padding: 0 .3rem;
        flex-shrink: 0;
    }

    /* ── Tables ── */
    .db-table-wrap { overflow-x: auto; max-width: 100%; }
    .db-table { width: 100%; border-collapse: collapse; font-size: .8rem; table-layout: fixed; }
    .db-table th {
        text-align: left; font-size: .62rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: #9ca3af; padding: .5rem .75rem; border-bottom: 1px solid #f3f4f6;
    }
    .db-table td {
        padding: .55rem .75rem; border-bottom: 1px solid #f9fafb; color: #374151;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .db-table tr:hover td { background: #fafafa; }
    .db-table a { color: #ea580c; font-weight: 600; text-decoration: none; }
    .db-table a:hover { text-decoration: underline; }
    .db-empty { padding: 1.5rem; text-align: center; color: #9ca3af; font-size: .8rem; }

    /* ── Charts ── */
    .db-charts-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(0, 1fr);
        grid-template-rows: auto auto;
        gap: .85rem;
        min-width: 0;
    }
    @media (max-width: 1024px) { .db-charts-grid { grid-template-columns: minmax(0, 1fr); } }
    .db-chart-card { min-width: 0; }
    .db-chart-card--sales { grid-column: 1; grid-row: 1; }
    .db-chart-card--procurement { grid-column: 2; grid-row: 1; }
    .db-chart-card--shops { grid-column: 1 / -1; grid-row: 2; }
    @media (max-width: 1024px) {
        .db-chart-card--sales,
        .db-chart-card--procurement,
        .db-chart-card--shops { grid-column: 1; grid-row: auto; }
    }
    .db-chart-title {
        font-size: .68rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: .06em; color: #6b7280;
        display: flex; align-items: center; gap: .4rem; margin-bottom: .15rem;
    }
    .db-chart-desc { font-size: .72rem; color: #9ca3af; margin-bottom: .5rem; }
    .db-chart-wrap { position: relative; max-width: 100%; overflow: hidden; }
    .db-chart-wrap canvas { max-width: 100% !important; }
    .db-chart-wrap--tall { height: 280px; }
    .db-chart-wrap--doughnut { height: 200px; }
    .db-chart-wrap--bar { height: 220px; }

    /* ── Activity tables mosaic ── */
    .db-tables-mosaic {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: .85rem;
        min-width: 0;
    }
    @media (max-width: 900px) {
        .db-tables-mosaic { grid-template-columns: minmax(0, 1fr); }
    }
    .db-table-panel { min-width: 0; }
    .db-table-panel--wide    { grid-column: span 7; }
    .db-table-panel--medium  { grid-column: span 5; }
    .db-table-panel--narrow  { grid-column: span 5; }
    .db-table-panel--full    { grid-column: 1 / -1; }
    @media (max-width: 900px) {
        .db-table-panel--wide,
        .db-table-panel--medium,
        .db-table-panel--narrow,
        .db-table-panel--full { grid-column: 1 / -1; }
    }

    /* legacy equal grid */
    .db-tables-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .85rem;
        min-width: 0;
    }
    @media (max-width: 900px) { .db-tables-grid { grid-template-columns: minmax(0, 1fr); } }

    /* ── Attendant ── */
    .db-attendant-actions {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;
    }
    @media (max-width: 640px) { .db-attendant-actions { grid-template-columns: minmax(0, 1fr); } }
    .db-attendant-btn {
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .75rem;
        padding: 2rem 1.5rem; border-radius: 1rem; border: 2px solid #e5e7eb;
        background: #fff; text-decoration: none; transition: all .15s; text-align: center;
    }
    .db-attendant-btn:hover { border-color: #ff6b35; background: #fff7ed; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,107,53,.12); }
    .db-attendant-btn--primary { border-color: #ff6b35; background: linear-gradient(135deg, #fff7ed, #ffedd5); }
    .db-attendant-btn i { font-size: 2rem; color: #ff6b35; }
    .db-attendant-btn span { font-size: 1.1rem; font-weight: 700; color: #111827; }
    .db-attendant-btn small { font-size: .75rem; color: #6b7280; }
</style>
