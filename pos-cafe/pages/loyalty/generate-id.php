<?php
declare(strict_types=1);
/* Loyalty — generate a new loyalty ID and create the card (from loyalty_generate_id.php). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'loyalty';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'loyalty';
$l = new Loyalty();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/loyalty/generate-id.php'));
    }
    $loyaltyId = trim((string) input('loyalty_id', ''));
    if ($loyaltyId === '') {
        $loyaltyId = $l->generateUniqueId();
    }
    if ($l->findByLoyaltyId($loyaltyId)) {
        flash('That loyalty ID already exists.', 'error');
        redirect(url('pages/loyalty/generate-id.php'));
    }
    $l->create(['loyalty_id' => $loyaltyId, 'points' => 0, 'is_active' => 1]);
    flash('Card ' . $loyaltyId . ' created.', 'success');
    redirect(url('pages/loyalty/lookup.php?loyalty_id=' . urlencode($loyaltyId)));
}

/* Pre-generate one so the page shows a ready ID on load. */
$suggested = $l->generateUniqueId();

$pageTitle = 'New Loyalty Card';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main', ['title' => 'New Loyalty Card', 'crumbs' => ['Sales', 'Loyalty', 'New']]);
?>
<div class="mx-auto max-w-md">
  <form method="POST" action="<?= e(url('pages/loyalty/generate-id.php')) ?>" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <?= csrf_field() ?>
    <div class="flex flex-col items-center text-center">
      <div class="mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-amber-50 text-2xl text-brand dark:bg-slate-800"><i class="fa-solid fa-id-card"></i></div>
      <h3 class="text-lg font-bold text-slate-800 dark:text-white">Generate a loyalty ID</h3>
      <p class="mt-1 text-sm text-slate-500">A unique ID is created for the customer's new card.</p>
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Loyalty ID</label>
      <div class="flex items-center gap-2">
        <input type="text" name="loyalty_id" id="genId" value="<?= e($suggested) ?>" required
               class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center font-mono text-sm font-bold tracking-wider outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
        <button type="button" onclick="genRegenerate()" title="Regenerate" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300"><i class="fa-solid fa-rotate" id="genSpin"></i></button>
        <button type="button" onclick="genCopy()" title="Copy" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300"><i class="fa-solid fa-copy"></i></button>
      </div>
    </div>

    <button type="submit" class="w-full rounded-xl bg-brand py-3 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-plus"></i> Create Card
    </button>
  </form>
</div>

<script>
  function genRegenerate() {
    var spin = document.getElementById('genSpin');
    spin.classList.add('fa-spin');
    fetch(<?= json_encode(url('api/loyalty/generate-id.php')) ?>)
      .then(function (r) { return r.json(); })
      .then(function (d) { if (d.loyalty_id) document.getElementById('genId').value = d.loyalty_id; })
      .catch(function () { window.toast && window.toast('Could not generate', 'error'); })
      .finally(function () { spin.classList.remove('fa-spin'); });
  }
  function genCopy() {
    var v = document.getElementById('genId').value;
    navigator.clipboard.writeText(v).then(function () { window.toast && window.toast('Copied ' + v, 'success'); });
  }
</script>

<?php component('layout/footer'); ?>
