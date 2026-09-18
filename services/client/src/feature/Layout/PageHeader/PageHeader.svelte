<script lang="ts">
  import { Subtitle, Title } from '@slink/feature/Text';
  import type { Snippet } from 'svelte';

  interface Props {
    size?: 'md' | 'lg';
    title: Snippet;
    subtitle?: Snippet;
    actions?: Snippet;
    children?: Snippet;
  }

  let { size, title, subtitle, actions, children }: Props = $props();
</script>

<header class="mb-8 space-y-6">
  <div
    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
  >
    <div class="min-w-0">
      <Title {size}>{@render title()}</Title>
      {#if subtitle}
        <Subtitle>{@render subtitle()}</Subtitle>
      {/if}
    </div>
    {#if actions}
      <div class="flex items-center gap-3 shrink-0">
        {@render actions()}
      </div>
    {/if}
  </div>
  {@render children?.()}
</header>
