<style>
:root {
    --bg:           #f0f2f5;
    --surface:      #ffffff;
    --surface-2:    #f8f9fb;
    --glass:        rgba(255,255,255,0.95);
    --glass-hi:     rgba(255,255,255,1);
    --border:       #e5e7eb;
    --border-hi:    #d1d5db;
    --navy:         #0f172a;
    --navy-light:   #1e293b;
    --teal:         #0d9488;
    --teal-light:   #14b8a6;
    --teal-dim:     rgba(13,148,136,0.1);
    --teal-glow:    rgba(13,148,136,0.2);
    --amber:        #d1904b;
    --amber-light:  #e8b87a;
    --amber-dim:    rgba(209,144,75,0.1);
    --amber-glow:   rgba(209,144,75,0.2);
    --emerald:      #10b981;
    --emerald-dim:  rgba(16,185,129,0.1);
    --blue:         #3b82f6;
    --blue-dim:     rgba(59,130,246,0.1);
    --red:          #ef4444;
    --red-dim:      rgba(239,68,68,0.1);
    --orange:       #f97316;
    --orange-dim:   rgba(249,115,22,0.1);
    --purple:       #8b5cf6;
    --purple-dim:   rgba(139,92,246,0.1);
    --yellow:       #eab308;
    --yellow-dim:   rgba(234,179,8,0.1);
    --indigo:       #6366f1;
    --indigo-dim:   rgba(99,102,241,0.1);
    --accent:       var(--teal);
    --success:      var(--emerald);
    --warning:      var(--amber);
    --danger:       var(--red);
    --text:         #111827;
    --text-muted:   #6b7280;
    --text-xs:      #9ca3af;
    --r:            16px;
    --r-sm:         12px;
    --r-xs:         8px;
    --shadow:       0 1px 3px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.04);
    --shadow-lg:    0 4px 16px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.04);
    --shadow-card:  0 1px 3px rgba(0,0,0,.05), 0 2px 8px rgba(0,0,0,.03);
    --ease:         cubic-bezier(.4,0,.2,1);
    --spring:       cubic-bezier(.34,1.56,.64,1);
    --gold:         var(--amber);
    --accent-2:     var(--teal);
    --pos:          var(--emerald);
    --neg:          var(--red);
    --bg-card:      var(--surface);
    --bg-card-hover: var(--surface-2);
    --border-hover: var(--border-hi);
    --refund-color: #8b5cf6;
    --refund-light: #a78bfa;
}
[data-theme="dark"] {
    --bg:           #0b0f1a;
    --surface:      #111827;
    --surface-2:    #1e293b;
    --glass:        rgba(255,255,255,0.04);
    --glass-hi:     rgba(255,255,255,0.07);
    --border:       #1e293b;
    --border-hi:    #334155;
    --text:         #f1f5f9;
    --text-muted:   #94a3b8;
    --text-xs:      #64748b;
    --shadow:       0 4px 24px rgba(0,0,0,.45);
    --shadow-lg:    0 8px 40px rgba(0,0,0,.65);
    --shadow-card:  0 2px 8px rgba(0,0,0,.3);
}

[data-theme="dark"] body { background: var(--bg); }

* { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--bg); color: var(--text); font-family: 'Poppins', sans-serif; }

/* ── Top Navigation Header ── */
.top-nav {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 12px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.top-nav-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.top-nav-left .welcome-text {
    font-size: 14px;
    color: var(--text-muted);
}
.top-nav-left .welcome-text strong {
    color: var(--text);
    font-weight: 600;
}
.top-nav-right {
    display: flex;
    align-items: center;
    gap: 20px;
}
.top-nav-icon {
    position: relative;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: var(--surface-2);
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid var(--border);
}
.top-nav-icon:hover {
    background: var(--teal-dim);
    color: var(--teal);
    border-color: var(--teal);
}
.top-nav-icon .badge {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--red);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--surface);
}
.top-nav-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 12px 6px 6px;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.2s;
}
.top-nav-profile:hover {
    background: var(--surface-2);
}
.top-nav-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--teal), var(--teal-light));
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
}
.top-nav-user-info {
    display: flex;
    flex-direction: column;
}
.top-nav-user-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    line-height: 1.2;
}
.top-nav-user-role {
    font-size: 11px;
    color: var(--text-muted);
}

