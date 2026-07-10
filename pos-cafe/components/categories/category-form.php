<?php
declare(strict_types=1);
/* Create/Edit form. component('categories/category-form', ['category'=>$c, 'errors'=>$errs, 'action'=>url(...)]); */
$category = $category ?? [];
$errors   = $errors   ?? [];
$action   = $action   ?? '';
$isEdit   = !empty($category['category_id']);

$val = static fn(string $k, $d = '') => e($category[$k] ?? $d);
?>
<form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
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
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Name</label>
      <input type="text" name="name" value="<?= $val('name') ?>" required
             class="slug-source w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Slug</label>
      <input type="text" name="slug" value="<?= $val('slug') ?>" <?= $isEdit ? 'required' : 'readonly' ?>
             class="slug-target w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800 <?= $isEdit ? '' : 'bg-slate-50 dark:bg-slate-800' ?>">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Icon (Font Awesome class)</label>
      <input type="text" name="icon" value="<?= $val('icon') ?>" placeholder="fa-tag"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Display order</label>
      <input type="number" name="display_order" min="0" value="<?= $val('display_order', '0') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>
  </div>

  <div>
    <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Image</label>
    <div class="mb-3" id="imagePreviewWrap" <?= ($isEdit && !empty($category['image'])) ? '' : 'style="display:none"' ?>>
      <img src="<?= ($isEdit && !empty($category['image'])) ? e(root_url($category['image'])) : '' ?>" alt="" id="imagePreview" class="h-20 w-20 rounded-lg border border-slate-200 object-cover dark:border-slate-700">
    </div>
    <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp,image/gif"
           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none file:mr-3 file:rounded-lg file:border-0 file:bg-brand file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-600 focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    <input type="hidden" name="existing_image" value="<?= $val('image') ?>">
  </div>

  <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/30">
    <div class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Customization Options</div>
    <div class="flex flex-wrap gap-4">
      <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300">
        <input type="hidden" name="enable_ice" value="0">
        <input type="checkbox" name="enable_ice" value="1" <?= (int) ($category['enable_ice'] ?? 1) === 1 ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
        Ice
      </label>
      <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300">
        <input type="hidden" name="enable_sugar" value="0">
        <input type="checkbox" name="enable_sugar" value="1" <?= (int) ($category['enable_sugar'] ?? 1) === 1 ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
        Sugar
      </label>
      <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300">
        <input type="hidden" name="enable_milk" value="0">
        <input type="checkbox" name="enable_milk" value="1" <?= (int) ($category['enable_milk'] ?? 1) === 1 ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
        Milk
      </label>
      <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300">
        <input type="hidden" name="enable_addons" value="0">
        <input type="checkbox" name="enable_addons" value="1" <?= (int) ($category['enable_addons'] ?? 1) === 1 ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
        Add-ons
      </label>
    </div>
  </div>

  <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
    <input type="checkbox" name="is_active" value="1" <?= (int) ($category['is_active'] ?? 1) === 1 ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
    Active
  </label>

  <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
    <a href="<?= e(url('pages/categories/index.php')) ?>" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
    <button type="submit" class="rounded-xl bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-check"></i> <?= $isEdit ? 'Save changes' : 'Create category' ?>
    </button>
  </div>
</form>

<?php if (!$isEdit): ?>
<script>
  (function () {
    var src = document.querySelector('.slug-source');
    var tgt = document.querySelector('.slug-target');
    if (!src || !tgt) return;
    function sync() {
      tgt.value = src.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }
    src.addEventListener('input', sync);
  })();
</script>
<?php endif; ?>

<script>
  (function () {
    var input   = document.getElementById('imageInput');
    var wrap    = document.getElementById('imagePreviewWrap');
    var preview = document.getElementById('imagePreview');
    if (!input || !wrap || !preview) return;
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.src = e.target.result;
        wrap.style.display = '';
      };
      reader.readAsDataURL(file);
    });
  })();
</script>
