<?php
declare(strict_types=1);
/* ============================================================
   Dashboard: style tokens — dark/light theme, KPI cards,
   panels, tables, and quick-access tiles.  Paired with
   layout/header.php (Tailwind shell) which owns the sidebar
   and top navbar.
   ============================================================ */
$_is_mgr = $_is_mgr ?? false;
?>
<style>
:root {
    --bg:           #0b0b0b;
    --surface:      #111111;
    --surface-2:    #181818;
    --glass:        rgba(255,255,255,0.04);
    --glass-hi:     rgba(255,255,255,0.07);
    --border:       #1f1f1f;
    --border-hi:    #2a2a2a;
    --amber:        #d1904b;
    --amber-light:  #e8b87a;
    --amber-dim:    rgba(209,144,75,0.15);
    --amber-glow:   rgba(209,144,75,0.25);
    --emerald:      #55e087;
    --emerald-dim:  rgba(85,224,135,0.13);
    --blue:         #3498db;
    --blue-dim:     rgba(52,152,219,0.13);
    --red:          #ff6b6b;
    --red-dim:      rgba(255,107,107,0.13);
    --purple:       #9b59b6;
    --purple-dim:   rgba(155,89,182,0.13);
    --accent:       var(--amber);
    --success:      var(--emerald);
    --warning:      var(--amber);
    --danger:       var(--red);
    --text:         #f5f5f5;
    --text-muted:   #888;
    --text-xs:      #444;
    --r:            14px;
    --r-sm:         10px;
    --r-xs:         7px;
    --shadow:       0 4px 24px rgba(0,0,0,.45);
    --shadow-lg:    0 8px 40px rgba(0,0,0,.65);
    --ease:         cubic-bezier(.4,0,.2,1);
    --spring:       cubic-bezier(.34,1.56,.64,1);
}
[data-theme="light"] {
    --bg:        #ECEEF2;
    --surface:   #FFF;
    --surface-2: #F5F7FA;
    --glass:     rgba(255,255,255,.90);
    --glass-hi:  rgba(255,255,255,.98);
    --border:    #E2E5EA;
    --border-hi: #CDD0D8;
    --text:      #111827;
    --text-muted:#5A6373;
    --text-xs:   #9CA3AF;
    --shadow:    0 1px 3px rgba(0,0,0,.07), 0 4px 14px rgba(0,0,0,.06);
    --shadow-lg: 0 4px 20px rgba(0,0,0,.09), 0 1px 4px rgba(0,0,0,.05);
}

.dash *{margin:0;padding:0;box-sizing:border-box;}
.dash{font-family:'Poppins',sans-serif;color:var(--text);line-height:1.5;}
.dash a{color:inherit;text-decoration:none;}
.dash img{display:block;}

/* Animations */
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.fu{animation:fadeUp .4s var(--spring) both}

/* ambient glow */
.dash-body::before{
    content:'';position:fixed;top:-160px;left:0;right:0;height:480px;
    background:radial-gradient(ellipse at 55% 0%,rgba(209,144,75,.045) 0%,transparent 68%);
    pointer-events:none;z-index:0;
}

/* Dashboard header */
.dash-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px;position:relative;z-index:1;}
.dash-header h1{font-size:26px;font-weight:700;color:var(--text);}
.dash-header h1 .name{color:var(--amber);}
.header-sub{display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-muted);margin-top:4px;}
.header-actions{display:flex;align-items:center;gap:10px;flex-shrink:0;margin-top:4px;}

/* Theme toggle */
.theme-toggle{display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:10px;font-size:13px;font-weight:500;background:var(--glass);border:1px solid var(--border);color:var(--text-muted);transition:background .2s,border-color .2s,color .2s;}
.theme-toggle:hover{background:var(--glass-hi);border-color:var(--border-hi);color:var(--text);}

/* Logout btn */
.logout-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:10px;font-size:13px;font-weight:500;background:rgba(255,107,107,.08);border:1px solid rgba(255,107,107,.2);color:#ff6b6b;transition:.2s;}
.logout-btn:hover{background:rgba(255,107,107,.15);}

/* Alert strip */
.alert-strip{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;position:relative;z-index:1;}
.alert-pill{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:var(--r-sm);font-size:13px;font-weight:600;transition:.2s;}
.alert-pill.danger{background:var(--red-dim);border:1px solid rgba(255,107,107,.22);color:var(--red);}
.alert-pill.warning{background:var(--amber-dim);border:1px solid var(--amber-glow);color:var(--amber-light);}
.alert-pill:hover{transform:translateY(-1px);}

