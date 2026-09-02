<script lang="ts">
  import * as HoverCard from '@slink/ui/components/hover-card';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { setPolicyInfoContext } from './PolicyInfo.context';

  interface Props {
    title: string;
    value: string;
    note?: Snippet;
    children: Snippet;
  }

  let { title, value, note, children }: Props = $props();

  const optionsLabel = $derived(`${title} options`);

  setPolicyInfoContext({
    get value() {
      return value;
    },
  });
</script>

<HoverCard.Root openDelay={200} closeDelay={150}>
  <HoverCard.Trigger>
    {#snippet child({ props })}
      <button
        {...props}
        type="button"
        aria-label={optionsLabel}
        class="inline-flex items-center justify-center w-3.5 h-3.5 rounded text-foreground-subtle hover:text-foreground-soft transition-colors duration-150"
      >
        <Icon icon="lucide:info" class="w-3.5 h-3.5" />
      </button>
    {/snippet}
  </HoverCard.Trigger>

  <HoverCard.Content variant="glass" width="md" rounded="xl" size="sm">
    <div class="space-y-3">
      <h4 class="text-sm font-semibold text-foreground">
        {title}
      </h4>

      <div class="space-y-1">
        {@render children()}
      </div>

      {#if note}
        <div class="pt-2 border-t border-border/60">
          <p class="text-[11px] text-foreground-muted">
            {@render note()}
          </p>
        </div>
      {/if}
    </div>
  </HoverCard.Content>
</HoverCard.Root>
