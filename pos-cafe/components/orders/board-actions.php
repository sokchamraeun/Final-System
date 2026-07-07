<?php
declare(strict_types=1);
/* Orders board — data loading, order-detail rendering, order actions
   (call/paid/complete/cancel/refund/remake/delete), search, real-time
   socket, keyboard shortcuts, init.
   Expects in scope: $_socketAvailable, $_flash_welcome. Uses the global
   SOCKET_URL constant defined by the page controller. */
?>
<script>
const BOARD_API  = <?= json_encode(url('api/orders-board.php')) ?>;
const CANCEL_URL = <?= json_encode(url('cancel_order.php')) ?>;
const REFUND_URL = <?= json_encode(url('refund_order.php')) ?>;
const REMAKE_URL = <?= json_encode(url('remake_order.php')) ?>;

let currentCancelId = 0;
let currentRefundId = 0;
let currentRemakeId = 0;
let currentDetailId = 0;
let allOrders = [];

// ── Load Orders ──
async function loadOrders() {
    try {
        const date = document.getElementById('dateFilter')?.value || '';
        const url = date ? `${BOARD_API}?action=fetch&date=${date}` : `${BOARD_API}?action=fetch`;
        const r = await fetch(url, { cache: "no-store" });
        const raw = await r.json();

        const data = Array.isArray(raw) ? raw : (raw.orders || []);
        allOrders = data;
        if (raw.announcements !== undefined) updateAnnouncements(raw.announcements);

        const currentIds = new Set();

        if (data.length === 0) {
            if (tbody.children.length === 0) {
                const empty = document.createElement("tr");
                empty.id = "ordersEmptyState";
                empty.innerHTML = '<td colspan="12"><div class="ob-empty"><i class="fa-regular fa-rectangle-list"></i><h3>No Orders Yet</h3><p>Orders will appear here in real-time.</p></div></td>';
                tbody.appendChild(empty);
            }
            updateStatCards();
            return;
        }

        const emptyEl = document.getElementById('ordersEmptyState');
        if (emptyEl) emptyEl.remove();

        updateCounts(data);
        populateStaffFilter(data);

        data.forEach(o => {
            const id = String(o.order_id);
            currentIds.add(id);
            if (known.has(id)) {
                updateExistingRow(o);
            } else {
                known.add(id);
                addRow(o);
            }
        });

        Array.from(known).forEach(id => {
            if (!currentIds.has(id)) {
                const row = document.getElementById("row-" + id);
                if (row) {
                    row.classList.add("fade-out");
                    setTimeout(() => { row.remove(); known.delete(id); }, 400);
                }
            }
        });

        applyFilters();
    } catch (err) {
        console.error("Fetch failed:", err);
    }
}

// ── Clear Local ──
function clearLocalView() {
    document.getElementById('searchInput').value = '';
    searchQuery = '';
    document.getElementById('staffFilter').value = '';
    document.getElementById('dateFilter').value = '';
    try { localStorage.removeItem('ann_dismissed'); } catch (e) {}
    filterStatus(userRole === 'staff' ? 'PendingPayment' : (userRole === 'barista' ? 'Preparing' : 'all'));
    loadOrders();
}