/* ── Mode Switcher ── */
.mode-switcher {
    display: flex;
    gap: 4px;
    padding: 4px;
    background: rgba(255,255,255,0.1);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.12);

}
.mode-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 500;
    border: none;
    background: transparent;
    color: rgba(255,255,255,0.55);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(.4,0,.2,1);
    white-space: nowrap;
}
.mode-btn:hover {
    color: #fff;
    background: rgba(255,255,255,0.08);
}
.mode-btn.active {
    color: #fff;
    background: var(--teal);
    font-weight: 600;
    box-shadow: 0 2px 10px rgba(13,148,136,0.4);
}
.mode-btn.active:hover {
    background: var(--teal-light);
}

/* ── Hero Banner ── */
.hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f4c5c 100%);
    padding: 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    position: relative;
    overflow: hidden;
    margin: 24px 32px 0;
    border-radius: var(--r);
    box-shadow: var(--shadow-lg);
}
.hero-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(13,148,136,0.15) 0%, transparent 70%);
    pointer-events: none;
}
.hero-banner::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: 10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(209,144,75,0.1) 0%, transparent 70%);
    pointer-events: none;
}
.hero-content {
    position: relative;
    z-index: 1;
    flex: 1;
}
.hero-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.hero-brand-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(13,148,136,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--teal-light);
    font-size: 14px;
}
.hero-brand-name {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: rgba(255,255,255,0.5);
}
.hero-title {
    font-size: 28px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 8px;
    line-height: 1.2;
}
.hero-desc {
    font-size: 14px;
    color: rgba(255,255,255,0.6);
    max-width: 500px;
    line-height: 1.5;
}
.hero-actions {
    position: relative;
    z-index: 1;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.hero-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.08);
    color: #fff;
    cursor: pointer;
    transition: all 0.2s;

}
.hero-btn:hover {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.25);
    transform: translateY(-1px);
}
.hero-btn.primary {
    background: var(--teal);
    border-color: var(--teal);
}
.hero-btn.primary:hover {
    background: var(--teal-light);
    border-color: var(--teal-light);
}

/* ── Report Category Navigation ── */
.report-tabs {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 6px;
    margin: 24px 32px 0;
    display: flex;
    gap: 4px;
    overflow: hidden;
    box-shadow: var(--shadow-card);
}
.report-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: var(--r-sm);
    font-size: 13px;
    font-weight: 500;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(.4,0,.2,1);
    white-space: nowrap;
    border: none;
    background: transparent;
    position: relative;
    flex-shrink: 0;
}
.report-tab:hover {
    background: var(--surface-2);
    color: var(--text);
}
.report-tab.active {
    background: var(--navy);
    color: #fff;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(15,23,42,0.3);
}
.report-tab.active:hover {
    background: var(--navy-light);
}
.report-tab.active::after {
    content: '';
    position: absolute;
    bottom: -7px;
    left: 50%;
    transform: translateX(-50%);
    width: 24px;
    height: 3px;
    border-radius: 3px;
    background: var(--teal);
}
.report-tab i {
    font-size: 14px;
    transition: transform 0.25s cubic-bezier(.4,0,.2,1);
}
.report-tab:hover i {
    transform: scale(1.15);
}

/* ── Filter Panel ── */
.filter-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 24px;
    margin: 24px 32px 0;
    box-shadow: var(--shadow-card);
}
.filter-row {
    display: flex;
    align-items: flex-end;
    gap: 0;
    flex-wrap: wrap;
}
.filter-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.filter-section--left {
    flex: 1;
    min-width: 280px;
    padding-right: 24px;
}
.filter-section--right {
    flex: 1;
    min-width: 300px;
    padding-left: 24px;
}
.filter-section--action {
    flex-shrink: 0;
    padding-left: 16px;
    align-self: flex-end;
}
.filter-section-divider {
    width: 1px;
    background: var(--border);
    align-self: stretch;
    margin: 0 0 4px;
    flex-shrink: 0;
}
.filter-section-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 4px;
}
.filter-section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--teal-dim);
    color: var(--teal);
    font-size: 16px;
    flex-shrink: 0;
}
.filter-section-text {
    margin-bottom: 4px;
}
.filter-section--left .filter-section-text {
    margin-bottom: 0;
}
.filter-section-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
}
.filter-section-subtitle {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 2px;
}
.filter-section-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
}
.quick-select {
    display: flex;
    gap: 6px;
}
.quick-btn {
    padding: 8px 16px;
    border-radius: var(--r-xs);
    font-size: 13px;
    font-weight: 500;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s;
}
.quick-btn:hover {
    border-color: var(--teal);
    color: var(--teal);
    background: var(--teal-dim);
}
.quick-btn.active {
    background: var(--teal);
    color: #fff;
    border-color: var(--teal);
}
.date-range {
    display: flex;
    align-items: flex-end;
    gap: 12px;
}
.date-input-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.date-input-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
}
.date-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.date-input-wrapper input {
    padding: 9px 12px 9px 36px;
    border-radius: var(--r-xs);
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--text);
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    min-width: 160px;
    transition: border-color 0.2s;
}
.date-input-wrapper input:focus {
    outline: none;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px var(--teal-dim);
}
.date-input-wrapper i {
    position: absolute;
    left: 12px;
    color: var(--text-xs);
    font-size: 14px;
    pointer-events: none;
}
.date-divider {
    padding-bottom: 9px;
    color: var(--text-xs);
    font-size: 13px;
}

