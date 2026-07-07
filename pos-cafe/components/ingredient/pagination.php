<?php
$currentPage = $currentPage ?? 1;
$totalPages  = $totalPages  ?? 1;
$baseUrl     = $baseUrl     ?? '';
?>
<div class="pagination-wrap">
    <?php if ($currentPage > 1): ?>
    <a href="<?= htmlspecialchars($baseUrl . 'page=' . ($currentPage - 1)) ?>" class="pg-btn pg-prev"><i class="fa-solid fa-chevron-left"></i></a>
    <?php endif; ?>
    <?php
    $start = max(1, $currentPage - 3);
    $end   = min($totalPages, $currentPage + 3);
    if ($start > 1) echo '<a href="' . htmlspecialchars($baseUrl . 'page=1') . '" class="pg-btn">1</a>';
    if ($start > 2) echo '<span class="pg-dots">…</span>';
    for ($p = $start; $p <= $end; $p++):
    ?>
    <a href="<?= htmlspecialchars($baseUrl . 'page=' . $p) ?>" class="pg-btn <?= $p === $currentPage ? 'pg-active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <?php if ($end < $totalPages - 1): echo '<span class="pg-dots">…</span>'; endif; ?>
    <?php if ($end < $totalPages): ?>
    <a href="<?= htmlspecialchars($baseUrl . 'page=' . $totalPages) ?>" class="pg-btn"><?= $totalPages ?></a>
    <?php endif; ?>
    <?php if ($currentPage < $totalPages): ?>
    <a href="<?= htmlspecialchars($baseUrl . 'page=' . ($currentPage + 1)) ?>" class="pg-btn pg-next"><i class="fa-solid fa-chevron-right"></i></a>
    <?php endif; ?>
</div>
