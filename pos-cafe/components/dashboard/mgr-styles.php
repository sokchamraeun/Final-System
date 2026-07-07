<?php declare(strict_types=1);
/* ============================================================
   Dashboard (manager) — light-first styles for the redesigned
   overview: stat-card grid, revenue chart card, recent-orders
   table. Scoped to .mdash so it never touches the employee
   dashboard (.dash) or other pages. Dark mode via the shell's
   .dark class on <html>.
   ============================================================ */
?>
<style>
.mdash{
  --card:#ffffff; --card-br:#e9edf2; --ink:#0f172a; --ink-2:#475569; --ink-3:#94a3b8;
  --line:#eef1f5; --shadow:0 1px 2px rgba(16,24,40,.04),0 4px 16px rgba(16,24,40,.05);
  --accent:#0f766e; --accent-2:#10b981; --blue:#3b82f6;
  font-family:'Inter','Poppins',sans-serif; color:var(--ink);
}
.dark .mdash{
  --card:#0f172a; --card-br:#1e293b; --ink:#f1f5f9; --ink-2:#94a3b8; --ink-3:#64748b;
  --line:#1e293b; --shadow:0 1px 2px rgba(0,0,0,.3),0 4px 20px rgba(0,0,0,.35);
}
.mdash *{box-sizing:border-box;}
.mdash a{text-decoration:none;color:inherit;}

/* ── Page header (title + clock) ── */
.mdash-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:22px;flex-wrap:wrap;}
.mdash-title{font-size:30px;font-weight:800;letter-spacing:-.02em;color:var(--accent);line-height:1.1;}
.mdash-sub{font-size:13.5px;color:var(--ink-2);margin-top:6px;}
.mdash-clock{display:flex;align-items:center;gap:12px;background:var(--card);border:1px solid var(--card-br);border-radius:16px;padding:12px 18px;box-shadow:var(--shadow);}
.mdash-clock .ic{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;background:rgba(15,118,110,.1);color:var(--accent);font-size:16px;}
.mdash-clock .t{font-size:19px;font-weight:800;font-variant-numeric:tabular-nums;color:var(--ink);line-height:1.1;}
.mdash-clock .d{font-size:11.5px;color:var(--ink-2);margin-top:2px;}

