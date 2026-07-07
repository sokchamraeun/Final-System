<?php declare(strict_types=1);
/* ── Manager dashboard: Revenue & Orders chart card (matches design). ──
   Expects: $chart = ['hourly'|'daily'|'monthly'|'yearly'|'custom' => series]
   where each series has labels[], revenue[], orders[], totalRevenue,
   totalOrders, avg, peak. ── */
$chart   = $chart ?? [];
$periods = [
    'hourly'  => 'Hourly',
    'daily'   => 'Daily',
    'monthly' => 'Monthly',
    'yearly'  => 'Yearly',
    'custom'  => 'Custom',
];
$default = 'daily';
$sub = ['hourly' => "Today, by hour", 'daily' => "Daily performance over the last 7 days.",
        'monthly' => "Monthly performance over the last 12 months.",
        'yearly' => "Yearly performance over the last 5 years.",
        'custom' => "Daily performance over the last 30 days."];
?>
<div class="mcard">
  <div class="mcard-head">
    <div class="rc-top">
      <div>
        <h3>Revenue &amp; Orders</h3>
        <p id="rcSub"><?= e($sub[$default]) ?></p>
      </div>
      <div class="rc-toggle" id="rcToggle">
        <?php foreach ($periods as $key => $label): ?>
        <button type="button" data-period="<?= e($key) ?>" class="<?= $key === $default ? 'on' : '' ?>"><?= e($label) ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="rc-mini">
    <div class="rc-box"><div class="k">Total Revenue</div><div class="v" id="rcTotalRev">$0.00</div></div>
    <div class="rc-box"><div class="k">Total Orders</div><div class="v" id="rcTotalOrd">0</div></div>
    <div class="rc-box"><div class="k">Avg Order Value</div><div class="v" id="rcAvg">$0.00</div></div>
    <div class="rc-box"><div class="k">Peak Revenue</div><div class="v" id="rcPeak">$0.00</div></div>
  </div>

  <div class="rc-legend">
    <span><i class="rc-dot" style="background:#0f766e"></i> Revenue</span>
    <span><i class="rc-dot" style="background:#3b82f6"></i> Orders</span>
  </div>

  <div class="rc-canvas-wrap"><canvas id="rcChart" aria-label="Revenue and orders over time"></canvas></div>
</div>

<script>
(function(){
  var DATA = <?= json_encode($chart, JSON_UNESCAPED_UNICODE) ?>;
  var SUBS = <?= json_encode($sub, JSON_UNESCAPED_UNICODE) ?>;
  var current = '<?= $default ?>';
  var chart = null;

  function money(n){ return '$' + Number(n).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}); }

  function setMini(s){
    var g = function(id){ return document.getElementById(id); };
    if (g('rcTotalRev')) g('rcTotalRev').textContent = money(s.totalRevenue);
    if (g('rcTotalOrd')) g('rcTotalOrd').textContent = Number(s.totalOrders).toLocaleString();
    if (g('rcAvg'))      g('rcAvg').textContent      = money(s.avg);
    if (g('rcPeak'))     g('rcPeak').textContent     = money(s.peak);
  }

  function render(period){
    var s = DATA[period]; if (!s) return;
    current = period;
    setMini(s);
    var sub = document.getElementById('rcSub'); if (sub) sub.textContent = SUBS[period] || '';
    var canvas = document.getElementById('rcChart'); if (!canvas || !window.Chart) return;
    var ctx = canvas.getContext('2d');
    var grad = ctx.createLinearGradient(0, 0, 0, 300);
    grad.addColorStop(0, 'rgba(15,118,110,.28)');
    grad.addColorStop(1, 'rgba(15,118,110,0)');

    var cfg = {
      type: 'line',
      data: {
        labels: s.labels,
        datasets: [
          { label:'Revenue', data:s.revenue, borderColor:'#0f766e', backgroundColor:grad,
            borderWidth:2.5, fill:true, tension:.4, pointRadius:3, pointBackgroundColor:'#0f766e',
            pointHoverRadius:5, yAxisID:'y' },
          { label:'Orders', data:s.orders, borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,.0)',
            borderWidth:2, fill:false, tension:.4, pointRadius:2, pointBackgroundColor:'#3b82f6',
            borderDash:[5,4], yAxisID:'y1' }
        ]
      },
      options: {
        responsive:true, maintainAspectRatio:false, interaction:{mode:'index', intersect:false},
        plugins:{ legend:{display:false},
          tooltip:{ callbacks:{ label:function(c){ return c.dataset.label + ': ' + (c.dataset.label==='Revenue' ? money(c.parsed.y) : c.parsed.y); } } } },
        scales:{
          x:{ grid:{display:false}, ticks:{color:'#94a3b8', font:{size:11}} },
          y:{ position:'left', grid:{color:'rgba(148,163,184,.15)'}, ticks:{color:'#94a3b8', font:{size:11}, callback:function(v){ return '$'+v; }} },
          y1:{ position:'right', grid:{drawOnChartArea:false}, ticks:{color:'#94a3b8', font:{size:11}, precision:0} }
        }
      }
    };
    if (chart) chart.destroy();
    chart = new Chart(canvas, cfg);
  }

  function boot(){
    render(current);
    var wrap = document.getElementById('rcToggle');
    if (wrap) wrap.addEventListener('click', function(e){
      var b = e.target.closest('button[data-period]'); if (!b) return;
      wrap.querySelectorAll('button').forEach(function(x){ x.classList.remove('on'); });
      b.classList.add('on');
      render(b.dataset.period);
    });
  }

  // Chart.js may not be present yet when injected via the SPA router — load on demand.
  if (window.Chart) { boot(); }
  else {
    var existing = document.getElementById('chartjs-cdn');
    if (existing) { existing.addEventListener('load', boot); }
    else {
      var sc = document.createElement('script');
      sc.id = 'chartjs-cdn';
      sc.src = 'https://cdn.jsdelivr.net/npm/chart.js@4';
      sc.onload = boot;
      document.head.appendChild(sc);
    }
  }
})();
</script>
