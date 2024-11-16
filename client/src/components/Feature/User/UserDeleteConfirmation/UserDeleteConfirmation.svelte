<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';

  import { type Readable, readable } from 'svelte/store';

  import { Button } from '@slink/components/UI/Action';
  import { Loader } from '@slink/components/UI/Loader';
  import { Badge } from '@slink/components/UI/Text';

  export let user: User;
  export let loading: Readable<boolean> = readable(false);
  export let close: () => void;
  export let confirm: () => void;
</script>

<div class="text-left">
  <h3
    class="flex items-center justify-between text-lg font-medium capitalize leading-6 text-gray-800 dark:text-white"
  >
    <span>User Deletion</span>
    {#if $loading}
      <Loader size="xs" />
    {/if}
  </h3>
  <p class="mt-2 text-sm">
    <span class="block">Are you sure you want to delete:</span>
    <Badge class="inline">{user.email}</Badge>

    <span class="mt-2 block text-[0.7em]"> This action cannot be undone. </span>
  </p>
</div>

<div class="mt-5 flex gap-2">
  <Button variant="outline" size="sm" class="w-1/2" on:click={close}>
    Cancel
  </Button>

  <Button variant="danger" size="sm" class="w-1/2" on:click={confirm}>
    Delete
  </Button>
</div>
