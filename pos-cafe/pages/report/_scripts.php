<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script data-dynamic="charts">
(function() {
    if (localStorage.getItem('theme') === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
    }
})();

function toggleTheme() {
    const html = document.documentElement;
    const icon = document.getElementById('themeIcon');
    const text = document.getElementById('themeText');
    if (html.getAttribute('data-theme') === 'light') {
        html.removeAttribute('data-theme');
        if (icon) icon.className = 'fa-solid fa-moon';
        if (text) text.textContent = 'Dark';
        localStorage.setItem('theme', 'dark');
    } else {
        html.setAttribute('data-theme', 'light');
        if (icon) icon.className = 'fa-solid fa-sun';
        if (text) text.textContent = 'Light';
        localStorage.setItem('theme', 'light');
    }
    if (typeof rebuildAllCharts === 'function') rebuildAllCharts();
}

document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('theme') === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
        const icon = document.getElementById('themeIcon');
        const text = document.getElementById('themeText');
        if (icon) icon.className = 'fa-solid fa-sun';
        if (text) text.textContent = 'Light';
    }
});

function getChartConfig() {
    const w = window.innerWidth;
    if (w < 480) return { mobile: true,  fontSize: 9,  ticksX: 4, ticksY: 4, pointR: 3 };
    if (w < 768) return { mobile: true,  fontSize: 10, ticksX: 5, ticksY: 5, pointR: 4 };
    if (w < 1024)return { mobile: false, fontSize: 11, ticksX: 6, ticksY: 6, pointR: 5 };
    return             { mobile: false, fontSize: 12, ticksX: 8, ticksY: 7, pointR: 6 };
}

const tooltipDefaults = {
    backgroundColor: 'rgba(18,18,18,0.95)',
    titleColor: '#fff',
    titleFont: { family: 'Poppins', size: 14, weight: '600' },
    bodyColor: '#5eead4',
    bodyFont: { family: 'Poppins', size: 13 },
    borderColor: 'rgba(13,148,136,0.3)',
    borderWidth: 1,
    padding: 12,
    cornerRadius: 10,
};

function CT() {
    const cs = getComputedStyle(document.documentElement);
    const light = document.documentElement.getAttribute('data-theme') === 'light';
    return {
        text:    (cs.getPropertyValue('--text-muted') || '').trim() || '#6b7280',
        grid:    light ? 'rgba(17,24,39,0.06)' : 'rgba(255,255,255,0.06)',
        surface: (cs.getPropertyValue('--bg-card') || '').trim() || '#111827'
    };
}

const categoryLabels = <?= json_encode(array_keys($categorySales), JSON_UNESCAPED_UNICODE) ?>;
const categoryQty    = <?= json_encode(array_map(function($d) { return (float)$d['qty']; }, array_values($categorySales))) ?>;
const categoryCanvas = document.getElementById('categoryChart');
let categoryChart    = null;

function buildCategoryChart() {
    if (!categoryCanvas || categoryLabels.length === 0) return;
    const cfg = getChartConfig();
    const maxDisplay   = Math.min(8, categoryLabels.length);
    const displayLabels = categoryLabels.slice(0, maxDisplay);
    const displayData   = categoryQty.slice(0, maxDisplay);
    const colors = ['rgba(13,148,136,0.85)','rgba(20,184,166,0.85)','rgba(6,182,212,0.85)','rgba(34,211,238,0.85)','rgba(14,165,156,0.85)','rgba(45,212,191,0.85)','rgba(94,234,212,0.85)'];
    if (categoryChart) categoryChart.destroy();
    categoryChart = new Chart(categoryCanvas, {
        type: 'bar',
        data: { labels: displayLabels, datasets: [{ label: 'Quantity Sold', data: displayData, backgroundColor: colors.slice(0, displayLabels.length), borderColor: '#0d9488', borderWidth: 1, borderRadius: 8, hoverBackgroundColor: 'rgba(13,148,136,1)', hoverBorderColor: '#5eead4', hoverBorderWidth: 2 }] },
        options: {
            responsive: true, maintainAspectRatio: false, clip: false,
            layout: { padding: { left: 10, right: 10, bottom: 20 } },
            plugins: { legend: { display: false }, tooltip: { ...tooltipDefaults, callbacks: { label: ctx => ' Qty: ' + ctx.raw } } },
            scales: {
                x: { ticks: { color: CT().text, font: { family: 'Poppins', size: cfg.fontSize }, maxRotation: 30, minRotation: 0, autoSkip: false, callback: function(v, i, ticks) { const l = this.getLabelForValue(v); if (typeof l !== 'string') return l; const cw = this.chart.width, tc = ticks.length || 1, px = cw / tc, ca = Math.max(5, Math.floor(px / (cfg.fontSize * 0.55))); return l.length > ca ? l.slice(0, ca - 1) + '\u2026' : l; } }, grid: { display: false }, barPercentage: 0.9, categoryPercentage: 1.0 },
                y: { beginAtZero: true, ticks: { color: CT().text, precision: 0, font: { family: 'Poppins', size: cfg.fontSize }, maxTicksLimit: cfg.ticksY }, grid: { color: CT().grid, drawBorder: false } }
            },
            animation: { duration: 800, easing: 'easeOutQuart' }
        }
    });
}

