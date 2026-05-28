<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { Overlay } from '@slink/ui/components/popover';

  import Icon from '@iconify/svelte';

  import type {
    CollectionLoadStrategy,
    CollectionsState,
  } from '@slink/lib/settings/UserSettings.svelte';

  interface Props {
    value: CollectionsState;
    pageSizeOptions?: number[];
    onChange: (next: CollectionsState) => void;
  }

  let { value, pageSizeOptions = [12, 24, 48, 96], onChange }: Props = $props();

  let open = $state(false);

  const setPageSize = (size: number) => {
    if (size === value.pageSize) return;
    onChange({ ...value, pageSize: size });
  };

  const setLoadStrategy = (strategy: CollectionLoadStrategy) => {
    if (strategy === value.loadStrategy) return;
    onChange({ ...value, loadStrategy: strategy });
  };
</script>

<Overlay
  bind:open
  variant="floating"
  rounded="xl"
  size="none"
  contentProps={{ align: 'end', side: 'bottom', sideOffset: 8 }}
>
  {#snippet trigger()}
    <Button
      variant="glass"
      size="sm"
      rounded="full"
      justify="center"
      title="View preferences"
      aria-label="View preferences"
    >
      {#snippet leftIcon()}
        <span
          class="inline-flex transition-transform duration-200"
          class:rotate-180={open}
        >
          <Icon icon="ph:caret-down" class="h-4 w-4" />
        </span>
      {/snippet}
    </Button>
  {/snippet}

  <div class="w-64 p-3 space-y-4">
    <div class="space-y-2">
      <p
        class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
      >
        Items per page
      </p>
      <div class="grid grid-cols-4 gap-1.5">
        {#each pageSizeOptions as size (size)}
          {#if size === value.pageSize}
            <Button
              variant="primary"
              size="xs"
              rounded="md"
              justify="center"
              onclick={() => setPageSize(size)}
            >
              {size}
            </Button>
          {:else}
            <Button
              variant="glass"
              size="xs"
              rounded="md"
              justify="center"
              onclick={() => setPageSize(size)}
            >
              {size}
            </Button>
          {/if}
        {/each}
      </div>
    </div>

    <div class="space-y-2">
      <p
        class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
      >
        Load strategy
      </p>
      <div class="flex flex-wrap gap-1.5">
        {#if value.loadStrategy === 'load_more'}
          <Button
            variant="primary"
            size="xs"
            rounded="md"
            justify="center"
            class="flex-auto whitespace-nowrap gap-1.5"
            onclick={() => setLoadStrategy('load_more')}
          >
            <Icon icon="ph:plus-circle" class="h-3.5 w-3.5" />
            Load more
          </Button>
        {:else}
          <Button
            variant="glass"
            size="xs"
            rounded="md"
            justify="center"
            class="flex-auto whitespace-nowrap gap-1.5"
            onclick={() => setLoadStrategy('load_more')}
          >
            <Icon icon="ph:plus-circle" class="h-3.5 w-3.5" />
            Load more
          </Button>
        {/if}

        {#if value.loadStrategy === 'infinite_scroll'}
          <Button
            variant="primary"
            size="xs"
            rounded="md"
            justify="center"
            class="flex-auto whitespace-nowrap gap-1.5"
            onclick={() => setLoadStrategy('infinite_scroll')}
          >
            <Icon icon="ph:infinity" class="h-3.5 w-3.5" />
            Infinite
          </Button>
        {:else}
          <Button
            variant="glass"
            size="xs"
            rounded="md"
            justify="center"
            class="flex-auto whitespace-nowrap gap-1.5"
            onclick={() => setLoadStrategy('infinite_scroll')}
          >
            <Icon icon="ph:infinity" class="h-3.5 w-3.5" />
            Infinite
          </Button>
        {/if}
      </div>
    </div>
  </div>
</Overlay>
