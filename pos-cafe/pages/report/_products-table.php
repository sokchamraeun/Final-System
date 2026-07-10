<?php if (!empty($topProducts)): ?>
<div class="card" data-tab-content="products">
    <div class="section-hdr">
        <div class="section-hdr-row">
            <i class="fa-solid fa-table"></i>
            <span class="section-hdr-title">Product Breakdown</span>
        </div>
        <p class="section-desc">Per-item revenue, ingredient cost (COGS), profit, and margin. Click any column header to sort. Green margin = &ge;50%, orange = &ge;25%, yellow = below 25%.</p>
    </div>
    <div class="refund-table-wrapper">
        <table class="refund-table" id="productsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th onclick="sortTable(1,'str')" style="cursor:pointer;" title="Sort by name">Product <i class="fa-solid fa-sort" style="font-size:10px;opacity:0.5;"></i></th>
                    <th onclick="sortTable(2,'num')" style="cursor:pointer;text-align:right;" title="Sort by qty">Qty <i class="fa-solid fa-sort" style="font-size:10px;opacity:0.5;"></i></th>
                    <th onclick="sortTable(3,'money')" style="cursor:pointer;text-align:right;" title="Sort by revenue">Revenue <i class="fa-solid fa-sort" style="font-size:10px;opacity:0.5;"></i></th>
                    <th onclick="sortTable(4,'money')" style="cursor:pointer;text-align:right;" title="Sort by cogs">COGS <i class="fa-solid fa-sort" style="font-size:10px;opacity:0.5;"></i></th>
                    <th onclick="sortTable(5,'money')" style="cursor:pointer;text-align:right;" title="Sort by profit">Profit <i class="fa-solid fa-sort" style="font-size:10px;opacity:0.5;"></i></th>
                    <th onclick="sortTable(6,'pct')" style="cursor:pointer;text-align:right;" title="Sort by margin">Margin <i class="fa-solid fa-sort" style="font-size:10px;opacity:0.5;"></i></th>
                </tr>
            </thead>
            <tbody>
                <?php $rank=1; foreach ($topProducts as $pname => $pd):
                    $rev  = (float)$pd['revenue'];
                    $cogs = (float)$pd['cogs'];
                    $prof = $rev - $cogs;
                    $mgn  = $rev > 0 ? ($prof / $rev * 100) : 0;
                    $mgClass = $mgn >= 50 ? 'color:var(--pos)' : ($mgn >= 25 ? 'color:var(--teal)' : 'color:var(--amber)');
                ?>
                <tr>
                    <td style="color:var(--text-muted);font-weight:600;"><?= $rank++ ?></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($pname) ?></td>
                    <td style="text-align:right;"><?= (int)$pd['qty'] ?></td>
                    <td style="text-align:right;color:var(--teal);font-weight:600;">$<?= fmtMoney($rev) ?></td>
                    <td style="text-align:right;color:var(--text-muted);">$<?= fmtMoney($cogs) ?></td>
                    <td style="text-align:right;font-weight:700;<?= $prof>=0?'color:var(--pos)':'color:var(--neg)' ?>">
                        <?= $prof >= 0 ? '+' : '' ?>$<?= fmtMoney($prof) ?>
                    </td>
                    <td style="text-align:right;font-weight:700;<?= $mgClass ?>">
                        <?= number_format($mgn, 1) ?>%
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="report-pager" id="productsPager"></div>
</div>
<?php endif; ?>