// ── Order Detail ──
function getActionButtonsHtml(o) {
    let buttons = '';

    if (o.status === 'Paid' || o.status === 'Preparing') {
        buttons += `<button class="call-btn" onclick="callOrder(${Number(o.order_id)}, '${escapeHtml(o.customer_name)}', ${Number(o.daily_order_no)})"><i class="fa-solid fa-bell"></i> Call</button>`;
    }
    if (o.status === 'PendingPayment' && userRole !== 'barista') {
        buttons += `<button class="paid-btn" onclick="markPaid(${Number(o.order_id)})"><i class="fa-solid fa-credit-card"></i> Paid</button>`;
    }
    if (o.status === 'Preparing') {
        buttons += `<button class="complete-btn" onclick="completeOrder(${Number(o.order_id)})"><i class="fa-solid fa-check"></i> Complete</button>`;
    }
    const canCancel = o.status !== 'Completed' && o.status !== 'Cancelled' && o.status !== 'Refunded' && userRole !== 'barista'
        && (userRole !== 'staff' || o.status === 'PendingPayment');
    if (canCancel) {
        buttons += `<button class="cancel-btn" onclick="showCancelModal(${Number(o.order_id)}, ${Number(o.daily_order_no)})"><i class="fa-solid fa-ban"></i> Cancel</button>`;
    }
    if (o.status === 'Completed') {
        if (canManageOrders && !o.is_remade) {
            buttons += `<button class="refund-btn" onclick="showRefundModal(${Number(o.order_id)}, ${Number(o.daily_order_no)}, ${parseFloat(o.total).toFixed(2)})"><i class="fa-solid fa-rotate-left"></i> Refund</button>`;
        }
        if (canRemake) {
            buttons += `<button class="remake-btn" onclick="showRemakeModal(${Number(o.order_id)}, ${Number(o.daily_order_no)})"><i class="fa-solid fa-repeat"></i> Remake</button>`;
        }
    }
    if ((o.status === 'PendingPayment' || o.status === 'Cancelled') && isAdmin) {
        buttons += `<button class="delete-btn" onclick="removeOrder(${Number(o.order_id)})"><i class="fa-solid fa-trash-can"></i></button>`;
    }

    return buttons || `<span class="ob-muted" style="font-size:12px;">No actions available</span>`;
}

function buildItemsDetail(items) {
    if (!items || items.length === 0) return '<div class="ob-muted" style="font-size:13px;">No items</div>';
    return items.map(i => {
        const chips = [];
        if (i.size)      chips.push(`<span class="detail-item-chip">Size: ${escapeHtml(i.size)}</span>`);
        if (i.sweetness) chips.push(`<span class="detail-item-chip">${escapeHtml(i.sweetness)}</span>`);
        if (i.ice)       chips.push(`<span class="detail-item-chip">${escapeHtml(i.ice)}</span>`);
        if (i.milk)      chips.push(`<span class="detail-item-chip">${escapeHtml(i.milk)}</span>`);
        return `<div class="detail-item-line">
            <div class="detail-item-qty">×${escapeHtml(String(i.quantity))}</div>
            <div>
                <div class="detail-item-name">${escapeHtml(i.product_name)}</div>
                ${chips.length ? `<div class="detail-item-chips">${chips.join('')}</div>` : ''}
            </div>
        </div>`;
    }).join('');
}

