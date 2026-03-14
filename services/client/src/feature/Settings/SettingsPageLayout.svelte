<script lang="ts">
  import { Subtitle, Title } from '@slink/feature/Text';
  import type { Snippet } from 'svelte';

  import { fade } from 'svelte/transition';

  import SettingsSkeleton from './SettingsSkeleton.svelte';

  interface Props {
    title: string;
    description: string;
    isInitialized: boolean;
    navigation?: Snippet;
    actions?: Snippet;
    children: Snippet;
  }

  let {
    title,
    description,
    isInitialized,
    navigation,
    actions,
    children,
  }: Props = $props();
</script>

<div
  class="flex flex-col w-full max-w-2xl px-6 py-8"
  in:fade={{ duration: 150 }}
>
  {#if navigation}
    {@render navigation()}
  {/if}

  <header class="mb-8">
    <div class="flex items-center justify-between">
      <div>
        <Title size="sm">{title}</Title>
        <Subtitle>{description}</Subtitle>
      </div>
      {#if actions}
        {@render actions()}
      {/if}
    </div>
  </header>

  {#if isInitialized}
    <div class="space-y-8">
      {@render children()}
    </div>
  {:else}
    <SettingsSkeleton />
  {/if}
</div>
