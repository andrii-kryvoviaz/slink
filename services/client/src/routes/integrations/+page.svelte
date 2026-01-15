<script lang="ts">
  import { ApiKeyManager } from '@slink/feature/User';

  import { fade } from 'svelte/transition';
</script>

<svelte:head>
  <title>Integrations | Slink</title>
</svelte:head>

<div
  class="flex flex-col w-full max-w-2xl px-6 py-8"
  in:fade={{ duration: 150 }}
>
  <header class="mb-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
      Integrations
    </h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      Manage your API keys for ShareX and other third-party integrations
    </p>
  </header>

  <div class="space-y-8">
    <section class="space-y-1">
      <div class="flex items-center justify-between gap-4 pb-3">
        <h2
          class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
        >
          API Keys
        </h2>
      </div>

      <div
        class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
      >
        <div class="px-4 py-4">
          <ApiKeyManager />
        </div>
      </div>
    </section>
  </div>
</div>
