<?php
declare(strict_types=1);
/* Orders board — CSS. Dark/light theme using CSS variables matching
   the dashboard dark-mode style (0b0b0b bg, 111111 surface, etc.).
   data-theme="light" overrides for light mode. */
?>
<style>
:root {
    --teal:        #14B8A6;
    --teal-dark:   #0F766E;
    --teal-light:  #14B8A6;
    --bg:          #0F172A;
    --card:        #111827;
    --surface-2:   #1A2332;
    --ink:         #F8FAFC;
    --muted:       #94A3B8;
    --muted-xs:    #475569;
    --border:      #23314D;
    --border-hi:   #2D3F5F;
    --badge-bg:    rgba(255,255,255,.08);
    --blue:        #3B82F6;
    --green:       #14B8A6;
    --amber:       #F59E0B;
    --red:         #EF4444;
    --purple:      #8B5CF6;
    --card-tint:   rgba(20,184,166,.06);
    --mint:        var(--card);
    --mint-border: var(--border);
    --shadow-sm:   0 4px 24px rgba(0,0,0,.5);
    --shadow-md:   0 8px 40px rgba(0,0,0,.65);
    --radius:      16px;
}
[data-theme="light"] {
    --bg:          #ECEEF2;
    --card:        #ffffff;
    --surface-2:   #F5F7FA;
    --ink:         #111827;
    --muted:       #5A6373;
    --muted-xs:    #9CA3AF;
    --border:      #E2E5EA;
    --border-hi:   #CDD0D8;
    --badge-bg:    rgba(15,23,42,.06);
    --card-tint:   #ffffff;
    --teal:        #0F766E;
    --teal-dark:   #0B5E5A;
    --blue:        #3B82F6;
    --green:       #10B981;
    --amber:       #F59E0B;
    --red:         #EF4444;
    --purple:      #8B5CF6;
    --mint:        #f0fdfa;
    --mint-border: #99f6e4;
    --shadow-sm:   0 1px 3px rgba(0,0,0,.07), 0 4px 14px rgba(0,0,0,.06);
    --shadow-md:   0 4px 20px rgba(0,0,0,.09), 0 1px 4px rgba(0,0,0,.05);
}

* { box-sizing: border-box; }

body {
    background: var(--bg);
    font-family: 'Poppins', sans-serif;
    color: var(--ink);
    margin: 0;
    min-height: 100vh;
}

.vo-flex-wrap { display: flex; min-height: 100vh; }
.vo-main-col { flex: 1; display: flex; flex-direction: column; min-width: 0; overflow-y: auto; padding: 28px 32px; }

@media (max-width: 768px) {
    .vo-main-col { padding: 16px; }
    .sb-navbar-toggle { display: flex !important; }
}

/* ── Page header ── */
.dash-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom: 22px; }
.ob-title { font-size: 32px; font-weight: 800; color: var(--teal-dark); margin: 0; line-height: 1.15; }
.ob-subtitle { margin: 6px 0 0; font-size: 13.5px; color: var(--muted); font-weight: 500; }
.header-actions { display:flex; align-items:center; gap:8px; flex-shrink:0; }

.btn-pill {
    display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 999px;
    font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all .15s ease;
    background: var(--card); border: 1px solid var(--border); color: var(--ink);
}
.btn-pill:hover { border-color: var(--teal); color: var(--teal); }
.btn-clear-local { background: rgba(255,107,107,.08); border-color: rgba(255,107,107,.2); color: var(--red); }
.btn-clear-local:hover { background: rgba(255,107,107,.15); }
.theme-toggle { background:var(--card); border:1px solid var(--border); color: var(--muted); }
.logout-btn { background:rgba(255,107,107,.08); border-color:rgba(255,107,107,.2); color:var(--red); }
.logout-btn:hover { background:rgba(255,107,107,.15); }

/* ── Stat cards ── */
.stat-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 18px; }
@media (max-width: 1200px) { .stat-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 700px)  { .stat-grid { grid-template-columns: repeat(2, 1fr); } }