const productLabels = <?= json_encode(array_keys($topProducts), JSON_UNESCAPED_UNICODE) ?>;
const productQty    = <?= json_encode(array_map(function($d) { return (float)$d['qty']; }, array_values($topProducts))) ?>;
const productCanvas = document.getElementById('productChart');
let productChart    = null;

function buildProductChart() {
    if (!productCanvas || productLabels.length === 0) return;
    const cfg = getChartConfig();
    const maxDisplay = Math.min(5, productLabels.length);
    const displayLabels = productLabels.slice(0, maxDisplay);
    const displayData = productQty.slice(0, maxDisplay);
    if (productChart) productChart.destroy();
    productChart = new Chart(productCanvas, {
        type: 'line',
        data: { labels: displayLabels, datasets: [{ label: 'Quantity Sold', data: displayData, borderColor: '#0d9488', backgroundColor: 'rgba(13,148,136,0.12)', borderWidth: cfg.mobile ? 2 : 3, pointBackgroundColor: '#0d9488', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: cfg.pointR, pointHoverRadius: cfg.pointR + 2, tension: 0.4, fill: true, borderCapStyle: 'round', borderJoinStyle: 'round' }] },
        options: {
            responsive: true, maintainAspectRatio: false, clip: false,
            layout: { padding: { left: 10, right: 10, bottom: 20 } },
            plugins: { legend: { display: false }, tooltip: { ...tooltipDefaults, callbacks: { label: ctx => ' Qty: ' + ctx.raw } } },
            scales: {
                x: { ticks: { color: CT().text, font: { family: 'Poppins', size: cfg.fontSize }, maxRotation: 30, minRotation: 0, autoSkip: false, callback: function(v, i, ticks) { const l = this.getLabelForValue(v); if (typeof l !== 'string') return l; const cw = this.chart.width, tc = ticks.length || 1, px = cw / tc, ca = Math.max(5, Math.floor(px / (cfg.fontSize * 0.55))); return l.length > ca ? l.slice(0, ca - 1) + '\u2026' : l; } }, grid: { display: false } },
                y: { beginAtZero: true, ticks: { color: CT().text, precision: 0, font: { family: 'Poppins', size: cfg.fontSize }, maxTicksLimit: cfg.ticksY }, grid: { color: CT().grid, drawBorder: false } }
            },
            animation: { duration: 1000, easing: 'easeOutQuart' }
        }
    });
}

const refundChartData = <?= json_encode($refundChartData, JSON_UNESCAPED_UNICODE) ?>;
const refundCanvas = document.getElementById('refundChart');
let refundChart = null;

