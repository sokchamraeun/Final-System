<?php
function businessRangeFromDate(string $dateYmd): array {
    $start = new DateTime($dateYmd . " 06:00:00");
    $end = clone $start;
    $end->modify("+1 day")->modify("-1 second");
    return [$start, $end];
}

function getBusinessDateToday(): string {
    $now = new DateTime();
    if ((int)$now->format("H") < 6) {
        $now->modify("-1 day");
    }
    return $now->format("Y-m-d");
}

function fmtQty($n): string {
    return rtrim(rtrim(number_format((float)$n, 2, '.', ''), '0'), '.');
}

function fmtMoney($n): string {
    return number_format((float)$n, 2);
}

function deltaStr(float $current, float $prev): string {
    if ($prev <= 0) return '';
    $pct = round(($current - $prev) / $prev * 100, 1);
    if ($pct === 0.0) return '<span class="delta neutral">= same</span>';
    $cls = $pct > 0 ? 'up' : 'down';
    $arrow = $pct > 0 ? '&#9650;' : '&#9660;';
    return "<span class=\"delta {$cls}\">{$arrow} " . abs($pct) . "%</span>";
}
