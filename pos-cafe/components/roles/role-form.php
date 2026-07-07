<?php
declare(strict_types=1);
/* Role form. component('roles/role-form', ['role' => $role, 'errors' => $errs, 'action' => url(...)]); */
$role   = $role   ?? [];
$errors = $errors ?? [];
$action = $action ?? '';
$isEdit = !empty($role['id']);

$val = static fn(string $k, $d = '') => e($role[$k] ?? $d);
?>
<form method="POST" action="<?= e($action) ?>" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
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
      <input type="text" name="name" value="<?= $val('name') ?>" required maxlength="100"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Slug</label>
      <input type="text" name="slug" value="<?= $val('slug') ?>" required maxlength="50"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      <p class="mt-1 text-xs text-slate-400">Lowercase, hyphenated identifier (e.g. "kitchen_staff").</p>
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Icon</label>
      <input type="text" name="icon" value="<?= $val('icon') ?>" maxlength="50" placeholder="fa-user"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      <p class="mt-1 text-xs text-slate-400">Font Awesome icon class (e.g. "fa-user").</p>
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Color</label>
      <div class="flex gap-2">
        <input type="color" name="color" value="<?= $val('color', '#3B82F6') ?>"
               class="h-10 w-10 cursor-pointer rounded-lg border border-slate-200 bg-white p-0.5 dark:border-slate-700">
        <input type="text" name="color_hex" value="<?= $val('color', '#3B82F6') ?>" maxlength="7"
               class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      </div>
    </div>

    <div class="sm:col-span-2">
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Description</label>
      <textarea name="description" rows="3" maxlength="255"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800"><?= $val('description') ?></textarea>
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
      <input type="checkbox" name="is_system" value="1"
             <?= (int) ($role['is_system'] ?? 0) === 1 ? 'checked' : '' ?>
             <?= $isEdit && !empty($role['is_system']) ? 'disabled' : '' ?>
             class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
      System role (protected)
      <?php if ($isEdit && !empty($role['is_system'])): ?>
      <input type="hidden" name="is_system" value="1">
      <?php endif; ?>
    </label>
  </div>

  <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
    <a href="<?= e(url('pages/roles/index.php')) ?>" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
    <button type="submit" class="rounded-xl bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-check"></i> <?= $isEdit ? 'Save changes' : 'Create role' ?>
    </button>
  </div>
</form>

<script>
  (function () {
    var colorInput = document.querySelector('input[type="color"][name="color"]');
    var hexInput = document.querySelector('input[name="color_hex"]');
    if (!colorInput || !hexInput) return;
    colorInput.addEventListener('input', function () { hexInput.value = this.value; });
    hexInput.addEventListener('input', function () { colorInput.value = this.value; });
  })();
</script>
