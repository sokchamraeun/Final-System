<?php
$stats = $stats ?? [];
?>
<div class="stats-row">
    <?php foreach ($stats as $s): ?>
    <div class="stat-card" style="<?= $s['style'] ?? '' ?>">
        <div class="stat-label"><?= htmlspecialchars($s['label'] ?? '') ?></div>
        <div class="stat-value" style="color:<?= htmlspecialchars($s['color'] ?? 'var(--accent)') ?>">
            <?= $s['value'] ?? '0' ?>
        </div>
        <?php if (!empty($s['hint'])): ?>
        <div class="stat-hint"><?= $s['hint'] ?></div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
