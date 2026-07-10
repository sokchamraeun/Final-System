<body>
<div class="vo-flex-wrap" style="display:flex; min-height:100vh;">
<?php $navActive = 'report.php'; ?>
<?php require __DIR__ . '/../../components/sidebar/index.php'; ?>
<div class="vo-main-col" style="flex:1; min-width:0;">

<style>
#appSidebar { background: #000000 !important; border-right: none !important; }
[data-theme="light"] #appSidebar { background: #000000 !important; border-right: none !important; }
[data-theme="light"] #appSidebar .nav-link { color: #9aa1ac !important; }
[data-theme="light"] #appSidebar .nav-link:hover { color: #2dd4bf !important; }
[data-theme="light"] #appSidebar .nav-link.active { color: #ffffff !important; }
</style>

<!-- Top Navigation Header -->
<div class="top-nav fu" style="animation-delay:.0s">
  <div class="top-nav-left">
    <div class="welcome-text">
      Welcome back, <strong><?= htmlspecialchars($admin_name ?? ($_SESSION['username'] ?? 'Admin')) ?></strong>
    </div>
  </div>
  <div class="top-nav-right">
    <?php
      $_cart_count = 0;
      foreach (($_SESSION['cart'] ?? []) as $_cart_item) {
          $_cart_count += (int)($_cart_item['qty'] ?? 1);
      }
    ?>
    <a href="<?= e(url('menu')) ?>" class="top-nav-icon" title="View cart" style="text-decoration:none;">
      <i class="fa-solid fa-cart-shopping"></i>
      <?php if ($_cart_count > 0): ?><span class="badge"><?= $_cart_count > 99 ? '99+' : $_cart_count ?></span><?php endif; ?>
    </a>
    <div class="top-nav-icon" title="Notifications">
      <i class="fa-solid fa-bell"></i>
      <span class="badge">3</span>
    </div>
    <div class="top-nav-profile">
      <div class="top-nav-avatar">
        <?= strtoupper(substr($admin_name ?? ($_SESSION['username'] ?? 'A'), 0, 1)) ?>
      </div>
      <div class="top-nav-user-info">
        <div class="top-nav-user-name"><?= htmlspecialchars($admin_name ?? ($_SESSION['username'] ?? 'Admin')) ?></div>
        <div class="top-nav-user-role"><?= htmlspecialchars($_cur_role_name ?? 'Admin') ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Hero Banner -->
<div class="hero-banner fu" style="animation-delay:.05s">
  <div class="hero-content">
    <div class="hero-brand">
      <div class="hero-brand-icon">
        <i class="fa-solid fa-mug-hot"></i>
      </div>
      <div class="hero-brand-name">The Birdnest Cafe</div>
    </div>
    <h1 class="hero-title">Sales Overview</h1>
    <p class="hero-desc">Comprehensive business analytics and reporting dashboard. Track sales, monitor performance, and gain insights into your cafe's operations.</p>
  </div>
  <div class="hero-actions">
    <div class="mode-switcher">
      <button class="mode-btn<?= $mode === 'daily' ? ' active' : '' ?>" onclick="switchMode('daily')" title="Daily Report">
        <i class="fa-solid fa-calendar-day"></i> Daily
      </button>
      <button class="mode-btn<?= $mode === 'monthly' ? ' active' : '' ?>" onclick="switchMode('monthly')" title="Monthly Report">
        <i class="fa-solid fa-calendar-days"></i> Monthly
      </button>
      <button class="mode-btn<?= $mode === 'range' ? ' active' : '' ?>" onclick="switchMode('range')" title="Custom Range">
        <i class="fa-solid fa-calendar"></i> Range
      </button>
    </div>
    <button class="hero-btn" onclick="refreshContent()" title="Refresh data">
      <i class="fa-solid fa-arrows-rotate"></i> Refresh
    </button>
    <button class="hero-btn" onclick="window.print()" title="Print report">
      <i class="fa-solid fa-print"></i> Print
    </button>
    <button class="hero-btn" onclick="exportExcel()" title="Export as Excel">
      <i class="fa-solid fa-file-excel"></i> Excel
    </button>
    <button class="hero-btn" onclick="exportCSV()" title="Export as CSV">
      <i class="fa-solid fa-file-csv"></i> CSV
    </button>
    <button class="hero-btn primary" onclick="exportPDF()" title="Export as PDF">
      <i class="fa-solid fa-file-pdf"></i> PDF
    </button>
  </div>
