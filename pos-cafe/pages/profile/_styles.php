<style>
:root {
    --bg:           #0F172A;
    --surface:      #111827;
    --surface-2:    #1A2332;
    --glass:        rgba(255,255,255,0.04);
    --glass-hi:     rgba(255,255,255,0.07);
    --border:       #23314D;
    --border-hi:    #2E3A56;
    --teal:         #14B8A6;
    --teal-light:   #5EEAD4;
    --teal-dark:    #0D9488;
    --teal-dim:     rgba(20,184,166,0.15);
    --teal-glow:    rgba(20,184,166,0.25);
    --emerald:      #14B8A6;
    --emerald-dim:  rgba(20,184,166,0.13);
    --blue:         #3B82F6;
    --blue-dim:     rgba(59,130,246,0.13);
    --red:          #ff6b6b;
    --red-dim:      rgba(255,107,107,0.13);
    --purple:       #9b59b6;
    --accent:       var(--teal);
    --success:      var(--teal);
    --warning:      var(--teal);
    --danger:       var(--red);
    --text:         #F8FAFC;
    --text-muted:   #94A3B8;
    --text-xs:      #6B7280;
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

@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{
    font-family:'Poppins',sans-serif;
    background:radial-gradient(ellipse 80% 40% at 50% 0%,rgba(20,184,166,.045) 0%,transparent 100%),var(--bg);
    color:var(--text);
    min-height:100vh;
}

/* ── Topbar ── */
.topbar{
    position:sticky;top:0;z-index:100;
    background:rgba(10,10,10,.95);
    border-bottom:1px solid var(--border);
    backdrop-filter:blur(12px);
    display:flex;align-items:center;justify-content:space-between;
    padding:0 28px;height:62px;
    animation:fadeIn .35s ease both;
}
[data-theme="light"] .topbar{background:rgba(255,255,255,.92)}
.topbar-left{display:flex;align-items:center;gap:16px}
.page-title{font-size:15px;font-weight:700;color:var(--text)}
.page-title span{color:var(--teal)}
.theme-toggle{
    display:inline-flex;align-items:center;gap:7px;
    padding:8px 15px;border-radius:var(--r-sm);
    font-size:13px;font-weight:500;
    background:var(--glass);border:1px solid var(--border);
    color:var(--text-muted);cursor:pointer;
    font-family:'Poppins',sans-serif;
    transition:background .2s,border-color .2s,color .2s;
}
.theme-toggle:hover{background:var(--glass-hi);border-color:var(--border-hi);color:var(--text)}
.topbar-right{display:flex;align-items:center;gap:10px}
.user-chip{
    display:flex;align-items:center;gap:8px;
    padding:5px 12px 5px 5px;
    background:var(--glass);border:1px solid var(--border);
    border-radius:20px;
}
.user-avatar{
    width:30px;height:30px;border-radius:50%;
    background:linear-gradient(135deg,var(--teal),var(--teal-dark));
    display:flex;align-items:center;justify-content:center;
    font-size:12px;color:#fff;font-weight:700;
}
.user-name{font-size:12.5px;font-weight:600;color:var(--text)}
.user-role{font-size:10px;color:var(--teal);font-weight:500;text-transform:capitalize}

/* ── Layout ── */
.page-wrap{max-width:820px;margin:0 auto;padding:28px 20px 60px}

/* ── Banners ── */
.banner{
    display:flex;align-items:center;gap:14px;
    padding:14px 20px;border-radius:var(--r-sm);
    margin-bottom:20px;
    animation:fadeUp .4s var(--spring) both;
}
.banner.warning{background:var(--teal-dim);border:1px solid var(--teal-glow);color:var(--teal-light)}
.banner i{font-size:18px;flex-shrink:0}
.banner strong{display:block;font-size:14px;color:var(--text);margin-bottom:2px}
.banner p{font-size:13px;color:var(--text-muted)}

/* ── Panel (card) ── */
.panel{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:var(--r);
    overflow:hidden;
    margin-bottom:18px;
    animation:fadeUp .45s cubic-bezier(.16,1,.3,1) both;
    transition:border-color .2s,box-shadow .2s;
}
.panel:hover{border-color:var(--border-hi);box-shadow:var(--shadow)}
.panel:nth-of-type(1){animation-delay:.14s}
.panel:nth-of-type(2){animation-delay:.22s}
.panel:nth-of-type(3){animation-delay:.30s}
.panel-head{
    display:flex;align-items:center;gap:14px;
    padding:18px 22px;
    border-bottom:1px solid var(--border);
}
.panel-icon{
    width:38px;height:38px;border-radius:var(--r-sm);
    background:var(--teal-dim);
    border:1px solid var(--teal-glow);
    display:flex;align-items:center;justify-content:center;
    color:var(--teal);font-size:16px;flex-shrink:0;
}
.panel-title{font-size:15px;font-weight:700;color:var(--text)}
.panel-subtitle{font-size:12px;color:var(--text-muted);margin-top:2px}
.panel-body{padding:20px 22px}
.panel-divider{height:1px;background:var(--border);margin:0}

/* ── Form fields ── */
.field-row{display:grid;grid-template-columns:1fr;gap:14px;margin-bottom:14px}
.field-row.two{grid-template-columns:1fr 1fr}
.field-group{position:relative}
.fl-label{
    display:block;font-size:11px;font-weight:700;
    color:var(--text-muted);text-transform:uppercase;
    letter-spacing:.06em;margin-bottom:6px;
}
.fl-input{
    width:100%;height:46px;
    padding:0 42px 0 14px;
    border-radius:var(--r-sm);
    border:1px solid var(--border);
    background:var(--glass);
    color:var(--text);
    font-size:14px;font-family:'Poppins',sans-serif;
    outline:none;
    transition:border-color .2s,box-shadow .2s,background .2s;
}
.fl-input:focus{
    border-color:var(--teal);
    background:var(--glass-hi);
    box-shadow:0 0 0 3px var(--teal-dim);
}
.fl-input.select-input{padding-right:14px;cursor:pointer}
.fl-input.select-input option{background:var(--surface-2)}
.input-wrap{position:relative}
.fl-toggle{
    position:absolute;right:12px;top:50%;transform:translateY(-50%);
    background:none;border:none;
    color:var(--text-muted);font-size:14px;
    cursor:pointer;padding:4px;transition:color .2s;
}
.fl-toggle:hover{color:var(--text)}
.fl-hint{font-size:12px;color:var(--text-muted);margin-top:5px}

/* ── Strength meter ── */
.strength-wrap{margin-top:8px}
.s-bars{display:flex;gap:4px;margin-bottom:5px}
.s-bar{flex:1;height:4px;border-radius:4px;background:var(--glass-hi);transition:background .3s}
.s-text{font-size:11px;font-weight:600;color:var(--text-muted);transition:color .3s}
.s-reqs{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
.req{
    display:flex;align-items:center;gap:5px;
    font-size:10.5px;color:var(--text-muted);
    padding:3px 8px;border-radius:20px;
    border:1px solid var(--border);
    background:var(--glass);
    transition:all .25s;
}
.req.met{color:var(--emerald);border-color:var(--emerald-dim);background:var(--emerald-dim)}
.req i{font-size:9px}
.match-hint{font-size:12px;margin-top:5px;min-height:18px}

/* ── Primary button ── */
.btn-primary{
    display:inline-flex;align-items:center;gap:8px;
    padding:0 22px;height:44px;border:none;border-radius:var(--r-sm);
    background:linear-gradient(135deg,var(--teal),var(--teal-dark));
    color:#fff;font-family:'Poppins',sans-serif;
    font-size:14px;font-weight:600;cursor:pointer;
    box-shadow:0 3px 14px rgba(20,184,166,.3);
    transition:transform .15s,box-shadow .15s;
}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 6px 22px rgba(20,184,166,.4)}
.btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none}