function renderOrderDetail(o) {
    document.getElementById('detailOrderNumber').textContent =
        `#${o.daily_order_no} — ${o.customer_name || 'Guest'}`;

    let reasonHtml = '';
    if (o.status === 'Cancelled' && o.cancel_reason) {
        reasonHtml += `<div class="detail-reason cancel-reason"><i class="fa-solid fa-ban"></i> ${escapeHtml(truncReason(o.cancel_reason))}${o.cancelled_by ? ` — ${escapeHtml(o.cancelled_by)}` : ''}</div>`;
    }
    if (o.status === 'Refunded' && o.refund_reason) {
        reasonHtml += `<div class="detail-reason refund-reason"><i class="fa-solid fa-rotate-left"></i> ${escapeHtml(truncReason(o.refund_reason))}${o.refunded_by ? ` — ${escapeHtml(o.refunded_by)}` : ''}</div>`;
    }
    if (o.remake_count > 0 && o.remake_reasons && o.remake_reasons.length > 0) {
        reasonHtml += `<div class="detail-reason remake-reason"><i class="fa-solid fa-repeat"></i> ${o.remake_reasons.map(r => escapeHtml(truncReason(r))).join('; ')}</div>`;
    }

    document.getElementById('detailBody').innerHTML = `
        <div class="detail-row"><span class="k">Customer</span><span class="v">${escapeHtml(o.customer_name || 'Guest')}</span></div>
        <div class="detail-row"><span class="k">Phone</span><span class="v">${o.phone ? escapeHtml(o.phone) : '-'}</span></div>
        <div class="detail-row"><span class="k">Table</span><span class="v">${o.table_number ? escapeHtml(String(o.table_number)) : '-'}</span></div>
        <div class="detail-row"><span class="k">Status</span><span class="v">${statusPill(o.status)}</span></div>
        <div class="detail-row"><span class="k">Payment</span><span class="v">${paymentPill(o.payment_status)} &nbsp; ${methodLabel(o.payment_method)}</span></div>
        <div class="detail-row"><span class="k">Placed By</span><span class="v">${escapeHtml(o.employee_name || '-')}${o.employee_role ? ` (${roleLabel(o.employee_role)})` : ''}</span></div>
        ${o.prepared_by ? `<div class="detail-row"><span class="k">Prepared By</span><span class="v">${escapeHtml(o.prepared_by)}${o.prepared_by_role ? ` (${roleLabel(o.prepared_by_role)})` : ''}</span></div>` : ''}
        <div class="detail-row"><span class="k">Placed</span><span class="v">${timeAgo(o.order_date)}</span></div>
        <div class="detail-row"><span class="k">Total</span><span class="v">$${parseFloat(o.total || 0).toFixed(2)}</span></div>
        ${reasonHtml}
        <div class="detail-items">${buildItemsDetail(o.items || [])}</div>
    `;
    document.getElementById('detailActions').innerHTML = getActionButtonsHtml(o);
}

function openOrderDetail(id) {
    const o = allOrders.find(x => x.order_id == id);
    if (!o) return;
    currentDetailId = o.order_id;
    renderOrderDetail(o);
    document.getElementById('orderDetailModal').classList.add('active');
}

function closeOrderDetail() {
    document.getElementById('orderDetailModal').classList.remove('active');
    currentDetailId = 0;
}

// ── Call Order ──
function callOrder(id, customerName, orderNumber) {
    play("callSound");
    document.getElementById('callOrderNumber').textContent = '#' + orderNumber;
    document.getElementById('callCustomerName').textContent = 'Customer: ' + customerName;
    document.getElementById('callModal').classList.add('active');
}
function dismissCall() {
    document.getElementById('callModal').classList.remove('active');
}

// ── Cancel Modal ──
function showCancelModal(id, orderNumber) {
    currentCancelId = id;
    document.getElementById('cancelOrderNumber').textContent = '#' + orderNumber;
    document.getElementById('cancelReason').value = '';
    document.getElementById('cancelModal').classList.add('active');
}
function closeCancelModal() {
    document.getElementById('cancelModal').classList.remove('active');
    currentCancelId = 0;
}
async function confirmCancel() {
    const reason = document.getElementById('cancelReason').value.trim();
    if (!reason) { showToast('Please provide a reason for cancellation.', 'error'); return; }
    const id = currentCancelId;
    if (!id) return;
    closeCancelModal();

    try {
        const formData = new FormData();
        formData.append('cancel_reason', reason);
        formData.append('restore_stock', '1');
        const r = await fetch(`${CANCEL_URL}?order_id=${id}`, { method: 'POST', body: formData });
        if (!r.ok) { showToast('❌ Server error: ' + r.status, 'error'); return; }
        const res = await r.json();
        if (res.ok) {
            closeOrderDetail();
            await loadOrders();
            showToast('✅ ' + (res.message || 'Order cancelled successfully'));
        } else {
            showToast('❌ Failed: ' + (res.error || 'Unknown error'), 'error');
        }
    } catch (err) {
        showToast('❌ Request failed: ' + err.message, 'error');
    }
}

