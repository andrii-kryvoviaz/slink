<script lang="ts">
  import Icon from '@iconify/svelte';
  import { type Readable, readable } from 'svelte/store';

  import { Button } from '@slink/components/UI/Action';
  import { Toggle } from '@slink/components/UI/Form';
  import { Loader } from '@slink/components/UI/Loader';
  import { Tooltip } from '@slink/components/UI/Tooltip';

  type ConfirmAction = {
    preserveOnDiskAfterDeletion: boolean;
  };

  interface Props {
    image: { id: string };
    loading?: Readable<boolean>;
    close: () => void;
    confirm: ({ preserveOnDiskAfterDeletion }: ConfirmAction) => void;
  }

  let { image, loading = readable(false), close, confirm }: Props = $props();

  let preserveOnDiskAfterDeletion: boolean = $state(false);
</script>

<div class="text-left">
  <h3
    class="flex items-center justify-between text-lg font-medium capitalize leading-6 text-gray-800 dark:text-white"
  >
    <span>Image Deletion</span>
    {#if $loading}
      <Loader size="xs" />
    {/if}
  </h3>
  <div class="mt-2 text-sm">
    <span class="block">Are you sure you want to delete this image?</span>
    <p>
      <span>ID:</span>
      <a href={`/info/${image.id}`}>
        <span class="underline">{image.id}</span>
      </a>
    </p>

    <span class="mt-2 block text-[0.7em]"> This action cannot be undone. </span>
  </div>
</div>

<div class="mt-4 flex items-center gap-2 sm:flex-grow">
  <Toggle
    size="md"
    checked={!preserveOnDiskAfterDeletion}
    on={{ change: (checked) => (preserveOnDiskAfterDeletion = !checked) }}
  />

  <div class="flex flex-grow items-center justify-between">
    <span class="text-[0.5em]">
      {#if preserveOnDiskAfterDeletion}
        Preserve in Storage
      {:else}
        Remove from Storage
      {/if}
    </span>
    <Tooltip size="xs" side="top" align="end" width="lg">
      {#snippet trigger()}
        <Icon icon="ep:info-filled" class="hidden cursor-help xs:block" />
      {/snippet}

      Deletes the file from storage, not just the database
    </Tooltip>
  </div>
</div>

<div class="mt-5 flex gap-2">
  <Button variant="outline" size="sm" class="w-1/2" onclick={close}>
    Cancel
  </Button>

  <Button
    variant="danger"
    size="sm"
    class="w-1/2"
    onclick={() => confirm({ preserveOnDiskAfterDeletion })}
  >
    Delete
  </Button>
</div>
