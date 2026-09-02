<script lang="ts">
  import * as HoverCard from '@slink/ui/components/hover-card';
  import type { Snippet } from 'svelte';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';

  import { themes } from '@slink/lib/settings';

  interface Props {
    showPreferencesLink?: boolean;
    children: Snippet;
  }

  let { showPreferencesLink = false, children }: Props = $props();

  const { settings } = page.data;
  const themeLabel = $derived(
    themes.find((t) => t.name === settings.theme.current)?.label ?? '',
  );
</script>

<HoverCard.Root openDelay={1000} closeDelay={200}>
  <HoverCard.Trigger>
    <div class="flex items-center">
      {@render children()}
    </div>
  </HoverCard.Trigger>
  <HoverCard.Content
    variant="glass"
    size="sm"
    rounded="xl"
    width="auto"
    side="bottom"
    sideOffset={8}
    align="end"
    class="min-w-52"
  >
    <div class="flex flex-col gap-2">
      <div class="flex items-center gap-2">
        <Icon icon="ph:palette" class="h-4 w-4 text-info" />
        <span class="text-sm font-semibold">Appearance</span>
      </div>
      <p class="text-xs text-foreground-muted leading-relaxed">
        Toggle light and dark mode.
      </p>
      {#if showPreferencesLink}
        <div
          class="flex items-center justify-between gap-4 border-t border-muted pt-2 mt-0.5"
        >
          <span class="text-xs text-foreground-muted">
            Theme: {themeLabel}
          </span>
          <a
            href="/preferences#appearance"
            class="inline-flex items-center gap-1 text-xs text-info hover:underline"
          >
            <span>Customize</span>
            <Icon icon="ph:arrow-up-right" class="h-3.5 w-3.5" />
          </a>
        </div>
      {/if}
    </div>
  </HoverCard.Content>
</HoverCard.Root>