/* ── Badges ── */
.badge{
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 12px;border-radius:50px;
    font-size:11px;font-weight:700;
}
.badge-ok{background:var(--emerald-dim);color:var(--emerald)}
.badge-missing{background:var(--teal-dim);color:var(--teal-light)}

/* ── Profile summary ── */
.profile-summary{
    display:flex;align-items:center;gap:20px;
    padding:22px 24px;
}
.profile-avatar{
    width:66px;height:66px;border-radius:var(--r);flex-shrink:0;
    background:linear-gradient(135deg,var(--teal),var(--teal-dark));
    display:flex;align-items:center;justify-content:center;
    font-size:26px;color:#fff;font-weight:800;
    box-shadow:0 4px 20px rgba(20,184,166,.35);
}
.profile-info{flex:1}
.profile-name{font-size:20px;font-weight:800;color:var(--text);margin-bottom:4px}
.profile-badges{display:flex;gap:7px;flex-wrap:wrap}
.profile-stats{
    display:grid;grid-template-columns:repeat(3,1fr);
    gap:1px;background:var(--border);
    border-top:1px solid var(--border);
}
.pstat{padding:14px 20px;background:var(--surface);display:flex;flex-direction:column;gap:3px}
.pstat-val{font-size:16px;font-weight:700;color:var(--teal)}
.pstat-lbl{font-size:11px;color:var(--text-muted)}

/* ── Toast ── */
.toast{
    position:fixed;bottom:28px;right:28px;z-index:9999;
    display:flex;align-items:center;gap:10px;
    padding:13px 18px;border-radius:var(--r-sm);
    font-size:13px;font-weight:500;
    box-shadow:var(--shadow-lg);
    animation:toastIn .4s ease both;
    min-width:240px;max-width:380px;
}
.toast.success{background:linear-gradient(135deg,#0a2a1a,#0d331f);border:1px solid rgba(20,184,166,.25);color:#9bdfb0}
.toast.error{background:linear-gradient(135deg,#2a0a0a,#330d0d);border:1px solid rgba(255,107,107,.25);color:#ffb3b3}
.toast i{font-size:16px;flex-shrink:0}
@keyframes toastIn{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
@keyframes toastOut{from{opacity:1;transform:translateY(0)}to{opacity:0;transform:translateY(12px)}}

@media(max-width:600px){
    .field-row.two{grid-template-columns:1fr}
    .profile-stats{grid-template-columns:1fr 1fr}
    .page-wrap{padding:20px 14px 50px}
}
</style>
