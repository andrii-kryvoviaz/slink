<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';

  import { type Readable, readable } from 'svelte/store';

  import { Button } from '@slink/components/UI/Action';
  import { Loader } from '@slink/components/UI/Loader';

  interface Props {
    user: User;
    loading?: Readable<boolean>;
    close: () => void;
    confirm: () => void;
  }

  let { user, loading = readable(false), close, confirm }: Props = $props();
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
  <div class="mt-2 text-sm">
    <span class="block">Are you sure you want to delete this user?</span>
    <span
      class="my-2 block rounded-md bg-neutral-200 p-2 text-center dark:bg-neutral-800"
    >
      {user.email}
    </span>

    <span class="mt-2 block text-[0.7em]"> This action cannot be undone. </span>
  </div>
</div>

<div class="mt-5 flex gap-2">
  <Button variant="outline" size="sm" class="w-1/2" onclick={close}>
    Cancel
  </Button>

  <Button variant="danger" size="sm" class="w-1/2" onclick={confirm}>
    Delete
  </Button>
</div>
