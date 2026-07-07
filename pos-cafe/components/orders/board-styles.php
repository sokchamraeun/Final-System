<?php
declare(strict_types=1);
/* Orders board — CSS. Light theme, teal accent: stat cards, search/filter
   toolbar, status table, and the order-detail modal that houses the
   call/paid/complete/cancel/refund/remake/delete actions. */
?>
<style>
:root {
    --teal:        #0f766e;
    --teal-dark:   #115e59;
    --teal-light:  #14b8a6;
    --mint:        #f0fdfa;
    --mint-border: #99f6e4;
    --ink:         #0f172a;
    --muted:       #64748b;
    --border:      #e2e8f0;
    --bg:          #f8fafc;
    --card:        #ffffff;
    --blue:        #2563eb;
    --green:       #16a34a;
    --amber:       #f59e0b;
    --red:         #e11d48;
    --shadow-sm:   0 1px 3px rgba(15,23,42,0.06), 0 1px 2px rgba(15,23,42,0.04);
    --shadow-md:   0 4px 16px rgba(15,23,42,0.08);
    --radius:      16px;
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
.ob-subtitle { margin: 6px 0 0; font-size: 13.5px; color: var(--teal-light); font-weight: 500; }
.header-actions { display:flex; align-items:center; gap:8px; flex-shrink:0; }

.btn-pill {
    display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 999px;
    font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all .15s ease;
    background: #fff; border: 1px solid var(--border); color: var(--ink);
}
.btn-pill:hover { border-color: var(--teal); color: var(--teal); }
.btn-clear-local { background: #fff1f2; border-color: #fecdd3; color: #e11d48; }
.btn-clear-local:hover { background: #ffe4e6; border-color: #fda4af; color: #be123c; }
.theme-toggle { background:#fff; border:1px solid var(--border); color: var(--muted); }
.logout-btn { background:#fff1f2; border-color:#fecdd3; color:#e11d48; }
.logout-btn:hover { background:#ffe4e6; }

/* ── Stat cards ── */
.stat-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 22px; }
@media (max-width: 1200px) { .stat-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 700px)  { .stat-grid { grid-template-columns: repeat(2, 1fr); } }

.stat-card {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 18px 20px; box-shadow: var(--shadow-sm); transition: transform .15s ease, box-shadow .15s ease;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.stat-head { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom: 10px; }
.stat-label { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); }
.stat-icon {
    width: 38px; height: 38px; border-radius: 12px; display:flex; align-items:center; justify-content:center;
    color: #fff; font-size: 15px; flex-shrink: 0;
}
.stat-icon.c-blue  { background: var(--blue); }
.stat-icon.c-green { background: var(--green); }
.stat-icon.c-amber { background: var(--amber); }
.stat-icon.c-red   { background: var(--red); }
.stat-icon.c-teal  { background: var(--teal); }
.stat-value { font-size: 26px; font-weight: 800; color: var(--ink); line-height: 1.1; }
.stat-desc { margin-top: 6px; font-size: 12px; color: var(--muted); }

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
    background: #fff; font-family: 'Poppins', sans-serif; font-size: 13.5px; outline: none; color: var(--ink);
}
.ob-search-wrap input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(15,118,110,.12); }
.ob-select, .ob-date-btn {
    padding: 10px 14px; border-radius: 12px; border: 1px solid var(--border); background: #fff;
    font-family: 'Poppins', sans-serif; font-size: 13.5px; color: var(--ink); cursor: pointer; outline: none;
}
.ob-date-btn { display:inline-flex; align-items:center; gap: 8px; white-space: nowrap; }

/* ── Filter pills ── */
.status-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
.status-tab {
    padding: 9px 22px; border-radius: 999px; background: #fff; border: 1px solid var(--border);
    color: var(--ink); text-decoration: none; font-weight: 600; font-size: 13.5px; cursor: pointer;
    display: inline-flex; align-items: center; gap: 8px; transition: all .15s ease;
}
.status-tab:hover { border-color: var(--teal); color: var(--teal); }
.status-tab.active { background: var(--teal); border-color: var(--teal); color: #fff; }
.status-tab .badge { background: rgba(15,23,42,.06); padding: 1px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; }
.status-tab.active .badge { background: rgba(255,255,255,.22); color: #fff; }

/* ── Table ── */
.ob-table-wrap {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
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
.ob-table tbody tr:hover { background: var(--mint); }
.ob-order-no { font-weight: 700; color: var(--teal); }
.ob-muted { color: var(--muted); }

.ob-pill {
    display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 999px;
    font-size: 11.5px; font-weight: 700; background: var(--mint); border: 1px solid var(--mint-border); color: var(--teal-dark);
}
.ob-pill.tone-blue   { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.ob-pill.tone-green  { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.ob-pill.tone-amber  { background: #fffbeb; border-color: #fde68a; color: #b45309; }
.ob-pill.tone-red    { background: #fff1f2; border-color: #fecdd3; color: #be123c; }
.ob-pill.tone-purple { background: #faf5ff; border-color: #e9d5ff; color: #7e22ce; }

.btn-view-detail {
    display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: 999px;
    background: #fff; border: 1px solid var(--teal); color: var(--teal); font-size: 12.5px; font-weight: 700;
    cursor: pointer; transition: all .15s ease;
}
.btn-view-detail:hover { background: var(--teal); color: #fff; }

.ob-empty { text-align: center; padding: 60px 20px; color: var(--muted); }
.ob-empty i { font-size: 40px; display: block; margin-bottom: 14px; color: var(--border); }
.ob-empty h3 { color: var(--ink); font-weight: 700; margin: 0 0 6px; }

/* ── Order Detail Modal ── */
.detail-modal {
    display: none; position: fixed; inset: 0; background: rgba(15,23,42,.45); backdrop-filter: blur(3px);
    z-index: 9999; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 40px 16px;
}
.detail-modal.active { display: flex; }
.detail-modal-content {
    background: #fff; border-radius: 20px; max-width: 640px; width: 100%; box-shadow: var(--shadow-md);
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
.detail-item-chip { font-size: 10px; padding: 1px 7px; border-radius: 999px; background: #fff; border: 1px solid var(--border); color: var(--muted); }
.detail-reason { font-size: 12.5px; font-style: italic; padding: 10px 12px; border-radius: 10px; margin-top: 14px; }
.detail-reason.cancel-reason { background: #fff1f2; color: #be123c; }
.detail-reason.refund-reason { background: #faf5ff; color: #7e22ce; }
.detail-reason.remake-reason { background: #fffbeb; color: #b45309; }
.detail-foot { display:flex; flex-wrap: wrap; gap: 8px; padding: 16px 24px; border-top: 1px solid var(--border); background: var(--bg); }
.detail-foot button {
    flex: 1; min-width: 110px; padding: 10px 14px; border-radius: 12px; border: 1px solid transparent;
    font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif;
    display: flex; align-items: center; justify-content: center; gap: 6px; transition: all .15s ease;
}
.detail-foot button:hover { transform: translateY(-1px); }
.detail-foot button:disabled { opacity: .4; cursor: not-allowed; transform: none; }
.detail-foot .call-btn     { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.detail-foot .paid-btn     { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.detail-foot .complete-btn { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
.detail-foot .cancel-btn   { background: #fff1f2; color: #be123c; border-color: #fecdd3; }
.detail-foot .refund-btn   { background: #faf5ff; color: #7e22ce; border-color: #e9d5ff; }
.detail-foot .remake-btn   { background: #fffbeb; color: #b45309; border-color: #fde68a; }
.detail-foot .delete-btn   { background: #fff1f2; color: #be123c; border-color: #fecdd3; flex: 0 0 auto; min-width: auto; padding: 10px 16px; }

/* ── Call Notification / Cancel / Refund / Remake modals (kept, restyled light) ── */
.call-modal, .cancel-modal, .refund-modal {
    display: none; position: fixed; inset: 0; background: rgba(15,23,42,.45); backdrop-filter: blur(3px);
    z-index: 10000; justify-content: center; align-items: center;
}
.call-modal.active, .cancel-modal.active, .refund-modal.active { display: flex; }
.call-modal-content, .cancel-modal-content, .refund-modal-content {
    background: #fff; border-radius: 20px; padding: 32px; text-align: center; max-width: 440px; width: 100%;
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
.btn-cancel-no, .btn-refund-no { background: var(--bg); color: var(--ink); border: 1px solid var(--border); }

/* Remake pill adjustments (reused inside the detail-driven remake modal) */
.remake-item-block { background: var(--bg); border: 1px solid var(--border); border-radius: 12px; padding: 12px 14px; margin-bottom: 10px; text-align: left; }
.remake-item-block + .remake-item-block { margin-top: 8px; }
.remake-item-name { font-size: 12px; font-weight: 700; color: var(--teal); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 10px; }
.remake-adj-group { margin-bottom: 8px; }
.remake-adj-group:last-child { margin-bottom: 0; }
.remake-adj-label { font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 5px; }
.pill-group { display: flex; flex-wrap: wrap; gap: 5px; }
.pill-opt { padding: 4px 11px; border-radius: 999px; border: 1px solid var(--border); background: #fff; color: var(--ink); font-size: 12px; cursor: pointer; transition: all .15s; font-family: inherit; }
.pill-opt:hover { border-color: var(--teal); color: var(--teal); }
.pill-opt.selected { background: var(--teal); border-color: var(--teal); color: #fff; font-weight: 600; }
#remakeAdjustments { max-height: 280px; overflow-y: auto; margin-bottom: 4px; }

.fade-out { opacity: 0; transform: translateX(20px); transition: all .4s ease; }

@media (max-width: 900px) {
    .ob-table-wrap { overflow-x: auto; }
    table.ob-table { min-width: 900px; }
}
</style>