.stat-card {
    background: var(--card-tint); border: 1px solid var(--border); border-radius: 12px;
    padding: 12px 14px; box-shadow: var(--shadow-sm); transition: transform .15s ease, box-shadow .15s ease;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.stat-head { display:flex; align-items:center; justify-content:space-between; margin-bottom: 6px; }
.stat-label { font-size: 10px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--muted); }
.stat-icon {
    width: 28px; height: 28px; border-radius: 8px; display:flex; align-items:center; justify-content:center;
    color: #fff; font-size: 12px; flex-shrink: 0;
}
.stat-icon.c-blue  { background: var(--blue); }
.stat-icon.c-green { background: var(--green); }
.stat-icon.c-amber { background: var(--amber); }
.stat-icon.c-red   { background: var(--red); }
.stat-icon.c-teal  { background: var(--teal); }
.stat-value { font-size: 20px; font-weight: 800; color: var(--ink); line-height: 1; }
.stat-desc { margin-top: 4px; font-size: 10.5px; color: var(--muted); }

/* ── Toolbar (search + staff + date) ── */
.ob-toolbar {
    display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    background: var(--mint); border: 1px solid var(--mint-border); border-radius: var(--radius);
    padding: 14px 16px; margin-bottom: 16px;
}
.ob-search-wrap { position: relative; flex: 1; min-width: 240px; }
.ob-search-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 13px; }
.ob-search-wrap input {
    width: 100%; padding: 10px 14px 10px 36px; border-radius: 12px; border: 1px solid var(--border);
    background: var(--card); font-family: 'Poppins', sans-serif; font-size: 13.5px; outline: none; color: var(--ink);
}
.ob-search-wrap input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(15,118,110,.12); }
.ob-select, .ob-date-btn {
    padding: 10px 14px; border-radius: 12px; border: 1px solid var(--border); background: var(--card);
    font-family: 'Poppins', sans-serif; font-size: 13.5px; color: var(--ink); cursor: pointer; outline: none;
}
.ob-date-btn { display:inline-flex; align-items:center; gap: 8px; white-space: nowrap; }

