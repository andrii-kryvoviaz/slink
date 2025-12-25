<script lang="ts">
  import type { Snippet } from 'svelte';

  import { fade } from 'svelte/transition';

  import SettingsSkeleton from './SettingsSkeleton.svelte';

  interface Props {
    title: string;
    description: string;
    isInitialized: boolean;
    children: Snippet;
  }

  let { title, description, isInitialized, children }: Props = $props();
</script>

<div
  class="flex flex-col w-full max-w-2xl px-6 py-8"
  in:fade={{ duration: 150 }}
>
  <header class="mb-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
      {title}
    </h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      {description}
    </p>
  </header>

  {#if isInitialized}
    <div class="space-y-8">
      {@render children()}
    </div>
  {:else}
    <SettingsSkeleton />
  {/if}
</div>