/* ── Search & Filter Toolbar ── */
.search-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 16px 32px 0;
    padding: 12px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    box-shadow: var(--shadow-card);
    flex-wrap: wrap;
}
.search-toolbar .search-box {
    flex: 1;
    min-width: 240px;
    position: relative;
}
.search-toolbar .search-box input {
    width: 100%;
    padding: 10px 12px 10px 40px;
    border-radius: var(--r-xs);
    border: 1px solid var(--border);
    background: var(--surface-2);
    color: var(--text);
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    transition: border-color 0.2s;
}
.search-toolbar .search-box input:focus {
    outline: none;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px var(--teal-dim);
    background: var(--surface);
}
.search-toolbar .search-box input::placeholder {
    color: var(--text-xs);
}
.search-toolbar .search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-xs);
    font-size: 14px;
    pointer-events: none;
}
.toolbar-dropdown {
    min-width: 150px;
}
.toolbar-dropdown select {
    width: 100%;
    padding: 10px 32px 10px 12px;
    border-radius: var(--r-xs);
    border: 1px solid var(--border);
    background: var(--surface-2);
    color: var(--text);
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    transition: border-color 0.2s;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}
.toolbar-dropdown select:focus {
    outline: none;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px var(--teal-dim);
}

/* ── Filter Responsive ── */
@media (max-width: 992px) {
    .filter-row { flex-direction: column; gap: 16px; }
    .filter-section--left,
    .filter-section--right { padding: 0; min-width: 100%; }
    .filter-section-divider { display: none; }
    .filter-section--action { padding: 0; align-self: stretch; }
    .filter-section--action .quick-btn { width: 100%; }
    .date-range { flex-wrap: wrap; }
    .search-toolbar { flex-direction: column; align-items: stretch; }
    .search-toolbar .search-box { min-width: 100%; }
    .toolbar-dropdown { min-width: 100%; }
}

/* ── Compact dashboard KPI grid ── */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 20px 32px 0; }
@media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 992px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 576px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }

.kpi-card {
    background: var(--surface);
    border: none;
    border-radius: var(--r);
    padding: 20px;
    transition: .15s var(--ease);
    overflow: hidden;
    position: relative;
    display: block;
    box-shadow: var(--shadow-card);
}
.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.kpi-icon-box {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
    flex-shrink: 0;
}
.kpi-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    margin-bottom: 10px;
    padding-right: 48px;
}
.kpi-value {
    font-size: 24px;
    font-weight: 400;
    color: var(--text);
    font-variant-numeric: tabular-nums;
    line-height: 1.1;
    margin-bottom: 4px;
}
.kpi-sub { font-size: 11px; color: var(--text-xs); line-height: 1.3; }

.live-bar { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--emerald); margin: 16px 32px 0; padding: 10px 16px; background: var(--emerald-dim); border: 1px solid rgba(16,185,129,0.2); border-radius: var(--r-sm); width: fit-content; }
.live-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--emerald); flex-shrink: 0; }
.live-dot.pulsing { animation: livePulse 1.8s ease-in-out infinite; }
@keyframes livePulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.75)} }
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.fu{animation:fadeUp .4s var(--spring) both}

