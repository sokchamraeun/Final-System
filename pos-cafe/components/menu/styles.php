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
      --bg: #000000; --bg-card: #111111; --bg-card-hover: #1a1a1a; --bg-input: #1a1a1a; --bg-header: rgba(0,0,0,0.96);
      --border: #222222; --border-hover: #333333;
      --text: #f1f5f9; --text-sec: #94a3b8; --text-muted: #555555; --text-inv: #000;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.3); --shadow-md: 0 4px 16px rgba(0,0,0,0.4); --shadow-lg: 0 8px 32px rgba(0,0,0,0.5);
      --card-bg: #0a0a0a; --card-bg-card: #111111; --card-border: #222222;
      --card-text: #f1f5f9; --card-text-sec: #94a3b8; --card-text-muted: #555555;
      --card-shadow: 0 8px 24px rgba(0,0,0,0.5); --card-shadow-hover: 0 18px 40px rgba(0,0,0,0.6);
      --orange: #d1904b; --orange-dark: #a0702a; --orange-light: #e8b87a; --orange-glow: rgba(209,144,75,0.18);
      --shadow-accent: 0 4px 20px rgba(209,144,75,0.25);
      --card-primary: #d1904b; --card-primary-dark: #a0702a; --card-primary-glow: rgba(209,144,75,0.35);
      --card-badge: #d1904b; --card-badge-glow: rgba(209,144,75,0.35); --card-price: #d1904b;
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
    .header-center { @apply flex-1 max-w-[480px] flex items-center gap-2; }
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
    .menu-panel .cat-nav { @apply flex items-center gap-2 px-5 pt-2 pb-2.5 border-b overflow-x-auto shrink-0 sticky top-0 z-50; background: var(--bg-header, rgba(255,252,248,.97)); border-color: var(--border,#e0d4c4); }
    .menu-panel .menu-scroll { @apply flex-1 overflow-y-auto pb-10; }
    .menu-scroll { scrollbar-width: thin; scrollbar-color: rgba(20,184,166,.35) transparent; }
    .menu-main { @apply px-4; }
    .add-order-banner { @apply flex items-center gap-2 px-5 py-2.5 text-white text-[13px] font-semibold shrink-0; background: #9b59b6; }

    /* ── CATEGORY NAV / PILLS ── */
    .cat-nav { -ms-overflow-style: none; scrollbar-width: none; }
    .cat-pill { @apply inline-flex flex-col items-center gap-1 px-3 pt-2.5 pb-2 min-w-[72px] rounded-[14px] border-0 no-underline cursor-pointer shrink-0 transition-all; background: transparent; color: #000; }
    .cat-pill:hover { @apply -translate-y-0.5; color: #D89A4C; background: transparent; }
    .cat-pill.active { @apply translate-y-0 text-white; background: linear-gradient(145deg,#0d9488,#14b8a6); box-shadow: inset 0 3px 7px rgba(0,0,0,.3), 0 4px 20px rgba(20,184,166,.4); }
    .cat-pill-img { @apply flex items-center justify-center w-10 h-10 rounded-[10px] overflow-hidden shrink-0; background: rgba(0,0,0,.3); }
    .cat-pill-img img { @apply w-full h-full object-cover block; }
    .cat-pill-img i { @apply text-lg; color: rgba(255,255,255,.35); }
    .cat-pill.active .cat-pill-img i { color: rgba(255,255,255,.8); }
    .cat-pill-label { @apply text-xs font-semibold text-center whitespace-nowrap max-w-[72px] overflow-hidden text-ellipsis; line-height: 1.2; }
    .cat-pill .pill-count { @apply inline-flex items-center justify-center px-[5px] rounded-[20px] text-[9px] font-bold min-w-[16px] h-[14px]; background: rgba(0,0,0,.08); line-height: 1; color: #000; }
    .cat-pill:not(.active) .pill-count { background: rgba(216,154,76,.15); color: #000; }

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
    .cat-section { @apply mb-8; }
    .cat-header { @apply flex items-center gap-3 mb-3 pb-2; border-bottom: 2px solid var(--card-border, var(--border)); }
    .cat-icon { @apply flex items-center justify-center w-10 h-10 rounded-full text-white text-lg shrink-0; background: var(--card-primary, var(--orange)); }
    .cat-title-text h2 { @apply text-xl font-bold leading-tight; color: var(--card-text, var(--text)); }
    .cat-title-text span { @apply text-xs; color: var(--card-text-muted, var(--text-muted)); }
    .product-grid { @apply grid gap-4; grid-template-columns: repeat(4, 1fr); }

    /* ── PRODUCT CARD ── */
    .product-card { @apply rounded-[18px] overflow-hidden cursor-pointer border relative p-2 transition-all flex flex-col; background: var(--card-bg-card, var(--bg-card)); border-color: var(--card-border, var(--border)); box-shadow: var(--card-shadow, 0 8px 24px rgba(0,0,0,.08)); }
    .product-card:hover { @apply -translate-y-1.5; box-shadow: var(--card-shadow-hover, 0 18px 40px rgba(0,0,0,.12)); border-color: transparent; }
    .card-img { @apply relative overflow-hidden rounded-[10px]; aspect-ratio: 1/1; background: var(--card-text-muted,#eee); }
    .card-img img { @apply w-full h-full object-cover block; transition: transform .35s ease; }
    .product-card:hover .card-img img { @apply scale-105; }
    .product-badge { @apply absolute top-3 left-3 z-[2] inline-flex items-center px-3.5 py-1.5 rounded-full text-white text-xs font-bold pointer-events-none; background: var(--card-badge,#14b8a6); box-shadow: 0 4px 10px var(--card-badge-glow, rgba(20,184,166,.35)); }
    .badge-bestseller { @apply absolute top-3 right-3 z-[2] inline-flex items-center px-3.5 py-1.5 rounded-full text-white text-[11px] font-semibold pointer-events-none; background: var(--card-primary,#14b8a6); box-shadow: 0 4px 10px var(--card-primary-glow, rgba(20,184,166,.35)); }
    .card-info { @apply pt-2.5 flex flex-col flex-1; }
    .card-name { @apply text-lg font-bold mb-1 leading-snug; color: var(--card-text, var(--text)); }
    .card-desc { @apply text-[13px] leading-normal mb-4 overflow-hidden; color: var(--card-text-sec, var(--text-muted)); display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; }
    .card-divider { @apply border-0 mb-4; border-top: 1px solid #ECECEC; }
    .card-bottom { @apply flex justify-between items-center mt-auto; }
    .card-price { @apply text-[22px] font-bold leading-none; color: var(--card-price, var(--orange)); }
    .btn-add-circle { @apply w-12 h-12 rounded-full text-white text-2xl cursor-pointer flex items-center justify-center shrink-0 transition-all; background: var(--card-primary,#14b8a6); box-shadow: 0 6px 16px var(--card-primary-glow, rgba(20,184,166,.35)); line-height: 1; }
    .btn-add-circle:hover { @apply scale-[1.08]; background: var(--card-primary-dark,#0d9488); }
    .btn-add-circle:active { @apply scale-95; }
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
    .cp-item-img { @apply w-16 h-16 rounded-xl object-cover shrink-0; background: #f5f0ea; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
    .cp-item-body { @apply flex-1 min-w-0 flex flex-col gap-1; }
    .cp-item-top { @apply flex items-start justify-between gap-2; }
    .cp-item-name { @apply text-sm font-semibold leading-snug flex-1; color: var(--text,#1a1410); }
    .cp-item-remove { @apply w-6 h-6 border-0 bg-transparent cursor-pointer text-[11px] rounded-md flex items-center justify-center shrink-0 transition-all; color: #c0b0a0; }
    .cp-item-remove:hover { color: #e74c3c; background: rgba(231,76,60,.1); }
    .cp-item-meta { @apply text-xs leading-normal; color: var(--text-sec,#5a4a3a); }
    .cp-item-bottom { @apply flex items-center justify-between gap-2 mt-1; }
    .cp-item-price { @apply text-[15px] font-bold; color: var(--orange); }
    .cp-qty { @apply flex items-center rounded-[10px] overflow-hidden border; background: #f5f0ea; border-color: #e8ddd0; }
    .cp-qty button { @apply w-8 h-8 border-0 bg-transparent text-base font-semibold cursor-pointer flex items-center justify-center transition-colors; color: var(--orange); }
    .cp-qty button:hover { background: rgba(20,184,166,.12); }
    .cp-qty input { @apply w-8 text-center text-sm font-bold bg-transparent border-0 outline-none p-0; color: var(--text,#1a1410); -moz-appearance: textfield; }
    .cp-qty input::-webkit-inner-spin-button, .cp-qty input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .cp-free-row { @apply flex items-center gap-3 px-[18px] py-2.5; background: rgba(39,174,96,.04); border-top: 1px dashed #27ae60; }
    .cp-free-icon { @apply w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0; background: linear-gradient(135deg,#e8f5e9,#f0fff4); }
    .cp-free-badge { @apply text-white text-[10px] font-bold px-2 py-0.5 rounded-[20px] align-middle; background: #27ae60; }
    .cp-summary { @apply shrink-0 px-[18px] pb-3 border-t; border-color: var(--border,#e0d4c4); background: var(--bg-card,#fff); }
    .cp-sum-row { @apply flex justify-between items-center py-[5px] text-[13px]; color: var(--text-sec,#5a4a3a); }
    .cp-sum-row.discount { color: #e74c3c; }
    .cp-sum-divider { @apply h-px my-1.5; background: var(--border,#e0d4c4); }
    .cp-sum-total { @apply flex justify-between items-center py-1.5; }
    .cp-sum-total .lbl { @apply text-[15px] font-bold; color: var(--text,#1a1410); }
    .cp-sum-total .amt { @apply text-2xl font-extrabold; color: var(--orange); }
    .cp-discount-toggle { @apply w-full px-3 py-[7px] rounded-[10px] bg-transparent text-xs font-semibold cursor-pointer flex items-center justify-center gap-1.5 transition-all my-1; border: 1px dashed var(--border-hover,#c9b89f); color: var(--orange); }
    .cp-discount-toggle:hover { background: rgba(20,184,166,.06); border-color: var(--orange); }
    .cp-discount-toggle.remove { color: #e74c3c; border-color: rgba(231,76,60,.3); }
    .cp-discount-toggle.remove:hover { background: rgba(231,76,60,.06); border-color: #e74c3c; }
    #cpDiscountForm { @apply rounded-xl p-3 my-1; background: rgba(20,184,166,.05); border: 1px solid rgba(20,184,166,.2); }
    .cp-dtype-row { @apply flex gap-1.5 mb-2; }
    .cp-dtype-btn { @apply flex-1 py-1.5 rounded-lg bg-transparent text-xs font-semibold cursor-pointer transition-all; border: 1px solid var(--border-hover,#c9b89f); color: var(--text-sec,#5a4a3a); }
    .cp-dtype-btn.active { @apply text-white; background: var(--orange); border-color: var(--orange); }
    .cp-disc-inputs { @apply flex flex-col gap-1.5 mb-2; }
    .cp-disc-inputs input { @apply w-full px-2.5 py-2 rounded-lg text-[13px] outline-none; border: 1px solid var(--border-hover,#c9b89f); background: var(--bg-card,#fff); color: var(--text,#1a1410); }
    .cp-disc-inputs input:focus { border-color: var(--orange); }
    .cp-disc-actions { @apply flex gap-1.5; }
    .cp-btn-apply { @apply flex-1 py-2 text-white border-0 rounded-lg text-[13px] font-bold cursor-pointer flex items-center justify-center gap-1; background: var(--orange); }
    .cp-btn-apply:hover { @apply opacity-90; }
    .cp-btn-cancel { @apply px-3 py-2 bg-transparent rounded-lg text-xs cursor-pointer; color: var(--text-muted,#9a8070); border: 1px solid var(--border-hover,#c9b89f); }
    .cp-options { @apply px-[18px] pb-2 border-t shrink-0; border-color: var(--border,#e0d4c4); background: var(--bg-card,#fff); }
    .cp-opt-row { @apply flex gap-2 mt-2; }
    .cp-opt-btn { @apply flex-1 flex items-center justify-center gap-1.5 p-2 rounded-[10px] text-xs font-medium cursor-pointer transition-all; border: 1.5px solid var(--border,#e0d4c4); background: var(--bg,#f4efe9); color: var(--text-sec,#5a4a3a); }
    .cp-opt-btn.active { @apply font-semibold; border-color: var(--orange); background: rgba(20,184,166,.1); color: var(--orange); }
    .cp-opt-field { @apply mt-2; }
    .cp-opt-field label { @apply flex items-center gap-1.5 text-[11px] font-semibold mb-1; color: var(--text-sec,#5a4a3a); }
    .cp-opt-field input { @apply w-full px-2.5 py-2 rounded-[10px] text-[13px] outline-none; border: 1px solid var(--border,#e0d4c4); background: var(--bg,#f4efe9); color: var(--text,#1a1410); }
    .cp-opt-field input:focus { border-color: var(--orange); }
    .cp-loyalty { @apply flex items-center justify-between mt-2 py-1.5; }
    .cp-loyalty-info { @apply text-[11px] flex items-center gap-1.5; color: var(--text-sec,#5a4a3a); }
    .cp-loyalty-info .linked { @apply font-semibold; color: var(--orange); }
    .cp-loyalty-btn { @apply text-white border-0 rounded-lg px-3 py-1.5 text-[11px] font-semibold cursor-pointer; background: var(--orange); }
    .cp-loyalty-btn:hover { background: #0d9488; }
    .cp-footer { @apply shrink-0 px-[18px] pt-3 pb-4 border-t; background: var(--bg-card,#fff); border-color: var(--border,#e0d4c4); box-shadow: 0 -4px 20px rgba(0,0,0,.04); }
    .cp-pay-pills { @apply flex gap-1.5 mb-3; }
    .cp-pay-pill { @apply flex-1 flex items-center justify-center gap-1.5 px-1 py-2 rounded-[10px] cursor-pointer text-xs font-medium transition-all select-none; border: 1.5px solid var(--border,#e0d4c4); background: var(--bg,#f4efe9); color: var(--text-sec,#5a4a3a); }
    .cp-pay-pill:hover { border-color: #b0a090; }
    .cp-pay-pill.selected { @apply font-semibold; border-color: var(--orange); background: rgba(20,184,166,.1); color: var(--orange); box-shadow: 0 0 0 2px rgba(20,184,166,.15); }
    .cp-pay-pill i { @apply text-sm; }
    .cp-pay-pill input { @apply hidden; }
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

    /* ── PRODUCT MODAL (dark) ── */
    .pm-overlay { @apply fixed inset-0 hidden items-center justify-center z-[9999] p-5; background: rgba(0,0,0,.75); backdrop-filter: blur(18px); }
    .pm-card { @apply rounded-[32px] max-w-[520px] w-full flex flex-col relative; max-height: 92vh; background: #181818; border: 1px solid rgba(255,255,255,.06); box-shadow: 0 25px 80px rgba(0,0,0,.7); animation: modalIn .35s cubic-bezier(.16,1,.3,1); }
    .pm-top-row { @apply flex items-center gap-5 pt-6 px-7 pb-0; }
    .pm-img-wrap { @apply shrink-0; }
    .pm-img { @apply w-[120px] h-[120px] object-cover block rounded-[18px]; background: #202020; border: 2px solid rgba(255,255,255,.08); box-shadow: 0 8px 32px rgba(0,0,0,.3); }
    .pm-head-row { @apply flex-1 flex flex-col items-start gap-2; }
    .pm-title { @apply text-2xl font-bold text-white leading-snug; }
    .pm-price-pill { @apply text-lg font-bold px-4 py-1.5 rounded-full whitespace-nowrap inline-block; background: rgba(216,154,76,.14); color: #D89A4C; }
    .pm-close { @apply absolute top-4 right-4 w-10 h-10 rounded-full border-0 text-white text-xl cursor-pointer flex items-center justify-center z-10 transition-all; background: rgba(255,255,255,.12); backdrop-filter: blur(12px); }
    .pm-close:hover { @apply scale-[1.08]; background: rgba(255,255,255,.25); }
    .pm-body { @apply flex-1 overflow-y-auto px-7; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.08) transparent; }
    .pm-desc { @apply text-sm mb-[22px] leading-relaxed; color: rgba(255,255,255,.5); }
    .pm-section { @apply mb-3.5 px-[18px] pt-4 pb-[18px] rounded-[22px]; background: #1E1E1E; }
    .pm-section-title { @apply text-xs font-semibold uppercase mb-3 flex items-center gap-2; color: rgba(255,255,255,.4); letter-spacing: .8px; }
    .pm-bullet { @apply w-1.5 h-1.5 rounded-full inline-block; background: #D89A4C; }
    .pm-grid { @apply grid gap-1.5; grid-template-columns: repeat(3,1fr); }
    .pm-btn { @apply px-1.5 py-2.5 rounded-[14px] border-0 text-[13px] font-medium cursor-pointer transition-all select-none text-center; background: linear-gradient(145deg,#333,#262626); color: rgba(255,255,255,.6); line-height: 1.2; box-shadow: 0 4px 8px rgba(0,0,0,.3), inset 0 1px 0 rgba(255,255,255,.06); }
    .pm-btn:hover { color: #D89A4C; background: linear-gradient(145deg,#383838,#2a2a2a); box-shadow: 0 6px 12px rgba(0,0,0,.4), inset 0 1px 0 rgba(255,255,255,.08); }
    .pm-btn.active { @apply text-white; background: linear-gradient(145deg,#0d9488,#14b8a6); box-shadow: inset 0 3px 6px rgba(0,0,0,.3), inset 0 -1px 0 rgba(255,255,255,.1); }
    .pm-qty-box { @apply flex flex-col items-center gap-2.5 px-[18px] py-4 rounded-[22px] mb-3.5 w-full; background: #1E1E1E; }
    .pm-qty-box::before { content: 'Quantity'; @apply text-xs font-semibold uppercase; color: rgba(255,255,255,.4); letter-spacing: .8px; }
    .pm-qty-box .pm-qty-inner { @apply flex items-center justify-between w-full max-w-[260px] rounded-full; background: #2A2A2A; padding: 3px 4px; box-shadow: 0 4px 12px rgba(0,0,0,.3), inset 0 1px 0 rgba(255,255,255,.06); }
    .pm-qty-box .pm-qty-btn { @apply w-[38px] h-[38px] border-0 bg-transparent text-xl font-semibold cursor-pointer rounded-full inline-flex items-center justify-center transition-all select-none; color: #D89A4C; line-height: 1; }
    .pm-qty-box .pm-qty-btn:hover { @apply scale-110; background: rgba(216,154,76,.12); }
    .pm-qty-box .pm-qty-btn:active { @apply scale-90; background: rgba(216,154,76,.2); }
    .pm-qty-box .pm-qty-val { @apply w-10 text-center font-bold text-lg text-white; animation: qtyPop .2s ease; }
    .pm-bottom { @apply flex items-center gap-4 pt-4 px-7 pb-5 shrink-0; background: #181818; border-top: 1px solid rgba(255,255,255,.05); border-radius: 0 0 32px 32px; }
    .pm-bottom-left { @apply flex flex-col gap-0.5; }
    .pm-total-lbl { @apply text-[11px] font-semibold uppercase; letter-spacing: .6px; color: rgba(255,255,255,.3); }
    .pm-total-val { @apply text-[26px] font-bold; color: #D89A4C; line-height: 1; }
    .pm-add { @apply ml-auto px-7 py-3.5 text-white border-0 rounded-2xl text-base font-bold cursor-pointer transition-all flex items-center gap-2.5 whitespace-nowrap; background: #D89A4C; box-shadow: 0 4px 24px rgba(216,154,76,.25); }
    .pm-add:hover { @apply -translate-y-[3px]; background: #c88a3c; box-shadow: 0 8px 32px rgba(216,154,76,.4); }
    .pm-add:active { @apply -translate-y-px; }
    .pm-add:disabled { @apply opacity-50 cursor-not-allowed translate-y-0; box-shadow: none; }

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
    .pm-body::-webkit-scrollbar { width: 4px; }
    .pm-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 4px; }

    /* ── KEYFRAMES ── */
    @keyframes modalIn { from { opacity: 0; transform: scale(.92) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    @keyframes qtyPop { 0% { transform: scale(.8); } 50% { transform: scale(1.15); } 100% { transform: scale(1); } }
    @keyframes cpPmIn { from { opacity: 0; transform: translateY(16px) scale(.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

    /* ── DARK THEME OVERRIDES (non-token specifics) ── */
    [data-theme="dark"] .cart-panel { background: #131313; border-left: 1px solid rgba(209,144,75,.08); box-shadow: -4px 0 24px rgba(0,0,0,.2); }
    [data-theme="dark"] .cp-header { background: #131313; border-color: #222; }
    [data-theme="dark"] .cp-hdr-count { background: #1e1e1e; color: #aaa; }
    [data-theme="dark"] .cp-clear-btn { color: #555; }
    [data-theme="dark"] .cp-clear-btn:hover { background: rgba(231,76,60,.08); color: #e74c3c; }
    [data-theme="dark"] .cp-item:hover { background: rgba(209,144,75,.03); }
    [data-theme="dark"] .cp-item + .cp-item { border-color: #1e1e1e; }
    [data-theme="dark"] .cp-item-img { background: #1e1e1e; }
    [data-theme="dark"] .cp-item-remove { color: #444; }
    [data-theme="dark"] .cp-item-remove:hover { background: rgba(231,76,60,.08); color: #e74c3c; }
    [data-theme="dark"] .cp-qty { background: #1a1a1a; border-color: #2a2a2a; }
    [data-theme="dark"] .cp-qty input { color: #eee; }
    [data-theme="dark"] .cp-summary { background: #131313; border-color: #1e1e1e; }
    [data-theme="dark"] .cp-sum-divider { background: #222; }
    [data-theme="dark"] .cp-options { background: #131313; border-color: #1e1e1e; }
    [data-theme="dark"] .cp-opt-btn { background: #1a1a1a; border-color: #282828; color: #999; }
    [data-theme="dark"] .cp-opt-btn.active { background: rgba(209,144,75,.12); border-color: #d1904b; color: #d1904b; }
    [data-theme="dark"] .cp-opt-field input { background: #1a1a1a; border-color: #252525; color: #eee; }
    [data-theme="dark"] .cp-loyalty-btn:hover { background: #a0702a; }
    [data-theme="dark"] .cp-footer { background: #131313; border-color: #222; }
    [data-theme="dark"] .cp-pay-pill { background: #1a1a1a; border-color: #282828; color: #888; }
    [data-theme="dark"] .cp-pay-pill:hover { border-color: #444; }
    [data-theme="dark"] .cp-pay-pill.selected { background: rgba(209,144,75,.12); border-color: #d1904b; color: #d1904b; }
    [data-theme="dark"] .cp-split-row input { background: #1a1a1a; border-color: #252525; color: #eee; }
    [data-theme="dark"] .cp-change-calc { background: rgba(85,224,135,.02); border-color: rgba(85,224,135,.1); }
    [data-theme="dark"] .cp-change-calc input { background: #1a1a1a; border-color: #2a2a2a; color: #eee; }
    [data-theme="dark"] .cp-discount-toggle { border-color: #333; }
    [data-theme="dark"] .cp-discount-toggle:hover { background: rgba(209,144,75,.06); border-color: #d1904b; }
    [data-theme="dark"] #cpDiscountForm { background: rgba(209,144,75,.04); border-color: rgba(209,144,75,.12); }
    [data-theme="dark"] .cp-disc-inputs input { background: #1a1a1a; border-color: #252525; color: #eee; }
    [data-theme="dark"] .cp-add-order-note { background: rgba(155,89,182,.08); border-color: rgba(155,89,182,.25); }
    [data-theme="dark"] .cp-free-row { background: rgba(39,174,96,.03); }
    [data-theme="dark"] .cp-paymodal-card { background: #161616; border-color: #252525; }
    [data-theme="dark"] .cp-pm-breakdown { background: #1a1a1a; border-color: #252525; }
    [data-theme="dark"] .menu-header { background: rgba(14,14,14,.96); border-color: #252525; }
    [data-theme="dark"] .menu-panel .cat-nav { background: rgba(14,14,14,.96); border-color: #252525; }
    [data-theme="dark"] .cat-pill { color: rgba(255,255,255,.5); }
    [data-theme="dark"] .cat-pill:hover { color: #D89A4C; }
    [data-theme="dark"] .cat-pill .pill-count { background: rgba(255,255,255,.15); color: rgba(255,255,255,.5); }
    [data-theme="dark"] .cat-pill:not(.active) .pill-count { background: rgba(216,154,76,.15); color: #D89A4C; }
    [data-theme="dark"] .cat-pill.active { background: linear-gradient(145deg,#a0702a,#d1904b); box-shadow: inset 0 3px 7px rgba(0,0,0,.3), 0 4px 20px rgba(209,144,75,.4); }
    [data-theme="dark"] .cp-pm-confirm { background: linear-gradient(135deg,#d99a55,#c07f37); box-shadow: 0 6px 18px rgba(209,144,75,.28); }
    [data-theme="dark"] .pm-btn.active { background: linear-gradient(145deg,#a0702a,#d1904b); box-shadow: inset 0 3px 6px rgba(0,0,0,.3), inset 0 -1px 0 rgba(255,255,255,.1); }
    [data-theme="dark"] .menu-scroll { scrollbar-color: rgba(209,144,75,.35) transparent; }
    [data-theme="dark"] .menu-scroll::-webkit-scrollbar-thumb { background: rgba(209,144,75,.35); }
    [data-theme="dark"] .menu-scroll::-webkit-scrollbar-thumb:hover { background: rgba(209,144,75,.65); }

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
      .btn-add-circle { width: 42px; height: 42px; font-size: 20px; }
      .header-right .brand-name { display: none; }
      .cat-nav { padding: 8px 16px 10px; }
      .menu-main { padding: 0 12px; }
      #chatBox { width: calc(100vw - 32px); right: 16px; }
    }
    @media (max-width: 480px) {
      .product-grid { grid-template-columns: 1fr; gap: 16px; }
      .card-name { font-size: 16px; }
      .card-desc { font-size: 13px; margin-bottom: 12px; }
      .card-price { font-size: 20px; }
      .btn-add-circle { width: 44px; height: 44px; font-size: 22px; }
    }

  </style>
