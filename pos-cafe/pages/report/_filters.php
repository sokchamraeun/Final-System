<div class="filter-panel fu" style="animation-delay:.15s" id="filter-form">
    <form method="GET" id="reportFilterForm">
        <?php if (isset($_GET['mode'])): ?>
        <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">
        <?php endif; ?>

        <!-- Quick Select + Date Range -->
        <div class="filter-row">
            <div class="filter-section filter-section--left">
                <div class="filter-section-head">
                    <div class="filter-section-icon">
                        <i class="fa-regular fa-calendar"></i>
                    </div>
                    <div class="filter-section-text">
                        <div class="filter-section-title">Quick Select</div>
                        <div class="filter-section-subtitle">Choose report date range</div>
                    </div>
                </div>
                <div class="quick-select">
                    <?php
                    $quickRanges = [
                        ['label' => 'Today', 'value' => 'daily', 'date' => getBusinessDateToday()],
                        ['label' => '7 Days', 'value' => 'range', 'from' => (new DateTime('-6 days'))->format('Y-m-d'), 'to' => getBusinessDateToday()],
                        ['label' => '30 Days', 'value' => 'range', 'from' => (new DateTime('-29 days'))->format('Y-m-d'), 'to' => getBusinessDateToday()],
                        ['label' => '1 Year', 'value' => 'range', 'from' => (new DateTime('-1 year'))->format('Y-m-d'), 'to' => getBusinessDateToday()],
                    ];
                    $currentFrom = $_GET['from_date'] ?? getBusinessDateToday();
                    $currentTo = $_GET['to_date'] ?? getBusinessDateToday();
                    $currentDate = $_GET['date'] ?? getBusinessDateToday();
                    ?>
                    <?php foreach ($quickRanges as $qr): ?>
                    <?php
                        $qrActive = $qr['value'] === 'daily'
                            ? ($mode === 'daily' && $currentDate === $qr['date'])
                            : ($mode === 'range' && $currentFrom === $qr['from'] && $currentTo === $qr['to']);
                    ?>
                    <button type="button" class="quick-btn<?= $qrActive ? ' active' : '' ?>"
                        onclick="applyQuickRange(this, '<?= $qr['value'] ?>', '<?= $qr['date'] ?? '' ?>', '<?= $qr['from'] ?? '' ?>', '<?= $qr['to'] ?? '' ?>')"
                        data-from="<?= $qr['from'] ?? '' ?>" data-to="<?= $qr['to'] ?? '' ?>" data-date="<?= $qr['date'] ?? '' ?>">
                        <?= $qr['label'] ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-section-divider"></div>

            <div class="filter-section filter-section--right">
                <div class="filter-section-text">
                    <div class="filter-section-title">Custom Date Range</div>
                    <div class="filter-section-subtitle">Select a reporting period</div>
                </div>
                <div class="date-range">
                    <div class="date-input-group">
                        <div class="date-input-label">From Date</div>
                        <div class="date-input-wrapper">
                            <i class="fa-regular fa-calendar"></i>
                            <input type="date" name="from_date" id="fromDate" value="<?= htmlspecialchars($_GET['from_date'] ?? getBusinessDateToday()) ?>">
                        </div>
                    </div>
                    <div class="date-divider">
                        <i class="fa-solid fa-arrow-right" style="font-size:12px;"></i>
                    </div>
                    <div class="date-input-group">
                        <div class="date-input-label">To Date</div>
                        <div class="date-input-wrapper">
                            <i class="fa-regular fa-calendar"></i>
                            <input type="date" name="to_date" id="toDate" value="<?= htmlspecialchars($_GET['to_date'] ?? getBusinessDateToday()) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-section filter-section--action">
                <button type="submit" class="quick-btn active" style="background:var(--teal);color:#fff;border-color:var(--teal);height:38px;">
                    <i class="fa-solid fa-magnifying-glass"></i> View Report
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Search & Filter Toolbar -->
<div class="search-toolbar fu" style="animation-delay:.18s" id="search-toolbar">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" id="searchInput" placeholder="Search order #, customer, or phone..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div class="toolbar-dropdown">
        <select name="status" id="statusFilter">
            <option value="">All Statuses</option>
            <option value="Completed" <?= ($_GET['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
            <option value="PendingPayment" <?= ($_GET['status'] ?? '') === 'PendingPayment' ? 'selected' : '' ?>>Pending Payment</option>
            <option value="Cancelled" <?= ($_GET['status'] ?? '') === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
    </div>
    <div class="toolbar-dropdown">
        <select name="payment_method" id="paymentFilter">
            <option value="">All Methods</option>
            <option value="cash" <?= ($_GET['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash</option>
            <option value="card" <?= ($_GET['payment_method'] ?? '') === 'card' ? 'selected' : '' ?>>Card</option>
            <option value="paylater" <?= ($_GET['payment_method'] ?? '') === 'paylater' ? 'selected' : '' ?>>Pay Later</option>
            <option value="qr" <?= ($_GET['payment_method'] ?? '') === 'qr' ? 'selected' : '' ?>>QR Code</option>
        </select>
    </div>
    <div class="toolbar-dropdown">
        <select name="user_id" id="userFilter">
            <option value="">All Sale Users</option>
            <?php
            $usersQuery = $conn->query("SELECT user_id, name FROM employees ORDER BY name");
            if ($usersQuery):
                while ($u = $usersQuery->fetch_assoc()):
            ?>
            <option value="<?= $u['user_id'] ?>" <?= (string)($_GET['user_id'] ?? '') === (string)$u['user_id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
            <?php
                endwhile;
            endif;
            ?>
        </select>
    </div>
</div>

<script>
function applyQuickRange(btn, mode, date, from, to) {
    document.querySelectorAll('.quick-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    var url = mode === 'daily'
        ? '?mode=daily&date=' + encodeURIComponent(date)
        : '?mode=range&from_date=' + encodeURIComponent(from) + '&to_date=' + encodeURIComponent(to);

    if (typeof loadReportContent === 'function') {
        loadReportContent(url, mode);
    } else {
        window.location.href = url;
    }
}
</script>
