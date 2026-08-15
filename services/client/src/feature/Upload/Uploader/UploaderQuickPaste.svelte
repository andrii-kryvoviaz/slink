<script lang="ts">
  import { Shortcut } from '@slink/ui/components';
</script>

<div class="group/shortcut">
  <div
    class="flex items-center gap-4 rounded-full py-[9px] pl-5 pr-2.5 bg-muted/70 dark:bg-foreground-muted/5 border border-border/60 dark:border-border/30 transition-colors duration-200"
  >
    <span
      class="text-[10.5px] font-semibold uppercase tracking-[0.18em] text-foreground-muted dark:text-foreground-subtle"
      >Quick paste</span
    >
    <span class="w-px h-[18px] bg-border-bold/20" aria-hidden="true"></span>
    <Shortcut
      control={true}
      key="v"
      class="h-auto rounded-[10px] px-3 py-[5px] text-[13px] dark:border-border-strong/55 dark:bg-muted/80 dark:text-foreground-soft"
    />
  </div>
</div>