// ── Refund Modal ──
function showRefundModal(id, orderNumber, total) {
    currentRefundId = id;
    document.getElementById('refundOrderNumber').textContent = '#' + orderNumber;
    document.getElementById('refundAmount').value = total;
    document.getElementById('refundReason').value = '';
    document.getElementById('refundModal').classList.add('active');
}
function closeRefundModal() {
    document.getElementById('refundModal').classList.remove('active');
    currentRefundId = 0;
}
async function confirmRefund() {
    const amount = parseFloat(document.getElementById('refundAmount').value);
    const reason = document.getElementById('refundReason').value.trim();
    if (!amount || amount <= 0) { showToast('Please enter a valid refund amount.', 'error'); return; }
    if (!reason) { showToast('Please provide a reason for refund.', 'error'); return; }
    const id = currentRefundId;
    if (!id) return;
    closeRefundModal();

    try {
        const formData = new FormData();
        formData.append('refund_amount', amount);
        formData.append('refund_reason', reason);
        const r = await fetch(`${REFUND_URL}?order_id=${id}`, { method: 'POST', body: formData });
        if (!r.ok) { showToast('❌ Server error: ' + r.status, 'error'); return; }
        const res = await r.json();
        if (res.ok) {
            closeOrderDetail();
            await loadOrders();
            showToast('✅ ' + (res.message || 'Order refunded successfully'));
        } else {
            showToast('❌ Failed: ' + (res.error || 'Unknown error'), 'error');
        }
    } catch (err) {
        showToast('❌ Request failed: ' + err.message, 'error');
    }
}

// ── Remake Modal ──
const SWEETNESS_OPTS = ['0%', '25%', '50%', '75%', '100%'];
const ICE_OPTS       = ['No Ice', 'Less Ice', 'Normal Ice', 'More Ice'];
const SUGAR_OPTS     = ['Less Sugar', 'Normal Sugar', 'More Sugar', 'Extra Sugar'];
const MILK_OPTS      = ['Fresh Milk', 'Almond Milk', 'Soy Milk', 'Oat Milk', 'No Milk'];

