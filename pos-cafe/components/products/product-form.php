<?php
declare(strict_types=1);
$product    = $product    ?? [];
$categories = $categories ?? [];
$errors     = $errors     ?? [];
$action     = $action     ?? '';
$isEdit     = !empty($product['product_id']);
$modal      = $modal      ?? false;
$sizes        = $sizes        ?? [];
$iceLevels    = $iceLevels    ?? [];
$sugarLevels  = $sugarLevels  ?? [];
$milkLevels   = $milkLevels   ?? [];
$sizeLevels   = $sizeLevels   ?? [];
$selectedIce   = $selectedIce   ?? [];
$selectedSugar = $selectedSugar ?? [];
$selectedMilk  = $selectedMilk  ?? [];
$selectedSizes  = $selectedSizes  ?? [];

$val = static fn(string $k, $d = '') => e($product[$k] ?? $d);
?>
<form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="space-y-5 <?= $modal ? '' : 'rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900' ?>">
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

  <div class="grid gap-5 sm:grid-cols-2">
    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Image</label>
      <?php if ($isEdit && !empty($product['image'])): ?>
      <div class="mb-3">
        <img src="<?= e(root_url($product['image'])) ?>" alt="" class="h-24 w-24 rounded-lg border border-slate-200 object-cover dark:border-slate-700">
      </div>
      <?php endif; ?>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none file:mr-3 file:rounded-lg file:border-0 file:bg-brand file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-600 focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      <input type="hidden" name="existing_image" value="<?= $val('image') ?>">
    </div>

    <div class="sm:col-span-2">
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Product name</label>
      <input type="text" name="name" value="<?= $val('name') ?>" required
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div class="sm:col-span-2">
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Category</label>
      <select name="category"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
        <option value="">— none —</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= e($c['slug']) ?>" data-id="<?= (int) $c['category_id'] ?>"
                  <?= ($product['category'] ?? '') === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="sm:col-span-2">
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Description</label>
      <textarea name="description" rows="3"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800"><?= $val('description') ?></textarea>
    </div>

    <div class="sm:col-span-2">
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Badge (optional)</label>
      <input type="text" name="badge_text" value="<?= $val('badge_text') ?>" maxlength="40" placeholder="New, Popular…"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div class="sm:col-span-2">
      <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
        <input type="checkbox" name="has_sizes" value="1" <?= (int) ($product['has_sizes'] ?? 0) === 1 ? 'checked' : '' ?>
               onchange="document.getElementById('sizeRows').style.display=this.checked?'block':'none'"
               class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
        Has multiple sizes (S / M / L)
      </label>
    </div>
  </div>

  <!-- Size rows -->
  <div id="sizeRows" style="display:<?= (int) ($product['has_sizes'] ?? 0) === 1 ? 'block' : 'none' ?>">
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/30">
      <div class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Size Pricing</div>
      <?php foreach ($sizeLevels as $sl):
        $slid = (int) $sl['id'];
        $checked = in_array($slid, $selectedSizes, true);
        $saved = null;
        foreach ($sizes as $sz) {
          if ((int) ($sz['size_level_id'] ?? 0) === $slid) { $saved = $sz; break; }
        }
      ?>
      <div class="mb-2 flex items-center gap-2">
        <input type="checkbox" name="size_level_ids[]" value="<?= $slid ?>"
               <?= $checked ? 'checked' : '' ?>
               class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
        <span class="w-16 text-sm font-medium text-slate-600 dark:text-slate-300"><?= e($sl['name']) ?></span>
        <input type="number" step="0.01" min="0" name="size_prices[]" value="<?= e($saved['price'] ?? '') ?>" placeholder="Price"
               class="w-28 rounded-lg border border-slate-200 px-3 py-1.5 text-sm outline-none focus:border-brand dark:border-slate-600 dark:bg-slate-700">
        <input type="number" step="0.01" min="0" name="size_factors[]" value="<?= e($saved['size_factor'] ?? '1.00') ?>" placeholder="Stock ×"
               class="w-20 rounded-lg border border-slate-200 px-3 py-1.5 text-sm outline-none focus:border-brand dark:border-slate-600 dark:bg-slate-700">
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Ice, Sugar & Milk Levels -->
  <div class="grid gap-5 sm:grid-cols-3">
    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Ice Levels</label>
      <div class="flex flex-wrap gap-3">
        <?php foreach ($iceLevels as $l): ?>
          <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300">
            <input type="checkbox" name="ice_level_ids[]" value="<?= (int) $l['id'] ?>"
                   <?= in_array((int) $l['id'], $selectedIce, true) ? 'checked' : '' ?>
                   class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
            <?= e($l['name']) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Sugar Levels</label>
      <div class="flex flex-wrap gap-3">
        <?php foreach ($sugarLevels as $l): ?>
          <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300">
            <input type="checkbox" name="sugar_level_ids[]" value="<?= (int) $l['id'] ?>"
                   <?= in_array((int) $l['id'], $selectedSugar, true) ? 'checked' : '' ?>
                   class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
            <?= e($l['name']) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Milk Levels</label>
      <div class="flex flex-wrap gap-3">
        <?php foreach ($milkLevels as $l): ?>
          <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300">
            <input type="checkbox" name="milk_level_ids[]" value="<?= (int) $l['id'] ?>"
                   <?= in_array((int) $l['id'], $selectedMilk, true) ? 'checked' : '' ?>
                   class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
            <?= e($l['name']) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
    <input type="checkbox" name="is_available" value="1" <?= (int) ($product['is_available'] ?? 1) === 1 ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
    Available on the menu
  </div>

  <input type="hidden" name="category_id" id="categoryIdField" value="<?= (int) ($product['category_id'] ?? 0) ?>">

  <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
    <?php if ($modal): ?>
    <button type="button" onclick="closeModal('editProductModal')" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</button>
    <?php else: ?>
    <a href="<?= e(url('pages/products/index.php')) ?>" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
    <?php endif; ?>
    <button type="submit" class="rounded-xl bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-check"></i> <?= $isEdit ? 'Save changes' : 'Create product' ?>
    </button>
  </div>
</form>

<script>
  (function () {
    var sel = document.querySelector('select[name="category"]');
    var hid = document.getElementById('categoryIdField');
    if (!sel || !hid) return;
    function sync(){ var o = sel.options[sel.selectedIndex]; hid.value = (o && o.dataset.id) ? o.dataset.id : 0; }
    sel.addEventListener('change', sync);
  })();
</script>