/* ── Insight Chips ── */
.insights-row { display: flex; flex-wrap: wrap; gap: 10px; margin: 20px 32px 0; }
.insight-chip { display: flex; align-items: center; gap: 12px; padding: 12px 18px; border-radius: var(--r-sm); background: var(--surface); border: 1px solid var(--border); font-size: 13px; transition: all 0.2s; box-shadow: var(--shadow-card); }
.insight-chip:hover { border-color: var(--border-hi); box-shadow: var(--shadow); }
.insight-chip i { color: var(--teal); font-size: 16px; }
.ic-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); }
.ic-val { font-weight: 600; color: var(--text); }
.ic-good i { color: var(--emerald); }
.ic-warn i { color: #eab308; }
.ic-alert i { color: var(--red); }

/* ── Content area ── */
.report-content { padding: 0 32px 32px; }

.section-hdr { margin-bottom: 16px; }
.section-hdr-row { display: flex; align-items: center; gap: 8px; }
.section-hdr-title { font-size: 16px; font-weight: 700; color: var(--text); }
.section-hdr i { color: var(--teal); }
.section-hdr-badge { font-size: 11px; padding: 4px 10px; border-radius: 20px; background: var(--teal-dim); color: var(--teal); font-weight: 600; }
.section-desc { font-size: 13px; color: var(--text-muted); margin: 4px 0 0; }

.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 24px;
    margin-top: 20px;
    transition: all 0.2s;
    box-shadow: var(--shadow-card);
}
.card:hover { border-color: var(--border-hi); box-shadow: var(--shadow); }

/* ── Charts ── */
.chart-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 768px) { .chart-grid { grid-template-columns: 1fr; } }
.chart-wrap { background: var(--surface-2); border: 1px solid var(--border); border-radius: var(--r-sm); padding: 20px; transition: all 0.2s; height: 300px; }
.chart-wrap:hover { border-color: var(--border-hi); }
.chart-title { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.chart-title i { color: var(--teal); }
.chart-wrap canvas { max-height: 280px; }

/* ── Badges ── */
.badge { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 50px; }
.badge.good { background: var(--emerald-dim); color: var(--emerald); }
.badge.warn { background: var(--yellow-dim); color: #ca8a04; }
.badge.refund { background: var(--purple-dim); color: var(--purple); }

.delta { font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 20px; }
.delta.up { color: var(--emerald); background: var(--emerald-dim); }
.delta.down { color: var(--red); background: var(--red-dim); }
.delta.neutral { color: var(--text-muted); background: var(--surface-2); }

/* ── Report Summary ── */
.report-summary { font-size: 14px; line-height: 1.7; color: var(--text-muted); padding: 18px; border-left: 3px solid var(--teal); background: var(--teal-dim); border-radius: 0 var(--r-sm) var(--r-sm) 0; }
.report-summary strong { color: var(--text); }
.report-summary strong.good { color: var(--emerald); }
.report-summary strong.ok { color: var(--teal); }
.report-summary strong.warn { color: #ca8a04; }

.empty { text-align: center; padding: 60px 20px; color: var(--text-muted); font-size: 14px; }
.empty i { font-size: 40px; display: block; margin-bottom: 12px; color: var(--border); }

/* ── Tables ── */
.refund-table, .remake-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.refund-table th, .remake-table th { text-align: left; padding: 12px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); border-bottom: 2px solid var(--border); background: var(--surface-2); }
.refund-table td, .remake-table td { padding: 12px 14px; border-bottom: 1px solid var(--border); color: var(--text); }
.refund-table tbody tr:hover, .remake-table tbody tr:hover { background: var(--surface-2); }
.refund-table-wrapper, .remake-table-wrapper { overflow-x: auto; margin-top: 16px; }

.refund-charts { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px; }
@media (max-width: 768px) { .refund-charts { grid-template-columns: 1fr; } }

.report-pager { display: flex; justify-content: center; gap: 6px; margin-top: 16px; }
.report-pager a, .report-pager span { padding: 6px 12px; border-radius: 8px; font-size: 13px; border: 1px solid var(--border); color: var(--text); text-decoration: none; transition: all 0.2s; }
.report-pager a:hover { border-color: var(--teal); color: var(--teal); }
.report-pager .active { background: var(--teal); color: #fff; border-color: var(--teal); }

/* ── Theme Toggle ── */
.theme-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: var(--r-xs);
    font-size: 13px;
    font-weight: 500;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.08);
    color: #fff;
    cursor: pointer;
    transition: all 0.2s;

}
.theme-toggle:hover {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.25);
}

@media print {
    body { padding: 20px; background: #fff !important; color: #000 !important; }
    .top-nav, .hero-banner, .report-tabs, .theme-toggle { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; background: #fff !important; break-inside: avoid; }
    .kpi-card { background: #f9f9f9 !important; }
    .chart-wrap { border: 1px solid #ddd !important; background: #fff !important; }
    .section-desc { color: #555 !important; }
    .insight-chip { border-color: #ddd !important; background: #fafafa !important; }
    .report-summary { border-left-color: #aaa !important; background: #f9f9f9 !important; color: #000 !important; }
    .report-pager { display: none !important; }
    .refund-table tbody tr { display: table-row !important; }
}
</style>
