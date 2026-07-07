<?php
declare(strict_types=1);
/* Reusable modal shell.
   component('common/modal', [
     'id' => 'confirmModal', 'title' => 'Delete product?',
     'body' => '<p>...</p>', 'footer' => '<button ...>',
   ]);
   Toggle from JS: openModal('confirmModal') / closeModal('confirmModal'). */
$id     = $id     ?? 'modal';
$title  = $title  ?? '';
$body   = $body   ?? '';
$footer = $footer ?? '';
?>
<div id="<?= e($id) ?>" class="fixed inset-0 z-[90] hidden items-center justify-center bg-black/50 p-4 backdrop-blur-sm" onclick="if(event.target===this)closeModal('<?= e($id) ?>')">
  <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl dark:bg-slate-900">
    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
      <h3 class="text-lg font-bold text-slate-800 dark:text-white"><?= e($title) ?></h3>
      <button type="button" onclick="closeModal('<?= e($id) ?>')" class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="px-6 py-5 text-sm text-slate-600 dark:text-slate-300"><?= $body ?></div>
    <?php if ($footer !== ''): ?>
    <div class="flex justify-end gap-2 border-t border-slate-100 px-6 py-4 dark:border-slate-800"><?= $footer ?></div>
    <?php endif; ?>
  </div>
</div>
<?php if (empty($GLOBALS['_modal_js'])): $GLOBALS['_modal_js'] = true; ?>
<script>
  function openModal(id){ var m=document.getElementById(id); if(m){ m.classList.remove('hidden'); m.classList.add('flex'); } }
  function closeModal(id){ var m=document.getElementById(id); if(m){ m.classList.add('hidden'); m.classList.remove('flex'); } }
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') document.querySelectorAll('.fixed.z-\\[90\\]:not(.hidden)').forEach(function(m){ m.classList.add('hidden'); m.classList.remove('flex'); }); });
</script>
<?php endif; ?>
