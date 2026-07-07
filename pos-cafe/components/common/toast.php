<?php
declare(strict_types=1);
/* Server-triggered toast: emits a script that fires window.toast() on load.
   component('common/toast', ['message' => 'Saved!', 'type' => 'success']);
   The toast container + window.toast() live in layout/footer.php. */
$message = $message ?? '';
$type    = $type    ?? 'success';
if ($message === '') return;
?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.toast) window.toast(<?= json_encode($message) ?>, <?= json_encode($type) ?>);
  });
</script>
