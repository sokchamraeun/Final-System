<?php
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'ingredients';
require POS_ROOT . '/middleware/permission.php';

$filter_ing  = (int)($_GET['ingredient_id'] ?? 0);
$filter_type = trim($_GET['type'] ?? '');
$filter_from = trim($_GET['from'] ?? '');
$filter_to   = trim($_GET['to']   ?? '');

$valid_types = ['order_deduct','order_restore','quick_restock','po_received','manual_adjust'];
if (!in_array($filter_type, $valid_types)) $filter_type = '';

$per_page = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));

function _hist_ref(array $r): string {
    if (!empty($r['order_id']) && !empty($r['daily_order_no'])) {
        return 'Order #' . (int)$r['daily_order_no'];
    }
    return ($r['reference'] ?? '') !== '' ? $r['reference'] : '—';
}

if (($_GET['ajax'] ?? '') === '1') {
    header('Content-Type: application/json');
    $last_id = (int)($_GET['last_id'] ?? 0);
    $where = "h.id > $last_id";
    $wq = "SELECT h.*, i.ingredient_name, o.daily_order_no FROM ingredient_history h JOIN ingredients i ON i.ingredient_id = h.ingredient_id LEFT JOIN orders o ON o.order_id = h.order_id WHERE $where ORDER BY h.id DESC LIMIT 20";
    $qr = $conn->query($wq);
    $list = [];
    while ($r = $qr->fetch_assoc()) $list[] = $r;
    echo json_encode(['rows' => $list]);
    exit;
}

$ing_list = [];
$ir = $conn->query("SELECT ingredient_id, ingredient_name FROM ingredients ORDER BY ingredient_name ASC");
while ($i = $ir->fetch_assoc()) $ing_list[] = $i;

$where = '1';
$params = []; $types = '';
if ($filter_ing > 0) { $where .= ' AND h.ingredient_id = ?'; $params[] = $filter_ing; $types .= 'i'; }
if ($filter_type !== '') { $where .= " AND h.change_type = ?"; $params[] = $filter_type; $types .= 's'; }
if ($filter_from !== '') { $where .= " AND h.created_at >= ?"; $params[] = $filter_from . ' 00:00:00'; $types .= 's'; }
if ($filter_to   !== '') { $where .= " AND h.created_at <= ?"; $params[] = $filter_to   . ' 23:59:59'; $types .= 's'; }

$count_sql = "SELECT COUNT(*) AS total FROM ingredient_history h WHERE $where";
$cst = $conn->prepare($count_sql);
if ($params) $cst->bind_param($types, ...$params);
$cst->execute();
$total_rows = (int)$cst->get_result()->fetch_assoc()['total'];
$total_pages = max(1, (int)ceil($total_rows / $per_page));
if ($page > $total_pages) $page = $total_pages;

$offset = ($page - 1) * $per_page;
$data_sql = "SELECT h.*, i.ingredient_name, o.daily_order_no FROM ingredient_history h JOIN ingredients i ON i.ingredient_id = h.ingredient_id LEFT JOIN orders o ON o.order_id = h.order_id WHERE $where ORDER BY h.id DESC LIMIT $per_page OFFSET $offset";
$dst = $conn->prepare($data_sql);
if ($params) $dst->bind_param($types, ...$params);
$dst->execute();
$rows = $dst->get_result()->fetch_all(MYSQLI_ASSOC);

$agg_sql = "SELECT SUM(CASE WHEN h.amount < 0 THEN ABS(h.amount) ELSE 0 END) AS deduct_total, SUM(CASE WHEN h.amount > 0 THEN h.amount ELSE 0 END) AS add_total, COUNT(*) AS total_events, SUM(h.amount) AS net_change FROM ingredient_history h WHERE $where";
$ast = $conn->prepare($agg_sql);
if ($params) $ast->bind_param($types, ...$params);
$ast->execute();
$agg = $ast->get_result()->fetch_assoc();
$cnt_deduct     = $agg['deduct_total'] ?? 0;
$cnt_add        = $agg['add_total']    ?? 0;
$total_events   = (int)($agg['total_events'] ?? 0);
$net_change     = $agg['net_change']   ?? 0;
$total_deducted = $cnt_deduct;
$total_added    = $cnt_add;

