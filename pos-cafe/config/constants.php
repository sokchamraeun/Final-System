<?php
declare(strict_types=1);

/* ============================================================
   UI / application-level constants.

   Business constants (TAX_RATE, HAPPY_HOUR_*, BUY_X_COUNT,
   KHR_RATE, STAND_COUNT, ...) are already defined by the root
   config.php from the `settings` table — do not redefine them.
   This file only holds presentation-layer constants for the
   pos-cafe component system.
   ============================================================ */

/* Pagination */
if (!defined('PER_PAGE'))       define('PER_PAGE', 12);

/* Theme palette (kept in sync with the Tailwind config in the layout) */
if (!defined('COLOR_PRIMARY'))  define('COLOR_PRIMARY', '#F59E0B'); // amber-500
if (!defined('COLOR_SECONDARY'))define('COLOR_SECONDARY', '#0F766E'); // teal-700
if (!defined('COLOR_SIDEBAR'))  define('COLOR_SIDEBAR', '#111827'); // gray-900

/* Order status → badge colour map (Tailwind classes) */
if (!defined('STATUS_BADGES')) {
    define('STATUS_BADGES', [
        'Completed'      => 'bg-emerald-100 text-emerald-700',
        'Preparing'      => 'bg-amber-100 text-amber-700',
        'PendingPayment' => 'bg-red-100 text-red-700',
        'Cancelled'      => 'bg-slate-200 text-slate-600',
        'Refunded'       => 'bg-purple-100 text-purple-700',
    ]);
}