/* KPI row */
.kpi-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:22px;position:relative;z-index:1;}
.kpi-card{position:relative;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:20px 22px;overflow:hidden;transition:.2s var(--ease);}
.kpi-card:hover{transform:translateY(-3px);border-color:var(--border-hi);box-shadow:var(--shadow);}
.kpi-watermark{position:absolute;right:-6px;bottom:-10px;font-size:72px;opacity:.06;pointer-events:none;}
.kpi-card.c-amber .kpi-watermark{color:var(--amber);}
.kpi-card.c-green .kpi-watermark{color:var(--emerald);}
.kpi-card.c-blue .kpi-watermark{color:var(--blue);}
.kpi-label{font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:6px;}
.kpi-value{font-size:32px;font-weight:800;color:var(--text);font-variant-numeric:tabular-nums;line-height:1.1;margin-bottom:10px;}
.kpi-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:50px;font-size:11px;font-weight:600;}
.kpi-pill.up{background:var(--emerald-dim);color:var(--emerald);}
.kpi-pill.down{background:var(--red-dim);color:var(--red);}
.kpi-pill.flat{background:var(--glass);color:var(--text-muted);}

/* Mid grid (kitchen + top sellers) */
.mid-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px;position:relative;z-index:1;}

/* Panels */
.panel{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;transition:.2s var(--ease);}
.panel:hover{border-color:var(--border-hi);box-shadow:var(--shadow);}
.panel-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);}
.panel-head h3{font-size:14px;font-weight:700;display:flex;align-items:center;gap:8px;color:var(--text);}
.panel-link{font-size:12px;font-weight:600;color:var(--amber);display:flex;align-items:center;gap:5px;transition:.2s;}
.panel-link:hover{color:var(--amber-light);gap:8px;}
.panel-foot{display:flex;align-items:center;justify-content:space-between;padding:10px 20px;border-top:1px solid var(--border);font-size:11px;color:var(--text-muted);}

/* Kitchen queue */
.kitchen-body{padding:12px 16px;max-height:380px;overflow-y:auto;}
.k-item{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);}
.k-item:last-child{border-bottom:none;}
.k-no{font-weight:800;font-size:15px;color:var(--text);flex-shrink:0;width:44px;}
.k-name{flex:1;font-size:14px;font-weight:500;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.k-total{font-weight:700;font-size:14px;color:var(--amber);flex-shrink:0;}
.k-timer{padding:3px 10px;border-radius:50px;font-size:11px;font-weight:700;flex-shrink:0;}
.k-timer.ok{background:var(--emerald-dim);color:var(--emerald);}
.k-timer.warn{background:var(--amber-dim);color:var(--amber-light);}
.k-timer.urgent{background:var(--red-dim);color:var(--red);}
.k-status-pill{display:flex;align-items:center;gap:5px;padding:4px 10px;border-radius:50px;font-size:10px;font-weight:700;background:var(--amber-dim);color:var(--amber-light);flex-shrink:0;}
.k-empty{display:flex;align-items:center;justify-content:center;gap:10px;padding:32px;color:var(--text-muted);font-size:14px;}
.live-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--emerald);animation:pulse-dot 1.8s infinite;}
@keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:.3}}
.cnt-badge{padding:4px 12px;border-radius:50px;font-size:11px;font-weight:700;background:var(--glass);color:var(--text-muted);}
.cnt-badge.on{background:var(--emerald-dim);color:var(--emerald);}
.refresh-btn{width:32px;height:32px;border-radius:8px;background:var(--glass);border:1px solid var(--border);color:var(--text-muted);display:flex;align-items:center;justify-content:center;transition:.2s;}
.refresh-btn:hover{background:var(--glass-hi);border-color:var(--border-hi);color:var(--text);}

/* Top sellers */
.sellers-body{padding:12px 16px;}
.seller-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);}
.seller-row:last-child{border-bottom:none;}
.s-rank{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;background:var(--glass);color:var(--text-muted);flex-shrink:0;}
.s-rank.gold{background:linear-gradient(135deg,#f5b342,#d1904b);color:#000;}
.s-img{width:34px;height:34px;border-radius:8px;object-fit:cover;background:var(--glass);flex-shrink:0;}
.s-info{flex:1;min-width:0;}
.s-name{font-size:13px;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:4px;}
.s-track{height:4px;background:var(--glass);border-radius:2px;overflow:hidden;}
.s-bar{height:100%;background:var(--amber);border-radius:2px;transition:width .4s var(--spring);}
.s-count{font-weight:800;font-size:14px;color:var(--amber);flex-shrink:0;}

/* Orders table */
.orders-tbl{width:100%;border-collapse:collapse;}
.orders-tbl thead th{padding:12px 20px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);border-bottom:1px solid var(--border);}
.orders-tbl tbody td{padding:14px 20px;border-bottom:1px solid var(--border);font-size:13px;}
.orders-tbl tbody tr:last-child td{border-bottom:none;}
.orders-tbl tbody tr{transition:background .15s;}
.orders-tbl tbody tr:hover{background:var(--glass);}
.o-no{font-weight:700;color:var(--text);}
.cust-cell{display:flex;align-items:center;gap:10px;}
.cust-av{width:30px;height:30px;border-radius:50%;background:var(--glass);display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--text-muted);}
.badge{display:inline-flex;align-items:center;padding:4px 12px;border-radius:50px;font-size:11px;font-weight:700;gap:5px;}
.badge.pending{background:var(--amber-dim);color:var(--amber-light);}
.badge.paid{background:var(--emerald-dim);color:var(--emerald);}
.badge.preparing{background:var(--blue-dim);color:var(--blue);}
.badge.completed{background:var(--emerald-dim);color:var(--emerald);}
.badge.cancelled{background:var(--red-dim);color:var(--red);}
.badge.pendingpayment{background:var(--purple-dim);color:var(--purple);}
.tbl-empty{display:flex;align-items:center;justify-content:center;gap:10px;padding:32px;color:var(--text-muted);font-size:14px;}