/* ── Filter pills ── */
.status-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
.status-tab {
    padding: 9px 22px; border-radius: 999px; background: var(--card); border: 1px solid var(--border);
    color: var(--ink); text-decoration: none; font-weight: 600; font-size: 13.5px; cursor: pointer;
    display: inline-flex; align-items: center; gap: 8px; transition: all .15s ease;
}
.status-tab:hover { border-color: var(--teal); color: var(--teal); }
.status-tab.active { background: var(--teal); border-color: var(--teal); color: #fff; }
.status-tab .badge { background: var(--badge-bg); padding: 1px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; }
.status-tab.active .badge { background: rgba(255,255,255,.22); color: #fff; }

/* ── Table ── */
.ob-table-wrap {
    background: var(--card-tint); border: 1px solid var(--border); border-radius: var(--radius);
    overflow: hidden; box-shadow: var(--shadow-sm);
}
table.ob-table { width: 100%; border-collapse: collapse; }
.ob-table thead th {
    background: var(--teal-dark); color: #fff; text-align: left; font-size: 11.5px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em; padding: 14px 16px; white-space: nowrap;
}
.ob-table thead th.right, .ob-table tbody td.right { text-align: right; }
.ob-table tbody td { padding: 14px 16px; font-size: 13.5px; border-bottom: 1px solid var(--border); vertical-align: middle; }
.ob-table tbody tr:last-child td { border-bottom: none; }
.ob-table tbody tr { transition: background .12s ease; }
.ob-table tbody tr:hover { background: var(--surface-2); }
.ob-order-no { font-weight: 700; color: var(--teal); }
.ob-muted { color: var(--muted); }

.ob-pill {
    display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 999px;
    font-size: 11.5px; font-weight: 700; background: var(--mint); border: 1px solid var(--mint-border); color: var(--teal-dark);
}
.ob-pill.tone-blue   { background: rgba(52,152,219,.13); border-color: rgba(52,152,219,.25); color: var(--blue); }
.ob-pill.tone-green  { background: rgba(85,224,135,.13); border-color: rgba(85,224,135,.25); color: var(--green); }
.ob-pill.tone-amber  { background: rgba(209,144,75,.13); border-color: rgba(209,144,75,.25); color: var(--amber); }
.ob-pill.tone-red    { background: rgba(255,107,107,.13); border-color: rgba(255,107,107,.25); color: var(--red); }
.ob-pill.tone-purple { background: rgba(155,89,182,.13); border-color: rgba(155,89,182,.25); color: var(--purple); }

.btn-view-detail {
    display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: 999px;
    background: var(--card); border: 1px solid var(--teal); color: var(--teal); font-size: 12.5px; font-weight: 700;
    cursor: pointer; transition: all .15s ease;
}
.btn-view-detail:hover { background: var(--teal); color: #fff; }

.pay-modal {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.65); backdrop-filter:blur(4px);
    z-index:10000; justify-content:center; align-items:center;
}
.pay-modal.active { display:flex; }
.pay-modal-content {
    background:var(--card); border-radius:20px; max-width:420px; width:100%; overflow:hidden; box-shadow:var(--shadow-md);
}
.pay-modal-head {
    display:flex; align-items:center; justify-content:space-between;
    background:linear-gradient(135deg,#0d9488,#059669); padding:18px 24px; color:#fff;
}
.pay-modal-head h2 { margin:0; font-size:17px; font-weight:700; display:flex; align-items:center; gap:8px; }
.pay-close-btn {
    width:32px; height:32px; border-radius:10px; border:none; background:rgba(255,255,255,.15);
    color:#fff; font-size:15px; cursor:pointer; display:flex; align-items:center; justify-content:center;
}
.pay-close-btn:hover { background:rgba(255,255,255,.25); }
.pay-modal-body { padding:22px 24px 24px; }
.pay-order-no { font-size:22px; font-weight:800; color:var(--ink); margin-bottom:4px; }
.pay-prompt { font-size:13px; color:var(--muted); margin:0 0 16px; }
.ob-pay-methods { display:flex; gap:8px; }
.ob-pay-method { flex:1; display:flex; flex-direction:column; align-items:center; gap:5px; padding:16px 4px 12px; border-radius:14px; cursor:pointer; user-select:none; transition:all .2s; border:1.5px solid var(--border); background:var(--card); color:var(--muted); }
.ob-pay-method:hover { border-color:var(--teal-light); background:var(--surface-2); }
.ob-pay-method.selected { border-color:var(--teal); background:var(--surface-2); color:var(--teal); box-shadow:0 0 0 2px rgba(15,118,110,.2); }
.ob-pay-method input { display:none; }
.ob-pm-ico { font-size:22px; }
.ob-pm-lbl { font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
.ob-pm-check { opacity:0; transition:all .2s; font-size:14px; margin-top:2px; }
.ob-pay-method.selected .ob-pm-check { opacity:1; }
.pay-modal .pay-btn-group { display:flex; gap:10px; margin-top:20px; }
.pay-modal .pay-btn-group button { flex:1; padding:12px; border-radius:12px; border:none; font-weight:700; cursor:pointer; font-family:'Poppins',sans-serif; font-size:13.5px; transition:all .15s; }
.pay-modal .pay-btn-group button:hover { transform:translateY(-1px); }
.pay-btn-confirm { background:var(--teal); color:#fff; }
.pay-btn-confirm:hover { background:var(--teal-dark); }
.pay-btn-cancel { background:var(--surface-2); color:var(--muted); }
.pay-btn-cancel:hover { background:var(--border); }
.pay-order-summary { margin-bottom:16px; background:var(--bg); border-radius:12px; border:1px solid var(--border); overflow:hidden; }
.pay-summary-inner { padding:12px 16px; }
.pay-sum-row { display:flex; align-items:center; justify-content:space-between; padding:4px 0; font-size:13px; color:var(--ink); }
.pay-sum-name { font-weight:500; }
.pay-sum-qty { color:var(--muted); font-size:12px; margin-left:4px; }
.pay-sum-total { display:flex; align-items:center; justify-content:space-between; margin-top:8px; padding-top:8px; border-top:1.5px solid var(--border); font-size:15px; font-weight:700; color:var(--ink); }
.pay-qr-area { text-align:center; }
.pay-qr-wrap { margin:8px 0; display:flex; justify-content:center; }
.pay-qr-wrap img { width:220px; height:220px; border-radius:14px; border:2px solid var(--border); padding:8px; background:var(--card); }
.pay-qr-hint { font-size:12px; color:var(--muted); margin-bottom:4px; }
.pay-qr-status { font-size:13px; font-weight:600; color:var(--amber); margin-bottom:14px; }
.ob-empty { text-align: center; padding: 60px 20px; color: var(--muted); }
.ob-empty i { font-size: 40px; display: block; margin-bottom: 14px; color: var(--border); }
.ob-empty h3 { color: var(--ink); font-weight: 700; margin: 0 0 6px; }

/* ── Order Detail Modal ── */
.detail-modal {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,.65); backdrop-filter: blur(4px);
    z-index: 9999; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 40px 16px;
}
.detail-modal.active { display: flex; }
.detail-modal-content {
    background: var(--card); border-radius: 20px; max-width: 640px; width: 100%; box-shadow: var(--shadow-md);
    overflow: hidden;
}
.detail-head {
    display: flex; align-items: center; justify-content: space-between; padding: 20px 24px;
    background: var(--teal-dark); color: #fff;
}
.detail-head h2 { margin: 0; font-size: 18px; font-weight: 700; display:flex; align-items:center; gap:10px; }
.detail-head .btn-close-detail { background: rgba(255,255,255,.15); border: none; color: #fff; width: 32px; height: 32px; border-radius: 10px; cursor: pointer; font-size: 14px; }
.detail-body { padding: 22px 24px; max-height: 65vh; overflow-y: auto; }
.detail-row { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed var(--border); font-size: 13.5px; }
.detail-row:last-child { border-bottom: none; }
.detail-row .k { color: var(--muted); font-weight: 600; }
.detail-row .v { color: var(--ink); font-weight: 600; text-align: right; }
.detail-items { margin-top: 16px; }
.detail-item-line { display: flex; align-items: flex-start; gap: 10px; padding: 8px 10px; background: var(--bg); border-radius: 10px; margin-bottom: 6px; }
.detail-item-qty { background: var(--teal); color: #fff; font-size: 11px; font-weight: 700; min-width: 22px; height: 22px; border-radius: 6px; display:flex; align-items:center; justify-content:center; flex-shrink: 0; }
.detail-item-name { font-size: 13px; font-weight: 600; color: var(--ink); }
.detail-item-chips { display:flex; flex-wrap:wrap; gap:4px; margin-top:3px; }
.detail-item-chip { font-size: 10px; padding: 1px 7px; border-radius: 999px; background: var(--card); border: 1px solid var(--border); color: var(--muted); }
.detail-reason { font-size: 12.5px; font-style: italic; padding: 10px 12px; border-radius: 10px; margin-top: 14px; }
.detail-reason.cancel-reason { background: rgba(255,107,107,.13); color: var(--red); }
.detail-reason.refund-reason { background: rgba(155,89,182,.13); color: var(--purple); }
.detail-reason.remake-reason { background: rgba(209,144,75,.13); color: var(--amber); }
.detail-foot { display:flex; flex-wrap: wrap; gap: 8px; padding: 16px 24px; border-top: 1px solid var(--border); background: var(--bg); }
.detail-foot button {
    flex: 1; min-width: 110px; padding: 10px 14px; border-radius: 12px; border: 1px solid transparent;
    font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif;
    display: flex; align-items: center; justify-content: center; gap: 6px; transition: all .15s ease;
}
.detail-foot button:hover { transform: translateY(-1px); }
.detail-foot button:disabled { opacity: .4; cursor: not-allowed; transform: none; }
.detail-foot .open-btn     { background: rgba(85,224,135,.13); color: var(--green); border-color: rgba(85,224,135,.25); }
.detail-foot .call-btn     { background: rgba(52,152,219,.13); color: var(--blue); border-color: rgba(52,152,219,.25); }
.detail-foot .paid-btn     { background: rgba(52,152,219,.13); color: var(--blue); border-color: rgba(52,152,219,.25); }
.detail-foot .complete-btn { background: rgba(85,224,135,.13); color: var(--green); border-color: rgba(85,224,135,.25); }
.detail-foot .cancel-btn   { background: rgba(255,107,107,.13); color: var(--red); border-color: rgba(255,107,107,.25); }
.detail-foot .refund-btn   { background: rgba(155,89,182,.13); color: var(--purple); border-color: rgba(155,89,182,.25); }
.detail-foot .remake-btn   { background: rgba(209,144,75,.13); color: var(--amber); border-color: rgba(209,144,75,.25); }
.detail-foot .delete-btn   { background: rgba(255,107,107,.13); color: var(--red); border-color: rgba(255,107,107,.25); flex: 0 0 auto; min-width: auto; padding: 10px 16px; }

/* ── Call Notification / Cancel / Refund / Remake modals ── */
.call-modal, .cancel-modal, .refund-modal {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,.65); backdrop-filter: blur(4px);
    z-index: 10000; justify-content: center; align-items: center;
}
.call-modal.active, .cancel-modal.active, .refund-modal.active { display: flex; }
.call-modal-content, .cancel-modal-content, .refund-modal-content {
    background: var(--card); border-radius: 20px; padding: 32px; text-align: center; max-width: 440px; width: 100%;
    box-shadow: var(--shadow-md);
}
.call-modal-content h2 { font-size: 40px; color: var(--teal); margin: 0 0 8px; }
.call-modal-content .order-number { font-size: 56px; font-weight: 800; color: var(--ink); margin: 12px 0; }
.call-modal-content p { color: var(--muted); margin-bottom: 18px; }
.call-modal-content .btn-dismiss {
    padding: 11px 36px; border-radius: 999px; border: none; background: var(--teal); color: #fff;
    font-weight: 700; font-size: 15px; cursor: pointer;
}
.cancel-modal-content h2, .refund-modal-content h2 { font-size: 22px; margin: 0 0 8px; color: var(--ink); }
.cancel-modal-content .order-number, .refund-modal-content .order-number { font-size: 26px; font-weight: 700; color: var(--teal); margin: 6px 0; }
.cancel-modal-content p, .refund-modal-content p { color: var(--muted); font-size: 13px; }
.cancel-modal-content textarea, .refund-modal-content textarea, .refund-modal-content input {
    width: 100%; padding: 11px 14px; border-radius: 12px; border: 1px solid var(--border);
    background: var(--bg); color: var(--ink); font-family: 'Poppins', sans-serif; font-size: 13.5px; margin: 10px 0;
}
.cancel-modal-content textarea { min-height: 76px; resize: vertical; }
.refund-modal-content textarea { min-height: 56px; resize: vertical; }
.cancel-modal-content textarea:focus, .refund-modal-content textarea:focus, .refund-modal-content input:focus { outline: none; border-color: var(--teal); }
.refund-modal-content .form-group { margin: 10px 0; text-align: left; }
.refund-modal-content .form-group label { display: block; font-size: 12.5px; color: var(--muted); margin-bottom: 4px; }
.btn-group { display: flex; gap: 10px; margin-top: 14px; }
.btn-group button { flex: 1; padding: 11px; border-radius: 12px; border: none; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; font-size: 13.5px; }
.btn-cancel-yes, .btn-refund-yes { background: var(--red); color: #fff; }
.btn-refund-yes { background: var(--teal); }
.btn-cancel-no, .btn-refund-no { background: var(--surface-2); color: var(--ink); border: 1px solid var(--border); }

/* Remake pill adjustments (reused inside the detail-driven remake modal) */
.remake-item-block { background: var(--bg); border: 1px solid var(--border); border-radius: 12px; padding: 12px 14px; margin-bottom: 10px; text-align: left; }
.remake-item-block + .remake-item-block { margin-top: 8px; }
.remake-item-name { font-size: 12px; font-weight: 700; color: var(--teal); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 10px; }
.remake-adj-group { margin-bottom: 8px; }
.remake-adj-group:last-child { margin-bottom: 0; }
.remake-adj-label { font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 5px; }
.pill-group { display: flex; flex-wrap: wrap; gap: 5px; }
.pill-opt { padding: 4px 11px; border-radius: 999px; border: 1px solid var(--border); background: var(--card); color: var(--ink); font-size: 12px; cursor: pointer; transition: all .15s; font-family: inherit; }
.pill-opt:hover { border-color: var(--teal); color: var(--teal); }
.pill-opt.selected { background: var(--teal); border-color: var(--teal); color: #fff; font-weight: 600; }
#remakeAdjustments { max-height: 280px; overflow-y: auto; margin-bottom: 4px; }

.fade-out { opacity: 0; transform: translateX(20px); transition: all .4s ease; }

@media (max-width: 900px) {
    .ob-table-wrap { overflow-x: auto; }
    table.ob-table { min-width: 900px; }
}
</style>
