<?php
declare(strict_types=1);
/* Orders board — slim account-controls bar: theme toggle, clock in/out, profile, logout.
   Expects in scope: $_is_clocked_in, $_clock_since. */
$clocked  = $_is_clocked_in ?? false;
$clkIcon  = $clocked ? 'right-from-bracket'    : 'fingerprint';
$clkLabel = $clocked ? 'Clock Out'             : 'Clock In';
$clkTitle = $clocked ? 'Clocked in at ' . ($_clock_since ?? '') : 'Not clocked in';
?>
<div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:18px;">
    <button class="btn-pill theme-toggle" onclick="toggleTheme()">
        <i class="fa-solid fa-moon" id="themeIcon"></i>
        <span id="themeText">Light</span>
    </button>
    <button id="clockBtn" data-clocked="<?= $clocked ? '1' : '0' ?>"
        class="btn-pill<?= $clocked ? ' logout-btn' : '' ?>"
        onclick="toggleClock()"
        title="<?= e($clkTitle) ?>">
        <i class="fa-solid fa-<?= $clkIcon ?>"></i> <?= $clkLabel ?>
    </button>
    <?php if (can('my_profile')): ?>
    <a href="<?= e(url('profile')) ?>" class="btn-pill">
        <i class="fa-solid fa-circle-user"></i> Profile
    </a>
    <?php endif; ?>
    <a href="<?= e(url('shift_report.php')) ?>" class="btn-pill logout-btn">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>

<script>
function toggleTheme() {
    var html = document.documentElement;
    var light = html.getAttribute('data-theme') === 'light';
    if (light) { html.removeAttribute('data-theme'); localStorage.setItem('theme', 'dark'); }
    else       { html.setAttribute('data-theme', 'light'); localStorage.setItem('theme', 'light'); }
    syncThemeToggle();
}
function syncThemeToggle() {
    var light = document.documentElement.getAttribute('data-theme') === 'light';
    var icon = document.getElementById('themeIcon');
    var text = document.getElementById('themeText');
    if (icon) icon.className = light ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    if (text) text.textContent = light ? 'Light' : 'Dark';
}
syncThemeToggle();

async function toggleClock() {
    var btn = document.getElementById('clockBtn');
    var clocked = btn.dataset.clocked === '1';
    btn.disabled = true;
    btn.style.opacity = '.6';

    try {
        var resp = await fetch('<?= url('attendance/action') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=' + (clocked ? 'clock_out' : 'clock_in')
        });
        var data = await resp.json();

        if (data.ok) {
            if (!clocked) {
                btn.dataset.clocked = '1';
                btn.classList.add('logout-btn');
                btn.innerHTML = '<i class="fa-solid fa-right-from-bracket"></i> Clock Out';
                btn.title = 'Clocked in at ' + (data.time || '');
            } else {
                btn.dataset.clocked = '0';
                btn.classList.remove('logout-btn');
                btn.innerHTML = '<i class="fa-solid fa-fingerprint"></i> Clock In';
                btn.title = 'Not clocked in';
            }
            showClockToast(data.msg, false);
        } else {
            showClockToast(data.msg, true);
        }
    } catch(e) {
        showClockToast('Connection error.', true);
    }

    btn.disabled = false;
    btn.style.opacity = '1';
}

function showClockToast(msg, isErr) {
    var el = document.createElement('div');
    el.style.cssText = 'position:fixed;bottom:28px;left:50%;transform:translateX(-50%);' +
        'padding:12px 22px;border-radius:12px;font-size:13px;font-weight:600;z-index:9999;' +
        'box-shadow:0 8px 32px rgba(15,23,42,.18);' +
        (isErr
            ? 'background:#fff1f2;border:1px solid #fecdd3;color:#be123c;'
            : 'background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;');
    el.innerHTML = (isErr ? '<i class="fa-solid fa-circle-exclamation"></i> ' : '<i class="fa-solid fa-circle-check"></i> ') + msg;
    document.body.appendChild(el);
    setTimeout(function(){ el.remove(); }, 3000);
}
</script>
