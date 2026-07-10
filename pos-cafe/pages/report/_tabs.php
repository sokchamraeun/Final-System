<div class="report-tabs fu" style="animation-delay:.1s">
    <button class="report-tab active" data-tab="sales" onclick="switchTab(this)">
        <i class="fa-solid fa-chart-line"></i>
        <span>Sales</span>
    </button>
    <button class="report-tab" data-tab="products" onclick="switchTab(this)">
        <i class="fa-solid fa-cube"></i>
        <span>Products</span>
    </button>
    <button class="report-tab" data-tab="inventory" onclick="switchTab(this)">
        <i class="fa-solid fa-boxes-stacked"></i>
        <span>Inventory</span>
    </button>
    <button class="report-tab" data-tab="purchases" onclick="switchTab(this)">
        <i class="fa-solid fa-file-invoice"></i>
        <span>Purchases</span>
    </button>
    <button class="report-tab" data-tab="profit" onclick="switchTab(this)">
        <i class="fa-solid fa-coins"></i>
        <span>Profit</span>
    </button>
    <button class="report-tab" data-tab="staff" onclick="switchTab(this)">
        <i class="fa-solid fa-users"></i>
        <span>Staff</span>
    </button>
    <button class="report-tab" data-tab="customers" onclick="switchTab(this)">
        <i class="fa-solid fa-user-group"></i>
        <span>Customers</span>
    </button>
    <button class="report-tab" data-tab="payments" onclick="switchTab(this)">
        <i class="fa-solid fa-credit-card"></i>
        <span>Payments</span>
    </button>
</div>

<script>
var CURRENT_TAB = 'sales';

function switchTab(btn) {
    document.querySelectorAll('.report-tab').forEach(function(t) {
        t.classList.remove('active');
        t.style.transform = '';
    });
    btn.classList.add('active');
    btn.style.transform = 'scale(0.95)';
    setTimeout(function() { btn.style.transform = ''; }, 150);

    var tab = btn.getAttribute('data-tab');
    CURRENT_TAB = tab;
    applyTabFilter(tab);
}

function applyTabFilter(tab) {
    document.querySelectorAll('[data-tab-content]').forEach(function(el) {
        var section = el.getAttribute('data-tab-content');
        if (section === tab) {
            el.style.display = '';
            el.classList.remove('tab-hidden');
        } else {
            el.style.display = 'none';
            el.classList.add('tab-hidden');
        }
    });

    var placeholder = document.getElementById('tab-placeholder');
    var hasVisible = document.querySelectorAll('[data-tab-content="' + tab + '"]').length > 0;
    if (placeholder) {
        placeholder.style.display = hasVisible ? 'none' : 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    applyTabFilter(CURRENT_TAB);
});
</script>
