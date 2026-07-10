<style type="text/tailwindcss">
    /* ============================================================
       Tailwind stylesheet — replaces assets/css/menu.css + the old
       inline CSS. Every original class name is preserved so all
       JavaScript hooks (renderCartPanel, cpTogglePayment, toasts,
       scrollspy, etc.) keep working unchanged. Component styles are
       authored with Tailwind @apply; theme colors stay as CSS
       variables (light default + [data-theme="dark"]).
       ============================================================ */

    /* ── DESIGN TOKENS (light default) ── */
    :root {
      --orange: #14b8a6; --orange-dark: #0d9488; --orange-light: #5eead4; --orange-glow: rgba(20,184,166,0.18);
      --bg: #f8fafc; --bg-card: #ffffff; --bg-card-hover: #f8fafc; --bg-input: #f1f5f9; --bg-header: rgba(255,255,255,0.96);
      --border: #e2e8f0; --border-hover: #cbd5e1;
      --text: #1e293b; --text-sec: #64748b; --text-muted: #94a3b8; --text-inv: #ffffff;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.06); --shadow-md: 0 4px 16px rgba(0,0,0,0.08); --shadow-lg: 0 8px 32px rgba(0,0,0,0.1); --shadow-accent: 0 4px 20px rgba(20,184,166,0.25);
      --card-primary: #14b8a6; --card-primary-dark: #0d9488; --card-primary-glow: rgba(20,184,166,0.35);
      --card-bg: #ffffff; --card-bg-card: #ffffff; --card-border: #e2e8f0;
      --card-text: #1e293b; --card-text-sec: #64748b; --card-text-muted: #94a3b8;
      --card-badge: #14b8a6; --card-badge-glow: rgba(20,184,166,0.35); --card-price: #14b8a6;
      --card-shadow: 0 4px 16px rgba(0,0,0,0.08); --card-shadow-hover: 0 12px 32px rgba(0,0,0,0.14); --card-radius: 18px;
    }
    [data-theme="dark"] {
      --bg: #0f172a; --bg-card: #1e293b; --bg-card-hover: #243145; --bg-input: #1e293b; --bg-header: rgba(15,23,42,0.96);
      --border: #334155; --border-hover: #475569;
      --text: #f1f5f9; --text-sec: #94a3b8; --text-muted: #64748b; --text-inv: #fff;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.3); --shadow-md: 0 4px 16px rgba(0,0,0,0.4); --shadow-lg: 0 8px 32px rgba(0,0,0,0.5);
      --card-bg: #0f172a; --card-bg-card: #1e293b; --card-border: #334155;
      --card-text: #f1f5f9; --card-text-sec: #94a3b8; --card-text-muted: #64748b;
      --card-shadow: 0 8px 24px rgba(0,0,0,0.5); --card-shadow-hover: 0 18px 40px rgba(0,0,0,0.6);
      --orange: #14b8a6; --orange-dark: #0d9488; --orange-light: #5eead4; --orange-glow: rgba(20,184,166,0.18);
      --shadow-accent: 0 4px 20px rgba(20,184,166,0.25);
      --card-primary: #14b8a6; --card-primary-dark: #0d9488; --card-primary-glow: rgba(20,184,166,0.35);
      --card-badge: #14b8a6; --card-badge-glow: rgba(20,184,166,0.35); --card-price: #14b8a6;
    }

    /* ── BASE (POS full-height layout) ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    html, body { height: 100%; overflow: hidden; }
    body {
      @apply font-sans flex flex-col min-h-0 p-0;
      background: var(--bg, #f4efe9);
      color: var(--text, #1a1410);
    }

  @layer components {
    /* ── HEADER ── */
    .menu-header { @apply relative z-[1000] flex items-center justify-between gap-3 shrink-0 px-5 py-3 border-b; background: var(--bg-header, rgba(255,252,248,.97)); border-color: var(--border,#e0d4c4); backdrop-filter: blur(16px); box-shadow: 0 2px 10px rgba(90,60,20,.07); }
    .header-left   { @apply flex items-center gap-2 shrink-0; }
    .header-center { @apply flex-1; }
    .header-right  { @apply flex items-center gap-2.5 shrink-0; }
    .brand { @apply flex items-center gap-2; }
    .brand img { @apply w-9 h-9 rounded-full object-cover shrink-0; border: 2px solid var(--border,#e0d4c4); }
    .brand-name { @apply text-[15px] font-bold whitespace-nowrap; color: var(--text,#1a1410); }

    /* ── SEARCH / SORT / NAV ── */
    .search-form  { @apply flex items-center gap-2 w-full; }
    .search-inner { @apply flex items-center gap-2 flex-1 rounded-full px-3.5 py-[7px] border; background: var(--bg-input,#ede8e0); border-color: var(--border,#e0d4c4); }
    .search-inner:focus-within { border-color: var(--orange); box-shadow: var(--shadow-accent); }
    .search-inner i { @apply text-sm shrink-0; color: var(--text-muted,#9a8070); }
    .search-inner input { @apply flex-1 border-0 outline-none bg-transparent text-[13px]; color: var(--text,#1a1410); }
    .search-inner input::placeholder { color: var(--text-muted,#9a8070); }
    .search-clear { @apply flex items-center justify-center text-sm shrink-0 no-underline; color: var(--text-muted,#9a8070); }
    .search-clear:hover { color: var(--orange); }
    .sort-select { @apply rounded-full px-3 py-[7px] border text-xs outline-none cursor-pointer shrink-0; background: var(--bg-input,#ede8e0); border-color: var(--border,#e0d4c4); color: var(--text-sec,#5a4a3a); }
    .sort-select:hover, .sort-select:focus { border-color: var(--orange); color: var(--text,#1a1410); }
    .btn-nav { @apply inline-flex items-center gap-1.5 px-3.5 py-[7px] rounded-full border no-underline text-[13px] font-medium whitespace-nowrap transition-all cursor-pointer; background: var(--bg-input,#ede8e0); border-color: var(--border,#e0d4c4); color: var(--text-sec,#5a4a3a); }
    .btn-nav:hover { @apply text-white; background: var(--orange); border-color: var(--orange); }
    .btn-orders { @apply relative; }
    .badge { @apply inline-flex items-center justify-center w-[18px] h-[18px] rounded-full text-white text-[10px] font-bold; background: var(--orange); }
    .btn-nav:hover .badge { background: #fff; color: var(--orange); }
    .btn-theme { @apply flex items-center justify-center w-9 h-9 rounded-full border cursor-pointer shrink-0; background: var(--bg-input,#ede8e0); border-color: var(--border,#e0d4c4); color: var(--text,#1a1410); }
    .btn-theme:hover { border-color: var(--orange); color: var(--orange); }

    /* ── POS SPLIT LAYOUT ── */
    .pos-layout { @apply flex-1 flex overflow-hidden min-h-0; }
    .menu-panel { @apply flex-1 flex flex-col overflow-hidden min-w-0; background: var(--card-bg); }
    .cat-bar { @apply flex items-center gap-2 px-3 pt-2 pb-2.5 border-b shrink-0 sticky top-0 z-50; background: var(--bg-header, rgba(255,252,248,.97)); border-color: var(--border,#e0d4c4); }
    .cat-search { @apply flex items-center gap-1.5 shrink-0; }
    .cat-search .search-inner { @apply flex items-center gap-1.5 rounded-[6px] px-3 py-[7px] border text-xs; background: var(--bg-input,#ede8e0); border-color: var(--border,#e0d4c4); width:200px; }
    .cat-search .search-inner input { @apply flex-1 border-0 outline-none bg-transparent text-[12px] min-w-0; color: var(--text,#1a1410); }
    .cat-search .sort-select { @apply rounded-[6px] px-3 py-[7px] border text-xs outline-none cursor-pointer shrink-0; background: var(--bg-input,#ede8e0); border-color: var(--border,#e0d4c4); color: var(--text-sec,#5a4a3a); min-width:90px; }
    .cp-view-toggle { @apply flex items-center justify-center w-[34px] h-[34px] rounded-[6px] border cursor-pointer shrink-0; background: var(--bg-input,#ede8e0); border-color: var(--border,#e0d4c4); color: var(--text-sec,#5a4a3a); font-size:13px; transition:all .2s; }
    .cp-view-toggle:hover { border-color: var(--orange); color: var(--orange); }
    .menu-panel .cat-nav { @apply flex items-center gap-2 overflow-x-auto shrink-0; background: transparent; }
    .menu-panel .menu-scroll { @apply flex-1 overflow-y-auto pb-10; }
    .menu-scroll { scrollbar-width: thin; scrollbar-color: rgba(20,184,166,.35) transparent; }
    .menu-main { @apply px-4 pt-5; }
    .add-order-banner { @apply flex items-center gap-2 px-5 py-2.5 text-white text-[13px] font-semibold shrink-0; background: #9b59b6; }

    /* ── CATEGORY NAV / PILLS ── */
    .cat-nav { -ms-overflow-style: none; scrollbar-width: none; }
    .cat-pill { @apply inline-flex flex-col items-center gap-0.5 px-2 pt-2 pb-1.5 min-w-[60px] rounded-[10px] border-0 no-underline cursor-pointer shrink-0 transition-all; background: transparent; color: var(--text-sec,#5a4a3a); }
    .cat-pill:hover { @apply -translate-y-0.5; color: var(--orange); background: transparent; }
    .cat-pill.active { @apply translate-y-0 text-white; background: linear-gradient(145deg,#0d9488,#14b8a6); box-shadow: inset 0 3px 7px rgba(0,0,0,.3), 0 4px 20px rgba(20,184,166,.4); }
    .cat-pill-img { @apply flex items-center justify-center w-8 h-8 rounded-[8px] overflow-hidden shrink-0; background: var(--bg-input,#f1f5f9); }
    .cat-pill-img img { @apply w-full h-full object-cover block; }
    .cat-pill-img i { @apply text-sm; color: var(--text-muted,#94a3b8); }
    .cat-pill.active .cat-pill-img { background: rgba(255,255,255,.18); }
    .cat-pill.active .cat-pill-img i { color: rgba(255,255,255,.9); }
    .cat-pill-label { @apply text-[10px] font-semibold text-center whitespace-nowrap max-w-[60px] overflow-hidden text-ellipsis; line-height: 1.1; }
    .cat-pill .pill-count { @apply inline-flex items-center justify-center px-[4px] rounded-[12px] text-[8px] font-bold min-w-[14px] h-[12px]; background: var(--bg-input,#f1f5f9); line-height: 1; color: var(--text-sec,#5a4a3a); }
    .cat-pill.active .pill-count { background: rgba(255,255,255,.2); color: #fff; }

    /* ── TOP SELLERS / SECTION HEADERS ── */
    .top-sellers { @apply px-6 pt-6 pb-1 mb-2; }
    .section-header { @apply flex items-center justify-between mb-4; }
    .section-header h2 { @apply text-xl font-bold flex items-center gap-2; color: var(--card-text, var(--text)); }
    .sellers-strip { @apply flex gap-5 overflow-x-auto pb-4; -ms-overflow-style: none; scrollbar-width: none; }
    .seller-card { @apply shrink-0 w-[200px] rounded-[18px] border cursor-pointer transition-all p-2.5; background: var(--card-bg-card, var(--bg-card)); border-color: var(--card-border, var(--border)); box-shadow: var(--card-shadow, var(--shadow-sm)); }
    .seller-card:hover { @apply -translate-y-1.5; box-shadow: var(--card-shadow-hover, var(--shadow-md)); border-color: transparent; }
    .seller-card .card-img { @apply rounded-[10px] overflow-hidden relative; aspect-ratio: 1/1; background: var(--card-text-muted,#eee); }
    .seller-card .card-info { @apply pt-3.5; }
    .seller-rank { @apply text-xs font-semibold mb-2.5; color: var(--card-primary, var(--orange)); }

    /* ── CATEGORY SECTION / GRID ── */
    .cat-section { @apply mb-8; scroll-margin-top: 90px; }
    .cat-header { @apply flex items-center gap-3 mb-3 pb-2; border-bottom: 2px solid var(--card-border, var(--border)); }
    .cat-icon { @apply flex items-center justify-center w-10 h-10 rounded-full text-white text-lg shrink-0; background: var(--card-primary, var(--orange)); }
    .cat-title-text h2 { @apply text-xl font-bold leading-tight; color: var(--card-text, var(--text)); }
    .cat-title-text span { @apply text-xs; color: var(--card-text-muted, var(--text-muted)); }
    .product-grid { @apply grid gap-4; grid-template-columns: repeat(4, 1fr); }
    .product-grid.list-view { @apply grid-cols-1 gap-3; }
    .product-grid.list-view .product-card { @apply flex-row items-center p-2.5 rounded-[12px]; }
    .product-grid.list-view .product-card .card-img { @apply w-16 h-16 shrink-0 rounded-[8px]; aspect-ratio:auto; }
    .product-grid.list-view .product-card .card-info { @apply pt-0 pl-3 flex-row items-center flex-1; }
    .product-grid.list-view .product-card .card-name { @apply text-sm mb-0; }
    .product-grid.list-view .product-card .card-size-line { @apply mb-0 text-[10px]; }
    .product-grid.list-view .product-card .card-bottom { @apply flex-row items-center gap-3 mt-0 ml-auto shrink-0; }
    .product-grid.list-view .product-card .card-price-row { @apply items-center; }
    .product-grid.list-view .product-card .card-price { @apply text-sm; }
    .product-grid.list-view .product-card .btn-add-full { @apply w-auto px-4 py-2 text-xs rounded-lg; white-space:nowrap; }
    .product-grid.list-view .product-badge { @apply top-1 left-1 px-2 py-0.5 text-[9px]; }
    .product-grid.list-view .badge-bestseller { @apply top-1 right-1 px-2 py-0.5 text-[9px]; }
    .product-grid.list-view .seller-rank { display:none; }

    /* ── PRODUCT CARD ── */
    .product-card { @apply rounded-[18px] overflow-hidden cursor-pointer border relative p-2 transition-all flex flex-col; background: var(--card-bg-card, var(--bg-card)); border-color: var(--card-border, var(--border)); box-shadow: var(--card-shadow, 0 8px 24px rgba(0,0,0,.08)); }
    .product-card:hover { @apply -translate-y-1.5; box-shadow: var(--card-shadow-hover, 0 18px 40px rgba(0,0,0,.12)); border-color: transparent; }
    .card-img { @apply relative overflow-hidden rounded-[10px]; aspect-ratio: 1/1; background: var(--card-text-muted,#eee); }
    .card-img img { @apply w-full h-full object-cover block; transition: transform .35s ease; }
    .product-card:hover .card-img img { @apply scale-105; }
    .product-badge { @apply absolute top-3 left-3 z-[2] inline-flex items-center px-3.5 py-1.5 rounded-full text-white text-xs font-bold pointer-events-none; background: var(--card-badge,#14b8a6); box-shadow: 0 4px 10px var(--card-badge-glow, rgba(20,184,166,.35)); }
    .product-badge.badge-discount { @apply px-3 py-1 rounded-lg text-[11px]; background: #ef4444; box-shadow: 0 4px 10px rgba(239,68,68,.35); }
    .badge-bestseller { @apply absolute top-3 right-3 z-[2] inline-flex items-center px-3.5 py-1.5 rounded-full text-white text-[11px] font-semibold pointer-events-none; background: var(--card-primary,#14b8a6); box-shadow: 0 4px 10px var(--card-primary-glow, rgba(20,184,166,.35)); }
    .card-info { @apply pt-2.5 flex flex-col flex-1; }
    .card-name { @apply text-base font-bold mb-1 leading-snug; color: var(--card-text, var(--text)); }
    .card-size-line { @apply text-xs mb-3; color: var(--card-text-muted, var(--text-muted)); }
    .card-bottom { @apply flex flex-col gap-2.5 mt-auto; }
    .card-price-row { @apply flex items-baseline gap-2; }
    .card-price { @apply text-lg font-bold leading-none; color: var(--card-text, var(--text)); }
    .card-price.discounted { color: #ef4444; }
    .card-price-old { @apply text-xs font-medium line-through leading-none; color: var(--card-text-muted, var(--text-muted)); }
    .btn-add-full { @apply w-full py-2.5 rounded-xl text-white text-sm font-bold cursor-pointer flex items-center justify-center gap-1.5 transition-all border-0; background: var(--card-primary,#14b8a6); box-shadow: 0 4px 12px var(--card-primary-glow, rgba(20,184,166,.25)); }
    .btn-add-full:hover { @apply -translate-y-px; background: var(--card-primary-dark,#0d9488); }
    .btn-add-full:active { @apply translate-y-0 scale-[.98]; }
    .product-card.disabled { @apply opacity-50 cursor-not-allowed; filter: grayscale(.6); }
    .product-card.disabled:hover { @apply translate-y-0; box-shadow: var(--card-shadow, 0 8px 24px rgba(0,0,0,.08)); border-color: var(--card-border, var(--border)); }
    .out-of-stock { @apply absolute inset-0 flex items-center justify-center z-[3]; background: rgba(0,0,0,.55); }
    .out-of-stock span { @apply text-white px-4 py-1.5 rounded-full text-xs font-bold; background: #dc3545; }

    /* ── EMPTY STATE ── */
    .empty-state { @apply text-center py-20 px-5; color: var(--card-text-muted, var(--text-muted)); }
    .empty-state i { @apply text-[56px] mb-4 block; color: var(--card-primary, var(--orange)); }
    .empty-state h3 { @apply text-[22px] font-bold mb-2; color: var(--card-text, var(--text)); }
    .empty-state p { @apply text-sm mb-5; color: var(--card-text-muted, var(--text-muted)); }
    .btn-clear-search { @apply inline-flex items-center gap-1.5 px-[22px] py-2.5 rounded-full text-white no-underline font-semibold text-sm transition-all; background: var(--card-primary, var(--orange)); }
    .btn-clear-search:hover { background: var(--card-primary-dark, var(--orange-dark)); }

    /* ── CART PANEL ── */
    .cart-panel { @apply w-[420px] min-w-[360px] shrink-0 h-full border-l flex flex-col overflow-hidden; border-color: var(--border,#e0d4c4); background: var(--bg-card,#fff); box-shadow: -4px 0 24px rgba(0,0,0,.04); }
    .cp-header { @apply flex items-center justify-between px-[18px] py-3.5 border-b shrink-0; border-color: var(--border,#e0d4c4); background: var(--bg-card,#fff); }
    .cp-header-left { @apply flex items-center gap-2.5; }
    .cp-hdr-icon { @apply text-lg; color: var(--orange); }
    .cp-hdr-title { @apply text-base font-bold; color: var(--text,#1a1410); }
    .cp-hdr-count { @apply rounded-[20px] px-2.5 py-0.5 text-xs font-semibold; background: #f0ebe4; color: var(--text-sec,#5a4a3a); }
    .cp-clear-btn { @apply bg-transparent border-0 text-xs font-medium cursor-pointer flex items-center gap-1 px-2 py-1 rounded-lg transition-all; color: #b0a090; }
    .cp-clear-btn:hover { color: #e74c3c; background: #f5f0ea; }
    .cp-body { @apply flex-1 flex flex-col overflow-hidden min-h-0; }
    #cpItems { @apply flex-1 overflow-y-auto overflow-x-hidden py-2; scrollbar-width: thin; scrollbar-color: #ddd transparent; }
    .cp-empty { @apply flex flex-col items-center justify-center h-full py-10 px-5 text-center; }
    .cp-empty i { @apply text-[42px] mb-3; color: var(--orange); opacity: .25; }
    .cp-empty p { @apply text-[15px] font-semibold; color: var(--text-sec,#5a4a3a); }
    .cp-empty small { @apply text-xs; color: var(--text-muted,#9a8070); }
    .cp-item { @apply flex gap-3 px-[18px] py-2.5 relative transition-colors; }
    .cp-item:hover { background: rgba(20,184,166,.04); }
    .cp-item + .cp-item { border-top: 1px solid var(--border,#e0d4c4); }
    .cp-item-img-wrap { @apply relative shrink-0; }
    .cp-item-img { @apply w-16 h-16 rounded-xl object-cover block; background: #f5f0ea; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
    .cp-item-ribbon { @apply absolute -top-1.5 -left-1.5 z-[1] inline-flex items-center justify-center rounded-md px-1.5 py-0.5 text-white text-[9px] font-bold; background: #ef4444; box-shadow: 0 2px 6px rgba(239,68,68,.4); }
    .cp-item-body { @apply flex-1 min-w-0 flex flex-col gap-1; }
    .cp-item-top { @apply flex items-start justify-between gap-2; }
    .cp-item-name { font-size: 12px; font-weight: 600; line-height: 1.3; flex: 1; min-width: 0; color: var(--text,#1a1410); }
    .cp-item-remove { @apply w-6 h-6 border-0 bg-transparent cursor-pointer text-[11px] rounded-md flex items-center justify-center shrink-0 transition-all; color: #c0b0a0; }
    .cp-item-remove:hover { color: #e74c3c; background: rgba(231,76,60,.1); }
    .cp-item-meta { @apply flex flex-wrap gap-1 mt-0.5; }
    .cp-item-tag { @apply inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-medium; background: var(--bg-input,#f1f5f9); color: var(--text-sec,#5a4a3a); }
    .cp-item-bottom { @apply flex items-center justify-between gap-2 mt-1; }
    .cp-item-price-row { @apply flex items-baseline gap-1.5; }
    .cp-item-price { font-size: 13px; font-weight: 700; color: var(--orange); }
    .cp-item-price.discounted { color: #ef4444; }
    .cp-item-price-old { font-size: 11px; font-weight: 500; text-decoration: line-through; color: var(--text-muted,#9a8070); }
    .cp-qty { display: flex; align-items: center; border-radius: 10px; overflow: hidden; border: 1px solid #e8ddd0; background: #f5f0ea; }
    .cp-qty button { width: 28px; height: 28px; border: 0; background: transparent; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; color: var(--orange); }
    .cp-qty button:hover { background: rgba(20,184,166,.12); }
    .cp-qty input { width: 28px; text-align: center; font-size: 12px; font-weight: 700; background: transparent; border: 0; outline: none; padding: 0; color: var(--text,#1a1410); -moz-appearance: textfield; }
    .cp-qty input::-webkit-inner-spin-button, .cp-qty input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .cp-summary { @apply shrink-0 px-[18px] pb-3 border-t; border-color: var(--border,#e0d4c4); background: var(--bg-card,#fff); }
    .cp-sum-row { @apply flex justify-between items-center py-[5px] text-[13px]; color: var(--text-sec,#5a4a3a); }
    .cp-sum-row.discount { color: #e74c3c; }
    .cp-sum-divider { @apply h-px my-1.5; background: var(--border,#e0d4c4); }
    .cp-sum-total { @apply flex justify-between items-center py-1.5; }
    .cp-sum-total .lbl { @apply text-[15px] font-bold; color: var(--text,#1a1410); }
    .cp-sum-total .amt { @apply text-2xl font-extrabold; color: var(--orange); }

    .cp-options { @apply px-[18px] pb-2 border-t shrink-0; border-color: var(--border,#e0d4c4); background: var(--bg-card,#fff); }
    .cp-opt-row { @apply flex gap-2 mt-2; }
    .cp-opt-btn { @apply flex-1 flex items-center justify-center gap-1.5 p-2 rounded-[10px] text-xs font-medium cursor-pointer transition-all; border: 1.5px solid var(--border,#e0d4c4); background: var(--bg,#f4efe9); color: var(--text-sec,#5a4a3a); }
    .cp-opt-btn.active { @apply font-semibold; border-color: var(--orange); background: rgba(20,184,166,.1); color: var(--orange); }
    .cp-opt-field { @apply mt-2; }
    .cp-opt-field label { @apply flex items-center gap-1.5 text-[11px] font-semibold mb-1; color: var(--text-sec,#5a4a3a); }
    .cp-opt-field input { @apply w-full px-2.5 py-2 rounded-[14px] text-[13px] outline-none; border: 1.5px solid var(--border,#e0d4c4); background: var(--bg,#f4efe9); color: var(--text,#1a1410); }
    .cp-opt-field input:focus { border-color: var(--orange); }
    .cp-loyalty { @apply flex items-center justify-between mt-2 py-1.5; }
    .cp-loyalty-info { @apply text-[11px] flex items-center gap-1.5; color: var(--text-sec,#5a4a3a); }
    .cp-loyalty-info .linked { @apply font-semibold; color: var(--orange); }
    .cp-loyalty-btn { @apply text-white border-0 rounded-lg px-3 py-1.5 text-[11px] font-semibold cursor-pointer; background: var(--orange); }
    .cp-loyalty-btn:hover { background: #0d9488; }
    .cp-footer { @apply shrink-0 px-[18px] pt-3 pb-4 border-t; background: var(--bg-card,#fff); border-color: var(--border,#e0d4c4); box-shadow: 0 -4px 20px rgba(0,0,0,.04); }
    .cp-pay-methods { @apply flex gap-1.5; }
    .cp-pay-method { @apply flex-1 flex items-center justify-center gap-1 px-1 py-2.5 rounded-[14px] cursor-pointer select-none transition-all; border: 1.5px solid var(--border,#e0d4c4); background: var(--bg,#f4efe9); color: var(--text-sec,#5a4a3a); }
    .cp-pay-method:hover { border-color: #b0a090; }
    .cp-pay-method.selected { @apply font-semibold; border-color: var(--orange); background: rgba(20,184,166,.1); color: var(--orange); box-shadow: 0 0 0 2px rgba(20,184,166,.15); }
    .cp-pay-method input { @apply hidden; }
    .cp-pm-ico { @apply flex items-center justify-center; font-size: 14px; width: 20px; }
    .cp-pm-lbl { font-size: 11px; }
    .cp-pm-check { font-size: 13px; opacity: 0; transition: opacity .2s; color: var(--orange); }
    .cp-pay-method.selected .cp-pm-check { opacity: 1; }
    .cp-pm-items { @apply max-h-[180px] overflow-y-auto mb-3 -mx-1 px-1; scrollbar-width: thin; scrollbar-color: #ddd transparent; }
    .cp-pm-item { @apply flex items-center justify-between gap-2 px-3 py-2 rounded-[8px] text-[13px]; }
    .cp-pm-item:nth-child(odd) { background: rgba(0,0,0,.02); }
    .cp-pm-item-name { @apply flex-1 min-w-0 overflow-hidden text-ellipsis whitespace-nowrap; color: var(--text,#1a1410); }
    .cp-pm-item-meta { @apply text-[11px]; color: var(--text-muted,#9a8070); }
    .cp-pm-item-right { @apply flex items-center gap-3 shrink-0; }
    .cp-pm-item-qty { @apply font-semibold; color: var(--text-sec,#5a4a3a); }
    .cp-pm-item-price { @apply font-bold min-w-[52px] text-right; color: var(--orange); }
    .cp-split-inputs { @apply hidden mb-1.5; }
    .cp-split-inputs.active { @apply block; }
    .cp-split-row { @apply flex items-center gap-1.5 mb-1; }
    .cp-split-row label { @apply text-[11px] min-w-[48px]; color: var(--text-sec,#5a4a3a); }
    .cp-split-row input { @apply flex-1 px-2 py-[5px] rounded-md text-xs outline-none; border: 1px solid var(--border,#e0d4c4); background: var(--bg,#f4efe9); color: var(--text,#1a1410); }
    .cp-split-row input:focus { border-color: var(--orange); }
    .cp-change-calc { @apply hidden mt-1.5 p-2 rounded-lg; background: rgba(85,224,135,.04); border: 1px solid rgba(85,224,135,.15); }
    .cp-change-calc.visible { @apply block; }
    .cp-change-calc label { @apply text-[11px] font-semibold mb-1 block; color: var(--text-sec,#5a4a3a); }
    .cp-change-calc input { @apply w-full px-2.5 py-1.5 rounded-lg text-sm font-bold outline-none text-right; border: 1px solid rgba(85,224,135,.3); background: var(--bg-card,#fff); color: var(--text,#1a1410); }
    .cp-change-calc input:focus { border-color: #55e087; }
    .cp-change-row { @apply flex justify-between items-center mt-1.5 pt-1; border-top: 1px solid rgba(85,224,135,.1); }
    .cp-change-row .change-label { @apply text-[11px] font-semibold; color: var(--text-sec,#5a4a3a); }
    .cp-change-row .change-amount { @apply text-base font-extrabold; color: #55e087; }
    .cp-change-row .change-amount.not-enough { @apply text-xs; color: #e74c3c; }
    .cp-confirm-btn { @apply w-full p-3.5 border-0 rounded-xl text-white text-[15px] font-bold cursor-pointer flex items-center justify-center gap-2 transition-all; background: var(--orange); box-shadow: 0 4px 16px rgba(20,184,166,.3); letter-spacing: .01em; }
    .cp-confirm-btn:hover { @apply -translate-y-px; filter: brightness(1.08); box-shadow: 0 8px 28px rgba(20,184,166,.4); }
    .cp-confirm-btn:active { @apply translate-y-0; box-shadow: none; }
    .cp-confirm-btn:disabled { @apply opacity-50 cursor-not-allowed translate-y-0; box-shadow: none; }
    .cp-confirm-btn.paylater { background: linear-gradient(135deg,#8e44ad,#9b59b6); }
    .cp-shortcuts { @apply flex flex-wrap gap-x-3 gap-y-1 mt-2 text-[10px]; color: var(--text-muted,#9a8070); }
    .cp-shortcuts kbd { @apply rounded px-1.5 py-0.5 text-[9px] font-bold; background: var(--bg,#f4efe9); border: 1px solid var(--border,#e0d4c4); color: var(--text-sec,#5a4a3a); }
    .cp-add-order-note { @apply rounded-[10px] px-3 py-2.5 my-2 text-xs font-semibold; background: rgba(20,184,166,.08); border: 1px solid rgba(20,184,166,.3); color: var(--orange); }
    .cp-add-order-note small { @apply font-normal text-[11px] block mt-1; color: #888; }
    .cp-stand-pick { @apply flex items-center gap-1 mt-1.5; }
    .cp-stand-warn { @apply hidden mt-1 text-[11px] items-center gap-1; color: #f0ad4e; }
    #cpStandGrid { @apply hidden mt-1.5 p-2 rounded-[10px]; background: #f5f0ea; border: 1px solid var(--border,#e0d4c4); }

    /* ── PAYMENT MODAL ── */
    .cp-paymodal { @apply hidden fixed inset-0 z-[10000] items-center justify-center p-5; background: rgba(0,0,0,.5); backdrop-filter: blur(6px); }
    .cp-paymodal.active { @apply flex; }
    .cp-paymodal-card { @apply rounded-2xl w-full max-w-[420px] pt-[22px] px-[22px] pb-[18px] relative; background: var(--bg-card,#fff); border: 1px solid var(--border,#e0d4c4); box-shadow: 0 12px 48px rgba(90,60,20,.18); animation: cpPmIn .22s ease both; }
    .cp-paymodal-head { @apply flex items-center justify-between mb-3.5; }
    .cp-paymodal-head h3 { @apply text-base font-bold m-0; color: var(--text,#1a1410); }
    .cp-paymodal-close { @apply border-0 text-xl cursor-pointer leading-none; background: none; color: var(--text-muted,#9a8070); }
    .cp-pm-breakdown { @apply rounded-[10px] px-3.5 py-2.5 mb-3; background: var(--bg,#f4efe9); border: 1px solid var(--border,#e0d4c4); }
    .cp-pm-row { @apply flex justify-between text-[13px] py-0.5; color: var(--text-sec,#5a4a3a); font-variant-numeric: tabular-nums; }
    .cp-pm-hero { @apply flex flex-col px-1 mb-4; }
    .cp-pm-hero-label { @apply text-[10px] font-bold uppercase; letter-spacing: .12em; color: var(--text-muted,#9a8070); }
    .cp-pm-hero-amt { @apply text-[40px] font-extrabold; line-height: 1.04; color: var(--text,#1a1410); font-variant-numeric: tabular-nums; letter-spacing: -.02em; margin-top: 1px; }
    .cp-pm-hero-khr { @apply text-[13px] font-semibold mt-[3px]; color: var(--orange); font-variant-numeric: tabular-nums; }
    .cp-pm-confirm { @apply w-full mt-4 p-3.5 border-0 rounded-[13px] text-white text-[15px] font-bold cursor-pointer flex items-center justify-center gap-2 transition-all; background: linear-gradient(135deg,#14b8a6,#0d9488); box-shadow: 0 6px 18px rgba(20,184,166,.28); }
    .cp-pm-confirm:hover { filter: brightness(1.07); }
    .cp-pm-confirm:active { @apply scale-[.99]; }

    /* ── RECEIPT MODAL ── */
    .cp-receipt-modal { @apply hidden fixed inset-0 z-[10000] items-center justify-center p-5; background: rgba(0,0,0,.5); backdrop-filter: blur(6px); }
    .cp-receipt-modal.active { @apply flex; }
    .cp-receipt-card { @apply rounded-2xl w-full max-w-[400px] flex flex-col relative; max-height: 90vh; background: var(--bg-card,#fff); border: 1px solid var(--border,#e0d4c4); box-shadow: 0 12px 48px rgba(90,60,20,.18); animation: cpPmIn .22s ease both; }
    .cp-receipt-head { @apply flex items-center justify-between px-5 pt-4 pb-2 shrink-0; }
    .cp-receipt-head h3 { @apply text-base font-bold m-0; color: var(--text,#1a1410); }
    .cp-receipt-close { @apply border-0 text-xl cursor-pointer leading-none; background: none; color: var(--text-muted,#9a8070); }
    .cp-receipt-body { @apply flex-1 overflow-y-auto px-5 py-2; scrollbar-width: thin; }
    .cp-receipt-shop { @apply text-center mb-2; }
    .cp-receipt-name { @apply text-[16px] font-bold; color: var(--text,#1a1410); }
    .cp-receipt-info { @apply text-[11px]; color: var(--text-muted,#9a8070); }
    .cp-receipt-divider { @apply border-t border-dashed my-2; border-color: var(--border,#e0d4c4); }
    .cp-receipt-header { @apply flex flex-col gap-1; }
    .cp-receipt-field { @apply flex justify-between text-[12px] py-[1px]; color: var(--text-sec,#5a4a3a); }
    .cp-rf-label { color: var(--text-muted,#9a8070); }
    .cp-rf-value { @apply font-medium; color: var(--text,#1a1410); }
    .cp-rf-badge { @apply px-2 py-[1px] rounded text-[10px] font-bold; background: #dbeafe; color: #2563eb; }
    .cp-receipt-item { @apply flex justify-between items-start py-1.5 text-[12px]; }
    .cp-receipt-item-left { @apply flex-1 min-w-0; }
    .cp-receipt-item-name { @apply font-medium truncate; color: var(--text,#1a1410); }
    .cp-receipt-item-opts { @apply text-[10px]; color: var(--text-muted,#9a8070); margin-top: 1px; }
    .cp-receipt-item-right { @apply flex items-center gap-2 shrink-0 ml-2; }
    .cp-receipt-item-qty { color: var(--text-sec,#5a4a3a); }
    .cp-receipt-item-price { @apply font-bold min-w-[48px] text-right; color: var(--orange, #14b8a6); }
    .cp-receipt-totals { @apply flex flex-col gap-[2px]; }
    .cp-receipt-total-row { @apply flex justify-between text-[12px] py-[2px]; color: var(--text-sec,#5a4a3a); }
    .cp-receipt-total-row.total { @apply text-[15px] font-extrabold; color: var(--text,#1a1410); border-top: 1px solid var(--border,#e0d4c4); padding-top: 6px; margin-top: 2px; }
    .cp-receipt-total-row.discount { color: #c0392b; }
    .cp-receipt-total-row.tax { font-size: 11px; color: var(--text-muted,#9a8070); }
    .cp-receipt-payments { @apply mt-1; }
    .cp-receipt-pay-row { @apply flex justify-between text-[11px] py-[1px]; color: var(--text-muted,#9a8070); }
    .cp-receipt-actions { @apply flex gap-2 px-5 pb-4 pt-3 shrink-0; }
    .cp-receipt-btn { @apply flex-1 p-2.5 border-0 rounded-xl text-[13px] font-semibold cursor-pointer flex items-center justify-center gap-1.5 transition-all; }
    .cp-receipt-btn-new { background: var(--orange, #14b8a6); color: #fff; }
    .cp-receipt-btn-new:hover { filter: brightness(1.07); }
    .cp-receipt-btn-print { background: #64748b; color: #fff; }
    .cp-receipt-btn-print:hover { filter: brightness(1.1); }
    .cp-receipt-btn-cancel { background: #e74c3c; color: #fff; }
    .cp-receipt-btn-cancel:hover { filter: brightness(1.1); }
    .cp-receipt-btn-cash { background: #27ae60; color: #fff; }
    .cp-receipt-btn-cash:hover { filter: brightness(1.1); }
    .cp-receipt-btn-switch { background: #f39c12; color: #fff; }
    .cp-receipt-btn-switch:hover { filter: brightness(1.1); }
    .cp-switch-modal { @apply hidden fixed inset-0 z-[10001] items-center justify-center p-5; background: rgba(0,0,0,.5); backdrop-filter: blur(6px); }
    .cp-switch-modal.active { @apply flex; }
    .cp-switch-card { @apply rounded-2xl w-full max-w-[360px] pt-[22px] px-[22px] pb-[18px] relative; background: var(--bg-card,#fff); border: 1px solid var(--border,#e0d4c4); box-shadow: 0 12px 48px rgba(90,60,20,.18); animation: cpPmIn .22s ease both; }
    .cp-switch-head { @apply flex items-center justify-between mb-3; }
    .cp-switch-head h3 { @apply text-base font-bold m-0; color: var(--text,#1a1410); }
    .cp-switch-close { @apply border-0 text-xl cursor-pointer leading-none; background: none; color: var(--text-muted,#9a8070); }
    .cp-switch-desc { @apply text-[12px] mb-3; color: var(--text-sec,#5a4a3a); }
    .cp-switch-methods { @apply flex flex-col gap-2 mb-3; }
    .cp-switch-methods .cp-pay-method { @apply flex items-center gap-2 px-4 py-3 rounded-[14px] cursor-pointer select-none transition-all; border: 1.5px solid var(--border,#e0d4c4); background: var(--bg,#f4efe9); color: var(--text-sec,#5a4a3a); }
    .cp-switch-methods .cp-pay-method:hover { border-color: #b0a090; }
    .cp-switch-methods .cp-pay-method.selected { border-color: var(--orange); background: rgba(20,184,166,.1); color: var(--orange); box-shadow: 0 0 0 2px rgba(20,184,166,.15); }
    .cp-switch-methods .cp-pay-method input { @apply hidden; }
    .cp-switch-methods .cp-pm-ico { font-size: 16px; width: 24px; }
    .cp-switch-methods .cp-pm-lbl { font-size: 13px; font-weight: 500; }
    .cp-switch-methods .cp-pm-check { font-size: 16px; opacity: 0; transition: opacity .2s; color: var(--orange); margin-left: auto; }
    .cp-switch-methods .cp-pay-method.selected .cp-pm-check { opacity: 1; }
    [data-theme="dark"] .cp-switch-card { background: #1e293b; border-color: #334155; }
    [data-theme="dark"] .cp-switch-head h3 { color: #f1f5f9; }
    [data-theme="dark"] .cp-switch-close { color: #64748b; }
    [data-theme="dark"] .cp-switch-desc { color: #94a3b8; }
    [data-theme="dark"] .cp-switch-methods .cp-pay-method { background: #0f172a; border-color: #334155; color: #94a3b8; }
    [data-theme="dark"] .cp-switch-methods .cp-pay-method:hover { border-color: #475569; }
    [data-theme="dark"] .cp-switch-methods .cp-pay-method.selected { background: rgba(20,184,166,.12); border-color: #14b8a6; color: #14b8a6; }
    .cp-receipt-qr-btn { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:8px; padding:8px 12px; border-radius:8px; border:none; background:#d1904b; color:#fff; font-size:12px; font-weight:600; text-decoration:none; cursor:pointer; transition:filter .2s; }
    .cp-receipt-qr-btn:hover { filter: brightness(1.1); }
    .cp-paid-badge { text-align:center; font-size:16px; font-weight:800; color:#22c55e; margin-top:4px; animation: cpPaidIn .5s cubic-bezier(.16,1,.3,1) both; }
    .cp-paid-icon { animation: cpPaidIcon .6s ease-in-out both; }
    @keyframes cpPaidIn { 0% { opacity:0; transform:scale(.6); } 100% { opacity:1; transform:scale(1); } }
    @keyframes cpPaidIcon { 0% { transform:scale(0) rotate(-90deg); opacity:0; } 60% { transform:scale(1.3) rotate(10deg); } 100% { transform:scale(1) rotate(0deg); opacity:1; } }
    .cp-receipt-paid { animation: cpPaidGlow .8s ease-out; }
    @keyframes cpPaidGlow { 0% { box-shadow:0 0 0 0 rgba(34,197,94,.5); } 50% { box-shadow:0 0 0 12px rgba(34,197,94,.15); } 100% { box-shadow:0 0 0 0 rgba(34,197,94,0); } }

    /* ── PRODUCT MODAL (light, teal) ── */
    .pm-overlay { @apply fixed inset-0 hidden items-center justify-center z-[9999] p-5; background: rgba(0,0,0,.55); backdrop-filter: blur(8px); }
    .pm-card { @apply rounded-[24px] max-w-[480px] w-full flex flex-col relative; max-height: 85vh; background: #fff; box-shadow: 0 25px 80px rgba(0,0,0,.18); animation: modalIn .3s cubic-bezier(.16,1,.3,1); }
    .pm-header { @apply flex items-center gap-3 px-5 pt-5 pb-3 shrink-0; }
    .pm-thumb { @apply w-16 h-16 rounded-xl object-cover block shrink-0; background: #f0f0f0; border: 1px solid #eee; }
    .pm-header-info { @apply flex-1 min-w-0; }
    .pm-name { @apply text-lg font-bold; color: #1a1a1a; line-height: 1.3; }
    .pm-subtitle { @apply text-[13px]; color: #888; margin-top: 1px; }
    .pm-name-wrap { @apply flex items-center gap-2 flex-wrap; }
    .pm-badge { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold; background: #ef4444; color: #fff; letter-spacing: .02em; }
    .pm-close2 { @apply w-9 h-9 rounded-full border-0 text-[18px] cursor-pointer flex items-center justify-center shrink-0 transition-all; background: #fff; border: 1px solid #e0e0e0; color: #666; }
    .pm-close2:hover { @apply scale-[1.08]; background: #f5f5f5; color: #333; }
    .pm-body2 { @apply flex-1 overflow-y-auto px-5 py-2; scrollbar-width: thin; scrollbar-color: #ddd transparent; }
    .pm-body2::-webkit-scrollbar { width: 4px; }
    .pm-body2::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }
    .pm-section2 { @apply mb-4; }
    .pm-label { @apply text-[13px] font-semibold mb-2; color: #333; }
    .pm-grid3 { @apply grid gap-2; grid-template-columns: repeat(3,1fr); }
    .pm-grid2 { @apply grid gap-2; grid-template-columns: repeat(2,1fr); }
    .pm-btn2 { @apply px-2 py-2.5 rounded-[12px] text-[12px] font-medium cursor-pointer transition-all select-none text-center border; background: #fff; border-color: #e0e0e0; color: #333; line-height: 1.3; }
    .pm-btn2:hover { border-color: #0F9D94; color: #0F9D94; }
    .pm-btn2.active { @apply font-semibold; background: #0F9D94; border-color: #0F9D94; color: #fff; box-shadow: 0 2px 8px rgba(15,157,148,.25); }
    .pm-btn2 .pm-price-tag { @apply block text-[10px] font-normal mt-0.5; opacity: .75; }
    .pm-btn2.active .pm-price-tag { opacity: .9; }
    .pm-old-price { text-decoration: line-through; opacity: .6; margin-right: 2px; font-size: 10px; }
    .pm-two-col { @apply grid gap-3; grid-template-columns: 1fr 1fr; }
    .pm-divider { @apply border-0 h-px my-3; background: #eee; }
    .pm-addons { @apply flex flex-wrap gap-2; }
    .pm-addon-btn { @apply px-3.5 py-2 rounded-[12px] text-[12px] font-medium cursor-pointer transition-all select-none border; background: #fff; border-color: #e0e0e0; color: #333; }
    .pm-addon-btn:hover { border-color: #0F9D94; color: #0F9D94; }
    .pm-addon-btn.active { background: #0F9D94; border-color: #0F9D94; color: #fff; box-shadow: 0 2px 8px rgba(15,157,148,.25); }
    .pm-footer2 { @apply shrink-0 px-5 pt-3 pb-5; border-top: 1px solid #eee; background: #fff; border-radius: 0 0 24px 24px; }
    .pm-price-summary { @apply mb-3; }
    .pm-price-row { @apply flex justify-between items-center py-1 text-[14px]; color: #555; }
    .pm-price-row span:last-child { @apply font-semibold; color: #1a1a1a; }
    .pm-price-total { @apply border-t border-gray-100 pt-1.5 mt-1; }
    .pm-price-total span:last-child { @apply text-[22px] font-extrabold; color: #0F9D94; }
    .pm-add2 { @apply w-full py-3 border-0 rounded-[14px] text-white text-[15px] font-bold cursor-pointer flex items-center justify-center gap-2 transition-all; background: #0F9D94; box-shadow: 0 4px 16px rgba(15,157,148,.3); }
    .pm-add2:hover { background: #0B8A82; box-shadow: 0 6px 20px rgba(15,157,148,.4); }
    .pm-add2:active { @apply scale-[.98]; }
    .pm-add2:disabled { @apply opacity-50 cursor-not-allowed; box-shadow: none; }
    .pm-qty-row { @apply flex items-center justify-center gap-4 mt-3; }
    .pm-qty-row button { @apply w-9 h-9 rounded-full border-0 text-sm cursor-pointer flex items-center justify-center transition-all; background: #f5f5f5; border: 1px solid #e0e0e0; color: #555; }
    .pm-qty-row button:hover { background: #eee; color: #0F9D94; border-color: #0F9D94; }
    .pm-qty-row span { @apply text-lg font-bold min-w-[28px] text-center; color: #1a1a1a; }

    /* ── TOAST ── */
    #toast-container { @apply fixed top-20 right-5 z-[99999] flex flex-col gap-2 pointer-events-none; }
    .toast { @apply rounded-xl px-4 py-3 flex items-center gap-2.5 min-w-[200px] max-w-[320px] opacity-0 pointer-events-auto text-sm; background: var(--bg-card,#fff); border: 1px solid var(--border,#e0d4c4); border-left: 4px solid var(--orange); box-shadow: var(--shadow-md, 0 6px 28px rgba(90,60,20,.11)); color: var(--text,#1a1410); transform: translateX(120%); transition: all .35s ease; }
    .toast.show { @apply opacity-100; transform: translateX(0); }
    .toast i { @apply text-base shrink-0; color: var(--orange); }
    .toast.error { border-left-color: #e74c3c; }
    .toast.error i { color: #e74c3c; }

    /* ── CHAT ── */
    #chatToggle { @apply static flex items-center justify-center w-9 h-9 rounded-full border text-[15px] cursor-pointer shrink-0 transition-all; background: var(--bg-input,#ede8e0); border-color: var(--border,#e0d4c4); color: var(--text,#1a1410); animation: none; box-shadow: none; }
    #chatToggle:hover { border-color: var(--orange); color: var(--orange); transform: none; }
    #chatBox { @apply fixed w-[340px] h-[420px] rounded-[20px] hidden flex-col z-[9997] overflow-hidden; right: 24px; top: 66px; bottom: auto; max-width: calc(100vw - 48px); background: var(--bg-card,#fff); border: 1px solid var(--border,#e0d4c4); box-shadow: var(--shadow-lg, 0 12px 48px rgba(90,60,20,.16)); }
    .chat-header { @apply text-white px-[18px] py-3.5 flex items-center justify-between shrink-0; background: linear-gradient(135deg, var(--orange), var(--orange-dark)); }
    .chat-title { @apply text-sm font-semibold flex items-center gap-2; }
    #chatMessages { @apply flex-1 overflow-y-auto p-3.5 gap-2.5 flex flex-col; background: var(--bg-input,#ede8e0); }
    .msg-bot, .msg-user { @apply flex gap-2 mb-2.5; }
    .msg-user { @apply justify-end; }
    .msg-bot .avatar, .msg-user .avatar { @apply w-7 h-7 rounded-full text-white flex items-center justify-center text-[13px] shrink-0; background: var(--orange); }
    .msg-user .avatar { background: var(--text-muted); }
    .msg-bot .bubble { @apply px-3 py-2 text-[13px] max-w-[80%] leading-normal; background: var(--bg-card,#fff); border: 1px solid var(--border,#e0d4c4); border-radius: 4px 14px 14px 14px; color: var(--text,#1a1410); }
    .msg-user .bubble { @apply text-white px-3 py-2 text-[13px] max-w-[80%] leading-normal; background: var(--orange); border-radius: 14px 4px 14px 14px; }
    .chat-input { @apply flex items-center gap-2 px-3.5 py-2.5 border-t shrink-0; border-color: var(--border,#e0d4c4); background: var(--bg-card,#fff); }
    .chat-input input { @apply flex-1 px-4 py-2 rounded-full text-[13px] outline-none; border: 1px solid var(--border,#e0d4c4); background: var(--bg-input,#ede8e0); color: var(--text,#1a1410); }
    .chat-input input:focus { border-color: var(--orange); }
    .chat-input button { @apply w-9 h-9 rounded-full text-white border-0 cursor-pointer flex items-center justify-center text-[15px] shrink-0; background: var(--orange); }
    .chat-input button:hover { background: var(--orange-dark); }
    .chat-input button:disabled { @apply opacity-50 cursor-not-allowed; }
  }

    /* ── CUSTOM SCROLLBARS (not expressible as utilities) ── */
    .menu-scroll::-webkit-scrollbar { width: 3px; }
    .menu-scroll::-webkit-scrollbar-track { background: transparent; }
    .menu-scroll::-webkit-scrollbar-thumb { background: rgba(20,184,166,.35); border-radius: 99px; }
    .menu-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20,184,166,.65); }
    #cpItems::-webkit-scrollbar { width: 4px; }
    #cpItems::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }
    .cat-nav::-webkit-scrollbar { display: none; }
    .sellers-strip::-webkit-scrollbar { display: none; }


    /* ── KEYFRAMES ── */
    @keyframes modalIn { from { opacity: 0; transform: scale(.92) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    @keyframes qtyPop { 0% { transform: scale(.8); } 50% { transform: scale(1.15); } 100% { transform: scale(1); } }
    @keyframes cpPmIn { from { opacity: 0; transform: translateY(16px) scale(.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

    /* ── DARK THEME OVERRIDES (non-token specifics) ── */
    [data-theme="dark"] .cart-panel { background: #1e293b; border-left: 1px solid rgba(20,184,166,.08); box-shadow: -4px 0 24px rgba(0,0,0,.2); }
    [data-theme="dark"] .cp-header { background: #1e293b; border-color: #334155; }
    [data-theme="dark"] .cp-hdr-count { background: #334155; color: #94a3b8; }
    [data-theme="dark"] .cp-clear-btn { color: #64748b; }
    [data-theme="dark"] .cp-clear-btn:hover { background: rgba(231,76,60,.08); color: #ef4444; }
    [data-theme="dark"] .cp-item:hover { background: rgba(20,184,166,.03); }
    [data-theme="dark"] .cp-item + .cp-item { border-color: #334155; }
    [data-theme="dark"] .cp-item-img { background: #334155; }
    [data-theme="dark"] .cp-item-remove { color: #64748b; }
    [data-theme="dark"] .cp-item-remove:hover { background: rgba(231,76,60,.08); color: #ef4444; }
    [data-theme="dark"] .cp-qty { background: #0f172a; border-color: #334155; }
    [data-theme="dark"] .cp-qty input { color: #f1f5f9; }
    [data-theme="dark"] .cp-summary { background: #1e293b; border-color: #334155; }
    [data-theme="dark"] .cp-sum-divider { background: #334155; }
    [data-theme="dark"] .cp-options { background: #1e293b; border-color: #334155; }
    [data-theme="dark"] .cp-opt-btn { background: #0f172a; border-color: #334155; color: #94a3b8; }
    [data-theme="dark"] .cp-opt-btn.active { background: rgba(20,184,166,.12); border-color: #14b8a6; color: #14b8a6; }
    [data-theme="dark"] .cp-opt-field input { background: #0f172a; border-color: #334155; color: #f1f5f9; }
    [data-theme="dark"] .cp-loyalty-btn:hover { background: #0d9488; }
    [data-theme="dark"] .cp-footer { background: #1e293b; border-color: #334155; }
    [data-theme="dark"] .cp-pay-method { background: #0f172a; border-color: #334155; color: #94a3b8; }
    [data-theme="dark"] .cp-pay-method:hover { border-color: #475569; }
    [data-theme="dark"] .cp-pay-method.selected { background: rgba(20,184,166,.12); border-color: #14b8a6; color: #14b8a6; }
    [data-theme="dark"] .cp-pm-item:nth-child(odd) { background: rgba(255,255,255,.03); }
    [data-theme="dark"] .cp-split-row input { background: #0f172a; border-color: #334155; color: #f1f5f9; }
    [data-theme="dark"] .cp-change-calc { background: rgba(85,224,135,.02); border-color: rgba(85,224,135,.1); }
    [data-theme="dark"] .cp-change-calc input { background: #0f172a; border-color: #334155; color: #f1f5f9; }

    [data-theme="dark"] .cp-add-order-note { background: rgba(155,89,182,.08); border-color: rgba(155,89,182,.25); }
    [data-theme="dark"] .cp-paymodal-card { background: #1e293b; border-color: #334155; }
    [data-theme="dark"] .cp-pm-breakdown { background: #0f172a; border-color: #334155; }
    [data-theme="dark"] .menu-header { background: rgba(15,23,42,.96); border-color: #334155; }
    [data-theme="dark"] .cat-bar { background: rgba(15,23,42,.96); border-color: #334155; }
    [data-theme="dark"] .cat-pill { color: rgba(255,255,255,.5); }
    [data-theme="dark"] .cat-pill:hover { color: #14b8a6; }
    [data-theme="dark"] .cat-pill .pill-count { background: rgba(255,255,255,.15); color: rgba(255,255,255,.5); }
    [data-theme="dark"] .cat-pill:not(.active) .pill-count { background: rgba(20,184,166,.15); color: #14b8a6; }
    [data-theme="dark"] .cat-pill.active { background: linear-gradient(145deg,#0d9488,#14b8a6); box-shadow: inset 0 3px 7px rgba(0,0,0,.3), 0 4px 20px rgba(20,184,166,.4); }
    [data-theme="dark"] .cp-pm-confirm { background: linear-gradient(135deg,#14b8a6,#0d9488); box-shadow: 0 6px 18px rgba(20,184,166,.28); }
    [data-theme="dark"] .cp-receipt-card { background: #1e293b; border-color: #334155; }
    [data-theme="dark"] .cp-receipt-head h3 { color: #f1f5f9; }
    [data-theme="dark"] .cp-receipt-close { color: #64748b; }
    [data-theme="dark"] .cp-receipt-name { color: #f1f5f9; }
    [data-theme="dark"] .cp-receipt-divider { border-color: #334155; }
    [data-theme="dark"] .cp-rf-value { color: #f1f5f9; }
    [data-theme="dark"] .cp-receipt-item-name { color: #f1f5f9; }
    [data-theme="dark"] .cp-receipt-btn-new { background: linear-gradient(135deg,#14b8a6,#0d9488); }
    [data-theme="dark"] .cp-receipt-btn-print { background: #475569; }
    [data-theme="dark"] .cp-receipt-btn-cancel { background: #ef4444; }
    [data-theme="dark"] .cp-receipt-btn-cash { background: #22c55e; }
    [data-theme="dark"] .cp-receipt-btn-switch { background: #f59e0b; }
    [data-theme="dark"] .cp-receipt-qr-btn { background: linear-gradient(135deg,#14b8a6,#0d9488); }
    [data-theme="dark"] .pm-card { background: #1e293b; box-shadow: 0 25px 80px rgba(0,0,0,.6); }
    [data-theme="dark"] .pm-name { color: #f1f5f9; }
    [data-theme="dark"] .pm-close2 { background: #334155; border-color: #475569; color: #94a3b8; }
    [data-theme="dark"] .pm-close2:hover { background: #475569; color: #fff; }
    [data-theme="dark"] .pm-btn2 { background: #0f172a; border-color: #334155; color: #cbd5e1; }
    [data-theme="dark"] .pm-btn2:hover { border-color: #14b8a6; color: #14b8a6; }
    [data-theme="dark"] .pm-btn2.active { background: #14b8a6; color: #fff; }
    [data-theme="dark"] .pm-addon-btn { background: #0f172a; border-color: #334155; color: #cbd5e1; }
    [data-theme="dark"] .pm-addon-btn:hover { border-color: #14b8a6; color: #14b8a6; }
    [data-theme="dark"] .pm-addon-btn.active { background: #14b8a6; color: #fff; }
    [data-theme="dark"] .pm-label { color: #94a3b8; }
    [data-theme="dark"] .pm-divider { background: #334155; }
    [data-theme="dark"] .pm-footer2 { background: #1e293b; border-color: #334155; }
    [data-theme="dark"] .pm-price-row { color: #94a3b8; }
    [data-theme="dark"] .pm-price-row span:last-child { color: #f1f5f9; }
    [data-theme="dark"] .pm-qty-row button { background: #334155; border-color: #475569; color: #94a3b8; }
    [data-theme="dark"] .pm-qty-row button:hover { color: #14b8a6; border-color: #14b8a6; }
    [data-theme="dark"] .pm-qty-row span { color: #f1f5f9; }
    [data-theme="dark"] .pm-subtitle { color: #64748b; }
    [data-theme="dark"] .menu-scroll { scrollbar-color: rgba(20,184,166,.35) transparent; }
    [data-theme="dark"] .menu-scroll::-webkit-scrollbar-thumb { background: rgba(20,184,166,.35); }
    [data-theme="dark"] .menu-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20,184,166,.65); }

    @media (prefers-reduced-motion: reduce) {
      .cp-paymodal-card { animation: none; }
      .cp-pay-method, .cp-pm-confirm, .cp-pay-method .cp-pm-ico, .cp-pay-method .cp-pm-check { transition: none; }
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 1024px) { .product-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; } }
    @media (max-width: 900px) {
      .menu-main { padding: 0 16px; }
      .menu-header { padding: 12px 16px; }
      .header-center { max-width: 300px; }
      .top-sellers { padding: 16px 16px 4px; }
    }
    @media (max-width: 640px) {
      .product-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
      .header-right .brand-name { display: none; }
      .cat-nav { padding: 8px 16px 10px; }
      .menu-main { padding: 0 12px; }
      #chatBox { width: calc(100vw - 32px); right: 16px; }
    }
    @media (max-width: 480px) {
      .product-grid { grid-template-columns: 1fr; gap: 16px; }
      .card-name { font-size: 16px; }
      .card-size-line { font-size: 13px; margin-bottom: 12px; }
      .card-price { font-size: 20px; }
    }

  </style>