function buildRefundChart() {
    if (!refundCanvas || refundChartData.length === 0) return;
    const cfg = getChartConfig();
    const labels = refundChartData.map(d => d.label);
    const counts = refundChartData.map(d => d.count);
    const totals = refundChartData.map(d => d.total);
    if (refundChart) refundChart.destroy();
    refundChart = new Chart(refundCanvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Number of Refunds', data: counts, backgroundColor: 'rgba(139,92,246,0.6)', borderColor: '#8b5cf6', borderWidth: 1, borderRadius: 6, categoryPercentage: 0.55, barPercentage: 0.8, maxBarThickness: 54, yAxisID: 'y', order: 2 },
                { label: 'Refund Amount ($)', data: totals, type: 'line', borderColor: '#a78bfa', backgroundColor: 'rgba(167,139,250,0.1)', borderWidth: 2, pointBackgroundColor: '#a78bfa', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: cfg.pointR, pointHoverRadius: cfg.pointR + 3, tension: 0.4, fill: true, yAxisID: 'y1', order: 1 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false, clip: false,
            layout: { padding: { left: 10, right: 10, bottom: 20 } },
            plugins: {
                legend: { labels: { color: CT().text, font: { family: 'Poppins', size: 12 }, padding: 16 } },
                tooltip: { backgroundColor: 'rgba(18,18,18,0.95)', titleColor: '#fff', titleFont: { family: 'Poppins', size: 14, weight: '600' }, bodyColor: '#a78bfa', bodyFont: { family: 'Poppins', size: 13 }, borderColor: 'rgba(139,92,246,0.3)', borderWidth: 1, padding: 12, cornerRadius: 10, callbacks: { label: function(ctx) { return ctx.dataset.label === 'Number of Refunds' ? ' Refunds: ' + ctx.raw : ' Amount: $' + ctx.raw.toFixed(2); } } }
            },
            scales: {
                x: { ticks: { color: CT().text, font: { family: 'Poppins', size: cfg.fontSize }, maxRotation: 30, minRotation: 0, autoSkip: false }, grid: { display: false } },
                y: { type: 'linear', display: true, position: 'left', beginAtZero: true, ticks: { color: CT().text, font: { family: 'Poppins', size: cfg.fontSize }, precision: 0, maxTicksLimit: cfg.ticksY }, grid: { color: CT().grid, drawBorder: false }, title: { display: true, text: 'Number of Refunds', color: CT().text, font: { family: 'Poppins', size: 11 } } },
                y1: { type: 'linear', display: true, position: 'right', beginAtZero: true, ticks: { color: CT().text, font: { family: 'Poppins', size: cfg.fontSize }, callback: function(value) { return '$' + value.toFixed(2); }, maxTicksLimit: cfg.ticksY }, grid: { display: false }, title: { display: true, text: 'Refund Amount ($)', color: CT().text, font: { family: 'Poppins', size: 11 } } }
            },
            animation: { duration: 1000, easing: 'easeOutQuart' }
        }
    });
}

let hourlyRawData = <?= json_encode($hourlyData) ?>;
const hourlyCanvas  = document.getElementById('hourlyChart');
let hourlyChart     = null;

function buildHourlyChart() {
    if (!hourlyCanvas || !hourlyRawData.length) return;
    const cfg = getChartConfig();
    const labels  = hourlyRawData.map(d => d.label);
    const counts  = hourlyRawData.map(d => d.count);
    const revenue = hourlyRawData.map(d => d.revenue);
    if (hourlyChart) hourlyChart.destroy();
    const maxRev = Math.max(...revenue);
    const bgColors = revenue.map(v => v === maxRev && v > 0 ? 'rgba(13,148,136,1)' : 'rgba(13,148,136,0.5)');
    hourlyChart = new Chart(hourlyCanvas, {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Revenue ($)', data: revenue, backgroundColor: bgColors, borderColor: '#0d9488', borderWidth: 1, borderRadius: 6, yAxisID: 'y', order: 2 }, { label: 'Orders', data: counts, type: 'line', borderColor: '#5eead4', backgroundColor: 'rgba(94,234,212,0.08)', borderWidth: 2, pointBackgroundColor: '#5eead4', pointRadius: cfg.pointR - 1, pointHoverRadius: cfg.pointR + 1, tension: 0.4, fill: true, yAxisID: 'y1', order: 1 }] },
        options: {
            responsive: true, maintainAspectRatio: false, clip: false,
            layout: { padding: { left: 8, right: 8, bottom: 8 } },
            plugins: { legend: { labels: { color: CT().text, font: { family: 'Poppins', size: 11 }, padding: 12 } }, tooltip: { ...tooltipDefaults, callbacks: { label: ctx => ctx.dataset.label === 'Orders' ? ' Orders: ' + ctx.raw : ' Revenue: $' + ctx.raw.toFixed(2) } } },
            scales: {
                x: { ticks: { color: CT().text, font: { family: 'Poppins', size: cfg.fontSize }, maxRotation: 0, minRotation: 0, autoSkip: true, maxTicksLimit: 9 }, grid: { display: false } },
                y: { position: 'left', beginAtZero: true, ticks: { color: CT().text, font: { family: 'Poppins', size: cfg.fontSize }, callback: v => '$' + v.toFixed(0), maxTicksLimit: 5 }, grid: { color: CT().grid } },
                y1: { position: 'right', beginAtZero: true, ticks: { color: CT().text, font: { family: 'Poppins', size: cfg.fontSize }, precision: 0, maxTicksLimit: 5 }, grid: { display: false } }
            },
            animation: { duration: 900, easing: 'easeOutQuart' }
        }
    });
}

