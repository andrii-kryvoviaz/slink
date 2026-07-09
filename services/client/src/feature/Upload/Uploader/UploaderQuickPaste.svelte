<script lang="ts">
  import { Shortcut } from '@slink/ui/components';
</script>

<div class="group/shortcut">
  <div
    class="flex items-center gap-4 pl-4 pr-3 py-2 rounded-xl bg-slate-100/70 dark:bg-slate-700/40 border border-slate-200/50 dark:border-slate-600/30 backdrop-blur-sm group-hover/shortcut:bg-slate-200/80 dark:group-hover/shortcut:bg-slate-600/50 transition-all duration-200"
  >
    <span
      class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400"
      >Quick paste</span
    >
    <span
      class="h-5 w-px bg-slate-300/70 dark:bg-slate-600/60"
      aria-hidden="true"
    ></span>
    <Shortcut control={true} key="v" size="lg" />
  </div>
</div>
