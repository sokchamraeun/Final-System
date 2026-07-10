<div class="card" data-tab-content="sales">
    <div class="section-hdr">
        <div class="section-hdr-row">
            <i class="fa-solid fa-chart-bar"></i>
            <span class="section-hdr-title">Sales Analytics</span>
        </div>
        <p class="section-desc">Quantity sold per category and your top-performing products for this period.</p>
    </div>

    <?php if (count($categorySales) > 0 || count($topProducts) > 0): ?>
        <div class="chart-grid">
            <div class="chart-wrap">
                <div class="chart-title">
                    <i class="fa-solid fa-tags"></i> Category Sales
                </div>
                <canvas id="categoryChart" role="img" aria-label="Quantity sold per product category for this period"></canvas>
            </div>

            <div class="chart-wrap">
                <div class="chart-title">
                    <i class="fa-solid fa-trophy"></i> Top Products
                </div>
                <canvas id="productChart" role="img" aria-label="Top-performing products by revenue for this period"></canvas>
            </div>
        </div>
    <?php else: ?>
        <div class="empty">
            <i class="fa-regular fa-chart-bar"></i>
            No sales data in this range.
        </div>
    <?php endif; ?>
</div>

<?php if ($mode !== 'daily' && !empty($dailyTrendData)): ?>
<div class="card" data-tab-content="sales">
    <div class="section-hdr">
        <div class="section-hdr-row">
            <i class="fa-solid fa-chart-area"></i>
            <span class="section-hdr-title">Daily Sales Trend</span>
        </div>
        <p class="section-desc">Revenue and order count per day &mdash; spot your busiest days and slow periods across the selected range.</p>
    </div>
    <div class="chart-wrap" style="height:280px;">
        <canvas id="dailyTrendChart" role="img" aria-label="Daily sales trend across the selected period"></canvas>
    </div>
</div>
<?php endif; ?>

<?php if ($mode === 'daily' && !empty($hourlyData)): ?>
<div class="card" data-tab-content="sales">
    <div class="section-hdr">
        <div class="section-hdr-row">
            <i class="fa-solid fa-clock"></i>
            <span class="section-hdr-title">Sales by Hour</span>
            <?php if ($peakHour): ?>
            <span class="section-hdr-tag"><i class="fa-solid fa-fire"></i> Peak: <?= $peakHour ?></span>
            <?php endif; ?>
        </div>
        <p class="section-desc">Hourly revenue and order volume from 06:00&ndash;22:00. Highlighted bar = highest-revenue hour. Right panel ranks top 5 hours.</p>
    </div>
    <div class="hourly-grid" style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:stretch;">
        <div class="chart-wrap" style="height:280px;aspect-ratio:unset;min-height:unset;">
            <canvas id="hourlyChart" role="img" aria-label="Sales broken down by hour of the day"></canvas>
        </div>
        <div class="chart-wrap" style="height:280px;aspect-ratio:unset;min-height:unset;display:flex;flex-direction:column;">
            <div style="font-size:13px;font-weight:600;color:var(--teal);margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                <i class="fa-solid fa-ranking-star" style="color:var(--teal);"></i>
                Top Revenue Hours
            </div>
            <canvas id="topHoursChart" style="flex:1;" role="img" aria-label="Busiest hours ranked by sales"></canvas>
        </div>
    </div>
</div>
<?php endif; ?>