$filter_ing_name = '';
if ($filter_ing > 0) {
    foreach ($ing_list as $il) {
        if ((int)$il['ingredient_id'] === $filter_ing) { $filter_ing_name = $il['ingredient_name']; break; }
    }
}

$base_url = url('pages/ingredient/history.php') . '?';
$qp = [];
if ($filter_ing > 0)   $qp[] = 'ingredient_id=' . $filter_ing;
if ($filter_type !== '') $qp[] = 'type=' . urlencode($filter_type);
if ($filter_from !== '') $qp[] = 'from=' . urlencode($filter_from);
if ($filter_to   !== '') $qp[] = 'to='   . urlencode($filter_to);
if ($qp) $base_url .= implode('&', $qp) . '&';

function fmtQ($n) { return rtrim(rtrim(number_format((float)$n, 3, '.', ','), '0'), '.'); }
function h($s)    { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Stock History | Bird's Nest Coffee</title>
<script>(function(){if(localStorage.getItem('theme')==='light')document.documentElement.setAttribute('data-theme','light');}());</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--bg:#0b0b0b;--bg-card:#131313;--bg-card-hover:#1a1a1a;--bg-input:#1a1a1a;--border:#222;--border-hover:#333;--accent:#d1904b;--accent-light:#e8b87a;--accent-dark:#a0702a;--text:#f5f5f5;--text-muted:#888;--text-light:#fff;--ok:#55e087;--low:#f1c40f;--danger:#ff5f5f;--blue:#3498db;--purple:#9b59b6;--shadow-sm:0 2px 8px rgba(0,0,0,.35);--shadow-md:0 4px 20px rgba(0,0,0,.45);--shadow-accent:0 0 0 3px rgba(209,144,75,.12);--radius:14px;--transition:all .22s cubic-bezier(.4,0,.2,1);}
[data-theme="light"]{--bg:#F0F2F5;--bg-card:#FFFFFF;--bg-card-hover:#F5F7FA;--bg-input:#F9FAFB;--border:#E5E7EB;--border-hover:#D1D5DB;--text:#111827;--text-muted:#6B7280;--text-light:#111827;--shadow-sm:0 2px 8px rgba(0,0,0,.06);--shadow-md:0 4px 20px rgba(0,0,0,.08);}
[data-theme="light"] .topbar{background:rgba(255,255,255,.97);}
[data-theme="light"] thead th{background:#fff;}
[data-theme="light"] tr:hover td{background:rgba(0,0,0,.02);}
[data-theme="light"] input,[data-theme="light"] select{background:var(--bg-input)!important;color:var(--text)!important;border-color:var(--border)!important;color-scheme:light;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding-bottom:40px;}
::-webkit-scrollbar{width:5px;height:5px;}
::-webkit-scrollbar-thumb{background:var(--accent);border-radius:10px;}
.topbar{position:sticky;top:0;z-index:200;display:flex;align-items:center;gap:10px;padding:10px 24px;background:rgba(11,11,11,.97);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);flex-wrap:wrap;}
.brand-icon{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--accent-dark),var(--accent));display:flex;align-items:center;justify-content:center;font-size:15px;color:#fff;flex-shrink:0;}
.brand-text{display:flex;flex-direction:column;line-height:1.2;}
.brand-title{font-size:15px;font-weight:700;color:var(--text-light);}
.brand-sub{font-size:10px;color:var(--text-muted);}
.topbar-sep{width:1px;height:22px;background:var(--border);flex-shrink:0;}
.topbar-right{display:flex;align-items:center;gap:6px;margin-left:auto;flex-wrap:wrap;}
.btn-nav{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:50px;border:1px solid var(--border);background:var(--bg-input);color:var(--text-muted);text-decoration:none;font-size:12px;font-weight:500;transition:var(--transition);cursor:pointer;white-space:nowrap;font-family:'Poppins',sans-serif;}
.btn-nav:hover{border-color:var(--accent);color:var(--accent);}
.btn-nav.icon-only{padding:7px 10px;}
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;padding:14px 24px 0;}
.stat-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:16px 18px;display:flex;align-items:center;gap:12px;position:relative;overflow:hidden;}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:var(--radius) var(--radius) 0 0;}
.stat-card.s-deduct::before{background:linear-gradient(90deg,#c0392b,var(--danger));}
.stat-card.s-add::before{background:linear-gradient(90deg,#2ecc71,var(--ok));}
.stat-card.s-total::before{background:linear-gradient(90deg,var(--accent-dark),var(--accent-light));}
.stat-card.s-net::before{background:linear-gradient(90deg,#2471a3,var(--blue));}
.stat-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;}
.stat-icon.deduct{background:rgba(255,95,95,.12);color:var(--danger);}
.stat-icon.add{background:rgba(85,224,135,.12);color:var(--ok);}
.stat-icon.total{background:rgba(209,144,75,.12);color:var(--accent);}
.stat-icon.net{background:rgba(52,152,219,.12);color:var(--blue);}
.stat-label{font-size:10px;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.5px;}
.stat-num{font-size:18px;font-weight:800;line-height:1.2;}
.stat-hint{font-size:10px;color:var(--text-muted);margin-top:1px;}
.s-deduct .stat-num{color:var(--danger);}
.s-add .stat-num{color:var(--ok);}
.s-total .stat-num{color:var(--accent);}
.s-net .stat-num{color:var(--blue);}
.filter-bar{margin:12px 24px 0;padding:14px 18px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;}
.filter-group{display:flex;flex-direction:column;gap:5px;min-width:140px;}
.filter-label{font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;}
.filter-input{padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:var(--bg-input);color:var(--text);font-size:13px;font-family:'Poppins',sans-serif;outline:none;transition:var(--transition);}
.filter-input:focus{border-color:var(--accent);box-shadow:var(--shadow-accent);}
.filter-actions{display:flex;gap:8px;margin-left:auto;align-items:flex-end;}
.btn-filter{padding:7px 16px;border-radius:8px;border:1px solid var(--accent);background:var(--accent);color:#000;font-size:13px;font-weight:700;cursor:pointer;font-family:'Poppins',sans-serif;transition:var(--transition);}
.btn-filter:hover{background:var(--accent-light);}
.btn-reset{padding:7px 14px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-muted);font-size:13px;cursor:pointer;font-family:'Poppins',sans-serif;transition:var(--transition);text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
.btn-reset:hover{border-color:var(--border-hover);color:var(--text);}
.filter-chips{display:flex;align-items:center;gap:6px;padding:8px 24px 0;flex-wrap:wrap;}
.chip{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:50px;font-size:11px;font-weight:600;background:rgba(209,144,75,.12);color:var(--accent);border:1px solid rgba(209,144,75,.25);}
.chip a{color:inherit;text-decoration:none;opacity:.7;margin-left:2px;}
.chip a:hover{opacity:1;}
.table-card{margin:12px 24px 0;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);}
.table-header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border);}
.table-title{font-size:14px;font-weight:700;display:flex;align-items:center;gap:8px;}
.row-count{font-size:12px;color:var(--text-muted);}
.table-wrap{overflow:auto;max-height:calc(100vh - 350px);}
table{width:100%;border-collapse:collapse;font-size:13px;}
thead{position:sticky;top:0;z-index:10;}
th{padding:10px 14px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);background:var(--bg-card);border-bottom:1px solid var(--border);white-space:nowrap;}
td{padding:11px 14px;border-bottom:1px solid var(--border);color:var(--text);white-space:nowrap;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:rgba(255,255,255,.025);}
tr.hidden{display:none!important;}
.type-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:50px;font-size:11px;font-weight:700;}
.type-badge.order_deduct{background:rgba(255,95,95,.1);color:var(--danger);border:1px solid rgba(255,95,95,.2);}
.type-badge.order_restore{background:rgba(241,196,15,.1);color:var(--low);border:1px solid rgba(241,196,15,.2);}
.type-badge.quick_restock{background:rgba(85,224,135,.1);color:var(--ok);border:1px solid rgba(85,224,135,.2);}
.type-badge.po_received{background:rgba(52,152,219,.1);color:var(--blue);border:1px solid rgba(52,152,219,.2);}
.type-badge.manual_adjust{background:rgba(155,89,182,.1);color:var(--purple);border:1px solid rgba(155,89,182,.2);}
#toast-cnt{position:fixed;bottom:24px;right:20px;z-index:99999;display:flex;flex-direction:column-reverse;gap:8px;pointer-events:none;}
.toast{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:11px 16px;font-size:13px;font-weight:500;color:var(--text);box-shadow:var(--shadow-md);display:flex;align-items:center;gap:10px;min-width:220px;max-width:320px;transform:translateX(120%);transition:transform .3s cubic-bezier(.34,1.56,.64,1);pointer-events:auto;}
.toast.show{transform:translateX(0);}
.toast.success{border-left:3px solid var(--ok);}
.toast.error{border-left:3px solid var(--danger);}
.amount-neg{color:var(--danger);font-weight:700;}
.amount-pos{color:var(--ok);font-weight:700;}
.ing-cell{display:flex;align-items:center;gap:8px;}
.ing-dot{width:24px;height:24px;border-radius:6px;background:rgba(209,144,75,.1);display:flex;align-items:center;justify-content:center;font-size:11px;color:var(--accent);flex-shrink:0;}
.empty-state{text-align:center;padding:60px 20px;}
.empty-state .ei{font-size:42px;color:var(--border-hover);margin-bottom:14px;}
.empty-state h3{font-size:16px;font-weight:600;margin-bottom:6px;}
.empty-state p{font-size:13px;color:var(--text-muted);}
.search-wrap{display:flex;align-items:center;gap:8px;padding:7px 12px;border-radius:50px;border:1px solid var(--border);background:var(--bg-input);transition:var(--transition);max-width:240px;}
.search-wrap:focus-within{border-color:var(--accent);}
.search-wrap i{color:var(--text-muted);font-size:12px;}
.search-wrap input{border:none;background:transparent;outline:none;color:var(--text);font-size:12px;font-family:'Poppins',sans-serif;width:100%;}
.search-wrap input::placeholder{color:var(--text-muted);}
.pagination-wrap{display:flex;flex-direction:column;align-items:center;gap:10px;padding:18px 24px;border-top:1px solid var(--border);}
.pagination{display:flex;align-items:center;gap:4px;flex-wrap:wrap;justify-content:center;}
.pg-btn{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;border-radius:8px;border:1px solid var(--border);background:var(--bg-input);color:var(--text-muted);text-decoration:none;font-size:13px;font-weight:500;transition:var(--transition);cursor:pointer;white-space:nowrap;}
.pg-btn:hover:not(.disabled){border-color:var(--accent);color:var(--accent);background:rgba(209,144,75,.06);}
.pg-btn.pg-active{background:var(--accent);border-color:var(--accent);color:#000;font-weight:700;cursor:default;}
.pg-btn.disabled{opacity:.3;cursor:default;pointer-events:none;}
.pg-ellipsis{color:var(--text-muted);padding:0 2px;font-size:14px;line-height:34px;}
.pg-info{font-size:12px;color:var(--text-muted);}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@media (max-width:900px){.stats-row{grid-template-columns:repeat(2,1fr);}}
@media (max-width:640px){.stats-row,.filter-bar,.table-card,.filter-chips{margin-left:14px;margin-right:14px;}.topbar{padding:10px 14px;}}
@media print{.topbar,.filter-bar,.filter-chips{display:none!important;}body{background:#fff;color:#000;}.table-card{box-shadow:none;border:1px solid #ccc;margin:0;}.table-wrap{max-height:none;overflow:visible;}}
</style>
</head>
<body>

<div class="topbar">
    <a href="index.php" class="btn-nav icon-only" title="Back to Ingredients"><i class="fa-solid fa-arrow-left"></i></a>
    <div class="brand-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
    <div class="brand-text">
        <span class="brand-title">Stock History<?= $filter_ing_name ? ' — ' . h($filter_ing_name) : '' ?></span>
        <span class="brand-sub">Bird's Nest Coffee › Ingredients</span>
    </div>
    <div class="topbar-right">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input id="searchInput" placeholder="Search…" autocomplete="off" oninput="liveSearch(this.value)">
        </div>
        <button class="btn-nav" onclick="exportCSV()" title="Export current view to CSV"><i class="fa-solid fa-file-csv"></i> Export CSV</button>
        <a href="report.php" class="btn-nav" title="Consumption summary report"><i class="fa-solid fa-chart-bar"></i> Report</a>
        <button class="btn-nav icon-only" onclick="toggleTheme()" title="Toggle theme"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
    </div>
</div>

<div class="stats-row">
    <div class="stat-card s-deduct">
        <div class="stat-icon deduct"><i class="fa-solid fa-arrow-trend-down"></i></div>
        <div>
            <div class="stat-label">Deductions</div>
            <div class="stat-num" id="statDeductCnt"><?= $cnt_deduct ?></div>
            <div class="stat-hint" id="statDeductAmt"><?= fmtQ($total_deducted) ?> units total</div>
        </div>
    </div>
    <div class="stat-card s-add">
        <div class="stat-icon add"><i class="fa-solid fa-arrow-trend-up"></i></div>
        <div>
            <div class="stat-label">Additions</div>
            <div class="stat-num" id="statAddCnt"><?= $cnt_add ?></div>
            <div class="stat-hint" id="statAddAmt"><?= fmtQ($total_added) ?> units total</div>
        </div>
    </div>
    <div class="stat-card s-total">
        <div class="stat-icon total"><i class="fa-solid fa-list"></i></div>
        <div>
            <div class="stat-label">Total Events</div>
            <div class="stat-num"><?= $total_events ?></div>
            <div class="stat-hint">Filtered results</div>
        </div>
    </div>
    <div class="stat-card s-net">
        <div class="stat-icon net"><i class="fa-solid fa-scale-balanced"></i></div>
        <div>
            <div class="stat-label">Net Change</div>
            <div class="stat-num"><?= $net_change >= 0 ? '+' . fmtQ($net_change) : fmtQ($net_change) ?></div>
            <div class="stat-hint"><?= $net_change >= 0 ? 'Increase' : 'Decrease' ?></div>
        </div>
    </div>
</div>

<form method="GET" action="history.php" class="filter-bar" style="margin-bottom:0;">
    <div class="filter-group">
        <span class="filter-label">Ingredient</span>
        <select name="ingredient_id" class="filter-input">
            <option value="0">All ingredients</option>
            <?php foreach ($ing_list as $il): ?>
            <option value="<?= (int)$il['ingredient_id'] ?>" <?= $filter_ing === (int)$il['ingredient_id'] ? 'selected' : '' ?>><?= h($il['ingredient_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-group">
        <span class="filter-label">Movement Type</span>
        <select name="type" class="filter-input">
            <option value="">All types</option>
            <option value="order_deduct"  <?= $filter_type==='order_deduct'?'selected':'' ?>>Order Deduct</option>
            <option value="order_restore" <?= $filter_type==='order_restore'?'selected':'' ?>>Order Restore</option>
            <option value="quick_restock" <?= $filter_type==='quick_restock'?'selected':'' ?>>Quick Restock</option>
            <option value="po_received"   <?= $filter_type==='po_received'?'selected':'' ?>>PO Received</option>
            <option value="manual_adjust" <?= $filter_type==='manual_adjust'?'selected':'' ?>>Manual Adjust</option>
        </select>
    </div>
    <div class="filter-group">
        <span class="filter-label">From</span>
        <input type="date" name="from" class="filter-input" value="<?= h($filter_from) ?>">
    </div>
    <div class="filter-group">
        <span class="filter-label">To</span>
        <input type="date" name="to" class="filter-input" value="<?= h($filter_to) ?>">
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn-filter"><i class="fa-solid fa-filter"></i> Filter</button>
        <a href="history.php" class="btn-reset"><i class="fa-solid fa-xmark"></i> Reset</a>
    </div>
</form>

<?php if ($filter_ing > 0 || $filter_type !== '' || $filter_from !== '' || $filter_to !== ''): ?>
<div class="filter-chips">
    <?php if ($filter_ing > 0): ?>
    <span class="chip">Ingredient: <?= h($filter_ing_name) ?><a href="<?= h(str_replace('ingredient_id='.$filter_ing, '', $base_url)) ?>"><i class="fa-solid fa-xmark"></i></a></span>
    <?php endif; ?>
    <?php if ($filter_type !== ''): ?>
    <span class="chip">Type: <?= h(str_replace('_', ' ', $filter_type)) ?><a href="<?= h(str_replace('type='.urlencode($filter_type), '', $base_url)) ?>"><i class="fa-solid fa-xmark"></i></a></span>
    <?php endif; ?>
    <?php if ($filter_from !== ''): ?>
    <span class="chip">From: <?= h($filter_from) ?><a href="<?= h(str_replace('from='.urlencode($filter_from), '', $base_url)) ?>"><i class="fa-solid fa-xmark"></i></a></span>
    <?php endif; ?>
    <?php if ($filter_to !== ''): ?>
    <span class="chip">To: <?= h($filter_to) ?><a href="<?= h(str_replace('to='.urlencode($filter_to), '', $base_url)) ?>"><i class="fa-solid fa-xmark"></i></a></span>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="table-card">
    <div class="table-header">
        <div class="table-title"><i class="fa-solid fa-clock-rotate-left"></i> Movement History</div>
        <span class="row-count" id="rowCount"><?= $total_rows ?> records</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Ingredient</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Reference</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody id="histBody">
                <?php if (empty($rows)): ?>
                <tr><td colspan="6">
                    <div class="empty-state">
                        <div class="ei"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <h3>No history found</h3>
                        <p>Try adjusting your filters or adding ingredient stock first.</p>
                    </div>
                </td></tr>
                <?php else: foreach ($rows as $r): ?>
                <tr data-name="<?= h(strtolower($r['ingredient_name'])) ?>">
                    <td style="font-size:12px;color:var(--text-muted);"><?= date('d M Y<br>g:i A', strtotime($r['created_at'])) ?></td>
                    <td><div class="ing-cell"><span class="ing-dot"><i class="fa-solid fa-egg"></i></span><?= h($r['ingredient_name']) ?></div></td>
                    <td><span class="type-badge <?= h($r['change_type']) ?>"><?= h(str_replace('_', ' ', $r['change_type'])) ?></span></td>
                    <td class="<?= (float)$r['amount'] < 0 ? 'amount-neg' : 'amount-pos' ?>"><?= (float)$r['amount'] < 0 ? fmtQ($r['amount']) : '+' . fmtQ($r['amount']) ?></td>
                    <td style="font-size:12px;color:var(--text-muted);"><?= h(_hist_ref($r)) ?></td>
                    <td style="font-size:12px;color:var(--text-muted);"><?= h($r['created_by'] ?? '—') ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_pages > 1): ?>
    <div class="pagination-wrap">
        <div class="pagination">
            <a href="<?= h($base_url . 'page=1') ?>" class="pg-btn <?= $page <= 1 ? 'disabled' : '' ?>" <?= $page <= 1 ? 'tabindex="-1"' : '' ?>><i class="fa-solid fa-angles-left"></i></a>
            <a href="<?= h($base_url . 'page=' . max(1, $page-1)) ?>" class="pg-btn <?= $page <= 1 ? 'disabled' : '' ?>" <?= $page <= 1 ? 'tabindex="-1"' : '' ?>><i class="fa-solid fa-chevron-left"></i></a>
            <?php
            $start = max(1, $page - 2);
            $end   = min($total_pages, $page + 2);
            if ($start > 1) echo '<span class="pg-ellipsis">…</span>';
            for ($p = $start; $p <= $end; $p++):
            ?>
            <a href="<?= h($base_url . 'page=' . $p) ?>" class="pg-btn <?= $p === $page ? 'pg-active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <?php if ($end < $total_pages): echo '<span class="pg-ellipsis">…</span>'; endif; ?>
            <a href="<?= h($base_url . 'page=' . min($total_pages, $page+1)) ?>" class="pg-btn <?= $page >= $total_pages ? 'disabled' : '' ?>" <?= $page >= $total_pages ? 'tabindex="-1"' : '' ?>><i class="fa-solid fa-chevron-right"></i></a>
            <a href="<?= h($base_url . 'page=' . $total_pages) ?>" class="pg-btn <?= $page >= $total_pages ? 'disabled' : '' ?>" <?= $page >= $total_pages ? 'tabindex="-1"' : '' ?>><i class="fa-solid fa-angles-right"></i></a>
        </div>
        <span class="pg-info">Page <?= $page ?> of <?= $total_pages ?> (<?= $total_rows ?> records)</span>
    </div>
    <?php endif; ?>
</div>

<div id="toast-cnt"></div>

<script>
var _pollTimer = null;
function liveSearch(q) {
    document.querySelectorAll('#histBody tr[data-name]').forEach(function(tr) {
        tr.classList.toggle('hidden', q && tr.dataset.name.indexOf(q.toLowerCase()) < 0);
    });
}
function toggleTheme() {
    var html = document.documentElement;
    var isLight = html.getAttribute('data-theme') === 'light';
    if (isLight) { html.removeAttribute('data-theme'); localStorage.setItem('theme','dark'); }
    else { html.setAttribute('data-theme','light'); localStorage.setItem('theme','light'); }
    var icon = document.getElementById('themeIcon');
    if (icon) icon.className = isLight ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
}
function exportCSV() {
    var rows = [['Time','Ingredient','Type','Amount','Reference','By']];
    document.querySelectorAll('#histBody tr:not(.hidden)').forEach(function(tr) {
        var tds = tr.querySelectorAll('td');
        if (!tds.length) return;
        rows.push([
            tds[0].innerText.replace(/\n/g,' '),
            tds[1].innerText,
            tds[2].innerText,
            tds[3].innerText,
            tds[4].innerText,
            tds[5].innerText
        ]);
    });
    var csv = rows.map(function(r){ return r.map(function(c){ return '"'+c.replace(/"/g,'""')+'"'; }).join(','); }).join('\n');
    var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8'});
    var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'ingredient_history.csv'; a.click();
}
function pollHistory() {
    var lastId = 0;
    var lastTd = document.querySelector('#histBody td:last-child');
    if (lastTd) {
        var row = lastTd.closest('tr');
        if (row) { /* use last row's first td text as fallback */ }
    }
}
function buildRow(r, esc) {
    var sign = parseFloat(r.amount) < 0 ? '' : '+';
    var cls  = parseFloat(r.amount) < 0 ? 'amount-neg' : 'amount-pos';
    var t    = r.created_at ? new Date(r.created_at.replace(' ','T')+'Z') : new Date();
    var ts   = t.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'})+' '+t.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
    var ref  = r.daily_order_no ? 'Order #'+r.daily_order_no : (r.reference || '—');
    return '<tr data-name="'+(r.ingredient_name||'').toLowerCase()+'">'+
        '<td style="font-size:12px;color:var(--text-muted);">'+ts+'</td>'+
        '<td><div class="ing-cell"><span class="ing-dot"><i class="fa-solid fa-egg"></i></span>'+esc(r.ingredient_name)+'</div></td>'+
        '<td><span class="type-badge '+esc(r.change_type)+'">'+esc((r.change_type||'').replace(/_/g,' '))+'</span></td>'+
        '<td class="'+cls+'">'+sign+esc(r.amount)+'</td>'+
        '<td style="font-size:12px;color:var(--text-muted);">'+esc(ref)+'</td>'+
        '<td style="font-size:12px;color:var(--text-muted);">'+esc(r.created_by||'—')+'</td></tr>';
}
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function showToast(msg, isErr) {
    var cnt = document.getElementById('toast-cnt');
    if (!cnt) return;
    var t = document.createElement('div');
    t.className = 'toast '+(isErr?'error':'success');
    t.innerHTML = '<i class="fa-solid fa-'+ (isErr?'circle-exclamation':'check-circle') +'"></i>'+msg;
    cnt.appendChild(t);
    setTimeout(function(){ t.classList.add('show'); },10);
    setTimeout(function(){ t.classList.remove('show'); setTimeout(function(){ t.remove(); },400); },3000);
}
document.addEventListener('DOMContentLoaded', function() {
    var icon = document.getElementById('themeIcon');
    if (icon && document.documentElement.getAttribute('data-theme')==='light') icon.className = 'fa-solid fa-sun';
});
</script>
</body>
</html>
