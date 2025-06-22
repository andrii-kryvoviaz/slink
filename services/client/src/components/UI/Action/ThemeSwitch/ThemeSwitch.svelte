<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Theme } from '@slink/lib/settings';

  interface Props {
    disabled?: boolean;
    checked?: boolean;
    on: { change: (theme: Theme) => void };
  }

  let { disabled = false, checked = false, on }: Props = $props();

  const handleThemeChange = () => {
    if (disabled) return;
    on.change(checked ? Theme.LIGHT : Theme.DARK);
  };
</script>

<button
  type="button"
  class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-background border border-bc-header text-muted-foreground hover:text-foreground hover:bg-background/80 hover:border-border/80 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm hover:shadow-md backdrop-blur-sm"
  onclick={handleThemeChange}
  {disabled}
  aria-label="Toggle theme"
>
  {#if checked}
    <Icon
      icon="ph:moon"
      class="h-4 w-4 transition-all duration-200 hover:scale-110"
    />
  {:else}
    <Icon
      icon="ph:sun"
      class="h-4 w-4 transition-all duration-200 hover:scale-110"
    />
  {/if}
</button>
