<?php
declare(strict_types=1);
/* Orders board — table row rendering + client-side filtering + stat cards.
   Expects in scope: $_all_roles (slug => ['name' => ...]). */
?>
<script>
const tbody = document.getElementById("ordersBody");
const known = new Set();
let currentFilter = '<?= ($_SESSION['role'] ?? '') === 'staff' ? 'PendingPayment' : 'all' ?>';
let searchQuery = '';

// ── Get user role from PHP ──
const userRole = "<?= $_SESSION['role'] ?? 'staff' ?>";
const isAdmin = userRole === 'admin';
const canManageOrders = userRole === 'admin' || userRole === 'manager';
const canRemake = userRole === 'admin' || userRole === 'manager' || userRole === 'staff';

// ── Play Sound ──
function play(id) {
    const a = document.getElementById(id);
    if (a) { a.currentTime = 0; a.play().catch(() => {}); }
}

// ── Escape HTML ──
function escapeHtml(text) {
    return String(text ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function truncReason(text, max = 120) {
    const s = String(text ?? '').trim();
    return s.length > max ? s.slice(0, max) + '…' : s;
}

function roleLabel(role) {
    const map = <?= json_encode(array_map(fn($r) => $r['name'], $_all_roles), JSON_UNESCAPED_UNICODE) ?>;
    return map[role] || role;
}

// ── Get Time Ago ──
function timeAgo(dateString) {
    const now = new Date();
    const past = new Date(dateString);
    const diff = Math.floor((now - past) / 1000);

    if (diff < 60) return diff + ' sec ago';
    if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
    return Math.floor(diff / 86400) + ' day ago';
}

// ── Status / payment pill helpers ──
function statusPill(status) {
    const map = {
        PendingPayment: ['New', 'tone-amber'],
        Paid:           ['Open', 'tone-blue'],
        Preparing:      ['Open', 'tone-blue'],
        Completed:      ['Completed', ''],
        Cancelled:      ['Cancelled', 'tone-red'],
        Refunded:       ['Refunded', 'tone-purple'],
    };
    const [label, tone] = map[status] || [status, ''];
    return `<span class="ob-pill ${tone}">${label}</span>`;
}

function paymentPill(paymentStatus) {
    return paymentStatus === 'unpaid'
        ? `<span class="ob-pill tone-amber">Unpaid</span>`
        : `<span class="ob-pill tone-green">Paid</span>`;
}

function methodLabel(method) {
    if (!method) return '-';
    const map = { cash: 'Cash', bakong: 'Bakong', paylater: 'Pay Later', riel: 'Riel' };
    return map[method] || escapeHtml(method);
}

// ── Build Table Row ──
function buildRowInner(o) {
    return `
        <td class="ob-order-no">#${escapeHtml(String(o.daily_order_no))}</td>
        <td>${escapeHtml(o.customer_name || 'Guest')}</td>
        <td class="ob-muted">${o.phone ? escapeHtml(o.phone) : '-'}</td>
        <td class="ob-muted">${o.table_number ? escapeHtml(String(o.table_number)) : '-'}</td>
        <td class="right">${(o.items || []).reduce((n, i) => n + Number(i.quantity || 0), 0)}</td>
        <td class="right"><strong>$${parseFloat(o.total || 0).toFixed(2)}</strong></td>
        <td class="ob-muted" data-timestamp="${escapeHtml(o.order_date)}">${timeAgo(o.order_date)}</td>
        <td>${statusPill(o.status)}</td>
        <td>${paymentPill(o.payment_status)}</td>
        <td class="ob-muted">${methodLabel(o.payment_method)}</td>
        <td class="ob-muted">${escapeHtml(o.employee_name || '-')}</td>
        <td class="right"><button class="btn-view-detail" onclick="openOrderDetail(${Number(o.order_id)})"><i class="fa-solid fa-eye"></i> View Detail</button></td>
    `;
}

// ── Add Row ──
function addRow(o) {
    const row = document.createElement("tr");
    row.id = "row-" + o.order_id;
    row.dataset.status = o.status;
    row.dataset.orderId = o.order_id;
    row.dataset.employee = o.employee_name || '';
    row.innerHTML = buildRowInner(o);
    tbody.appendChild(row);
}

// ── Update Existing Row ──
function updateExistingRow(o) {
    const row = document.getElementById("row-" + o.order_id);
    if (!row) return;

    // Play bell when a remade order transitions back to Preparing
    if (o.status === 'Preparing' && o.remake_count > 0 && row.dataset.status !== 'Preparing') {
        play('bell');
    }

    row.dataset.status = o.status;
    row.dataset.employee = o.employee_name || '';
    row.innerHTML = buildRowInner(o);

    // Refresh the detail modal in place if it's currently showing this order
    if (currentDetailId === o.order_id) renderOrderDetail(o);
}

// ── Populate "All Staff" dropdown from the loaded orders ──
function populateStaffFilter(data) {
    const sel = document.getElementById('staffFilter');
    const current = sel.value;
    const names = Array.from(new Set(data.map(o => o.employee_name).filter(Boolean))).sort();
    sel.innerHTML = '<option value="">All Staff</option>' +
        names.map(n => `<option value="${escapeHtml(n)}">${escapeHtml(n)}</option>`).join('');
    if (names.includes(current)) sel.value = current;
}

// ── Apply Filters ──
function applyFilters() {
    const rows = document.querySelectorAll('#ordersBody tr[data-order-id]');
    const query = searchQuery.toLowerCase().trim();
    const staff = document.getElementById('staffFilter')?.value || '';

    rows.forEach(row => {
        const status = row.dataset.status;
        let visible = true;

        if (query) {
            visible = row.textContent.toLowerCase().includes(query);
        } else if (currentFilter !== 'all' && status !== currentFilter) {
            visible = false;
        }

        if (visible && staff && row.dataset.employee !== staff) visible = false;

        row.style.display = visible ? '' : 'none';
    });

    const anyVisible = Array.from(rows).some(r => r.style.display !== 'none');
    let emptyEl = document.getElementById('ordersEmptyState');
    if (!anyVisible && rows.length > 0) {
        if (!emptyEl) {
            emptyEl = document.createElement('tr');
            emptyEl.id = 'ordersEmptyState';
            emptyEl.innerHTML = '<td colspan="12"><div class="ob-empty"><i class="fa-regular fa-rectangle-list"></i><h3>No Orders</h3><p>No orders match the current filter.</p></div></td>';
            tbody.appendChild(emptyEl);
        }
    } else if (emptyEl) {
        emptyEl.remove();
    }

    updateStatCards();
}

// ── Filter Status ──
function filterStatus(status) {
    currentFilter = status;
    document.querySelectorAll('.status-tab').forEach(tab => {
        tab.classList.toggle('active', tab.dataset.status === status);
    });
    applyFilters();
}

// ── Update tab counts (always reflects the full loaded set, not the filter) ──
function updateCounts(data) {
    const counts = { all: 0, PendingPayment: 0, Paid: 0, Preparing: 0, Completed: 0, Cancelled: 0, Refunded: 0 };
    data.forEach(o => {
        counts.all++;
        if (counts[o.status] !== undefined) counts[o.status]++;
    });
    const setCount = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    Object.keys(counts).forEach(k => setCount('count-' + k, counts[k]));
}

// ── Stat cards: computed from whatever rows are currently visible (search + filters applied) ──
function updateStatCards() {
    const rows = Array.from(document.querySelectorAll('#ordersBody tr[data-order-id]')).filter(r => r.style.display !== 'none');
    const ids = rows.map(r => Number(r.dataset.orderId));
    const shown = allOrders.filter(o => ids.includes(o.order_id));

    const paid      = shown.filter(o => o.payment_status === 'paid').length;
    const unpaid    = shown.filter(o => o.payment_status === 'unpaid').length;
    const refunded  = shown.filter(o => o.status === 'Refunded').length;
    const revenue   = shown.filter(o => o.status !== 'Cancelled' && o.status !== 'Refunded')
                           .reduce((sum, o) => sum + parseFloat(o.total || 0), 0);

    const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setText('stat-shown', shown.length);
    setText('stat-paid', paid);
    setText('stat-unpaid', unpaid);
    setText('stat-refunded', refunded);
    setText('stat-revenue', '$' + revenue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
}
</script>
