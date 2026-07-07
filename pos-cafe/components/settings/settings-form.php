<?php
declare(strict_types=1);
/* Settings form. component('settings/settings-form', ['settings' => $settings, 'errors' => $errs]); */
$settings = $settings ?? [];
$errors   = $errors   ?? [];

$val = static fn(string $k, $d = '') => e((string) ($settings[$k] ?? $d));
?>
<form method="POST" action="<?= e(url('pages/settings/index.php')) ?>" class="space-y-6">
  <?= csrf_field() ?>

  <?php if ($errors): ?>
  <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
    <ul class="list-inside list-disc space-y-0.5">
      <?php foreach ($errors as $fieldErrs): foreach ($fieldErrs as $msg): ?>
        <li><?= e($msg) ?></li>
      <?php endforeach; endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- Tax & Currency -->
  <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="mb-4 text-lg font-bold text-slate-800 dark:text-white">Tax &amp; Currency</h3>
    <div class="grid gap-5 sm:grid-cols-2">
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Tax Rate (%)</label>
        <input type="number" name="settings[tax_rate]" step="0.01" min="0" max="100" value="<?= $val('tax_rate', '0') ?>"
               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      </div>
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">KHR Exchange Rate</label>
        <input type="number" name="settings[khr_exchange_rate]" step="0.01" min="0" value="<?= $val('khr_exchange_rate', '4000') ?>"
               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
        <p class="mt-1 text-xs text-slate-400">1 USD = ? KHR</p>
      </div>
    </div>
  </div>

  <!-- Happy Hour -->
  <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="mb-4 text-lg font-bold text-slate-800 dark:text-white">Happy Hour</h3>
    <div class="grid gap-5 sm:grid-cols-2">
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Start Time</label>
        <input type="time" name="settings[happy_hour_start]" value="<?= $val('happy_hour_start', '14:00') ?>"
               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      </div>
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">End Time</label>
        <input type="time" name="settings[happy_hour_end]" value="<?= $val('happy_hour_end', '17:00') ?>"
               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      </div>
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Discount (%)</label>
        <input type="number" name="settings[happy_hour_discount]" step="0.01" min="0" max="100" value="<?= $val('happy_hour_discount', '10') ?>"
               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      </div>
      <div class="flex items-end pb-2">
        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
          <input type="checkbox" name="settings[happy_hour_enabled]" value="1" <?= (string) ($settings['happy_hour_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
          Enable Happy Hour
        </label>
      </div>
    </div>
  </div>

  <!-- Loyalty -->
  <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="mb-4 text-lg font-bold text-slate-800 dark:text-white">Loyalty Program</h3>
    <div class="grid gap-5 sm:grid-cols-2">
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Buy X Get Y Count</label>
        <input type="number" name="settings[buy_x_count]" min="1" step="1" value="<?= $val('buy_x_count', '10') ?>"
               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
        <p class="mt-1 text-xs text-slate-400">Buy X drinks, get 1 free.</p>
      </div>
      <div class="flex items-end pb-2">
        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
          <input type="checkbox" name="settings[loyalty_enabled]" value="1" <?= (string) ($settings['loyalty_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
          Enable Loyalty Program
        </label>
      </div>
    </div>
  </div>

  <!-- Stand / Display -->
  <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="mb-4 text-lg font-bold text-slate-800 dark:text-white">Display &amp; Stand</h3>
    <div class="grid gap-5 sm:grid-cols-2">
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Stand Count</label>
        <input type="number" name="settings[stand_count]" min="0" step="1" value="<?= $val('stand_count', '1') ?>"
               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      </div>
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Currency</label>
        <select name="settings[currency]"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
          <option value="USD" <?= ($settings['currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
          <option value="KHR" <?= ($settings['currency'] ?? 'USD') === 'KHR' ? 'selected' : '' ?>>KHR (៛)</option>
        </select>
      </div>
    </div>
  </div>

  <div class="flex items-center justify-end gap-2">
    <button type="submit" class="rounded-xl bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-floppy-disk"></i> Save Settings
    </button>
  </div>
</form>
