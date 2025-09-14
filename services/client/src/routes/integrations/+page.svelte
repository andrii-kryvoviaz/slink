<script lang="ts">
  import { ApiKeyManager } from '@slink/feature/User';

  import { fade } from 'svelte/transition';
</script>

<svelte:head>
  <title>API Keys | Slink</title>
</svelte:head>

<div class="min-h-full">
  <div
    class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8"
    in:fade={{ duration: 400 }}
  >
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">
        API Keys
      </h1>
      <p class="mt-2 text-slate-600 dark:text-slate-400">
        Manage your API keys for ShareX and other third-party integrations
      </p>
    </div>

    <div class="grid gap-8">
      <div class="space-y-6">
        <div
          class="rounded-2xl bg-white p-6 shadow-sm dark:bg-slate-800/50 border border-slate-200/50 dark:border-slate-700/50"
        >
          <ApiKeyManager />
        </div>
      </div>
    </div>
  </div>
</div>
