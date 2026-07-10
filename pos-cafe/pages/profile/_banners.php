<?php if ($must_change): ?>
<div class="banner warning" style="animation-delay:.05s">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        <strong>Password change required</strong>
        <p>Your account requires a new password before you can access the system.</p>
    </div>
</div>
<?php endif; ?>

<?php if (!$has_sq): ?>
<div class="banner" style="background:var(--teal-dim);border:1px solid var(--teal-glow);color:var(--teal-light);animation-delay:.1s">
    <i class="fa-solid fa-shield-exclamation"></i>
    <span><strong style="color:var(--text)">No security question set.</strong> <span style="color:var(--text-muted);font-size:13px">Set one up below so you can recover your account if you ever forget your password — without needing to contact an admin.</span></span>
</div>
<?php endif; ?>