const topHoursCanvas = document.getElementById('topHoursChart');
let topHoursChart  = null;

function buildTopHoursChart() {
    if (!topHoursCanvas || !hourlyRawData.length) return;
    const cfg = getChartConfig();
    const active = hourlyRawData.filter(d => d.revenue > 0 || d.count > 0).sort((a, b) => b.revenue - a.revenue).slice(0, 5);
    if (!active.length) return;
    const labels = active.map(d => d.label);
    const revs   = active.map(d => d.revenue);
    const cts    = active.map(d => d.count);
    const maxRev = Math.max(...revs);
    const colors = revs.map(v => v === maxRev ? 'rgba(94,234,212,0.95)' : 'rgba(13,148,136,0.65)');
    if (topHoursChart) topHoursChart.destroy();
    topHoursChart = new Chart(topHoursCanvas, {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Revenue ($)', data: revs, backgroundColor: colors, borderColor: '#0d9488', borderWidth: 1, borderRadius: 6 }] },
        options: {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            layout: { padding: { right: 8 } },
            plugins: { legend: { display: false }, tooltip: { ...tooltipDefaults, callbacks: { label: ctx => { const h = active[ctx.dataIndex]; return [' Revenue: $' + ctx.raw.toFixed(2), ' Orders:  ' + h.count]; } } } },
            scales: {
                x: { beginAtZero: true, ticks: { color: CT().text, font: { family: 'Poppins', size: cfg.fontSize }, callback: v => '$' + v.toFixed(0), maxTicksLimit: 5 }, grid: { color: CT().grid } },
                y: { ticks: { color: '#5eead4', font: { family: 'Poppins', size: cfg.fontSize + 1, weight: '600' } }, grid: { display: false } }
            },
            animation: { duration: 800, easing: 'easeOutQuart' }
        }
    });
}

const dailyTrendRaw    = <?= json_encode($dailyTrendData) ?>;
const dailyTrendCanvas = document.getElementById('dailyTrendChart');
let dailyTrendChart  = null;

function buildDailyTrendChart() {
    if (!dailyTrendCanvas || !dailyTrendRaw.length) return;
    const cfg = getChartConfig();
    const labels  = dailyTrendRaw.map(d => d.label);
    const counts  = dailyTrendRaw.map(d => d.count);
    const revenue = dailyTrendRaw.map(d => d.revenue);
    if (dailyTrendChart) dailyTrendChart.destroy();
    const maxRev = Math.max(...revenue);
    const bgColors = revenue.map(v => v === maxRev && v > 0 ? 'rgba(13,148,136,1)' : 'rgba(13,148,136,0.55)');
    const maxLabels = Math.min(labels.length, cfg.mobile ? 8 : 14);
    dailyTrendChart = new Chart(dailyTrendCanvas, {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Revenue ($)', data: revenue, backgroundColor: bgColors, borderColor: '#0d9488', borderWidth: 1, borderRadius: 6, yAxisID: 'y', order: 2 }, { label: 'Orders', data: counts, type: 'line', borderColor: '#5eead4', backgroundColor: 'rgba(94,234,212,0.08)', borderWidth: 2, pointBackgroundColor: '#5eead4', pointRadius: cfg.pointR - 1, pointHoverRadius: cfg.pointR + 1, tension: 0.4, fill: true, yAxisID: 'y1', order: 1 }] },
        options: {
            responsive: true, maintainAspectRatio: false, clip: false,
            layout: { padding: { left: 8, right: 8, bottom: 8 } },
            plugins: { legend: { labels: { color: CT().text, font: { family: 'Poppins', size: 11 }, padding: 12 } }, tooltip: { ...tooltipDefaults, callbacks: { label: ctx => ctx.dataset.label === 'Orders' ? ' Orders: ' + ctx.raw : ' Revenue: $' + ctx.raw.toFixed(2) } } },
            scales: {
                x: { ticks: { color: CT().text, font: { family: 'Poppins', size: cfg.fontSize }, maxRotation: 30, minRotation: 0, autoSkip: true, maxTicksLimit: maxLabels }, grid: { display: false } },
                y: { position: 'left', beginAtZero: true, ticks: { color: CT().text, font: { family: 'Poppins', size: cfg.fontSize }, callback: v => '$' + v.toFixed(0), maxTicksLimit: 5 }, grid: { color: CT().grid } },
                y1: { position: 'right', beginAtZero: true, ticks: { color: CT().text, font: { family: 'Poppins', size: cfg.fontSize }, precision: 0, maxTicksLimit: 5 }, grid: { display: false } }
            },
            animation: { duration: 900, easing: 'easeOutQuart' }
        }
    });
}