/* ── Stat cards ── */
.mdash-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:22px;}
.stat{background:var(--card);border:1px solid var(--card-br);border-radius:16px;padding:18px 18px 16px;box-shadow:var(--shadow);transition:transform .15s ease,box-shadow .15s ease;}
.stat:hover{transform:translateY(-3px);box-shadow:0 8px 26px rgba(16,24,40,.10);}
.stat-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:14px;}
.stat-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-2);line-height:1.35;}
.stat-ic{width:40px;height:40px;flex-shrink:0;border-radius:12px;display:grid;place-items:center;color:#fff;font-size:16px;}
.stat-value{font-size:26px;font-weight:800;color:var(--ink);font-variant-numeric:tabular-nums;line-height:1.05;}
.stat-foot{font-size:12px;color:var(--ink-3);margin-top:6px;}
.ic-green{background:#10b981;} .ic-teal{background:#0d9488;} .ic-orange{background:#f59e0b;}
.ic-blue{background:#3b82f6;} .ic-amber{background:#f97316;} .ic-red{background:#ef4444;}
.ic-indigo{background:#6366f1;}

/* ── 2-col: chart + recent orders ── */
.mdash-grid2{display:grid;grid-template-columns:1.15fr .85fr;gap:16px;align-items:start;}
.mcard{background:var(--card);border:1px solid var(--card-br);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;}
.mcard-head{padding:20px 22px 0;}
.mcard-head h3{font-size:19px;font-weight:800;color:var(--ink);letter-spacing:-.01em;}
.mcard-head p{font-size:12.5px;color:var(--ink-2);margin-top:3px;}

/* ── Revenue chart ── */
.rc-top{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;}
.rc-toggle{display:inline-flex;background:var(--line);border-radius:10px;padding:3px;gap:2px;}
.rc-toggle button{border:0;background:transparent;color:var(--ink-2);font:600 12.5px/1 'Inter',sans-serif;padding:7px 12px;border-radius:8px;cursor:pointer;transition:.15s;}
.rc-toggle button.on{background:var(--accent);color:#fff;box-shadow:0 1px 4px rgba(15,118,110,.35);}
.rc-mini{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;padding:18px 22px 6px;}
.rc-box{border:1px solid var(--card-br);border-radius:12px;padding:12px 14px;}
.rc-box .k{font-size:11px;font-weight:600;color:var(--ink-2);margin-bottom:5px;}
.rc-box .v{font-size:20px;font-weight:800;color:var(--ink);font-variant-numeric:tabular-nums;}
.rc-legend{display:flex;gap:18px;padding:6px 22px 0;font-size:12px;color:var(--ink-2);}
.rc-legend span{display:inline-flex;align-items:center;gap:6px;}
.rc-dot{width:9px;height:9px;border-radius:50%;display:inline-block;}
.rc-canvas-wrap{padding:12px 16px 20px;height:300px;}

/* ── Recent orders table ── */
.ro-wrap{padding:8px 6px 6px;overflow-x:auto;}
.ro-tbl{width:100%;border-collapse:collapse;min-width:520px;}
.ro-tbl th{padding:12px 14px;text-align:left;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);border-bottom:1px solid var(--line);white-space:nowrap;}
.ro-tbl td{padding:14px 14px;border-bottom:1px solid var(--line);font-size:13.5px;color:var(--ink);vertical-align:middle;}
.ro-tbl tbody tr:last-child td{border-bottom:0;}
.ro-tbl tbody tr{transition:background .12s;}
.ro-tbl tbody tr:hover{background:var(--line);}
.ro-id{font-weight:800;color:var(--ink);}
.ro-num{text-align:center;font-variant-numeric:tabular-nums;}
.ro-total{font-weight:800;font-variant-numeric:tabular-nums;}
.pill{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:50px;font-size:11.5px;font-weight:700;white-space:nowrap;}
.pill i{font-size:9px;opacity:.7;}
.pill.green{background:rgba(16,185,129,.12);color:#059669;}
.pill.red{background:rgba(239,68,68,.12);color:#dc2626;}
.pill.amber{background:rgba(245,158,11,.14);color:#d97706;}
.pill.purple{background:rgba(139,92,246,.14);color:#7c3aed;}
.pill.blue{background:rgba(59,130,246,.13);color:#2563eb;}
.pill.slate{background:rgba(100,116,139,.14);color:#475569;}
.dark .pill.green{color:#34d399;} .dark .pill.red{color:#f87171;} .dark .pill.amber{color:#fbbf24;}
.dark .pill.purple{color:#a78bfa;} .dark .pill.blue{color:#60a5fa;} .dark .pill.slate{color:#94a3b8;}
.ro-view{display:inline-flex;align-items:center;gap:6px;padding:6px 13px;border:1px solid var(--card-br);border-radius:9px;font-size:12px;font-weight:700;color:var(--accent);transition:.15s;}
.ro-view:hover{background:rgba(15,118,110,.08);border-color:var(--accent);}
.ro-empty{padding:36px;text-align:center;color:var(--ink-3);font-size:13.5px;}

/* ── Responsive ── */
@media(max-width:1200px){.mdash-stats{grid-template-columns:repeat(3,1fr);}.mdash-grid2{grid-template-columns:1fr;}}
@media(max-width:720px){.mdash-stats{grid-template-columns:repeat(2,1fr);}.rc-mini{grid-template-columns:repeat(2,1fr);}.mdash-title{font-size:24px;}}
@media(max-width:440px){.mdash-stats{grid-template-columns:1fr;}}
</style>
