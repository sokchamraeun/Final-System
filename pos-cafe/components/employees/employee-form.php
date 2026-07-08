<?php
declare(strict_types=1);
/* Create/Edit form. component('employees/employee-form', ['employee'=>$e, 'errors'=>$errs, 'action'=>url(...)]); */
$employee = $employee ?? [];
$errors   = $errors   ?? [];
$action   = $action   ?? '';
$isEdit   = !empty($employee['employee_id']);

$val = static fn(string $k, $d = '') => e($employee[$k] ?? $d);
$shifts = ['Morning', 'Afternoon', 'Evening', 'Night', 'Swing', 'Full-time'];
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
    <div class="sm:col-span-2">
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Full name</label>
      <input type="text" name="name" value="<?= $val('name') ?>" required
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Phone</label>
      <input type="text" name="phone" value="<?= $val('phone') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Job title</label>
      <input type="text" name="job_title" value="<?= $val('job_title') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Date of birth</label>
      <input type="date" name="date_of_birth" value="<?= $val('date_of_birth') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Hire date</label>
      <input type="date" name="hire_date" value="<?= $val('hire_date') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Salary ($)</label>
      <input type="number" name="salary" step="0.01" min="0" value="<?= $val('salary') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Shift</label>
      <select name="shift"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
        <option value="">— select —</option>
        <?php foreach ($shifts as $s): ?>
          <option value="<?= e($s) ?>" <?= ($employee['shift'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="sm:col-span-2">
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Address</label>
      <textarea name="address" rows="2"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800"><?= $val('address') ?></textarea>
    </div>

    <div class="sm:col-span-2">
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Avatar</label>
      <div class="mb-3 flex items-center gap-3" id="avatarPreviewWrap">
        <img src="<?= (!empty($employee['photo'])) ? e(root_url($employee['photo'])) : '' ?>" alt="" id="avatarPreview"
             class="h-16 w-16 rounded-full border border-slate-200 object-cover dark:border-slate-700 <?= empty($employee['photo']) ? 'hidden' : '' ?>">
        <div id="avatarInitials" class="grid h-16 w-16 place-items-center rounded-full bg-brand/10 text-lg font-bold text-brand <?= !empty($employee['photo']) ? 'hidden' : '' ?>">
          <?= e(strtoupper(substr((string) ($employee['name'] ?? '?'), 0, 1))) ?>
        </div>
      </div>
      <input type="file" name="photo_file" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none file:mr-3 file:rounded-lg file:border-0 file:bg-brand file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-600 focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      <input type="hidden" name="existing_photo" value="<?= $val('photo') ?>">
    </div>
  </div>

  <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
    <a href="<?= e(url('pages/employees/index.php')) ?>" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
    <button type="submit" class="rounded-xl bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-check"></i> <?= $isEdit ? 'Save changes' : 'Create employee' ?>
    </button>
  </div>
</form>

<script>
  (function () {
    var input    = document.getElementById('avatarInput');
    var preview  = document.getElementById('avatarPreview');
    var initials = document.getElementById('avatarInitials');
    if (!input || !preview || !initials) return;
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        initials.classList.add('hidden');
      };
      reader.readAsDataURL(file);
    });
  })();
</script>