const paymentRawData = <?= json_encode($paymentMethods) ?>;
const paymentCanvas  = document.getElementById('paymentChart');
let paymentChart     = null;

function buildPaymentChart() {
    if (!paymentCanvas || !paymentRawData.length) return;
    if (paymentChart) paymentChart.destroy();
    const labels   = paymentRawData.map(d => d.method);
    const revenues = paymentRawData.map(d => d.revenue);
    const palette  = ['#0d9488', '#3b82f6', '#8b5cf6', '#10b981', '#eab308'];
    paymentChart = new Chart(paymentCanvas, {
        type: 'doughnut',
        data: { labels, datasets: [{ data: revenues, backgroundColor: palette.slice(0, labels.length), borderWidth: 2, borderColor: CT().surface, hoverOffset: 8 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '62%',
            plugins: { legend: { position: 'bottom', labels: { color: CT().text, font: { family: 'Poppins', size: 12 }, padding: 14 } }, tooltip: { ...tooltipDefaults, callbacks: { label: ctx => ' $' + ctx.raw.toFixed(2) } } },
            animation: { duration: 900, animateRotate: true, easing: 'easeOutQuart' }
        }
    });
}

function exportPDF() {
    const params = new URLSearchParams(window.location.search);
    window.open('report_pdf.php?' + params.toString(), '_blank');
}

function exportCSV() {
    const rows = [['Product','Qty Sold','Revenue','COGS','Profit','Margin %']];
    const tbl  = document.getElementById('productsTable');
    if (tbl) {
        const trs = tbl.querySelectorAll('tbody tr');
        trs.forEach(tr => {
            const cells = tr.querySelectorAll('td');
            if (cells.length >= 7) {
                rows.push([cells[1].textContent.trim(), cells[2].textContent.trim(), cells[3].textContent.trim(), cells[4].textContent.trim(), cells[5].textContent.trim(), cells[6].textContent.trim()]);
            }
        });
    }
    rows.push([]);
    rows.push(['Metric', 'Value']);
    rows.push(['Orders', <?= $orderCount ?>]);
    rows.push(['Sales', '$<?= fmtMoney($totalSales) ?>']);
    rows.push(['Food Cost', '$<?= fmtMoney($totalCOGS) ?>']);
    rows.push(['Profit', '$<?= fmtMoney($totalProfit) ?>']);
    rows.push(['Profit Margin', '<?= number_format($margin, 1) ?>%']);
    rows.push(['Avg per Order', '$<?= fmtMoney($avgOrder) ?>']);
    rows.push(['Refunds', '$<?= fmtMoney($totalRefunded) ?>']);
    rows.push(['Net Revenue', '$<?= fmtMoney($netRevenue) ?>']);
    rows.push(['Remakes', <?= (int)$remakeCount ?>]);
    rows.push(['Peak Hour', <?= json_encode((string)$peakHour) ?>]);
    const csv  = rows.map(r => r.map(c => '"' + String(c).replace(/"/g,'""') + '"').join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = 'report_<?= preg_replace("/[^a-z0-9]/i", "_", $label) ?>.csv';
    a.click(); URL.revokeObjectURL(url);
}

function exportExcel() {
    exportCSV();
}

let sortState = { col: -1, dir: 1 };

const __pagers = {};
function setupPager(tableId, pagerId, pageSize) {
    const tbl = document.getElementById(tableId);
    const pager = document.getElementById(pagerId);
    if (!tbl || !pager) return;
    __pagers[tableId] = { pagerId, pageSize, page: 1 };
    renderPagerPage(tableId);
}
function renderPagerPage(tableId) {
    const st = __pagers[tableId];
    if (!st) return;
    const tbl = document.getElementById(tableId);
    const rows = Array.from(tbl.querySelector('tbody').querySelectorAll('tr'));
    const pages = Math.max(1, Math.ceil(rows.length / st.pageSize));
    if (st.page > pages) st.page = pages;
    const start = (st.page - 1) * st.pageSize, end = start + st.pageSize;
    rows.forEach((r, i) => { r.style.display = (i >= start && i < end) ? '' : 'none'; });
    buildPagerControls(tableId, pages);
}
function buildPagerControls(tableId, pages) {
    const st = __pagers[tableId];
    const pager = document.getElementById(st.pagerId);
    if (pages <= 1) { pager.innerHTML = ''; return; }
    const p = st.page;
    const btn = (label, target, o = {}) => o.ellipsis ? '<span class="rp-ellipsis">&hellip;</span>' : `<button class="rp-btn${o.active ? ' active' : ''}" ${o.disabled ? 'disabled' : ''} data-go="${target}">${label}</button>`;
    let html = btn('\u00ab', 1, { disabled: p === 1 }) + btn('\u2039', p - 1, { disabled: p === 1 });
    const win = [];
    if (pages <= 7) { for (let i = 1; i <= pages; i++) win.push(i); }
    else {
        win.push(1);
        let s = Math.max(2, p - 1), e = Math.min(pages - 1, p + 1);
        if (s > 2) win.push('...');
        for (let i = s; i <= e; i++) win.push(i);
        if (e < pages - 1) win.push('...');
        win.push(pages);
    }
    win.forEach(n => { html += n === '...' ? btn('', 0, { ellipsis: true }) : btn(n, n, { active: n === p }); });
    html += btn('\u203a', p + 1, { disabled: p === pages }) + btn('\u00bb', pages, { disabled: p === pages });
    pager.innerHTML = html;
    pager.querySelectorAll('button[data-go]').forEach(b => {
        b.addEventListener('click', () => { const g = parseInt(b.dataset.go); if (g >= 1 && g <= pages) { st.page = g; renderPagerPage(tableId); } });
    });
}
function pagerResetToFirst(tableId) {
    if (__pagers[tableId]) { __pagers[tableId].page = 1; renderPagerPage(tableId); }
}
document.addEventListener('DOMContentLoaded', function () {
    setupPager('productsTable', 'productsPager', 10);
    setupPager('refundTable',   'refundPager',   10);
    setupPager('remakeTable',   'remakePager',   10);
});

function sortTable(colIdx, type) {
    const tbl  = document.getElementById('productsTable');
    if (!tbl) return;
    const tbody = tbl.querySelector('tbody');
    const rows  = Array.from(tbody.querySelectorAll('tr'));
    if (sortState.col === colIdx) { sortState.dir *= -1; } else { sortState.col = colIdx; sortState.dir = 1; }
    const parse = (cell, t) => { const txt = cell.textContent.trim().replace(/[$%+,]/g, ''); if (t === 'num' || t === 'money' || t === 'pct') return parseFloat(txt) || 0; return txt.toLowerCase(); };
    rows.sort((a, b) => { const ca = a.querySelectorAll('td')[colIdx]; const cb = b.querySelectorAll('td')[colIdx]; const va = parse(ca, type), vb = parse(cb, type); return va < vb ? -sortState.dir : va > vb ? sortState.dir : 0; });
    rows.forEach((r, i) => { const rankCell = r.querySelector('td:first-child'); if (rankCell) rankCell.textContent = i + 1; });
    rows.forEach(r => tbody.appendChild(r));
    pagerResetToFirst('productsTable');
    tbl.querySelectorAll('thead th').forEach((th, i) => { const ico = th.querySelector('i'); if (!ico) return; ico.className = i === colIdx ? (sortState.dir === 1 ? 'fa-solid fa-sort-down' : 'fa-solid fa-sort-up') : 'fa-solid fa-sort'; ico.style.opacity = i === colIdx ? '0.9' : '0.5'; });
}

function whenChartReady(cb) {
    if (typeof Chart !== 'undefined') { cb(); return; }
    var iv = setInterval(function() { if (typeof Chart !== 'undefined') { clearInterval(iv); cb(); } }, 30);
    setTimeout(function() { clearInterval(iv); if (typeof Chart === 'undefined') cb(); }, 8000);
}

function rebuildAllCharts() {
    buildCategoryChart();
    buildProductChart();
    buildRefundChart();
    buildHourlyChart();
    buildTopHoursChart();
    buildPaymentChart();
    buildDailyTrendChart();
}

whenChartReady(rebuildAllCharts);

let resizeTimer;
window.addEventListener('resize', () => { clearTimeout(resizeTimer); resizeTimer = setTimeout(rebuildAllCharts, 250); });

const LIVE_MODE    = <?= json_encode($isLive) ?>;
const REPORT_PARAMS = <?= json_encode(['mode' => $mode, 'date' => $date ?? null, 'month' => $month ?? null, 'from_date' => $fromDate ?? null, 'to_date' => $toDate ?? null]) ?>;

function fmt2(n) { return Number(n).toFixed(2); }

function flash(el) {
    if (!el) return;
    el.classList.remove('kpi-flash');
    void el.offsetWidth;
    el.classList.add('kpi-flash');
}

function setEl(id, text, parentForFlash) {
    const el = document.getElementById(id);
    if (!el || el.textContent === text) return;
    el.textContent = text;
    flash(parentForFlash || el.closest('.kpi-card') || el);
}

function applyLiveData(d) {
    setEl('kv-total-order', '$' + fmt2(d.totalSales));
    setEl('kv-avg',         '$' + fmt2(d.avgOrder));
    setEl('kv-sales',       '$' + fmt2(d.totalSales));
    setEl('kv-net-sales',   '$' + fmt2(d.netRevenue));
    setEl('kv-unpaid',      '$' + fmt2(d.unpaidAmount ?? 0));
    setEl('kv-refunds',     '$' + fmt2(d.totalRefunded));
    setEl('kv-discount',    '$' + fmt2(d.totalDiscount ?? 0));
    setEl('kv-profit',      '$' + fmt2(d.totalProfit));

    const mb = document.getElementById('kv-margin');
    if (mb) {
        const newText = d.margin + '%';
        if (mb.textContent.trim() !== newText) {
            mb.textContent = newText;
            flash(mb.closest('.kpi-card'));
        }
    }

    // KV-refcount removed in new design - live update not needed

    // kv-peak removed in new design

    if (d.topProduct) {
        const bsv = document.getElementById('ic-bestseller-val');
        if (bsv) bsv.textContent = d.topProduct + ' \u2014 ' + d.topProductQty + ' sold';
    }
    if (d.topCategory) {
        const tcv = document.getElementById('ic-topcat-val');
        if (tcv) tcv.textContent = d.topCategory + ' \u2014 ' + d.topCategoryQty + ' items';
    }
    const mgv = document.getElementById('ic-margin-val');
    if (mgv) {
        mgv.textContent = d.margin + '% \u2014 ' + (d.margin >= 30 ? 'healthy' : 'below 30% target');
        const mc = document.getElementById('ic-margin');
        if (mc) mc.className = 'insight-chip ' + (d.margin >= 30 ? 'ic-good' : 'ic-warn');
    }
    if (d.peakHour) {
        const pkv = document.getElementById('ic-peak-val');
        if (pkv) pkv.textContent = d.peakHour + ' \u2014 most orders this hour';
    }
    if (d.refundCount > 0) {
        const rrv = document.getElementById('ic-refrate-val');
        if (rrv) rrv.textContent = d.refundRate + '% of orders \u2014 ' + d.refundCount + ' refunded';
    }

    const sumEl = document.getElementById('live-summary');
    if (sumEl && d.orderCount > 0) {
        const marginHealth = d.margin >= 50 ? 'excellent' : d.margin >= 30 ? 'healthy' : 'below the 30% target';
        const marginCls    = d.margin >= 50 ? 'good' : d.margin >= 30 ? 'ok' : 'warn';
        const refNote = d.refundCount > 0 ? ' <strong>' + d.refundCount + '</strong> ' + (d.refundCount === 1 ? 'order was' : 'orders were') + ' refunded, totalling <strong>$' + fmt2(d.totalRefunded) + '</strong>, bringing take-home to <strong>$' + fmt2(d.netRevenue) + '</strong>.' : ' No refunds were issued, so the full <strong>$' + fmt2(d.netRevenue) + '</strong> is the take-home revenue.';
        const pkNote  = d.peakHour ? ' The busiest sales hour was <strong>' + d.peakHour + '</strong>.' : '';
        const tpNote  = d.topProduct ? ' Best-selling item: <strong>' + d.topProduct + '</strong> with <strong>' + d.topProductQty + '</strong> ' + (d.topProductQty === 1 ? 'unit' : 'units') + ' sold.' : '';
        const itemsNote = (d.totalItemsSold > 0) ? ', serving <strong>' + d.totalItemsSold + '</strong> items,' : '';
        sumEl.innerHTML = 'During <strong>' + sumEl.dataset.label + '</strong>, the caf\u00e9 completed <strong>' + d.orderCount + '</strong> ' + (d.orderCount === 1 ? 'order' : 'orders') + itemsNote + ' totalling <strong>$' + fmt2(d.totalSales) + '</strong> in sales (avg <strong>$' + fmt2(d.avgOrder) + '</strong> per order). After ingredient costs of <strong>$' + fmt2(d.totalCOGS) + '</strong>, the gross profit was <strong>$' + fmt2(d.totalProfit) + '</strong> \u2014 a margin of <strong class="' + marginCls + '">' + d.margin + '%</strong>, which is ' + marginHealth + '.' + refNote + pkNote + tpNote;
    }

    if (d.hourlyData && d.hourlyData.length && typeof hourlyChart !== 'undefined' && hourlyChart) {
        hourlyRawData = d.hourlyData;
        const revenue = d.hourlyData.map(h => h.revenue);
        const counts  = d.hourlyData.map(h => h.count);
        const maxRev  = Math.max(...revenue);
        const bgColors = revenue.map(v => v > 0 && v === maxRev ? 'rgba(94,234,212,0.95)' : 'rgba(13,148,136,0.65)');
        hourlyChart.data.datasets[0].data = revenue;
        hourlyChart.data.datasets[0].backgroundColor = bgColors;
        hourlyChart.data.datasets[1].data = counts;
        hourlyChart.update('none');
        buildTopHoursChart();
    }

    const goalFill = document.getElementById('goal-fill');
    if (goalFill) {
        const target = <?= json_encode($dailyTarget) ?>;
        const pct = target > 0 ? Math.min(100, d.totalSales / target * 100) : 0;
        goalFill.style.width = pct.toFixed(1) + '%';
        const gc = document.getElementById('goal-current');
        if (gc) gc.textContent = fmt2(d.totalSales);
        const gp = document.getElementById('goal-pct');
        if (gp) gp.textContent = Math.round(pct) + '%';
    }

    document.title = 'Report \u2014 $' + fmt2(d.totalSales) + ' \u00b7 Caf\u00e9';

    const ts = document.getElementById('live-ts');
    if (ts) ts.textContent = d.ts;
}

if (LIVE_MODE) {
    const sumEl = document.getElementById('live-summary');
    if (sumEl) sumEl.dataset.label = <?= json_encode($label) ?>;

    const liveInterval = <?= $mode === 'daily' ? 30000 : 60000 ?>;

    let _pollFails = 0;

    function _setDotState(ok) {
        const dot = document.getElementById('live-dot');
        const ts  = document.getElementById('live-ts');
        if (!dot) return;
        if (ok) {
            dot.style.background = '';
            dot.classList.add('pulsing');
        } else {
            dot.style.background = '#f0b45a';
            dot.classList.remove('pulsing');
            if (ts && !ts.textContent.startsWith('reconnecting')) ts.textContent = 'reconnecting\u2026';
        }
    }

    function pollLive() {
        if (document.hidden) return;
        const url = new URL('report_live.php', window.location.href);
        Object.entries(REPORT_PARAMS).forEach(([k,v]) => { if (v !== null) url.searchParams.set(k, v); });
        fetch(url)
            .then(r => r.ok ? r.json() : null)
            .then(d => { if (d && !d.error) { _pollFails = 0; _setDotState(true); applyLiveData(d); } else { _pollFails++; if (_pollFails >= 3) _setDotState(false); } })
            .catch(() => { _pollFails++; if (_pollFails >= 3) _setDotState(false); });
    }

    setInterval(pollLive, liveInterval);
}
</script>
<script src="animations.js"></script>
