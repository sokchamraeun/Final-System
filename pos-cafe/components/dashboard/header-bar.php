<?php
declare(strict_types=1);
/* ── Expects in scope: $admin_name, $_is_mgr, $_is_clocked_in, $_clock_since, $_cur_role_color, $_cur_role_name, $_flash_welcome ── */
$clocked  = $_is_clocked_in ?? false;
$clkBg    = $clocked ? 'rgba(255,95,95,.08)'   : 'rgba(85,224,135,.08)';
$clkBr    = $clocked ? 'rgba(255,95,95,.25)'   : 'rgba(85,224,135,.25)';
$clkColor = $clocked ? '#ff6b6b'               : '#55e087';
$clkIcon  = $clocked ? 'right-from-bracket'    : 'fingerprint';
$clkLabel = $clocked ? 'Clock Out'             : 'Clock In';
$clkTitle = $clocked ? 'Clocked in at ' . ($_clock_since ?? '') : 'Not clocked in';
?>
<div class="dash-header fu" style="animation-delay:.0s">
  <div>
    <h1>Good <span id="timeOfDay">morning</span>, <span class="name"><?= e($admin_name) ?></span>
      <?php if (!$_is_mgr): ?>
      <span class="role-badge" style="--role-color:<?= e($_cur_role_color) ?>;"><?= e($_cur_role_name) ?></span>
      <?php endif; ?>
    </h1>
    <p class="header-sub">
      <i class="fa-regular fa-calendar-days"></i>
      <?= date("l, d F Y") ?>
    </p>
  </div>
  <div class="header-actions">
    <button class="theme-toggle" onclick="toggleTheme()">
      <i class="fa-solid fa-moon" id="themeIcon"></i>
      <span id="themeText">Dark</span>
    </button>
    <?php if (!$_is_mgr): ?>
    <button id="clockBtn" data-clocked="<?= $clocked ? '1' : '0' ?>"
        onclick="toggleClock()"
        title="<?= e($clkTitle) ?>"
        style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:10px;font-size:13px;font-weight:500;cursor:pointer;background:<?= $clkBg ?>;border:1px solid <?= $clkBr ?>;color:<?= $clkColor ?>;transition:all .2s;font-family:'Poppins',sans-serif">
      <i class="fa-solid fa-<?= $clkIcon ?>"></i> <?= $clkLabel ?>
    </button>
    <a href="<?= e(root_url('shift_report.php')) ?>" class="logout-btn" title="View shift report &amp; log out">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span>Logout</span>
    </a>
    <?php endif; ?>
  </div>
</div>