function buildPillGroup(type, options, current) {
    let html = `<div class="remake-adj-group"><div class="remake-adj-label">${type.toUpperCase()}</div><div class="pill-group">`;
    options.forEach(opt => {
        const sel = opt === current ? ' selected' : '';
        html += `<button type="button" class="pill-opt${sel}" onclick="selectPill(this)" data-type="${type}">${escapeHtml(opt)}</button>`;
    });
    return html + '</div></div>';
}
function selectPill(btn) {
    btn.closest('.pill-group').querySelectorAll('.pill-opt').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
}
function showRemakeModal(id, orderNumber) {
    currentRemakeId = id;
    document.getElementById('remakeOrderNumber').textContent = '#' + orderNumber;
    document.getElementById('remakeReason').value = '';
    const order = allOrders.find(o => o.order_id == id);
    const adjDiv = document.getElementById('remakeAdjustments');
    adjDiv.innerHTML = '';
    if (order && order.items && order.items.length > 0) {
        order.items.forEach(item => {
            const block = document.createElement('div');
            block.className = 'remake-item-block';
            block.dataset.itemId = item.item_id;
            block.innerHTML =
                `<div class="remake-item-name"><i class="fa-solid fa-mug-hot" style="margin-right:5px"></i>${escapeHtml(item.product_name)}</div>` +
                buildPillGroup('sweetness', SWEETNESS_OPTS, item.sweetness) +
                buildPillGroup('ice',       ICE_OPTS,       item.ice) +
                buildPillGroup('sugar',     SUGAR_OPTS,     item.sugar) +
                buildPillGroup('milk',      MILK_OPTS,      item.milk);
            adjDiv.appendChild(block);
        });
    }
    document.getElementById('remakeModal').classList.add('active');
}
function closeRemakeModal() {
    document.getElementById('remakeModal').classList.remove('active');
    currentRemakeId = 0;
}
async function confirmRemake() {
    const reason = document.getElementById('remakeReason').value.trim();
    if (!reason) { showToast('Please enter a reason for the remake.', 'error'); return; }
    const btn = document.querySelector('#remakeModal .btn-refund-yes');
    btn.disabled = true;
    const adjustments = [];
    document.querySelectorAll('#remakeAdjustments .remake-item-block').forEach(block => {
        adjustments.push({
            item_id:   block.dataset.itemId,
            sweetness: block.querySelector('[data-type="sweetness"].selected')?.textContent || '',
            ice:       block.querySelector('[data-type="ice"].selected')?.textContent || '',
            sugar:     block.querySelector('[data-type="sugar"].selected')?.textContent || '',
            milk:      block.querySelector('[data-type="milk"].selected')?.textContent || ''
        });
    });
    try {
        const formData = new FormData();
        formData.append('reason', reason);
        formData.append('adjustments', JSON.stringify(adjustments));
        const r = await fetch(`${REMAKE_URL}?order_id=${currentRemakeId}`, { method: 'POST', body: formData });
        const data = await r.json();
        if (data.ok) {
            closeRemakeModal();
            closeOrderDetail();
            showToast('🔁 ' + data.message);
            await loadOrders();
        } else {
            showToast('❌ ' + (data.error || 'Failed to log remake.'), 'error');
        }
    } catch(e) {
        showToast('❌ Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
    }
}

// ── Toast ──
function showToast(message, type = 'success') {
    Toastify({
        text: message, duration: 3000, gravity: "top", position: "right", offset: { y: 70 },
        style: { background: type === 'success' ? '#f0fdf4' : '#fff1f2', color: type === 'success' ? '#15803d' : '#be123c', fontWeight: '600', border: '1px solid ' + (type === 'success' ? '#bbf7d0' : '#fecdd3') }
    }).showToast();
}

// ── Mark as Paid ──
async function markPaid(id) {
    const btn = document.querySelector('#detailActions .paid-btn');
    if (btn) btn.disabled = true;
    try {
        const r = await fetch(`${BOARD_API}?action=paid&id=${id}`, { cache: "no-store" });
        const res = await r.json();
        if (res.ok) {
            closeOrderDetail();
            await loadOrders();
            showToast("✅ Order marked as paid");
        } else {
            showToast("❌ Failed: " + (res.error || "Unknown error"), 'error');
        }
    } catch (err) {
        showToast("❌ Request failed", 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

// ── Mark as Prepare ──
async function markPrepare(id) {
    const btn = document.querySelector('#detailActions .prepare-btn');
    if (btn) btn.disabled = true;
    try {
        const r = await fetch(`${BOARD_API}?action=prepare&id=${id}`, { cache: "no-store" });
        const res = await r.json();
        if (res.ok) {
            closeOrderDetail();
            await loadOrders();
            showToast("👨‍🍳 Order marked as preparing");
        } else {
            showToast("❌ Failed: " + (res.error || "Unknown error"), 'error');
        }
    } catch (err) {
        showToast("❌ Request failed", 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

// ── Complete Order ──
async function completeOrder(id) {
    const btn = document.querySelector('#detailActions .complete-btn');
    if (!btn) return;

    if (btn.dataset.confirming === 'true') {
        btn.dataset.confirming = 'false';
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Complete';
        btn.disabled = true;

        try {
            const r = await fetch(`${BOARD_API}?action=complete&id=${id}`, { cache: "no-store" });
            const res = await r.json();
            if (res.ok) {
                const o = allOrders.find(x => x.order_id == id);
                if (o) callOrder(id, o.customer_name, o.daily_order_no);
                closeOrderDetail();
                await loadOrders();
                showToast("✅ Order completed");
            } else {
                showToast("❌ Failed: " + (res.error || "Unknown error"), 'error');
            }
        } catch (err) {
            showToast("❌ Request failed", 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
        return;
    }

    btn.dataset.confirming = 'true';
    btn.textContent = '⚠️ Confirm?';
    setTimeout(() => {
        if (btn.dataset.confirming === 'true') {
            btn.dataset.confirming = 'false';
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Complete';
        }
    }, 3000);
}

// ── Delete Order ──
async function removeOrder(id) {
    const btn = document.querySelector('#detailActions .delete-btn');
    if (!btn) return;

    if (btn.dataset.confirming === 'true') {
        btn.dataset.confirming = 'false';
        btn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
        btn.disabled = true;

        try {
            const r = await fetch(`${BOARD_API}?action=delete&id=${id}`, { cache: "no-store" });
            const res = await r.json();
            if (res.ok) {
                const row = document.getElementById("row-" + id);
                if (row) { row.classList.add("fade-out"); setTimeout(() => row.remove(), 400); }
                known.delete(String(id));
                closeOrderDetail();
                showToast("🗑️ Order deleted");
            } else {
                showToast("❌ Failed: " + (res.error || "Unknown error"), 'error');
            }
        } catch (err) {
            showToast("❌ Request failed", 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
        return;
    }

    btn.dataset.confirming = 'true';
    btn.innerHTML = 'Confirm?';
    setTimeout(() => {
        if (btn.dataset.confirming === 'true') {
            btn.dataset.confirming = 'false';
            btn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
        }
    }, 3000);
}

// ── Search ──
function searchOrders() {
    searchQuery = document.getElementById('searchInput').value;
    document.querySelectorAll('.status-tab').forEach(t => { t.style.opacity = searchQuery ? '0.45' : ''; });
    applyFilters();
}
function clearSearch() {
    searchQuery = '';
    document.getElementById('searchInput').value = '';
    document.querySelectorAll('.status-tab').forEach(t => { t.style.opacity = ''; });
    applyFilters();
}

// ── Initial Load ──
loadOrders().then(() => {
    const params    = new URLSearchParams(window.location.search);
    const tab       = params.get('tab');
    const highlight = params.get('highlight');

    if (tab) filterStatus(tab);
    else if (userRole === 'staff') filterStatus('PendingPayment');
    else if (userRole === 'barista') filterStatus('Preparing');

    if (highlight) {
        setTimeout(() => openOrderDetail(Number(highlight)), 300);
    }
});
setInterval(loadOrders, 4000);

// ── Real-time Socket ──
let socket;
<?php if ($_socketAvailable): ?>
(function() {
    const s = document.createElement('script');
    s.src = "<?= SOCKET_URL ?>/socket.io/socket.io.js";
    s.onload = function() {
        try {
            socket = io("<?= SOCKET_URL ?>");
            socket.on("connect", () => { console.log("Connected to realtime server"); });
            socket.on("disconnect", () => { console.log("Disconnected from realtime server"); });
            socket.on("new_order", async (data) => {
                if (data && data.order_id) {
                    play("bell");
                    await loadOrders();
                    showToast("🔔 New order #" + data.order_id + " received!");
                }
            });
        } catch (e) {
            console.warn("Realtime server unavailable:", e.message);
        }
    };
    document.body.appendChild(s);
})();
<?php endif; ?>

// ── Keyboard shortcuts ──
document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    if (document.getElementById('callModal').classList.contains('active')) dismissCall();
    if (document.getElementById('cancelModal').classList.contains('active')) closeCancelModal();
    if (document.getElementById('refundModal').classList.contains('active')) closeRefundModal();
    if (document.getElementById('remakeModal').classList.contains('active')) closeRemakeModal();
    if (document.getElementById('orderDetailModal').classList.contains('active')) closeOrderDetail();
});

// ── Time-ago auto-refresh ──
setInterval(() => {
    document.querySelectorAll('[data-timestamp]').forEach(el => {
        el.textContent = timeAgo(el.dataset.timestamp);
    });
}, 30000);
</script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<?php if ($_flash_welcome): ?>
<script>document.addEventListener('DOMContentLoaded',()=>showToast('Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES) ?>!','success'));</script>
<?php endif; ?>
