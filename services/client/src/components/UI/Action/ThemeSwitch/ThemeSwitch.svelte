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
  class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground/70 hover:text-foreground hover:bg-muted/30 transition-all duration-150 focus:outline-none focus:ring-1 focus:ring-indigo-400/30 disabled:opacity-40 disabled:cursor-not-allowed"
  onclick={handleThemeChange}
  {disabled}
  aria-label="Toggle theme"
>
  {#if checked}
    <Icon
      icon="ph:moon-thin"
      class="h-4 w-4 transition-transform duration-200 hover:scale-110"
    />
  {:else}
    <Icon
      icon="ph:sun-thin"
      class="h-4 w-4 transition-transform duration-200 hover:scale-110"
    />
  {/if}
</button>
