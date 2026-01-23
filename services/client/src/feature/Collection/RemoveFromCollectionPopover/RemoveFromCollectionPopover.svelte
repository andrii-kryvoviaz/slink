<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';
  import { type Readable, readable } from 'svelte/store';

  interface Props {
    loading?: Readable<boolean>;
    close: () => void;
    confirm: () => void;
  }

  let { loading = readable(false), close, confirm }: Props = $props();
</script>

<div class="w-full max-w-sm p-2 space-y-4 relative">
  {#if $loading}
    <div class="absolute top-2 right-2 z-10">
      <Loader variant="minimal" size="xs" />
    </div>
  {/if}

  <div class="flex items-center gap-3">
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 border border-red-200/40 dark:border-red-800/30 shadow-sm shrink-0"
    >
      <Icon
        icon="ph:folder-open"
        class="h-5 w-5 text-red-600 dark:text-red-400"
      />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
        Remove from Collection
      </h3>
      <p class="text-xs text-gray-500 dark:text-gray-400">
        Image will remain in your library
      </p>
    </div>
  </div>

  <div class="flex gap-3 pt-2">
    <Button
      variant="glass"
      rounded="full"
      size="sm"
      onclick={close}
      class="flex-1"
      disabled={$loading}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      rounded="full"
      size="sm"
      onclick={confirm}
      class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
      disabled={$loading}
    >
      {#if $loading}
        <Icon icon="eos-icons:three-dots-loading" class="h-4 w-4 mr-2" />
      {:else}
        <Icon icon="heroicons:trash" class="h-4 w-4 mr-2" />
      {/if}
      Remove
    </Button>
  </div>
</div>