/* Role badge */
.role-badge{display:inline-block;padding:3px 10px;border-radius:50px;font-size:11px;font-weight:600;margin-left:8px;background:var(--role-color)22;color:var(--role-color);vertical-align:middle;border:1px solid var(--role-color)44;}

/* Focus card (employee view) */
.focus-card{display:flex;align-items:center;gap:18px;background:var(--surface-2);border:1px solid var(--border);border-left:4px solid var(--accent);border-radius:16px;padding:18px 22px;margin-bottom:22px;transition:transform .15s,border-color .15s;position:relative;z-index:1;}
.focus-card:hover{transform:translateY(-2px);border-color:var(--border-hi);}
.focus-icon{flex:0 0 auto;border-radius:16px;display:flex;align-items:center;justify-content:center;}
.focus-body{flex:1;min-width:0;}
.focus-count{font-variant-numeric:tabular-nums;line-height:1.1;}
.focus-sub{font-size:13px;color:var(--text-muted);margin-top:4px;}
.focus-cta{flex:0 0 auto;display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:600;white-space:nowrap;}

/* Quick-access tiles */
.qx-grid{display:flex;flex-direction:column;gap:14px;position:relative;z-index:1;}
.qa-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;}
.qa-hero-btn{grid-column:1 / -1;display:flex;align-items:center;justify-content:center;gap:10px;padding:20px;border-radius:var(--r);background:linear-gradient(135deg,rgba(209,144,75,.22),rgba(209,144,75,.08));border:1px solid var(--amber-glow);color:var(--amber-light);font-size:17px;font-weight:700;transition:.2s;}
.qa-hero-btn:hover{background:linear-gradient(135deg,rgba(209,144,75,.32),rgba(209,144,75,.12));transform:translateY(-2px);}
.qx-group,.qa-group{margin-bottom:6px;}
.qx-group-label,.qa-group-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin-bottom:8px;display:flex;align-items:center;gap:6px;}
.qx-tiles{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;}
.qa-tiles{display:grid;grid-template-columns:repeat(auto-fill,minmax(165px,1fr));gap:10px;}
.qx-tile,.qa-tile{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;padding:16px 12px;border-radius:var(--r-sm);background:var(--surface);border:1px solid var(--border);color:var(--text);font-size:12px;font-weight:600;transition:.2s;text-align:center;position:relative;}
.qx-tile:hover,.qa-tile:hover{background:var(--surface-2);border-color:var(--border-hi);transform:translateY(-2px);box-shadow:var(--shadow);}
.qx-tile i,.qa-tile i{font-size:22px;color:var(--amber);}
.qx-tile-badge,.qa-tile-badge{position:absolute;top:8px;right:8px;background:var(--red);color:#fff;font-size:10px;font-weight:700;min-width:18px;height:18px;border-radius:9px;display:flex;align-items:center;justify-content:center;padding:0 4px;line-height:1;}

/* Toast */
.toast-container{position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;}
.toast{display:flex;align-items:center;gap:10px;padding:13px 18px;border-radius:var(--r-sm);font-size:13px;font-weight:500;box-shadow:var(--shadow-lg);transition:opacity .3s var(--ease),transform .3s var(--ease);min-width:240px;max-width:400px;}
.toast.success{background:linear-gradient(135deg,#0a2a1a,#0d331f);border:1px solid rgba(85,224,135,.25);color:#9bdfb0;}
.toast.error{background:linear-gradient(135deg,#2a0a0a,#330d0d);border:1px solid rgba(255,107,107,.25);color:#ffb3b3;}
.close-toast{margin-left:auto;cursor:pointer;opacity:.6;font-size:16px;}

/* Role badge for non-managers */
.role-badge{display:inline-block;padding:3px 10px;border-radius:50px;font-size:11px;font-weight:600;margin-left:8px;background:var(--role-color)22;color:var(--role-color);vertical-align:middle;border:1px solid var(--role-color)44;}

/* Overlay (mobile) */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.72);backdrop-filter:blur(4px);z-index:90;}
.overlay.active{display:block;}

@media(max-width:768px){
    .kpi-row{grid-template-columns:1fr;}
    .mid-grid{grid-template-columns:1fr;}
    .dash-header{flex-direction:column;}
    .dash-header h1{font-size:22px;}
}
</style>
