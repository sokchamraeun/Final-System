<?php
declare(strict_types=1);
/* ── Dashboard-specific JavaScript.
     Note: Sidebar clock, theme toggle, toast, and SPA routing
           are owned by the layout shell / sidebar component. ── */
$_flash_welcome     = $_flash_welcome     ?? false;
$_flash_stock_alert = $_flash_stock_alert ?? false;
$low_stock          = $low_stock          ?? 0;
?>
<script>
/* ── Time-of-day greeting (only present in the employee header) ── */
(function(){
    var tod = document.getElementById('timeOfDay');
    if (!tod) return;
    var h = new Date().getHours();
    tod.textContent = h < 12 ? 'morning' : h < 17 ? 'afternoon' : 'evening';
})();

/* ── Clock in/out (attendance_action.php) ── */
window.toggleClock = function () {
    var btn = document.getElementById('clockBtn');
    if (!btn) return;
    var clocked = btn.dataset.clocked === '1';
    btn.disabled = true;
    btn.style.opacity = '.6';

    fetch('<?= e(url('attendance/action')) ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=' + (clocked ? 'clock_out' : 'clock_in')
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.ok) {
            if (!clocked) {
                btn.dataset.clocked = '1';
                btn.innerHTML = '<i class="fa-solid fa-right-from-bracket"></i> Clock Out';
                btn.style.background = 'rgba(255,95,95,.08)';
                btn.style.borderColor = 'rgba(255,95,95,.25)';
                btn.style.color = '#ff6b6b';
                btn.title = 'Clocked in at ' + (data.time || '');
            } else {
                btn.dataset.clocked = '0';
                btn.innerHTML = '<i class="fa-solid fa-fingerprint"></i> Clock In';
                btn.style.background = 'rgba(85,224,135,.08)';
                btn.style.borderColor = 'rgba(85,224,135,.25)';
                btn.style.color = '#55e087';
                btn.title = 'Not clocked in';
            }
            window.toast(data.msg, 'success');
        } else {
            window.toast(data.msg, 'error');
        }
    })
    .catch(function () { window.toast('Connection error.', 'error'); })
    .finally(function () { btn.disabled = false; btn.style.opacity = '1'; });
};

/* ── AJAX polling (kitchen + KPIs via dashboard_data.php) ── */
window.fetchDashboardData = function () {
    fetch('<?= e(root_url('dashboard_data.php')) ?>')
        .then(function (r) { return r.json(); })
        .then(function (d) {
            var lu = document.getElementById('lastUpdated');
            if (lu) lu.textContent = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});

            var rev = document.getElementById('kpiRevenue');
            var ord = document.getElementById('kpiOrders');
            var itm = document.getElementById('kpiItems');
            if (rev) rev.textContent = parseFloat(d.sales).toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2});
            if (ord) ord.textContent = d.total_orders;
            if (itm && d.items_sold !== undefined) itm.textContent = d.items_sold;

            var kc = document.getElementById('kitchenCount');
            var kl = document.getElementById('kitchenList');
            if (kc && d.kitchen_orders !== undefined) {
                kc.textContent = d.kitchen_orders.length + ' preparing';
                kc.className = 'cnt-badge' + (d.kitchen_orders.length > 0 ? ' on' : '');
            }
            if (kl && d.kitchen_orders !== undefined) {
                if (d.kitchen_orders.length > 0) {
                    kl.innerHTML = d.kitchen_orders.map(function (o) {
                        var mins = Math.floor((Date.now() - new Date(o.order_date.replace(' ','T'))) / 60000);
                        var tc = mins >= 20 ? 'urgent' : mins >= 10 ? 'warn' : 'ok';
                        return '<div class="k-item">' +
                            '<div class="k-no">#' + o.daily_order_no + '</div>' +
                            '<div class="k-name">' + o.customer_name + '</div>' +
                            '<div class="k-total">$' + parseFloat(o.total).toFixed(2) + '</div>' +
                            '<div class="k-timer ' + tc + '">' + mins + 'm</div>' +
                            '<span class="k-status-pill"><i class="fa-solid fa-fire-burner"></i> Preparing</span>' +
                            '</div>';
                    }).join('');
                } else {
                    kl.innerHTML = '<div class="k-empty"><i class="fa-solid fa-circle-check"></i><span>All clear \u2014 no orders preparing</span></div>';
                }
            }
        })
        .catch(function () {});
};
setInterval(fetchDashboardData, 5000);

/* ── Flash toasts from PHP ── */
<?php if ($_flash_welcome): ?>
document.addEventListener('DOMContentLoaded', function () {
    window.toast('Welcome back!', 'success');
});
<?php endif; ?>
<?php if ($_flash_stock_alert && $low_stock > 0 && can('ingredients')): ?>
document.addEventListener('DOMContentLoaded', function () {
    window.toast('<?= $low_stock ?> ingredient<?= $low_stock != 1 ? "s are" : " is" ?> low on stock', 'error');
});
<?php endif; ?>
</script>