</div>

<script>
var CURRENT_MODE = '<?= htmlspecialchars($mode) ?>';
var BUSINESS_TODAY = '<?= htmlspecialchars(getBusinessDateToday()) ?>';
var CURRENT_MONTH_VAL = '<?= htmlspecialchars((new DateTime())->format("Y-m")) ?>';

/* Shared AJAX loader — fetches a report URL and swaps the dynamic sections
   in place, so switching mode/date-range never triggers a full page reload. */
function loadReportContent(url, mode) {
    fetch(url)
        .then(function(r) { return r.text(); })
        .then(function(html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');

            function destroyCharts() {
                if (typeof Chart !== 'undefined') {
                    var ids = ['categoryChart','productChart','refundChart','hourlyChart','topHoursChart','dailyTrendChart','paymentChart'];
                    ids.forEach(function(id) {
                        var canvas = document.getElementById(id);
                        if (canvas) {
                            var existing = Chart.getChart(canvas);
                            if (existing) existing.destroy();
                        }
                    });
                }
            }

            /* Drop the fade-up entrance class from swapped-in nodes so
               re-loading content updates values only — no replayed animation. */
            function stripEntranceAnim(root) {
                if (!root) return;
                if (root.classList) root.classList.remove('fu');
                if (root.querySelectorAll) {
                    root.querySelectorAll('.fu').forEach(function(el) { el.classList.remove('fu'); });
                }
            }

            function swap(id) {
                var old = document.getElementById(id);
                var nw = doc.getElementById(id);
                if (old && nw) {
                    stripEntranceAnim(nw);
                    old.outerHTML = nw.outerHTML;
                }
            }

            function swapSel(sel) {
                var old = document.querySelector(sel);
                var nw = doc.querySelector(sel);
                if (old && nw) {
                    stripEntranceAnim(nw);
                    old.outerHTML = nw.outerHTML;
                }
            }

            destroyCharts();

            swap('filter-form');
            swap('search-toolbar');
            swap('kpi-grid');
            swap('live-bar');
            swapSel('.report-content');

            var oldDyn = document.querySelector('script[data-dynamic="charts"]');
            var newDyn = doc.querySelector('script[data-dynamic="charts"]');
            if (oldDyn && newDyn) {
                var s = document.createElement('script');
                s.setAttribute('data-dynamic', 'charts');
                s.textContent = newDyn.textContent;
                oldDyn.parentNode.replaceChild(s, oldDyn);
            }

            if (mode) {
                CURRENT_MODE = mode;
                document.querySelectorAll('.mode-btn').forEach(function(b) {
                    b.classList.toggle('active', b.getAttribute('onclick') === "switchMode('" + mode + "')");
                });
            }
            history.pushState({ mode: mode || CURRENT_MODE }, '', url);

            if (typeof rebuildAllCharts === 'function') {
                setTimeout(function() { rebuildAllCharts(); }, 200);
            }
            if (typeof setupPager === 'function') {
                setTimeout(function() {
                    setupPager('productsTable', 'productsPager', 10);
                    setupPager('refundTable', 'refundPager', 10);
                    setupPager('remakeTable', 'remakePager', 10);
                }, 200);
            }
            if (typeof applyTabFilter === 'function') {
                setTimeout(function() { applyTabFilter(CURRENT_TAB); }, 50);
            }
        })
        .catch(function() {});
}

function switchMode(mode) {
    if (mode === CURRENT_MODE) return;
    document.querySelectorAll('.mode-btn').forEach(function(b) { b.classList.remove('active'); });
    event.currentTarget.classList.add('active');

    var params = new URLSearchParams();
    params.set('mode', mode);
    if (mode === 'daily') {
        params.set('date', BUSINESS_TODAY);
    } else if (mode === 'monthly') {
        params.set('month', CURRENT_MONTH_VAL);
    } else {
        params.set('from_date', BUSINESS_TODAY);
        params.set('to_date', BUSINESS_TODAY);
    }

    loadReportContent('?' + params.toString(), mode);
}

function refreshContent() {
    loadReportContent(window.location.href, CURRENT_MODE);
}
</script>
