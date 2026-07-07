<?php
declare(strict_types=1);
/* Standalone theme toggle button (the top navbar already includes one;
   use this to drop a toggle elsewhere). Relies on toggleTheme() from
   layout/footer.php. */
?>
<button type="button" onclick="toggleTheme()" title="Toggle theme"
        class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
  <i class="fa-solid fa-moon dark:hidden"></i>
  <i class="fa-solid fa-sun hidden dark:inline"></i>
</button>
